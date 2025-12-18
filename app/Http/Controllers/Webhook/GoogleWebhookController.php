<?php
// app/Http/Controllers/Webhook/GoogleWebhookController.php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\Setting;
use App\Models\User;
use App\Services\LeadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GoogleWebhookController extends Controller
{
    protected LeadService $leadService;

    public function __construct(LeadService $leadService)
    {
        $this->leadService = $leadService;
    }

    /**
     * Handle Google Ads lead form webhook
     */
    public function handle(Request $request)
    {
        $payload = $request->all();

        Log::info('Google Ads Webhook received', ['payload' => $payload]);

        // Verify webhook secret
        $secret = $request->header('X-Webhook-Secret');
        $expectedSecret = Setting::get('google_webhook_secret');

        if ($expectedSecret && $secret !== $expectedSecret) {
            Log::warning('Google Webhook invalid secret');
            return response('Unauthorized', 401);
        }

        // Verify webhook is enabled
        if (!Setting::get('enable_lead_webhook', true)) {
            return response('OK', 200);
        }

        try {
            $this->processGoogleLead($payload);
            return response('OK', 200);
        } catch (\Exception $e) {
            Log::error('Google Webhook processing error', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);

            return response('OK', 200);
        }
    }

    /**
     * Process Google Ads lead
     */
    protected function processGoogleLead(array $payload): void
    {
        $leadId = $payload['lead_id'] ?? $payload['google_lead_id'] ?? uniqid('google_');
        
        // Check if lead already exists
        if (Lead::where('lead_id', $leadId)->exists()) {
            Log::info('Google lead already exists', ['lead_id' => $leadId]);
            return;
        }

        // Find client
        $client = $this->findClient($payload);

        if (!$client) {
            Log::warning('No client found for Google lead', $payload);
            return;
        }

        // Extract data based on Google's lead form structure
        $leadData = [
            'leadgen_id' => $leadId,
            'name' => $payload['user_column_data']['FULL_NAME'] 
                ?? $payload['full_name'] 
                ?? $payload['name'] 
                ?? 'Unknown',
            'email' => $payload['user_column_data']['EMAIL'] 
                ?? $payload['email'] 
                ?? null,
            'phone_number' => $payload['user_column_data']['PHONE_NUMBER'] 
                ?? $payload['phone'] 
                ?? null,
            'campaign_name' => $payload['campaign_name'] 
                ?? $payload['campaign'] 
                ?? null,
            'ad_name' => $payload['ad_group_name'] 
                ?? $payload['ad_name'] 
                ?? null,
            'form_name' => $payload['form_name'] 
                ?? $payload['asset_name'] 
                ?? null,
            'city' => $payload['user_column_data']['CITY'] 
                ?? $payload['city'] 
                ?? null,
            'state' => $payload['user_column_data']['REGION'] 
                ?? $payload['state'] 
                ?? null,
            'custom_fields' => $payload['user_column_data'] ?? [],
        ];

        $lead = $this->leadService->createFromWebhook($client, $leadData, 'google');

        Log::info('Lead created from Google webhook', [
            'lead_id' => $lead->id,
            'google_lead_id' => $leadId,
            'client_id' => $client->id,
        ]);
    }

    /**
     * Find client for Google lead
     */
    protected function findClient(array $payload): ?User
    {
        // Try to find by customer ID
        $customerId = $payload['customer_id'] ?? $payload['google_customer_id'] ?? null;

        if ($customerId) {
            $client = User::clients()
                ->active()
                ->whereHas('subscriptions', fn($q) => $q->active())
                ->whereJsonContains('onboarding_data->google_customer_id', $customerId)
                ->first();

            if ($client) {
                return $client;
            }
        }

        // Try account mapping
        $accountMapping = Setting::get('google_account_mapping', []);

        if (is_string($accountMapping)) {
            $accountMapping = json_decode($accountMapping, true) ?? [];
        }

        if ($customerId && isset($accountMapping[$customerId])) {
            return User::find($accountMapping[$customerId]);
        }

        // Fallback: return first active client (remove in production)
        return User::clients()
            ->active()
            ->whereHas('subscriptions', fn($q) => $q->active())
            ->first();
    }

    /**
     * Test webhook endpoint
     */
    public function test(Request $request)
    {
        return response()->json([
            'status' => 'ok',
            'message' => 'Google Ads webhook endpoint is working',
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}