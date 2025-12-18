<?php
// database/migrations/2024_01_01_000011_create_creative_comments_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('creative_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creative_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('parent_id')->nullable(); // For replies
            $table->text('comment');
            $table->json('position')->nullable(); // {x: 100, y: 200} for pinned comments
            $table->boolean('is_resolved')->default(false);
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('parent_id')->references('id')->on('creative_comments')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('creative_comments');
    }
};