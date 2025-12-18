<?php
// routes/web.php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/pricing', function () {
    $categories = \App\Models\ServiceCategory::active()
        ->with(['packages' => fn($q) => $q->active()->ordered()])
        ->ordered()
        ->get();
    return view('pricing', compact('categories'));
})->name('pricing');

Route::get('/services', function () {
    $categories = \App\Models\ServiceCategory::active()->ordered()->get();
    return view('services', compact('categories'));
})->name('services');

Route::get('/about', function () {
    return view('about');
})->name('about');

Route::get('/contact', function () {
    return view('contact');
})->name('contact');

Route::post('/contact', function (\Illuminate\Http\Request $request) {
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email',
        'phone' => 'nullable|string|max:20',
        'message' => 'required|string|max:2000',
    ]);
    
    // Send email or store in database
    \Mail::raw($request->message, function ($mail) use ($request) {
        $mail->to(config('mail.from.address'))
            ->subject('Contact Form: ' . $request->name)
            ->replyTo($request->email, $request->name);
    });
    
    return back()->with('success', 'Thank you for your message. We will get back to you soon!');
})->name('contact.submit');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| Onboarding Routes
|--------------------------------------------------------------------------
*/

// Route::middleware(['auth', 'verified'])->prefix('onboarding')->name('onboarding.')->group(function () {
//     Route::get('/', [\App\Http\Controllers\OnboardingController::class, 'index'])->name('index');
//     Route::get('/step/{step}', [\App\Http\Controllers\OnboardingController::class, 'showStep'])->name('step');
//     Route::post('/step/{step}', [\App\Http\Controllers\OnboardingController::class, 'processStep'])->name('process');
//     Route::post('/complete', [\App\Http\Controllers\OnboardingController::class, 'complete'])->name('complete');
//     Route::post('/skip', [\App\Http\Controllers\OnboardingController::class, 'skip'])->name('skip');
// });

/*
|--------------------------------------------------------------------------
| Client Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'onboarding', 'role:client'])
    ->prefix('client')
    ->name('client.')
    ->group(function () {
        require __DIR__.'/client.php';
    });

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'role:admin,manager'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        require __DIR__.'/admin.php';
    });

/*
|--------------------------------------------------------------------------
| Webhook Routes
|--------------------------------------------------------------------------
*/

require __DIR__.'/webhooks.php';

/*
|--------------------------------------------------------------------------
| Impersonation Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    Route::get('/stop-impersonation', [\App\Http\Controllers\Admin\ClientController::class, 'stopImpersonation'])
        ->name('stop-impersonation');
});


// Onboarding Routes
Route::middleware('auth')->prefix('onboarding')->name('onboarding.')->group(function () {

    Route::get('/', [\App\Http\Controllers\OnboardingController::class, 'index'])->name('index');

    // Show complete page (GET)
    Route::get('/complete', [\App\Http\Controllers\OnboardingController::class, 'complete'])
        ->name('complete.view');

    // Complete onboarding (POST)
    Route::post('/complete', [\App\Http\Controllers\OnboardingController::class, 'complete'])
        ->name('complete');

    Route::post('/skip', [\App\Http\Controllers\OnboardingController::class, 'skip'])
        ->name('skip');

    Route::get('/step/{step}', [\App\Http\Controllers\OnboardingController::class, 'show'])
        ->where('step', '[1-5]')
        ->name('step');

    Route::post('/step/{step}', [\App\Http\Controllers\OnboardingController::class, 'process'])
        ->where('step', '[1-5]')
        ->name('process');
});

// Public Pages (no auth required)
Route::get('/terms', function () {
    return view('pages.terms');
})->name('terms');

Route::get('/privacy', function () {
    return view('pages.privacy');
})->name('privacy');

Route::get('/refund-policy', function () {
    return view('pages.refund');
})->name('refund');

Route::get('/contact', function () {
    return view('pages.contact');
})->name('contact');
