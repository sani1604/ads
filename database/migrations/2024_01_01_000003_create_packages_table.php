<?php
// database/migrations/2024_01_01_000003_create_packages_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_category_id')->constrained()->onDelete('cascade');
            $table->foreignId('industry_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name'); // Starter, Growth, Enterprise
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('original_price', 10, 2)->nullable(); // For showing discount
            $table->enum('billing_cycle', ['monthly', 'quarterly', 'yearly'])->default('monthly');
            $table->integer('billing_cycle_days')->default(30);
            $table->json('features')->nullable(); // JSON array of features
            $table->json('deliverables')->nullable(); // What client will get
            $table->integer('max_creatives_per_month')->default(10);
            $table->integer('max_revisions')->default(3);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};