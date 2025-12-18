<?php
// database/seeders/PackageSeeder.php

namespace Database\Seeders;

use App\Models\Package;
use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        $metaAds = ServiceCategory::where('slug', 'meta-ads')->first();
        
        // Meta Ads Packages
        Package::create([
            'service_category_id' => $metaAds->id,
            'name' => 'Starter',
            'slug' => 'meta-ads-starter',
            'description' => 'Perfect for small businesses starting with Meta advertising',
            'short_description' => 'Best for beginners',
            'price' => 15000.00,
            'original_price' => 20000.00,
            'billing_cycle' => 'monthly',
            'billing_cycle_days' => 30,
            'features' => json_encode([
                '2 Ad Campaigns',
                'Up to 4 Ad Sets',
                '8 Creative Designs/Month',
                'Weekly Performance Reports',
                'Basic Audience Targeting',
                'WhatsApp Support',
            ]),
            'deliverables' => json_encode([
                'Campaign Setup & Management',
                'Ad Creative Design',
                'A/B Testing',
                'Monthly Strategy Call',
            ]),
            'max_creatives_per_month' => 8,
            'max_revisions' => 2,
            'is_featured' => false,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Package::create([
            'service_category_id' => $metaAds->id,
            'name' => 'Growth',
            'slug' => 'meta-ads-growth',
            'description' => 'Ideal for growing businesses looking to scale their reach',
            'short_description' => 'Most Popular',
            'price' => 30000.00,
            'original_price' => 40000.00,
            'billing_cycle' => 'monthly',
            'billing_cycle_days' => 30,
            'features' => json_encode([
                '5 Ad Campaigns',
                'Up to 10 Ad Sets',
                '15 Creative Designs/Month',
                'Bi-Weekly Performance Reports',
                'Advanced Audience Targeting',
                'Retargeting Campaigns',
                'Dedicated Account Manager',
                'Priority WhatsApp Support',
            ]),
            'deliverables' => json_encode([
                'Everything in Starter',
                'Competitor Analysis',
                'Landing Page Recommendations',
                'Bi-Weekly Strategy Calls',
                'Custom Audience Creation',
            ]),
            'max_creatives_per_month' => 15,
            'max_revisions' => 3,
            'is_featured' => true,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        Package::create([
            'service_category_id' => $metaAds->id,
            'name' => 'Enterprise',
            'slug' => 'meta-ads-enterprise',
            'description' => 'Complete solution for established businesses with high ad spend',
            'short_description' => 'Maximum Results',
            'price' => 60000.00,
            'original_price' => 75000.00,
            'billing_cycle' => 'monthly',
            'billing_cycle_days' => 30,
            'features' => json_encode([
                'Unlimited Ad Campaigns',
                'Unlimited Ad Sets',
                '30 Creative Designs/Month',
                'Daily Performance Monitoring',
                'Advanced Retargeting & Lookalike',
                'Conversion API Setup',
                'Dedicated Senior Account Manager',
                '24/7 Priority Support',
                'Custom Reporting Dashboard',
            ]),
            'deliverables' => json_encode([
                'Everything in Growth',
                'Full Funnel Strategy',
                'CRM Integration',
                'Weekly Strategy Calls',
                'Video Ad Creation',
                'Influencer Collaboration Support',
            ]),
            'max_creatives_per_month' => 30,
            'max_revisions' => 5,
            'is_featured' => false,
            'is_active' => true,
            'sort_order' => 3,
        ]);

        // Add more packages for other services...
    }
}