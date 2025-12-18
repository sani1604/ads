<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\Setting;
use App\Models\User;
use App\Models\WebhookLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CustomWebhookController extends Controller
{
    /**
     * Handle incoming custom webhook
     * 
     * URL: POST /webhooks/custom/{client_token}
     * OR: POST /webhooks/custom?api_key=xxx
     */
    public function handle(Request $request, ?string $clientToken = null)
    {
        $payload = $request->all();

        // Authenticate the request
        $client = $this->authenticateRequest($request, $clientToken);

        // Log the webhook
        $log = WebhookLog::create([
            'source' => 'custom',
            'event_type' => $payload['event'] ?? 'lead',
            'payload' => $payload,
            'ip_address' => $request->ip(),
            'status' => 'received',
        ]);

        if (!$client) {
            $log->update([
                'status' => 'failed',
                'error_message' => 'Authentication failed',
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Unauthorized. Invalid API key or token.',
            ], 401);
        }

        Log::info('Custom webhook received', [
            'client_id' => $client->id,
            'payload' => $payload,
        ]);

        try {
            $lead = $this->processLead($payload, $client, $log);

            $log->update(['status' => 'processed', 'processed_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => 'Lead received successfully',
                'data' => [
                    'lead_id' => $lead->id,
                    'status' => $lead->status,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Custom webhook error', [
                'error' => $e->getMessage(),
                'client_id' => $client->id,
            ]);

            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Authenticate the webhook request
     */
    protected function authenticateRequest(Request $request, ?string $clientToken): ?User
    {
        // Method 1: Token in URL path - /webhooks/custom/{token}
        if ($clientToken) {
            return User::where('webhook_token', $clientToken)
                ->where('role', 'client')
                ->where('is_active', true)
                ->first();
        }

        // Method 2: API key in header - X-API-Key: xxx
        $apiKey = $request->header('X-API-Key');
        if ($apiKey) {
            return User::where('api_key', $apiKey)
                ->where('role', 'client')
                ->where('is_active', true)
                ->first();
        }

        // Method 3: API key in query - ?api_key=xxx
        $apiKey = $request->query('api_key');
        if ($apiKey) {
            return User::where('api_key', $apiKey)
                ->where('role', 'client')
                ->where('is_active', true)
                ->first();
        }

        // Method 4: API key in body
        $apiKey = $request->input('api_key');
        if ($apiKey) {
            return User::where('api_key', $apiKey)
                ->where('role', 'client')
                ->where('is_active', true)
                ->first();
        }

        // Method 5: Global webhook secret + client_id
        $secret = $request->header('X-Webhook-Secret') ?? $request->input('webhook_secret');
        $globalSecret = Setting::get('custom_webhook_secret');
        
        if ($secret && $secret === $globalSecret) {
            $clientId = $request->input('client_id') ?? $request->header('X-Client-ID');
            if ($clientId) {
                return User::where('id', $clientId)
                    ->where('role', 'client')
                    ->where('is_active', true)
                    ->first();
            }
        }

        return null;
    }

    /**
     * Process and create lead
     */
    protected function processLead(array $payload, User $client, WebhookLog $log): Lead
    {
        // Extract lead data from various possible formats
        $leadData = $this->extractLeadData($payload);

        // Generate external ID if not provided
        $externalId = $leadData['external_id'] ?? ('custom_' . Str::random(16));

        // Check for duplicate
        $existingLead = Lead::where('external_id', $externalId)
            ->where('source', 'custom')
            ->first();

        if ($existingLead) {
            // Update existing lead instead of creating duplicate
            $existingLead->update([
                'name' => $leadData['name'] ?? $existingLead->name,
                'email' => $leadData['email'] ?? $existingLead->email,
                'phone' => $leadData['phone'] ?? $existingLead->phone,
                'raw_data' => array_merge($existingLead->raw_data ?? [], $payload),
            ]);

            Log::info('Existing lead updated', ['lead_id' => $existingLead->id]);
            return $existingLead;
        }

        // Create new lead
        $lead = Lead::create([
            'user_id' => $client->id,
            'source' => $payload['source'] ?? 'custom',
            'external_id' => $externalId,
            'name' => $leadData['name'],
            'email' => $leadData['email'],
            'phone' => $leadData['phone'],
            'company' => $leadData['company'],
            'platform' => $payload['platform'] ?? 'custom',
            'campaign_name' => $payload['campaign'] ?? $payload['campaign_name'] ?? null,
            'ad_name' => $payload['ad'] ?? $payload['ad_name'] ?? null,
            'form_name' => $payload['form'] ?? $payload['form_name'] ?? null,
            'status' => 'new',
            'raw_data' => $payload,
            'field_data' => $leadData['custom_fields'],
            'received_at' => now(),
            'metadata' => [
                'webhook_log_id' => $log->id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ],
        ]);

        Log::info('New lead created via custom webhook', [
            'lead_id' => $lead->id,
            'client_id' => $client->id,
        ]);

        // Trigger notification (optional)
        // event(new NewLeadReceived($lead));

        return $lead;
    }

    /**
     * Extract lead data from various payload formats
     */
    protected function extractLeadData(array $payload): array
    {
        $data = [
            'external_id' => null,
            'name' => null,
            'email' => null,
            'phone' => null,
            'company' => null,
            'custom_fields' => [],
        ];

        // Field mappings - check multiple possible field names
        $fieldMappings = [
            'external_id' => ['external_id', 'id', 'lead_id', 'reference_id', 'ref'],
            'name' => ['name', 'full_name', 'fullname', 'contact_name', 'customer_name', 'lead_name', 'first_name', 'firstname'],
            'email' => ['email', 'email_address', 'mail', 'contact_email', 'customer_email', 'e-mail'],
            'phone' => ['phone', 'phone_number', 'mobile', 'mobile_number', 'contact_phone', 'telephone', 'cell', 'whatsapp'],
            'company' => ['company', 'company_name', 'business', 'business_name', 'organization', 'org'],
        ];

        // Extract from root level
        foreach ($fieldMappings as $target => $possibleKeys) {
            foreach ($possibleKeys as $key) {
                // Check exact match
                if (!empty($payload[$key])) {
                    $data[$target] = $payload[$key];
                    break;
                }
                // Check case-insensitive
                $lowerPayload = array_change_key_case($payload, CASE_LOWER);
                if (!empty($lowerPayload[strtolower($key)])) {
                    $data[$target] = $lowerPayload[strtolower($key)];
                    break;
                }
            }
        }

        // Check nested 'lead' object
        if (isset($payload['lead']) && is_array($payload['lead'])) {
            foreach ($fieldMappings as $target => $possibleKeys) {
                if ($data[$target]) continue; // Already found
                foreach ($possibleKeys as $key) {
                    if (!empty($payload['lead'][$key])) {
                        $data[$target] = $payload['lead'][$key];
                        break;
                    }
                }
            }
        }

        // Check nested 'data' object
        if (isset($payload['data']) && is_array($payload['data'])) {
            foreach ($fieldMappings as $target => $possibleKeys) {
                if ($data[$target]) continue;
                foreach ($possibleKeys as $key) {
                    if (!empty($payload['data'][$key])) {
                        $data[$target] = $payload['data'][$key];
                        break;
                    }
                }
            }
        }

        // Check nested 'contact' object
        if (isset($payload['contact']) && is_array($payload['contact'])) {
            foreach ($fieldMappings as $target => $possibleKeys) {
                if ($data[$target]) continue;
                foreach ($possibleKeys as $key) {
                    if (!empty($payload['contact'][$key])) {
                        $data[$target] = $payload['contact'][$key];
                        break;
                    }
                }
            }
        }

        // Check 'fields' array format (like Typeform, JotForm)
        if (isset($payload['fields']) && is_array($payload['fields'])) {
            foreach ($payload['fields'] as $field) {
                $fieldName = strtolower($field['name'] ?? $field['label'] ?? $field['key'] ?? '');
                $fieldValue = $field['value'] ?? $field['answer'] ?? null;

                if (!$fieldValue) continue;

                $data['custom_fields'][$fieldName] = $fieldValue;

                // Map to standard fields
                if (!$data['name'] && (str_contains($fieldName, 'name') && !str_contains($fieldName, 'company'))) {
                    $data['name'] = $fieldValue;
                } elseif (!$data['email'] && str_contains($fieldName, 'email')) {
                    $data['email'] = $fieldValue;
                } elseif (!$data['phone'] && (str_contains($fieldName, 'phone') || str_contains($fieldName, 'mobile'))) {
                    $data['phone'] = $fieldValue;
                } elseif (!$data['company'] && str_contains($fieldName, 'company')) {
                    $data['company'] = $fieldValue;
                }
            }
        }

        // Combine first_name + last_name if separate
        if (!$data['name']) {
            $firstName = $payload['first_name'] ?? $payload['firstname'] ?? $payload['fname'] ?? '';
            $lastName = $payload['last_name'] ?? $payload['lastname'] ?? $payload['lname'] ?? '';
            if ($firstName || $lastName) {
                $data['name'] = trim($firstName . ' ' . $lastName);
            }
        }

        // Store all remaining fields as custom_fields
        $standardFields = ['name', 'email', 'phone', 'company', 'id', 'lead_id', 'external_id', 'api_key', 'webhook_secret', 'client_id'];
        foreach ($payload as $key => $value) {
            if (!in_array(strtolower($key), $standardFields) && !is_array($value)) {
                $data['custom_fields'][$key] = $value;
            }
        }

        return $data;
    }

    /**
     * Test endpoint - verify webhook is working
     */
    public function test(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Custom webhook endpoint is working',
            'timestamp' => now()->toIso8601String(),
            'ip' => $request->ip(),
            'method' => $request->method(),
        ]);
    }
}