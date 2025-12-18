<?php
// database/seeders/IndustrySeeder.php

namespace Database\Seeders;

use App\Models\Industry;
use Illuminate\Database\Seeder;

class IndustrySeeder extends Seeder
{
    public function run(): void
    {
        $industries = [
            ['name' => 'Real Estate', 'slug' => 'real-estate', 'icon' => 'building', 'sort_order' => 1],
            ['name' => 'Healthcare', 'slug' => 'healthcare', 'icon' => 'heart-pulse', 'sort_order' => 2],
            ['name' => 'E-commerce', 'slug' => 'e-commerce', 'icon' => 'shopping-cart', 'sort_order' => 3],
            ['name' => 'Education', 'slug' => 'education', 'icon' => 'graduation-cap', 'sort_order' => 4],
            ['name' => 'Finance', 'slug' => 'finance', 'icon' => 'landmark', 'sort_order' => 5],
            ['name' => 'Restaurant & Food', 'slug' => 'restaurant-food', 'icon' => 'utensils', 'sort_order' => 6],
            ['name' => 'Fitness & Gym', 'slug' => 'fitness-gym', 'icon' => 'dumbbell', 'sort_order' => 7],
            ['name' => 'Legal Services', 'slug' => 'legal-services', 'icon' => 'scale-balanced', 'sort_order' => 8],
            ['name' => 'Automotive', 'slug' => 'automotive', 'icon' => 'car', 'sort_order' => 9],
            ['name' => 'Travel & Tourism', 'slug' => 'travel-tourism', 'icon' => 'plane', 'sort_order' => 10],
            ['name' => 'Technology', 'slug' => 'technology', 'icon' => 'microchip', 'sort_order' => 11],
            ['name' => 'Other', 'slug' => 'other', 'icon' => 'briefcase', 'sort_order' => 99],
        ];

        foreach ($industries as $industry) {
            Industry::create($industry);
        }
    }
}