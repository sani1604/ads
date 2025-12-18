<?php
// app/Http/Controllers/Webhook/LeadWebhookController.php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\Setting;
use App\Models\User;
use App\Services\LeadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LeadWebhookController extends Controller
{
    protected LeadService $leadService;

    public function __construct(LeadService $leadService)
    {
        $this->leadService = $leadService;
    }

    /**
     * Handle generic lead webhook (for Zapier, custom integrations)
     */
    public function handle(Request $request, string $clientToken)
    {
        Log::info('Generic Lead Webhook received', [
            'client_token' => $clientToken,
            'payload' => $request->all(),
        ]);

        // Find client by token
        $client = $this->findClientByToken($clientToken);

        if (!$client) {
            Log::warning('Invalid client token for lead webhook', ['token' => $clientToken]);
            return response()->json(['error' => 'Invalid token'], 401);
        }

        // Verify webhook is enabled
        if (!Setting::get('enable_lead_webhook', true)) {
            return response()->json(['status' => 'webhook_disabled'], 200);
        }

        try {
            $lead = $this->processLead($client, $request->all());

            return response()->json([
                'status' => 'success',
                'lead_id' => $lead->lead_id,
                'message' => 'Lead created successfully',
            ], 201);
        } catch (\Exception $e) {
            Log::error('Lead webhook processing error', [
                'error' => $e->getMessage(),
                'client_id' => $client->id,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Find client by webhook token
     */
    protected function findClientByToken(string $token): ?User
    {
        return User::clients()
            ->active()
            ->whereHas('subscriptions', fn($q) => $q->active())
            ->whereJsonContains('onboarding_data->webhook_token', $token)
            ->first();
    }

    /**
     * Process incoming lead
     */
    protected function processLead(User $client, array $data): Lead
    {
        // Determine source
        $source = $data['source'] ?? $data['platform'] ?? 'webhook';
        
        // Map to valid source
        $validSources = ['facebook', 'instagram', 'google', 'linkedin', 'website', 'manual', 'other'];
        if (!in_array($source, $validSources)) {
            $source = 'other';
        }

        // Normalize field names
        $leadData = [
            'name' => $data['name'] 
                ?? $data['full_name'] 
                ?? $data['fullname']
                ?? ($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? '')
                ?? 'Unknown',
            'email' => $data['email'] ?? $data['email_address'] ?? null,
            'phone_number' => $data['phone'] 
                ?? $data['phone_number'] 
                ?? $data['mobile'] 
                ?? $data['contact'] 
                ?? null,
            'campaign_name' => $data['campaign'] ?? $data['campaign_name'] ?? $data['utm_campaign'] ?? null,
            'ad_name' => $data['ad_name'] ?? $data['ad'] ?? null,
            'form_name' => $data['form_name'] ?? $data['form'] ?? null,
            'city' => $data['city'] ?? $data['location'] ?? null,
            'state' => $data['state'] ?? $data['region'] ?? null,
            'custom_fields' => array_diff_key($data, array_flip([
                'name', 'full_name', 'email', 'phone', 'phone_number',
                'campaign', 'campaign_name', 'ad_name', 'form_name',
                'city', 'state', 'source', 'platform'
            ])),
        ];

        return $this->leadService->createFromWebhook($client, $leadData, $source);
    }

    /**
     * Generate webhook token for client
     */
    public function generateToken(User $client): string
    {
        $token = bin2hex(random_bytes(32));

        $onboardingData = $client->onboarding_data ?? [];
        $onboardingData['webhook_token'] = $token;

        $client->update(['onboarding_data' => $onboardingData]);

        return $token;
    }

    /**
     * Get webhook URL for client
     */
    public function getWebhookUrl(User $client): ?string
    {
        $token = $client->onboarding_data['webhook_token'] ?? null;

        if (!$token) {
            return null;
        }

        return route('webhook.lead.generic', ['clientToken' => $token]);
    }

    /**
     * Admin endpoint: Generate token for client
     */
    public function generateTokenForClient(Request $request, User $client)
    {
        $token = $this->generateToken($client);
        $webhookUrl = route('webhook.lead.generic', ['clientToken' => $token]);

        return response()->json([
            'token' => $token,
            'webhook_url' => $webhookUrl,
        ]);
    }
}