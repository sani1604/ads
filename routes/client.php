<?php
// routes/client.php

use App\Http\Controllers\Client\DashboardController;
use App\Http\Controllers\Client\SubscriptionController;
use App\Http\Controllers\Client\WalletController;
use App\Http\Controllers\Client\CreativeController;
use App\Http\Controllers\Client\LeadController;
use App\Http\Controllers\Client\InvoiceController;
use App\Http\Controllers\Client\ReportController;
use App\Http\Controllers\Client\NotificationController;
use App\Http\Controllers\Client\SupportController;
use App\Http\Controllers\Client\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| Subscription Management
|--------------------------------------------------------------------------
*/

Route::prefix('subscription')->name('subscription.')->group(function () {
    Route::get('/', [SubscriptionController::class, 'index'])->name('index');
    Route::get('/plans', [SubscriptionController::class, 'plans'])->name('plans');
    Route::get('/checkout/{package}', [SubscriptionController::class, 'checkout'])->name('checkout');
    Route::post('/create-order', [SubscriptionController::class, 'createOrder'])->name('create-order');
    Route::post('/verify-payment', [SubscriptionController::class, 'verifyPayment'])->name('verify-payment');
    Route::get('/success/{subscription}', [SubscriptionController::class, 'success'])->name('success');
    Route::post('/{subscription}/cancel', [SubscriptionController::class, 'cancel'])->name('cancel');
});

/*
|--------------------------------------------------------------------------
| Wallet Management
|--------------------------------------------------------------------------
*/

Route::prefix('wallet')->name('wallet.')->group(function () {
    Route::get('/', [WalletController::class, 'index'])->name('index');
    Route::get('/recharge', [WalletController::class, 'recharge'])->name('recharge');
    Route::post('/create-recharge-order', [WalletController::class, 'createRechargeOrder'])->name('create-recharge-order');
    Route::post('/verify-recharge', [WalletController::class, 'verifyRecharge'])->name('verify-recharge');
});

/*
|--------------------------------------------------------------------------
| Creatives Management
|--------------------------------------------------------------------------
*/

Route::prefix('creatives')->name('creatives.')->group(function () {
    Route::get('/', [CreativeController::class, 'index'])->name('index');
    Route::get('/create', [CreativeController::class, 'create'])->name('create');
    Route::post('/', [CreativeController::class, 'store'])->name('store');
    Route::get('/{creative}', [CreativeController::class, 'show'])->name('show');
    Route::post('/{creative}/approve', [CreativeController::class, 'approve'])->name('approve');
    Route::post('/{creative}/request-changes', [CreativeController::class, 'requestChanges'])->name('request-changes');
    Route::post('/{creative}/comment', [CreativeController::class, 'addComment'])->name('add-comment');
    Route::post('/comments/{comment}/resolve', [CreativeController::class, 'resolveComment'])->name('resolve-comment');
    Route::get('/{creative}/download', [CreativeController::class, 'download'])->name('download');
});

/*
|--------------------------------------------------------------------------
| Leads Management
|--------------------------------------------------------------------------
*/

Route::prefix('leads')->name('leads.')->group(function () {
    Route::get('/', [LeadController::class, 'index'])->name('index');
    Route::get('/create', [LeadController::class, 'create'])->name('create');
    Route::post('/', [LeadController::class, 'store'])->name('store');
    Route::get('/export', [LeadController::class, 'export'])->name('export');
    Route::get('/{lead}', [LeadController::class, 'show'])->name('show');
    Route::patch('/{lead}/status', [LeadController::class, 'updateStatus'])->name('update-status');
    Route::patch('/{lead}/quality', [LeadController::class, 'updateQuality'])->name('update-quality');
    Route::post('/{lead}/note', [LeadController::class, 'addNote'])->name('add-note');
});

/*
|--------------------------------------------------------------------------
| Invoices
|--------------------------------------------------------------------------
*/

Route::prefix('invoices')->name('invoices.')->group(function () {
    Route::get('/', [InvoiceController::class, 'index'])->name('index');
    Route::get('/{invoice}', [InvoiceController::class, 'show'])->name('show');
    Route::get('/{invoice}/download', [InvoiceController::class, 'download'])->name('download');
    Route::get('/{invoice}/view', [InvoiceController::class, 'view'])->name('view');
});

/*
|--------------------------------------------------------------------------
| Reports & Analytics
|--------------------------------------------------------------------------
*/

Route::prefix('reports')->name('reports.')->middleware('subscription')->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('index');
    Route::get('/chart-data', [ReportController::class, 'chartData'])->name('chart-data');
    Route::get('/export', [ReportController::class, 'export'])->name('export');
});

/*
|--------------------------------------------------------------------------
| Notifications
|--------------------------------------------------------------------------
*/

Route::prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::post('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('mark-read');
    Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
    Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
    Route::get('/recent', [NotificationController::class, 'recent'])->name('recent');
});

/*
|--------------------------------------------------------------------------
| Support Tickets
|--------------------------------------------------------------------------
*/

Route::prefix('support')->name('support.')->group(function () {
    Route::get('/', [SupportController::class, 'index'])->name('index');
    Route::get('/create', [SupportController::class, 'create'])->name('create');
    Route::post('/', [SupportController::class, 'store'])->name('store');
    Route::get('/{ticket}', [SupportController::class, 'show'])->name('show');
    Route::post('/{ticket}/reply', [SupportController::class, 'reply'])->name('reply');
    Route::post('/{ticket}/close', [SupportController::class, 'close'])->name('close');
    Route::post('/{ticket}/reopen', [SupportController::class, 'reopen'])->name('reopen');
});

/*
|--------------------------------------------------------------------------
| Profile & Settings
|--------------------------------------------------------------------------
*/

Route::prefix('profile')->name('profile.')->group(function () {
    Route::get('/', [ProfileController::class, 'edit'])->name('edit');
    Route::put('/', [ProfileController::class, 'update'])->name('update');
    Route::post('/avatar', [ProfileController::class, 'updateAvatar'])->name('update-avatar');
    Route::put('/password', [ProfileController::class, 'changePassword'])->name('change-password');
    Route::get('/activity', [ProfileController::class, 'activity'])->name('activity');
});

/*
|--------------------------------------------------------------------------
| Transactions (View Only)
|--------------------------------------------------------------------------
*/

Route::get('/transactions', function () {
    $transactions = auth()->user()->transactions()
        ->with('subscription.package')
        ->latest()
        ->paginate(20);
    return view('client.transactions.index', compact('transactions'));
})->name('transactions.index');