<?php
// routes/webhooks.php

use App\Http\Controllers\Webhook\MetaWebhookController;
use App\Http\Controllers\Webhook\GoogleWebhookController;
use App\Http\Controllers\Webhook\LeadWebhookController;
use App\Http\Controllers\Webhook\RazorpayWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Webhook Routes (No CSRF Protection)
|--------------------------------------------------------------------------
*/

Route::prefix('webhooks')->name('webhook.')->withoutMiddleware(['web'])->middleware(['api'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Meta (Facebook/Instagram) Webhooks
    |--------------------------------------------------------------------------
    */
    
    // Verification endpoint (GET)
    Route::get('/meta', [MetaWebhookController::class, 'verify'])->name('meta');
    
    // Event handler (POST)
    Route::post('/meta', [MetaWebhookController::class, 'handle']);
    
    // Test endpoint
    Route::get('/meta/test', [MetaWebhookController::class, 'test'])->name('meta.test');

    /*
    |--------------------------------------------------------------------------
    | Google Ads Webhooks
    |--------------------------------------------------------------------------
    */
    
    Route::post('/google', [GoogleWebhookController::class, 'handle'])->name('google');
    Route::get('/google/test', [GoogleWebhookController::class, 'test'])->name('google.test');

    /*
    |--------------------------------------------------------------------------
    | Generic Lead Webhook (Zapier, Custom Integrations)
    |--------------------------------------------------------------------------
    */
    
    Route::post('/lead/{clientToken}', [LeadWebhookController::class, 'handle'])->name('lead.generic');

    /*
    |--------------------------------------------------------------------------
    | Razorpay Webhooks
    |--------------------------------------------------------------------------
    */
    
    Route::post('/razorpay', [RazorpayWebhookController::class, 'handle'])->name('razorpay');

});

/*
|--------------------------------------------------------------------------
| Webhook Routes with Web Middleware (for testing via browser)
|--------------------------------------------------------------------------
*/

Route::prefix('webhooks')->name('webhook.')->middleware(['web'])->group(function () {
    
    // Meta verification also needs to work with web middleware for initial setup
    Route::get('/meta/verify', [MetaWebhookController::class, 'verify'])->name('meta.verify');

});