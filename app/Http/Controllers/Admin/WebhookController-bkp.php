<?php
// app/Http/Controllers/Admin/WebhookController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WebhookController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Show webhook settings
     */
    public function index()
    {
        $settings = [
            'meta_webhook_verify_token' => Setting::get('meta_webhook_verify_token'),
            'meta_access_token' => Setting::get('meta_access_token') ? '********' : null,
            'meta_page_mapping' => Setting::get('meta_page_mapping', []),
            'google_webhook_secret' => Setting::get('google_webhook_secret') ? '********' : null,
            'google_account_mapping' => Setting::get('google_account_mapping', []),
            'enable_lead_webhook' => Setting::get('enable_lead_webhook', true),
        ];

        $webhookUrls = [
            'meta_verify' => route('webhook.meta'),
            'meta_callback' => route('webhook.meta'),
            'google' => route('webhook.google'),
            'razorpay' => route('webhook.razorpay'),
        ];

        // Get clients with webhook tokens
        $clientsWithTokens = User::clients()
            ->whereNotNull('onboarding_data->webhook_token')
            ->get(['id', 'name', 'company_name', 'onboarding_data']);

        return view('admin.webhooks.index', compact('settings', 'webhookUrls', 'clientsWithTokens'));
    }

    /**
     * Update Meta webhook settings
     */
    public function updateMeta(Request $request)
    {
        $request->validate([
            'meta_webhook_verify_token' => 'nullable|string|max:255',
            'meta_access_token' => 'nullable|string|max:500',
            'meta_page_mapping' => 'nullable|array',
            'meta_page_mapping.*.page_id' => 'required_with:meta_page_mapping|string',
            'meta_page_mapping.*.client_id' => 'required_with:meta_page_mapping|exists:users,id',
        ]);

        if ($request->filled('meta_webhook_verify_token')) {
            Setting::set('meta_webhook_verify_token', $request->meta_webhook_verify_token, 'text', 'api');
        }

        if ($request->filled('meta_access_token')) {
            Setting::set('meta_access_token', $request->meta_access_token, 'text', 'api');
        }

        if ($request->has('meta_page_mapping')) {
            $mapping = [];
            foreach ($request->meta_page_mapping ?? [] as $item) {
                if (!empty($item['page_id']) && !empty($item['client_id'])) {
                    $mapping[$item['page_id']] = $item['client_id'];
                }
            }
            Setting::set('meta_page_mapping', json_encode($mapping), 'json', 'api');
        }

        Setting::clearCache();

        return back()->with('success', 'Meta webhook settings updated.');
    }

    /**
     * Update Google webhook settings
     */
    public function updateGoogle(Request $request)
    {
        $request->validate([
            'google_webhook_secret' => 'nullable|string|max:255',
            'google_account_mapping' => 'nullable|array',
            'google_account_mapping.*.customer_id' => 'required_with:google_account_mapping|string',
            'google_account_mapping.*.client_id' => 'required_with:google_account_mapping|exists:users,id',
        ]);

        if ($request->filled('google_webhook_secret')) {
            Setting::set('google_webhook_secret', $request->google_webhook_secret, 'text', 'api');
        }

        if ($request->has('google_account_mapping')) {
            $mapping = [];
            foreach ($request->google_account_mapping ?? [] as $item) {
                if (!empty($item['customer_id']) && !empty($item['client_id'])) {
                    $mapping[$item['customer_id']] = $item['client_id'];
                }
            }
            Setting::set('google_account_mapping', json_encode($mapping), 'json', 'api');
        }

        Setting::clearCache();

        return back()->with('success', 'Google webhook settings updated.');
    }

    /**
     * Toggle webhook status
     */
    public function toggleStatus(Request $request)
    {
        $enabled = !Setting::get('enable_lead_webhook', true);
        Setting::set('enable_lead_webhook', $enabled ? '1' : '0', 'boolean', 'api');

        Setting::clearCache();

        return back()->with('success', 'Webhook ' . ($enabled ? 'enabled' : 'disabled') . '.');
    }

    /**
     * Generate new verify token
     */
    public function regenerateMetaToken()
    {
        $token = Str::random(32);
        Setting::set('meta_webhook_verify_token', $token, 'text', 'api');

        Setting::clearCache();

        return back()->with('success', 'New Meta verify token generated: ' . $token);
    }

    /**
     * Generate new Google webhook secret
     */
    public function regenerateGoogleSecret()
    {
        $secret = Str::random(64);
        Setting::set('google_webhook_secret', $secret, 'text', 'api');

        Setting::clearCache();

        return back()->with('success', 'New Google webhook secret generated.');
    }

    /**
     * Generate webhook token for client
     */
    public function generateClientToken(User $client)
    {
        $token = bin2hex(random_bytes(32));

        $onboardingData = $client->onboarding_data ?? [];
        $onboardingData['webhook_token'] = $token;

        $client->update(['onboarding_data' => $onboardingData]);

        $webhookUrl = route('webhook.lead.generic', ['clientToken' => $token]);

        return back()->with('success', "Webhook URL for {$client->name}: {$webhookUrl}");
    }

    /**
     * Revoke client webhook token
     */
    public function revokeClientToken(User $client)
    {
        $onboardingData = $client->onboarding_data ?? [];
        unset($onboardingData['webhook_token']);

        $client->update(['onboarding_data' => $onboardingData]);

        return back()->with('success', "Webhook token revoked for {$client->name}.");
    }

    /**
     * Test Meta webhook
     */
    public function testMeta()
    {
        $verifyToken = Setting::get('meta_webhook_verify_token');

        if (!$verifyToken) {
            return back()->with('error', 'Meta verify token not configured.');
        }

        return back()->with('success', 'Meta webhook is configured. Verify Token: ' . $verifyToken);
    }

    /**
     * View webhook logs (if you have logging)
     */
    public function logs(Request $request)
    {
        // You can implement a webhook_logs table for this
        // For now, we'll show recent activity logs related to webhooks
        $logs = \App\Models\ActivityLog::where('log_type', 'like', '%webhook%')
            ->orWhere('log_type', 'like', '%lead_received%')
            ->latest()
            ->paginate(50);

        return view('admin.webhooks.logs', compact('logs'));
    }
}