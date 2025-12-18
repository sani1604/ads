<?php
// database/seeders/ServiceCategorySeeder.php

namespace Database\Seeders;

use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class ServiceCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Meta Ads',
                'slug' => 'meta-ads',
                'description' => 'Facebook & Instagram Advertising',
                'icon' => 'fa-brands fa-meta',
                'color' => '#1877F2',
                'sort_order' => 1,
            ],
            [
                'name' => 'Google Ads',
                'slug' => 'google-ads',
                'description' => 'Google Search & Display Advertising',
                'icon' => 'fa-brands fa-google',
                'color' => '#4285F4',
                'sort_order' => 2,
            ],
            [
                'name' => 'Social Media Management',
                'slug' => 'social-media',
                'description' => 'Complete Social Media Handling',
                'icon' => 'fa-solid fa-share-nodes',
                'color' => '#E4405F',
                'sort_order' => 3,
            ],
            [
                'name' => 'SEO Services',
                'slug' => 'seo',
                'description' => 'Search Engine Optimization',
                'icon' => 'fa-solid fa-magnifying-glass-chart',
                'color' => '#34A853',
                'sort_order' => 4,
            ],
            [
                'name' => 'Complete Digital Marketing',
                'slug' => 'complete-digital',
                'description' => 'All-in-One Digital Marketing Solution',
                'icon' => 'fa-solid fa-rocket',
                'color' => '#8B5CF6',
                'sort_order' => 5,
            ],
        ];

        foreach ($categories as $category) {
            ServiceCategory::create($category);
        }
    }
}