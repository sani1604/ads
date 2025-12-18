<?php
// database/migrations/2024_01_01_000013_create_campaign_reports_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('subscription_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('platform', ['facebook', 'instagram', 'google', 'linkedin']);
            $table->string('campaign_id')->nullable();
            $table->string('campaign_name')->nullable();
            $table->date('report_date');
            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedBigInteger('reach')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);
            $table->unsignedBigInteger('link_clicks')->default(0);
            $table->decimal('ctr', 8, 4)->default(0); // Click Through Rate
            $table->decimal('cpc', 10, 2)->default(0); // Cost Per Click
            $table->decimal('cpm', 10, 2)->default(0); // Cost Per 1000 Impressions
            $table->decimal('cpl', 10, 2)->default(0); // Cost Per Lead
            $table->unsignedInteger('leads')->default(0);
            $table->unsignedInteger('conversions')->default(0);
            $table->decimal('spend', 12, 2)->default(0);
            $table->unsignedBigInteger('video_views')->default(0);
            $table->unsignedBigInteger('engagements')->default(0);
            $table->json('additional_metrics')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'platform', 'campaign_id', 'report_date'], 'unique_daily_report');
            $table->index(['user_id', 'report_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_reports');
    }
};