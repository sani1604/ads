<?php
// database/migrations/2024_01_01_000006_create_transactions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('subscription_id')->nullable()->constrained()->onDelete('set null');
            $table->string('transaction_id')->unique();
            $table->enum('type', ['subscription', 'wallet_recharge', 'ad_spend', 'refund', 'adjustment']);
            $table->enum('payment_method', ['razorpay', 'stripe', 'bank_transfer', 'cash', 'wallet', 'manual']);
            $table->decimal('amount', 12, 2);
            $table->decimal('tax_amount', 10, 2)->default(0.00);
            $table->decimal('total_amount', 12, 2);
            $table->string('currency')->default('INR');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'refunded'])->default('pending');
            $table->string('razorpay_order_id')->nullable();
            $table->string('razorpay_payment_id')->nullable();
            $table->string('razorpay_signature')->nullable();
            $table->text('description')->nullable();
            $table->json('payment_response')->nullable();
            $table->json('meta_data')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};