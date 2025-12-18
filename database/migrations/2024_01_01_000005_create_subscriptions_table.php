<?php
// database/migrations/2024_01_01_000005_create_subscriptions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('package_id')->constrained()->onDelete('cascade');
            $table->string('subscription_code')->unique();
            $table->enum('status', ['pending', 'active', 'paused', 'cancelled', 'expired'])->default('pending');
            $table->decimal('amount', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->decimal('tax_amount', 10, 2)->default(0.00);
            $table->decimal('total_amount', 10, 2);
            $table->date('start_date');
            $table->date('end_date');
            $table->date('next_billing_date');
            $table->integer('billing_cycle_count')->default(0);
            $table->string('razorpay_subscription_id')->nullable();
            $table->string('razorpay_plan_id')->nullable();
            $table->json('meta_data')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};