<?php
// app/Http/Controllers/Webhook/MetaWebhookController.php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\Setting;
use App\Models\User;
use App\Services\LeadService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaWebhookController extends Controller
{
    protected LeadService $leadService;

    public function __construct(LeadService $leadService)
    {
        $this->leadService = $leadService;
    }

    /**
     * Verify webhook subscription (GET request from Meta)
     */
    public function verify(Request $request)
    {
        $mode = $request->get('hub_mode');
        $token = $request->get('hub_verify_token');
        $challenge = $request->get('hub_challenge');

        $verifyToken = Setting::get('meta_webhook_verify_token', config('services.meta.webhook_verify_token'));

        if ($mode === 'subscribe' && $token === $verifyToken) {
            Log::info('Meta Webhook verified successfully');
            return response($challenge, 200);
        }

        Log::warning('Meta Webhook verification failed', [
            'mode' => $mode,
            'token' => $token,
        ]);

        return response('Forbidden', 403);
    }

    /**
     * Handle incoming webhook events (POST request from Meta)
     */
    public function handle(Request $request)
    {
        $payload = $request->all();

        Log::info('Meta Webhook received', ['payload' => $payload]);

        // Verify webhook is enabled
        if (!Setting::get('enable_lead_webhook', true)) {
            Log::info('Lead webhook is disabled');
            return response('OK', 200);
        }

        try {
            // Process leadgen events
            if (isset($payload['object']) && $payload['object'] === 'page') {
                foreach ($payload['entry'] ?? [] as $entry) {
                    foreach ($entry['changes'] ?? [] as $change) {
                        if ($change['field'] === 'leadgen') {
                            $this->processLeadgenEvent($change['value']);
                        }
                    }
                }
            }

            return response('OK', 200);
        } catch (\Exception $e) {
            Log::error('Meta Webhook processing error', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);

            // Return 200 to prevent Meta from retrying
            return response('OK', 200);
        }
    }

    /**
     * Process leadgen event
     */
    protected function processLeadgenEvent(array $data): void
    {
        $leadgenId = $data['leadgen_id'] ?? null;
        $formId = $data['form_id'] ?? null;
        $pageId = $data['page_id'] ?? null;
        $adId = $data['ad_id'] ?? null;
        $adgroupId = $data['adgroup_id'] ?? null;
        $createdTime = $data['created_time'] ?? null;

        if (!$leadgenId) {
            Log::warning('Leadgen event missing leadgen_id', $data);
            return;
        }

        // Check if lead already exists
        if (Lead::where('lead_id', $leadgenId)->exists()) {
            Log::info('Lead already exists', ['leadgen_id' => $leadgenId]);
            return;
        }

        // Fetch lead details from Meta API
        $leadData = $this->fetchLeadData($leadgenId);

        if (!$leadData) {
            Log::error('Failed to fetch lead data from Meta', ['leadgen_id' => $leadgenId]);
            return;
        }

        // Find client by page ID or form mapping
        $client = $this->findClientByPageId($pageId);

        if (!$client) {
            Log::warning('No client found for page', ['page_id' => $pageId]);
            return;
        }

        // Extract field data
        $fieldData = $this->extractFieldData($leadData['field_data'] ?? []);

        // Create lead
        $lead = $this->leadService->createFromWebhook($client, [
            'leadgen_id' => $leadgenId,
            'name' => $fieldData['full_name'] ?? $fieldData['first_name'] ?? 'Unknown',
            'email' => $fieldData['email'] ?? null,
            'phone_number' => $fieldData['phone_number'] ?? $fieldData['phone'] ?? null,
            'campaign_name' => $this->getCampaignName($adgroupId),
            'ad_name' => $this->getAdName($adId),
            'form_name' => $leadData['form_name'] ?? null,
            'created_time' => $createdTime,
            'city' => $fieldData['city'] ?? null,
            'state' => $fieldData['state'] ?? null,
            'custom_fields' => $fieldData,
        ], 'facebook');

        Log::info('Lead created from Meta webhook', [
            'lead_id' => $lead->id,
            'leadgen_id' => $leadgenId,
            'client_id' => $client->id,
        ]);
    }

    /**
     * Fetch lead data from Meta Graph API
     */
    protected function fetchLeadData(string $leadgenId): ?array
    {
        $accessToken = Setting::get('meta_access_token', config('services.meta.access_token'));

        if (!$accessToken) {
            Log::error('Meta access token not configured');
            return null;
        }

        try {
            $response = Http::get("https://graph.facebook.com/v18.0/{$leadgenId}", [
                'access_token' => $accessToken,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Meta API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Meta API request failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Find client by Facebook page ID
     */
    protected function findClientByPageId(string $pageId): ?User
    {
        // Option 1: Store page ID mapping in settings/database
        $pageMapping = Setting::get('meta_page_mapping', []);

        if (is_string($pageMapping)) {
            $pageMapping = json_decode($pageMapping, true) ?? [];
        }

        if (isset($pageMapping[$pageId])) {
            return User::find($pageMapping[$pageId]);
        }

        // Option 2: Store in user's onboarding_data
        $client = User::clients()
            ->active()
            ->whereHas('subscriptions', fn($q) => $q->active())
            ->whereJsonContains('onboarding_data->facebook_page_id', $pageId)
            ->first();

        if ($client) {
            return $client;
        }

        // Option 3: Return first active client with subscription (for testing)
        // Remove this in production
        return User::clients()
            ->active()
            ->whereHas('subscriptions', fn($q) => $q->active())
            ->first();
    }

    /**
     * Extract field data from Meta lead form fields
     */
    protected function extractFieldData(array $fieldData): array
    {
        $extracted = [];

        foreach ($fieldData as $field) {
            $name = strtolower(str_replace(' ', '_', $field['name'] ?? ''));
            $value = $field['values'][0] ?? null;

            if ($value) {
                $extracted[$name] = $value;
            }
        }

        return $extracted;
    }

    /**
     * Get campaign name from adgroup ID
     */
    protected function getCampaignName(string $adgroupId = null): ?string
    {
        if (!$adgroupId) {
            return null;
        }

        $accessToken = Setting::get('meta_access_token');

        if (!$accessToken) {
            return null;
        }

        try {
            $response = Http::get("https://graph.facebook.com/v18.0/{$adgroupId}", [
                'access_token' => $accessToken,
                'fields' => 'campaign{name}',
            ]);

            if ($response->successful()) {
                return $response->json()['campaign']['name'] ?? null;
            }
        } catch (\Exception $e) {
            Log::error('Failed to fetch campaign name', ['error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Get ad name from ad ID
     */
    protected function getAdName(string $adId = null): ?string
    {
        if (!$adId) {
            return null;
        }

        $accessToken = Setting::get('meta_access_token');

        if (!$accessToken) {
            return null;
        }

        try {
            $response = Http::get("https://graph.facebook.com/v18.0/{$adId}", [
                'access_token' => $accessToken,
                'fields' => 'name',
            ]);

            if ($response->successful()) {
                return $response->json()['name'] ?? null;
            }
        } catch (\Exception $e) {
            Log::error('Failed to fetch ad name', ['error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Test webhook endpoint
     */
    public function test(Request $request)
    {
        return response()->json([
            'status' => 'ok',
            'message' => 'Meta webhook endpoint is working',
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}