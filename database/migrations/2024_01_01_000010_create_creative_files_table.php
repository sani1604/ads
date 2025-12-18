<?php
// database/migrations/2024_01_01_000010_create_creative_files_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('creative_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creative_id')->constrained()->onDelete('cascade');
            $table->string('file_name');
            $table->string('original_name');
            $table->string('file_path');
            $table->string('file_type'); // image/jpeg, video/mp4
            $table->string('mime_type');
            $table->unsignedBigInteger('file_size'); // in bytes
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('creative_files');
    }
};