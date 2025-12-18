<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use App\Models\WebhookLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WebhookController extends Controller
{
    /**
     * Show webhook configuration
     */
    public function index()
    {
        $settings = [
            'meta_webhook_verify_token' => Setting::get('meta_webhook_verify_token', ''),
            'meta_access_token' => Setting::get('meta_access_token', ''),
            'meta_page_mapping' => Setting::get('meta_page_mapping', []),
            'google_webhook_secret' => Setting::get('google_webhook_secret', ''),
            'google_account_mapping' => Setting::get('google_account_mapping', []),
        ];

        $webhookUrls = [
            'meta_callback' => url('/webhooks/meta'),
            'google' => url('/webhooks/google'),
            'razorpay' => url('/webhooks/razorpay'),
        ];

        $clients = User::where('role', 'client')
            ->orderBy('name')
            ->get(['id', 'name', 'company_name', 'email']);

        $recentLogs = WebhookLog::latest()->take(10)->get();

        return view('admin.webhooks.index', compact('settings', 'webhookUrls', 'clients', 'recentLogs'));
    }

    /**
     * Toggle webhook status
     */
    public function toggleStatus()
    {
        $current = Setting::get('enable_lead_webhook', true);
        Setting::set('enable_lead_webhook', !$current, 'boolean', 'webhooks');

        return back()->with('success', 'Webhook status updated.');
    }

    /**
     * Update Meta settings
     */
    public function updateMeta(Request $request)
    {
        $request->validate([
            'meta_webhook_verify_token' => 'nullable|string|max:255',
            'meta_access_token' => 'nullable|string',
        ]);

        if ($request->filled('meta_webhook_verify_token')) {
            Setting::set('meta_webhook_verify_token', $request->meta_webhook_verify_token, 'text', 'webhooks');
        }

        if ($request->filled('meta_access_token')) {
            Setting::set('meta_access_token', $request->meta_access_token, 'text', 'webhooks');
        }

        return back()->with('success', 'Meta settings updated.');
    }

    /**
     * Update Google settings
     */
    public function updateGoogle(Request $request)
    {
        $request->validate([
            'google_webhook_secret' => 'nullable|string|max:255',
        ]);

        if ($request->filled('google_webhook_secret')) {
            Setting::set('google_webhook_secret', $request->google_webhook_secret, 'text', 'webhooks');
        }

        return back()->with('success', 'Google settings updated.');
    }

    /**
     * Add Meta page mapping
     */
    public function addMetaMapping(Request $request)
    {
        $request->validate([
            'page_id' => 'required|string|max:100',
            'page_name' => 'nullable|string|max:255',
            'client_id' => 'required|exists:users,id',
        ]);

        // Get existing mapping
        $mapping = Setting::get('meta_page_mapping', []);
        
        // Ensure it's an array
        if (!is_array($mapping)) {
            $mapping = [];
        }

        // Check for duplicate
        if (isset($mapping[$request->page_id])) {
            return back()->with('error', 'This Page ID is already mapped.');
        }

        // Add new mapping
        $mapping[$request->page_id] = [
            'client_id' => (int) $request->client_id,
            'page_name' => $request->page_name ?? '',
            'created_at' => now()->toDateTimeString(),
        ];

        // Save with json type
        Setting::set('meta_page_mapping', $mapping, 'json', 'webhooks');

        return back()->with('success', 'Meta page mapping added.');
    }

    /**
     * Delete Meta page mapping
     */
    public function deleteMetaMapping(Request $request)
    {
        $request->validate([
            'page_id' => 'required|string',
        ]);

        $mapping = Setting::get('meta_page_mapping', []);
        
        if (!is_array($mapping)) {
            $mapping = [];
        }

        unset($mapping[$request->page_id]);
        
        Setting::set('meta_page_mapping', $mapping, 'json', 'webhooks');

        return back()->with('success', 'Mapping deleted.');
    }

    /**
     * Add Google account mapping
     */
    public function addGoogleMapping(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|string|max:100',
            'account_name' => 'nullable|string|max:255',
            'client_id' => 'required|exists:users,id',
        ]);

        // Normalize customer ID (remove dashes and spaces)
        $customerId = preg_replace('/[\s\-]/', '', $request->customer_id);

        // Get existing mapping
        $mapping = Setting::get('google_account_mapping', []);
        
        if (!is_array($mapping)) {
            $mapping = [];
        }

        // Check for duplicate
        if (isset($mapping[$customerId])) {
            return back()->with('error', 'This Customer ID is already mapped.');
        }

        // Add new mapping
        $mapping[$customerId] = [
            'client_id' => (int) $request->client_id,
            'account_name' => $request->account_name ?? '',
            'created_at' => now()->toDateTimeString(),
        ];

        Setting::set('google_account_mapping', $mapping, 'json', 'webhooks');

        return back()->with('success', 'Google account mapping added.');
    }

    /**
     * Delete Google account mapping
     */
    public function deleteGoogleMapping(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|string',
        ]);

        $mapping = Setting::get('google_account_mapping', []);
        
        if (!is_array($mapping)) {
            $mapping = [];
        }

        unset($mapping[$request->customer_id]);
        
        Setting::set('google_account_mapping', $mapping, 'json', 'webhooks');

        return back()->with('success', 'Mapping deleted.');
    }

    /**
     * Generate random token
     */
    public function generateToken()
    {
        return response()->json([
            'token' => Str::random(32),
        ]);
    }

    /**
     * View webhook logs
     */
    public function logs(Request $request)
    {
        $query = WebhookLog::query();

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $logs = $query->latest()->paginate(50);

        return view('admin.webhooks.logs', compact('logs'));
    }

    /**
     * Clear old logs
     */
    public function clearLogs()
    {
        WebhookLog::where('created_at', '<', now()->subDays(30))->delete();

        return back()->with('success', 'Old logs cleared.');
    }
}