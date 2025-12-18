<?php
// database/migrations/2024_01_01_000004_add_fields_to_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'manager', 'client'])->default('client')->after('email');
            $table->string('phone')->nullable()->after('role');
            $table->string('company_name')->nullable();
            $table->string('company_website')->nullable();
            $table->foreignId('industry_id')->nullable()->constrained()->onDelete('set null');
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->default('India');
            $table->string('postal_code')->nullable();
            $table->string('gst_number')->nullable();
            $table->string('avatar')->nullable();
            $table->decimal('wallet_balance', 12, 2)->default(0.00);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_onboarded')->default(false);
            $table->json('onboarding_data')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role', 'phone', 'company_name', 'company_website',
                'industry_id', 'address', 'city', 'state', 'country',
                'postal_code', 'gst_number', 'avatar', 'wallet_balance',
                'is_active', 'is_onboarded', 'onboarding_data', 'last_login_at'
            ]);
            $table->dropSoftDeletes();
        });
    }
};