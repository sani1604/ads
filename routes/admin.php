<?php
// routes/admin.php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\ServiceCategoryController;
use App\Http\Controllers\Admin\IndustryController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Admin\CreativeController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\CampaignReportController;
use App\Http\Controllers\Admin\SupportTicketController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\WebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard/stats', [DashboardController::class, 'getStats'])->name('dashboard.stats');

/*
|--------------------------------------------------------------------------
| Client Management
|--------------------------------------------------------------------------
*/

Route::prefix('clients')->name('clients.')->group(function () {
    Route::get('/', [ClientController::class, 'index'])->name('index');
    Route::get('/export', [ClientController::class, 'export'])->name('export');
    Route::get('/create', [ClientController::class, 'create'])->name('create');
    Route::post('/', [ClientController::class, 'store'])->name('store');
    Route::get('/{client}', [ClientController::class, 'show'])->name('show');
    Route::get('/{client}/edit', [ClientController::class, 'edit'])->name('edit');
    Route::put('/{client}', [ClientController::class, 'update'])->name('update');
    Route::delete('/{client}', [ClientController::class, 'destroy'])->name('destroy');
    
    // Client actions
    Route::post('/{client}/toggle-status', [ClientController::class, 'toggleStatus'])->name('toggle-status');
    Route::post('/{client}/credit-wallet', [ClientController::class, 'creditWallet'])->name('credit-wallet');
    Route::post('/{client}/debit-wallet', [ClientController::class, 'debitWallet'])->name('debit-wallet');
    Route::get('/{client}/login-as', [ClientController::class, 'loginAs'])->name('login-as')->middleware('role:admin');
});

/*
|--------------------------------------------------------------------------
| Package Management
|--------------------------------------------------------------------------
*/

Route::prefix('packages')->name('packages.')->group(function () {
    Route::get('/', [PackageController::class, 'index'])->name('index');
    Route::get('/create', [PackageController::class, 'create'])->name('create');
    Route::post('/', [PackageController::class, 'store'])->name('store');
    Route::get('/{package}', [PackageController::class, 'show'])->name('show');
    Route::get('/{package}/edit', [PackageController::class, 'edit'])->name('edit');
    Route::put('/{package}', [PackageController::class, 'update'])->name('update');
    Route::delete('/{package}', [PackageController::class, 'destroy'])->name('destroy');
    
    // Package actions
    Route::post('/{package}/toggle-status', [PackageController::class, 'toggleStatus'])->name('toggle-status');
    Route::post('/{package}/toggle-featured', [PackageController::class, 'toggleFeatured'])->name('toggle-featured');
    Route::post('/{package}/duplicate', [PackageController::class, 'duplicate'])->name('duplicate');
    Route::post('/update-order', [PackageController::class, 'updateOrder'])->name('update-order');
});

/*
|--------------------------------------------------------------------------
| Service Categories (Admin Only)
|--------------------------------------------------------------------------
*/

Route::prefix('service-categories')->name('service-categories.')->middleware('role:admin')->group(function () {
    Route::get('/', [ServiceCategoryController::class, 'index'])->name('index');
    Route::get('/create', [ServiceCategoryController::class, 'create'])->name('create');
    Route::post('/', [ServiceCategoryController::class, 'store'])->name('store');
    Route::get('/{serviceCategory}/edit', [ServiceCategoryController::class, 'edit'])->name('edit');
    Route::put('/{serviceCategory}', [ServiceCategoryController::class, 'update'])->name('update');
    Route::delete('/{serviceCategory}', [ServiceCategoryController::class, 'destroy'])->name('destroy');
    Route::post('/{serviceCategory}/toggle-status', [ServiceCategoryController::class, 'toggleStatus'])->name('toggle-status');
});

/*
|--------------------------------------------------------------------------
| Industries (Admin Only)
|--------------------------------------------------------------------------
*/

Route::prefix('industries')->name('industries.')->middleware('role:admin')->group(function () {
    Route::get('/', [IndustryController::class, 'index'])->name('index');
    Route::get('/create', [IndustryController::class, 'create'])->name('create');
    Route::post('/', [IndustryController::class, 'store'])->name('store');
    Route::get('/{industry}/edit', [IndustryController::class, 'edit'])->name('edit');
    Route::put('/{industry}', [IndustryController::class, 'update'])->name('update');
    Route::delete('/{industry}', [IndustryController::class, 'destroy'])->name('destroy');
    Route::post('/{industry}/toggle-status', [IndustryController::class, 'toggleStatus'])->name('toggle-status');
});

/*
|--------------------------------------------------------------------------
| Subscription Management
|--------------------------------------------------------------------------
*/

Route::prefix('subscriptions')->name('subscriptions.')->group(function () {
    Route::get('/', [SubscriptionController::class, 'index'])->name('index');
    Route::get('/export', [SubscriptionController::class, 'export'])->name('export');
    Route::get('/expiring', [SubscriptionController::class, 'getExpiring'])->name('expiring');
    Route::get('/create', [SubscriptionController::class, 'create'])->name('create');
    Route::post('/', [SubscriptionController::class, 'store'])->name('store');
    Route::get('/{subscription}', [SubscriptionController::class, 'show'])->name('show');
    Route::get('/{subscription}/edit', [SubscriptionController::class, 'edit'])->name('edit');
    Route::put('/{subscription}', [SubscriptionController::class, 'update'])->name('update');
    
    // Subscription actions
    Route::post('/{subscription}/extend', [SubscriptionController::class, 'extend'])->name('extend');
    Route::post('/{subscription}/pause', [SubscriptionController::class, 'pause'])->name('pause');
    Route::post('/{subscription}/resume', [SubscriptionController::class, 'resume'])->name('resume');
    Route::post('/{subscription}/cancel', [SubscriptionController::class, 'cancel'])->name('cancel');
    Route::post('/{subscription}/renew', [SubscriptionController::class, 'renew'])->name('renew');
    Route::post('/{subscription}/change-package', [SubscriptionController::class, 'changePackage'])->name('change-package');
    Route::post('/{subscription}/send-reminder', [SubscriptionController::class, 'sendRenewalReminder'])->name('send-reminder');
});

/*
|--------------------------------------------------------------------------
| Creative Management
|--------------------------------------------------------------------------
*/

Route::prefix('creatives')->name('creatives.')->group(function () {
    Route::get('/', [CreativeController::class, 'index'])->name('index');
    Route::get('/create', [CreativeController::class, 'create'])->name('create');
    Route::post('/', [CreativeController::class, 'store'])->name('store');
    Route::get('/{creative}', [CreativeController::class, 'show'])->name('show');
    Route::get('/{creative}/edit', [CreativeController::class, 'edit'])->name('edit');
    Route::put('/{creative}', [CreativeController::class, 'update'])->name('update');
    Route::delete('/{creative}', [CreativeController::class, 'destroy'])->name('destroy');
    
    // Creative actions
    Route::post('/{creative}/approve', [CreativeController::class, 'approve'])->name('approve');
    Route::post('/{creative}/request-changes', [CreativeController::class, 'requestChanges'])->name('request-changes');
    Route::post('/{creative}/reject', [CreativeController::class, 'reject'])->name('reject');
    Route::post('/{creative}/mark-published', [CreativeController::class, 'markPublished'])->name('mark-published');
    Route::post('/{creative}/comment', [CreativeController::class, 'addComment'])->name('add-comment');
    Route::post('/comments/{comment}/resolve', [CreativeController::class, 'resolveComment'])->name('resolve-comment');
    Route::delete('/{creative}/files/{fileId}', [CreativeController::class, 'deleteFile'])->name('delete-file');
    Route::get('/{creative}/download', [CreativeController::class, 'download'])->name('download');
    Route::get('/{creative}/download-all', [CreativeController::class, 'downloadAll'])->name('download-all');
    
    // Bulk actions
    Route::post('/bulk-approve', [CreativeController::class, 'bulkApprove'])->name('bulk-approve');
    Route::post('/bulk-delete', [CreativeController::class, 'bulkDelete'])->name('bulk-delete');
});

/*
|--------------------------------------------------------------------------
| Lead Management
|--------------------------------------------------------------------------
*/

Route::prefix('leads')->name('leads.')->group(function () {
    Route::get('/', [LeadController::class, 'index'])->name('index');
    Route::get('/export', [LeadController::class, 'export'])->name('export');
    Route::get('/import', [LeadController::class, 'showImportForm'])->name('import-form');
    Route::post('/import', [LeadController::class, 'import'])->name('import');
    Route::get('/analytics', [LeadController::class, 'analytics'])->name('analytics');
    Route::get('/create', [LeadController::class, 'create'])->name('create');
    Route::post('/', [LeadController::class, 'store'])->name('store');
    Route::get('/{lead}', [LeadController::class, 'show'])->name('show');
    Route::get('/{lead}/edit', [LeadController::class, 'edit'])->name('edit');
    Route::put('/{lead}', [LeadController::class, 'update'])->name('update');
    Route::delete('/{lead}', [LeadController::class, 'destroy'])->name('destroy');
    
    // Lead actions
    Route::patch('/{lead}/status', [LeadController::class, 'updateStatus'])->name('update-status');
    Route::patch('/{lead}/quality', [LeadController::class, 'updateQuality'])->name('update-quality');
    Route::post('/{lead}/note', [LeadController::class, 'addNote'])->name('add-note');
    
    // Bulk actions
    Route::post('/bulk-status', [LeadController::class, 'bulkUpdateStatus'])->name('bulk-status');
    Route::post('/bulk-delete', [LeadController::class, 'bulkDelete'])->name('bulk-delete');
    Route::post('/bulk-assign', [LeadController::class, 'assignToClient'])->name('bulk-assign');
});

/*
|--------------------------------------------------------------------------
| Invoice Management
|--------------------------------------------------------------------------
*/

Route::prefix('invoices')->name('invoices.')->group(function () {
    Route::get('/', [InvoiceController::class, 'index'])->name('index');
    Route::get('/export', [InvoiceController::class, 'export'])->name('export');
    Route::post('/generate-recurring', [InvoiceController::class, 'generateRecurring'])->name('generate-recurring');
    Route::get('/create', [InvoiceController::class, 'create'])->name('create');
    Route::post('/', [InvoiceController::class, 'store'])->name('store');
    Route::get('/{invoice}', [InvoiceController::class, 'show'])->name('show');
    Route::get('/{invoice}/edit', [InvoiceController::class, 'edit'])->name('edit');
    Route::put('/{invoice}', [InvoiceController::class, 'update'])->name('update');
    Route::delete('/{invoice}', [InvoiceController::class, 'destroy'])->name('destroy');
    
    // Invoice actions
    Route::post('/{invoice}/mark-paid', [InvoiceController::class, 'markAsPaid'])->name('mark-paid');
    Route::post('/{invoice}/mark-sent', [InvoiceController::class, 'markAsSent'])->name('mark-sent');
    Route::post('/{invoice}/cancel', [InvoiceController::class, 'cancel'])->name('cancel');
    Route::post('/{invoice}/send-email', [InvoiceController::class, 'sendEmail'])->name('send-email');
    Route::get('/{invoice}/download', [InvoiceController::class, 'download'])->name('download');
    Route::get('/{invoice}/view-pdf', [InvoiceController::class, 'viewPdf'])->name('view-pdf');
    Route::post('/{invoice}/regenerate-pdf', [InvoiceController::class, 'regeneratePdf'])->name('regenerate-pdf');
    
    // Bulk actions
    Route::post('/bulk-send', [InvoiceController::class, 'bulkSend'])->name('bulk-send');
});

/*
|--------------------------------------------------------------------------
| Transaction Management
|--------------------------------------------------------------------------
*/

Route::prefix('transactions')->name('transactions.')->group(function () {
    Route::get('/', [TransactionController::class, 'index'])->name('index');
    Route::get('/export', [TransactionController::class, 'export'])->name('export');
    Route::get('/analytics', [TransactionController::class, 'analytics'])->name('analytics');
    Route::get('/create', [TransactionController::class, 'create'])->name('create');
    Route::post('/', [TransactionController::class, 'store'])->name('store');
    Route::get('/{transaction}', [TransactionController::class, 'show'])->name('show');
    
    // Transaction actions
    Route::patch('/{transaction}/status', [TransactionController::class, 'updateStatus'])->name('update-status');
    Route::post('/{transaction}/refund', [TransactionController::class, 'refund'])->name('refund');
});

/*
|--------------------------------------------------------------------------
| Campaign Reports
|--------------------------------------------------------------------------
*/

Route::prefix('campaign-reports')->name('campaign-reports.')->group(function () {
    Route::get('/', [CampaignReportController::class, 'index'])->name('index');
    Route::get('/export', [CampaignReportController::class, 'export'])->name('export');
    Route::get('/import', [CampaignReportController::class, 'showImportForm'])->name('import-form');
    Route::post('/import', [CampaignReportController::class, 'import'])->name('import');
    Route::get('/analytics', [CampaignReportController::class, 'analytics'])->name('analytics');
    Route::get('/create', [CampaignReportController::class, 'create'])->name('create');
    Route::post('/', [CampaignReportController::class, 'store'])->name('store');
    Route::get('/{campaignReport}', [CampaignReportController::class, 'show'])->name('show');
    Route::get('/{campaignReport}/edit', [CampaignReportController::class, 'edit'])->name('edit');
    Route::put('/{campaignReport}', [CampaignReportController::class, 'update'])->name('update');
    Route::delete('/{campaignReport}', [CampaignReportController::class, 'destroy'])->name('destroy');
});

/*
|--------------------------------------------------------------------------
| Support Tickets
|--------------------------------------------------------------------------
*/

Route::prefix('support-tickets')->name('support-tickets.')->group(function () {
    Route::get('/', [SupportTicketController::class, 'index'])->name('index');
    Route::get('/export', [SupportTicketController::class, 'export'])->name('export');
    Route::get('/statistics', [SupportTicketController::class, 'statistics'])->name('statistics');
    Route::get('/create', [SupportTicketController::class, 'create'])->name('create');
    Route::post('/', [SupportTicketController::class, 'store'])->name('store');
    Route::get('/{supportTicket}', [SupportTicketController::class, 'show'])->name('show');
    Route::delete('/{supportTicket}', [SupportTicketController::class, 'destroy'])->name('destroy');
    
    // Ticket actions
    Route::post('/{supportTicket}/reply', [SupportTicketController::class, 'reply'])->name('reply');
    Route::patch('/{supportTicket}/status', [SupportTicketController::class, 'updateStatus'])->name('update-status');
    Route::patch('/{supportTicket}/priority', [SupportTicketController::class, 'updatePriority'])->name('update-priority');
    Route::post('/{supportTicket}/assign', [SupportTicketController::class, 'assign'])->name('assign');
    Route::post('/{supportTicket}/assign-self', [SupportTicketController::class, 'assignToSelf'])->name('assign-self');
    Route::post('/{supportTicket}/resolve', [SupportTicketController::class, 'resolve'])->name('resolve');
    Route::post('/{supportTicket}/reopen', [SupportTicketController::class, 'reopen'])->name('reopen');
    Route::post('/{supportTicket}/close', [SupportTicketController::class, 'close'])->name('close');
    Route::delete('/messages/{message}', [SupportTicketController::class, 'deleteMessage'])->name('delete-message');
    Route::get('/{supportTicket}/messages/{messageId}/attachments/{index}', [SupportTicketController::class, 'downloadAttachment'])->name('download-attachment');
    
    // Bulk actions
    Route::post('/bulk-assign', [SupportTicketController::class, 'bulkAssign'])->name('bulk-assign');
    Route::post('/bulk-status', [SupportTicketController::class, 'bulkUpdateStatus'])->name('bulk-status');
    Route::post('/merge', [SupportTicketController::class, 'merge'])->name('merge');
});

/*
|--------------------------------------------------------------------------
| Staff User Management (Admin Only)
|--------------------------------------------------------------------------
*/

Route::prefix('users')->name('users.')->middleware('role:admin')->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('index');
    Route::get('/create', [UserController::class, 'create'])->name('create');
    Route::post('/', [UserController::class, 'store'])->name('store');
    Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
    Route::put('/{user}', [UserController::class, 'update'])->name('update');
    Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    Route::post('/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('toggle-status');
});

/*
|--------------------------------------------------------------------------
| Settings (Admin Only)
|--------------------------------------------------------------------------
*/

Route::prefix('settings')->name('settings.')->middleware('role:admin')->group(function () {
    Route::get('/', [SettingController::class, 'index'])->name('index');
    Route::post('/general', [SettingController::class, 'updateGeneral'])->name('update-general');
    Route::post('/payment', [SettingController::class, 'updatePayment'])->name('update-payment');
    Route::post('/invoice', [SettingController::class, 'updateInvoice'])->name('update-invoice');
    Route::post('/email', [SettingController::class, 'updateEmail'])->name('update-email');
    Route::post('/notification', [SettingController::class, 'updateNotification'])->name('update-notification');
    Route::post('/social', [SettingController::class, 'updateSocial'])->name('update-social');
    Route::post('/api', [SettingController::class, 'updateApi'])->name('update-api');
    Route::post('/clear-cache', [SettingController::class, 'clearCache'])->name('clear-cache');
    Route::post('/test-email', [SettingController::class, 'testEmail'])->name('test-email');
    Route::get('/backup', [SettingController::class, 'backupDatabase'])->name('backup');
    Route::get('/system-info', [SettingController::class, 'systemInfo'])->name('system-info');
});

/*
|--------------------------------------------------------------------------
| Activity Logs
|--------------------------------------------------------------------------
*/

Route::prefix('activity-logs')->name('activity-logs.')->group(function () {
    Route::get('/', [ActivityLogController::class, 'index'])->name('index');
    Route::get('/export', [ActivityLogController::class, 'export'])->name('export');
    Route::get('/{activityLog}', [ActivityLogController::class, 'show'])->name('show');
    Route::post('/clear-old', [ActivityLogController::class, 'clearOld'])->name('clear-old')->middleware('role:admin');
});

/*
|--------------------------------------------------------------------------
| Webhooks Management (Admin Only)
|--------------------------------------------------------------------------
*/

// Route::prefix('webhooks')->name('webhooks.')->middleware('role:admin')->group(function () {
//     Route::get('/', [WebhookController::class, 'index'])->name('index');
//     Route::post('/meta', [WebhookController::class, 'updateMeta'])->name('update-meta');
//     Route::post('/google', [WebhookController::class, 'updateGoogle'])->name('update-google');
//     Route::post('/toggle-status', [WebhookController::class, 'toggleStatus'])->name('toggle-status');
//     Route::post('/regenerate-meta-token', [WebhookController::class, 'regenerateMetaToken'])->name('regenerate-meta-token');
//     Route::post('/regenerate-google-secret', [WebhookController::class, 'regenerateGoogleSecret'])->name('regenerate-google-secret');
//     Route::post('/clients/{client}/generate-token', [WebhookController::class, 'generateClientToken'])->name('generate-client-token');
//     Route::post('/clients/{client}/revoke-token', [WebhookController::class, 'revokeClientToken'])->name('revoke-client-token');
//     Route::get('/test-meta', [WebhookController::class, 'testMeta'])->name('test-meta');
//     Route::get('/logs', [WebhookController::class, 'logs'])->name('logs');
// });


// Webhook Management
Route::prefix('webhooks')->name('webhooks.')->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\WebhookController::class, 'index'])->name('index');
    Route::post('/toggle-status', [App\Http\Controllers\Admin\WebhookController::class, 'toggleStatus'])->name('toggle-status');
    Route::post('/update-meta', [App\Http\Controllers\Admin\WebhookController::class, 'updateMeta'])->name('update-meta');
    Route::post('/update-google', [App\Http\Controllers\Admin\WebhookController::class, 'updateGoogle'])->name('update-google');
    Route::post('/meta-mapping', [App\Http\Controllers\Admin\WebhookController::class, 'addMetaMapping'])->name('add-meta-mapping');
    Route::delete('/meta-mapping', [App\Http\Controllers\Admin\WebhookController::class, 'deleteMetaMapping'])->name('delete-meta-mapping');
    Route::post('/google-mapping', [App\Http\Controllers\Admin\WebhookController::class, 'addGoogleMapping'])->name('add-google-mapping');
    Route::delete('/google-mapping', [App\Http\Controllers\Admin\WebhookController::class, 'deleteGoogleMapping'])->name('delete-google-mapping');
    Route::get('/generate-token', [App\Http\Controllers\Admin\WebhookController::class, 'generateToken'])->name('generate-token');
    Route::get('/logs', [App\Http\Controllers\Admin\WebhookController::class, 'logs'])->name('logs');
    Route::get('/logs/{log}', [App\Http\Controllers\Admin\WebhookController::class, 'showLog'])->name('log-detail');
    Route::delete('/logs/clear', [App\Http\Controllers\Admin\WebhookController::class, 'clearLogs'])->name('clear-logs');
});