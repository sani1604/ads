<?php
// database/migrations/2024_01_01_000009_create_creatives_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('creatives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('subscription_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('service_category_id')->nullable()->constrained()->onDelete('set null');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['image', 'video', 'carousel', 'story', 'reel', 'document']);
            $table->enum('platform', ['facebook', 'instagram', 'google', 'linkedin', 'twitter', 'youtube', 'all']);
            $table->enum('status', ['draft', 'pending_approval', 'changes_requested', 'approved', 'rejected', 'published']);
            $table->integer('version')->default(1);
            $table->unsignedBigInteger('parent_id')->nullable(); // For versioning
            $table->json('dimensions')->nullable(); // width, height
            $table->text('ad_copy')->nullable();
            $table->string('cta_text')->nullable();
            $table->string('landing_url')->nullable();
            $table->date('scheduled_date')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('parent_id')->references('id')->on('creatives')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('creatives');
    }
};