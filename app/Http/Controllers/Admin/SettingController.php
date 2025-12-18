<?php
// app/Http/Controllers/Admin/SettingController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Show settings page
     */
    public function index()
    {
        $settings = Setting::all()->groupBy('group');

        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Update general settings
     */
    public function updateGeneral(Request $request)
    {
        $request->validate([
            'site_name' => 'required|string|max:255',
            'site_tagline' => 'nullable|string|max:255',
            'contact_email' => 'required|email',
            'contact_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
            'favicon' => 'nullable|image|mimes:png,ico|max:512',
        ]);

        Setting::set('site_name', $request->site_name);
        Setting::set('site_tagline', $request->site_tagline);
        Setting::set('contact_email', $request->contact_email);
        Setting::set('contact_phone', $request->contact_phone);
        Setting::set('address', $request->address);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $oldLogo = Setting::get('logo');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }

            $path = $request->file('logo')->store('settings', 'public');
            Setting::set('logo', $path);
        }

        // Handle favicon upload
        if ($request->hasFile('favicon')) {
            $oldFavicon = Setting::get('favicon');
            if ($oldFavicon && Storage::disk('public')->exists($oldFavicon)) {
                Storage::disk('public')->delete($oldFavicon);
            }

            $path = $request->file('favicon')->store('settings', 'public');
            Setting::set('favicon', $path);
        }

        Setting::clearCache();

        return back()->with('success', 'General settings updated successfully.');
    }

    /**
     * Update payment settings
     */
    public function updatePayment(Request $request)
    {
        $request->validate([
            'currency' => 'required|string|max:10',
            'currency_symbol' => 'required|string|max:5',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'tax_name' => 'required|string|max:50',
            'min_wallet_recharge' => 'required|numeric|min:0',
            'razorpay_key_id' => 'nullable|string|max:255',
            'razorpay_key_secret' => 'nullable|string|max:255',
        ]);

        Setting::set('currency', $request->currency);
        Setting::set('currency_symbol', $request->currency_symbol);
        Setting::set('tax_rate', $request->tax_rate, 'number', 'payment');
        Setting::set('tax_name', $request->tax_name);
        Setting::set('min_wallet_recharge', $request->min_wallet_recharge, 'number', 'payment');

        // Update Razorpay credentials in env (or store in settings)
        if ($request->filled('razorpay_key_id')) {
            Setting::set('razorpay_key_id', $request->razorpay_key_id, 'text', 'payment');
        }

        if ($request->filled('razorpay_key_secret')) {
            Setting::set('razorpay_key_secret', $request->razorpay_key_secret, 'text', 'payment');
        }

        Setting::clearCache();

        return back()->with('success', 'Payment settings updated successfully.');
    }

    /**
     * Update invoice settings
     */
    public function updateInvoice(Request $request)
    {
        $request->validate([
            'invoice_prefix' => 'required|string|max:20',
            'company_name' => 'required|string|max:255',
            'company_gst' => 'nullable|string|max:20',
            'company_pan' => 'nullable|string|max:20',
            'company_address' => 'nullable|string|max:500',
            'invoice_footer' => 'nullable|string|max:1000',
            'invoice_terms' => 'nullable|string|max:2000',
        ]);

        Setting::set('invoice_prefix', $request->invoice_prefix, 'text', 'invoice');
        Setting::set('company_name', $request->company_name, 'text', 'invoice');
        Setting::set('company_gst', $request->company_gst, 'text', 'invoice');
        Setting::set('company_pan', $request->company_pan, 'text', 'invoice');
        Setting::set('company_address', $request->company_address, 'text', 'invoice');
        Setting::set('invoice_footer', $request->invoice_footer, 'text', 'invoice');
        Setting::set('invoice_terms', $request->invoice_terms, 'text', 'invoice');

        Setting::clearCache();

        return back()->with('success', 'Invoice settings updated successfully.');
    }

    /**
     * Update email settings
     */
    public function updateEmail(Request $request)
    {
        $request->validate([
            'mail_from_name' => 'required|string|max:255',
            'mail_from_address' => 'required|email',
            'mail_host' => 'nullable|string|max:255',
            'mail_port' => 'nullable|integer',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:255',
            'mail_encryption' => 'nullable|in:tls,ssl,null',
        ]);

        Setting::set('mail_from_name', $request->mail_from_name, 'text', 'email');
        Setting::set('mail_from_address', $request->mail_from_address, 'text', 'email');
        Setting::set('mail_host', $request->mail_host, 'text', 'email');
        Setting::set('mail_port', $request->mail_port, 'number', 'email');
        Setting::set('mail_username', $request->mail_username, 'text', 'email');

        if ($request->filled('mail_password')) {
            Setting::set('mail_password', $request->mail_password, 'text', 'email');
        }

        Setting::set('mail_encryption', $request->mail_encryption, 'text', 'email');

        Setting::clearCache();

        return back()->with('success', 'Email settings updated successfully.');
    }

    /**
     * Update notification settings
     */
    public function updateNotification(Request $request)
    {
        $request->validate([
            'notify_new_lead' => 'boolean',
            'notify_creative_approval' => 'boolean',
            'notify_payment' => 'boolean',
            'notify_subscription_expiry' => 'boolean',
            'expiry_reminder_days' => 'nullable|integer|min:1|max:30',
            'admin_notification_email' => 'nullable|email',
        ]);

        Setting::set('notify_new_lead', $request->boolean('notify_new_lead') ? '1' : '0', 'boolean', 'notification');
        Setting::set('notify_creative_approval', $request->boolean('notify_creative_approval') ? '1' : '0', 'boolean', 'notification');
        Setting::set('notify_payment', $request->boolean('notify_payment') ? '1' : '0', 'boolean', 'notification');
        Setting::set('notify_subscription_expiry', $request->boolean('notify_subscription_expiry') ? '1' : '0', 'boolean', 'notification');
        Setting::set('expiry_reminder_days', $request->expiry_reminder_days ?? 7, 'number', 'notification');
        Setting::set('admin_notification_email', $request->admin_notification_email, 'text', 'notification');

        Setting::clearCache();

        return back()->with('success', 'Notification settings updated successfully.');
    }

    /**
     * Update social media links
     */
    public function updateSocial(Request $request)
    {
        $request->validate([
            'facebook_url' => 'nullable|url',
            'instagram_url' => 'nullable|url',
            'twitter_url' => 'nullable|url',
            'linkedin_url' => 'nullable|url',
            'youtube_url' => 'nullable|url',
        ]);

        Setting::set('facebook_url', $request->facebook_url, 'text', 'social');
        Setting::set('instagram_url', $request->instagram_url, 'text', 'social');
        Setting::set('twitter_url', $request->twitter_url, 'text', 'social');
        Setting::set('linkedin_url', $request->linkedin_url, 'text', 'social');
        Setting::set('youtube_url', $request->youtube_url, 'text', 'social');

        Setting::clearCache();

        return back()->with('success', 'Social media links updated successfully.');
    }

    /**
     * Update API/Webhook settings
     */
    public function updateApi(Request $request)
    {
        $request->validate([
            'meta_webhook_verify_token' => 'nullable|string|max:255',
            'google_webhook_secret' => 'nullable|string|max:255',
            'enable_lead_webhook' => 'boolean',
        ]);

        Setting::set('meta_webhook_verify_token', $request->meta_webhook_verify_token, 'text', 'api');
        Setting::set('google_webhook_secret', $request->google_webhook_secret, 'text', 'api');
        Setting::set('enable_lead_webhook', $request->boolean('enable_lead_webhook') ? '1' : '0', 'boolean', 'api');

        Setting::clearCache();

        return back()->with('success', 'API settings updated successfully.');
    }

    /**
     * Clear all caches
     */
    public function clearCache()
    {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');

        Setting::clearCache();

        return back()->with('success', 'All caches cleared successfully.');
    }

    /**
     * Test email configuration
     */
    public function testEmail(Request $request)
    {
        $request->validate([
            'test_email' => 'required|email',
        ]);

        try {
            \Mail::raw('This is a test email from your Agency Portal.', function ($message) use ($request) {
                $message->to($request->test_email)
                    ->subject('Test Email - Agency Portal');
            });

            return back()->with('success', 'Test email sent successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send test email: ' . $e->getMessage());
        }
    }

    /**
     * Backup database
     */
    public function backupDatabase()
    {
        try {
            $filename = 'backup_' . now()->format('Y-m-d_His') . '.sql';
            $path = storage_path('app/backups/' . $filename);

            // Ensure backup directory exists
            if (!file_exists(storage_path('app/backups'))) {
                mkdir(storage_path('app/backups'), 0755, true);
            }

            // Simple mysqldump (adjust for your setup)
            $command = sprintf(
                'mysqldump -u%s -p%s %s > %s',
                config('database.connections.mysql.username'),
                config('database.connections.mysql.password'),
                config('database.connections.mysql.database'),
                $path
            );

            exec($command);

            return response()->download($path, $filename)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return back()->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }

    /**
     * Show system info
     */
    public function systemInfo()
    {
        $info = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'database' => config('database.default'),
            'cache_driver' => config('cache.default'),
            'session_driver' => config('session.driver'),
            'queue_driver' => config('queue.default'),
            'timezone' => config('app.timezone'),
            'memory_limit' => ini_get('memory_limit'),
            'max_upload_size' => ini_get('upload_max_filesize'),
            'max_execution_time' => ini_get('max_execution_time') . 's',
            'storage_used' => $this->getDirectorySize(storage_path('app/public')),
        ];

        return view('admin.settings.system-info', compact('info'));
    }

    /**
     * Get directory size helper
     */
    protected function getDirectorySize($path): string
    {
        $size = 0;

        if (is_dir($path)) {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path)) as $file) {
                if ($file->isFile()) {
                    $size += $file->getSize();
                }
            }
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $size > 0 ? floor(log($size, 1024)) : 0;

        return number_format($size / pow(1024, $power), 2) . ' ' . $units[$power];
    }
}