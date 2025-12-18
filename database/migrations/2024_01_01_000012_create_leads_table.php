<?php
// database/migrations/2024_01_01_000012_create_leads_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('subscription_id')->nullable()->constrained()->onDelete('set null');
            $table->string('lead_id')->unique(); // External lead ID from Meta/Google
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('alternate_phone')->nullable();
            $table->enum('source', ['facebook', 'instagram', 'google', 'linkedin', 'website', 'manual', 'other']);
            $table->string('campaign_name')->nullable();
            $table->string('ad_name')->nullable();
            $table->string('adset_name')->nullable();
            $table->string('form_name')->nullable();
            $table->enum('status', ['new', 'contacted', 'qualified', 'converted', 'lost', 'spam'])->default('new');
            $table->enum('quality', ['hot', 'warm', 'cold'])->nullable();
            $table->json('custom_fields')->nullable(); // Additional form fields
            $table->text('notes')->nullable();
            $table->decimal('ad_spend', 10, 2)->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->timestamp('lead_created_at')->nullable(); // When lead was created on platform
            $table->timestamp('contacted_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'source']);
            $table->index(['user_id', 'status']);
            $table->index('lead_created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};