<?php
// routes/api.php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\ReportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public API endpoints
Route::prefix('v1')->name('api.v1.')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Health Check
    |--------------------------------------------------------------------------
    */
    
    Route::get('/health', function () {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toIso8601String(),
            'version' => '1.0.0',
        ]);
    })->name('health');

    /*
    |--------------------------------------------------------------------------
    | Authentication (Optional - Token Based)
    |--------------------------------------------------------------------------
    */
    
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/register', [AuthController::class, 'register'])->name('register');

    /*
    |--------------------------------------------------------------------------
    | Protected Routes
    |--------------------------------------------------------------------------
    */
    
    Route::middleware('auth:sanctum')->group(function () {
        
        // Current User
        Route::get('/user', function (Request $request) {
            return response()->json([
                'success' => true,
                'data' => $request->user()->load('industry'),
            ]);
        })->name('user');

        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        /*
        |--------------------------------------------------------------------------
        | Client API Endpoints
        |--------------------------------------------------------------------------
        */
        
        Route::middleware('role:client')->prefix('client')->name('client.')->group(function () {
            
            // Dashboard Stats
            Route::get('/dashboard', function (Request $request) {
                $user = $request->user();
                
                return response()->json([
                    'success' => true,
                    'data' => [
                        'wallet_balance' => $user->wallet_balance,
                        'active_subscription' => $user->activeSubscription?->load('package'),
                        'leads_this_month' => $user->leads()->thisMonth()->count(),
                        'pending_creatives' => $user->creatives()->pendingApproval()->count(),
                        'unread_notifications' => $user->getUnreadNotificationsCount(),
                    ],
                ]);
            })->name('dashboard');

            // Leads
            Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
            Route::get('/leads/{lead}', [LeadController::class, 'show'])->name('leads.show');
            Route::patch('/leads/{lead}/status', [LeadController::class, 'updateStatus'])->name('leads.update-status');

            // Reports
            Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
            Route::get('/reports/chart-data', [ReportController::class, 'chartData'])->name('reports.chart-data');

            // Notifications
            Route::get('/notifications', function (Request $request) {
                return response()->json([
                    'success' => true,
                    'data' => $request->user()->notifications()->latest()->paginate(20),
                ]);
            })->name('notifications.index');

            Route::post('/notifications/{notification}/read', function (Request $request, $notificationId) {
                $notification = $request->user()->notifications()->findOrFail($notificationId);
                $notification->markAsRead();
                
                return response()->json(['success' => true]);
            })->name('notifications.read');
        });

        /*
        |--------------------------------------------------------------------------
        | Admin API Endpoints
        |--------------------------------------------------------------------------
        */
        
        Route::middleware('role:admin,manager')->prefix('admin')->name('admin.')->group(function () {
            
            // Dashboard Stats
            Route::get('/stats', function () {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'total_clients' => \App\Models\User::clients()->count(),
                        'active_subscriptions' => \App\Models\Subscription::active()->count(),
                        'pending_creatives' => \App\Models\Creative::pendingApproval()->count(),
                        'open_tickets' => \App\Models\SupportTicket::open()->count(),
                        'today_leads' => \App\Models\Lead::today()->count(),
                        'today_revenue' => \App\Models\Transaction::completed()->whereDate('created_at', today())->sum('total_amount'),
                    ],
                ]);
            })->name('stats');

            // Clients
            Route::get('/clients', function (Request $request) {
                $clients = \App\Models\User::clients()
                    ->with(['industry', 'activeSubscription.package'])
                    ->when($request->search, fn($q, $s) => $q->search($s))
                    ->latest()
                    ->paginate(20);

                return response()->json([
                    'success' => true,
                    'data' => $clients,
                ]);
            })->name('clients.index');

            // Quick lead creation
            Route::post('/leads', function (Request $request) {
                $request->validate([
                    'user_id' => 'required|exists:users,id',
                    'name' => 'required|string|max:255',
                    'phone' => 'required|string|max:20',
                    'email' => 'nullable|email',
                    'source' => 'required|in:facebook,instagram,google,linkedin,website,manual,other',
                ]);

                $client = \App\Models\User::findOrFail($request->user_id);
                $leadService = app(\App\Services\LeadService::class);
                $lead = $leadService->create($client, $request->all());

                return response()->json([
                    'success' => true,
                    'data' => $lead,
                ], 201);
            })->name('leads.store');
        });
    });
});

/*
|--------------------------------------------------------------------------
| Fallback Route
|--------------------------------------------------------------------------
*/

Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'API endpoint not found.',
    ], 404);
});