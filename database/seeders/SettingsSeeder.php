<?php
// database/seeders/SettingsSeeder.php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General Settings
            ['group' => 'general', 'key' => 'site_name', 'value' => 'Agency Portal', 'type' => 'text'],
            ['group' => 'general', 'key' => 'site_tagline', 'value' => 'Your Growth Partner', 'type' => 'text'],
            ['group' => 'general', 'key' => 'contact_email', 'value' => 'hello@agencyportal.com', 'type' => 'text'],
            ['group' => 'general', 'key' => 'contact_phone', 'value' => '+91 9999999999', 'type' => 'text'],
            ['group' => 'general', 'key' => 'address', 'value' => 'Mumbai, Maharashtra, India', 'type' => 'text'],
            
            // Payment Settings
            ['group' => 'payment', 'key' => 'currency', 'value' => 'INR', 'type' => 'text'],
            ['group' => 'payment', 'key' => 'currency_symbol', 'value' => '₹', 'type' => 'text'],
            ['group' => 'payment', 'key' => 'tax_rate', 'value' => '18', 'type' => 'number'],
            ['group' => 'payment', 'key' => 'tax_name', 'value' => 'GST', 'type' => 'text'],
            ['group' => 'payment', 'key' => 'min_wallet_recharge', 'value' => '5000', 'type' => 'number'],
            
            // Invoice Settings
            ['group' => 'invoice', 'key' => 'invoice_prefix', 'value' => 'INV-', 'type' => 'text'],
            ['group' => 'invoice', 'key' => 'company_name', 'value' => 'Your Agency Pvt Ltd', 'type' => 'text'],
            ['group' => 'invoice', 'key' => 'company_gst', 'value' => '27XXXXX1234X1ZX', 'type' => 'text'],
            ['group' => 'invoice', 'key' => 'company_pan', 'value' => 'XXXXX1234X', 'type' => 'text'],
            
            // Notification Settings
            ['group' => 'notification', 'key' => 'notify_new_lead', 'value' => '1', 'type' => 'boolean'],
            ['group' => 'notification', 'key' => 'notify_creative_approval', 'value' => '1', 'type' => 'boolean'],
            ['group' => 'notification', 'key' => 'notify_payment', 'value' => '1', 'type' => 'boolean'],
        ];

        foreach ($settings as $setting) {
            Setting::create($setting);
        }
    }
}