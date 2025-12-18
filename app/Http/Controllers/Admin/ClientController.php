<?php
// app/Http/Controllers/Admin/ClientController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Industry;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin,manager']);
    }

    /**
     * List all clients
     */
    public function index(Request $request)
    {
        $query = User::clients()->with(['industry', 'activeSubscription.package']);

        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by industry
        if ($request->filled('industry')) {
            $query->where('industry_id', $request->industry);
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Filter by subscription status
        if ($request->filled('subscription')) {
            if ($request->subscription === 'active') {
                $query->whereHas('subscriptions', fn($q) => $q->active());
            } elseif ($request->subscription === 'expired') {
                $query->whereDoesntHave('subscriptions', fn($q) => $q->active());
            }
        }

        // Sorting
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $clients = $query->paginate(20)->withQueryString();

        $industries = Industry::active()->ordered()->get();

        $stats = [
            'total' => User::clients()->count(),
            'active' => User::clients()->active()->count(),
            'with_subscription' => User::clients()->whereHas('subscriptions', fn($q) => $q->active())->count(),
            'this_month' => User::clients()->whereMonth('created_at', now()->month)->count(),
        ];

        return view('admin.clients.index', compact('clients', 'industries', 'stats'));
    }

    /**
     * Show client details
     */
    public function show(User $client)
    {
        $this->ensureClient($client);

        $client->load([
            'industry',
            'subscriptions.package.serviceCategory',
            'transactions' => fn($q) => $q->latest()->take(10),
            'invoices' => fn($q) => $q->latest()->take(10),
            'leads' => fn($q) => $q->latest()->take(10),
            'creatives' => fn($q) => $q->latest()->take(10),
        ]);

        // Get stats
        $stats = [
            'total_spent' => $client->transactions()->completed()->sum('total_amount'),
            'total_leads' => $client->leads()->count(),
            'total_creatives' => $client->creatives()->count(),
            'wallet_balance' => $client->wallet_balance,
        ];

        // Activity log
        $activities = $client->activityLogs()->latest()->take(20)->get();

        return view('admin.clients.show', compact('client', 'stats', 'activities'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        $industries = Industry::active()->ordered()->get();

        return view('admin.clients.create', compact('industries'));
    }

    /**
     * Store new client
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|regex:/^[6-9]\d{9}$/|unique:users,phone',
            'password' => ['required', Password::min(8)],
            'company_name' => 'nullable|string|max:255',
            'industry_id' => 'nullable|exists:industries,id',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:10',
            'gst_number' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $client = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => 'client',
            'company_name' => $request->company_name,
            'industry_id' => $request->industry_id,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'postal_code' => $request->postal_code,
            'gst_number' => $request->gst_number,
            'is_active' => $request->boolean('is_active', true),
            'is_onboarded' => true,
            'email_verified_at' => now(),
        ]);

        ActivityLogService::log(
            'client_created',
            "New client created: {$client->name}",
            $client,
            ['email' => $client->email]
        );

        return redirect()->route('admin.clients.show', $client)
            ->with('success', 'Client created successfully.');
    }

    /**
     * Show edit form
     */
    public function edit(User $client)
    {
        $this->ensureClient($client);

        $industries = Industry::active()->ordered()->get();

        return view('admin.clients.edit', compact('client', 'industries'));
    }

    /**
     * Update client
     */
    public function update(Request $request, User $client)
    {
        $this->ensureClient($client);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $client->id,
            'phone' => 'required|string|regex:/^[6-9]\d{9}$/|unique:users,phone,' . $client->id,
            'company_name' => 'nullable|string|max:255',
            'industry_id' => 'nullable|exists:industries,id',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:10',
            'gst_number' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $client->update($request->only([
            'name', 'email', 'phone', 'company_name', 'industry_id',
            'address', 'city', 'state', 'postal_code', 'gst_number',
        ]));

        $client->update(['is_active' => $request->boolean('is_active', true)]);

        ActivityLogService::log(
            'client_updated',
            "Client updated: {$client->name}",
            $client
        );

        return redirect()->route('admin.clients.show', $client)
            ->with('success', 'Client updated successfully.');
    }

    /**
     * Toggle client status
     */
    public function toggleStatus(User $client)
    {
        $this->ensureClient($client);

        $client->update(['is_active' => !$client->is_active]);

        $status = $client->is_active ? 'activated' : 'deactivated';

        ActivityLogService::log(
            'client_status_changed',
            "Client {$status}: {$client->name}",
            $client
        );

        return back()->with('success', "Client {$status} successfully.");
    }

    /**
     * Credit wallet manually
     */
    public function creditWallet(Request $request, User $client)
    {
        $this->ensureClient($client);

        $request->validate([
            'amount' => 'required|numeric|min:1|max:1000000',
            'description' => 'required|string|max:500',
        ]);

        $client->creditWallet(
            $request->amount,
            $request->description,
            'manual',
            null
        );

        ActivityLogService::log(
            'wallet_credited_manual',
            "Wallet credited manually: ₹{$request->amount}",
            $client,
            ['amount' => $request->amount, 'description' => $request->description]
        );

        return back()->with('success', "₹{$request->amount} credited to wallet successfully.");
    }

    /**
     * Debit wallet manually
     */
    public function debitWallet(Request $request, User $client)
    {
        $this->ensureClient($client);

        $request->validate([
            'amount' => 'required|numeric|min:1|max:' . $client->wallet_balance,
            'description' => 'required|string|max:500',
        ]);

        $client->debitWallet(
            $request->amount,
            $request->description,
            'manual',
            null
        );

        ActivityLogService::log(
            'wallet_debited_manual',
            "Wallet debited manually: ₹{$request->amount}",
            $client,
            ['amount' => $request->amount, 'description' => $request->description]
        );

        return back()->with('success', "₹{$request->amount} debited from wallet successfully.");
    }

    /**
     * Login as client (Impersonation)
     */
    public function loginAs(User $client)
    {
        $this->ensureClient($client);

        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only admins can impersonate clients.');
        }

        session(['impersonator_id' => auth()->id()]);
        auth()->login($client);

        ActivityLogService::log(
            'impersonation_started',
            "Admin started impersonating client: {$client->name}",
            $client
        );

        return redirect()->route('client.dashboard')
            ->with('info', "You are now logged in as {$client->name}");
    }

    /**
     * Stop impersonation
     */
    public function stopImpersonation()
    {
        $impersonatorId = session('impersonator_id');

        if (!$impersonatorId) {
            return redirect()->route('client.dashboard');
        }

        $admin = User::find($impersonatorId);

        if ($admin) {
            session()->forget('impersonator_id');
            auth()->login($admin);

            return redirect()->route('admin.dashboard')
                ->with('success', 'Returned to admin account.');
        }

        return redirect()->route('login');
    }

    /**
     * Delete client (Soft delete)
     */
    public function destroy(User $client)
    {
        $this->ensureClient($client);

        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only admins can delete clients.');
        }

        $clientName = $client->name;
        $client->delete();

        ActivityLogService::log(
            'client_deleted',
            "Client deleted: {$clientName}",
            null,
            ['client_id' => $client->id, 'name' => $clientName]
        );

        return redirect()->route('admin.clients.index')
            ->with('success', 'Client deleted successfully.');
    }

    /**
     * Export clients
     */
    public function export(Request $request)
    {
        $clients = User::clients()->with(['industry', 'activeSubscription.package'])->get();

        $csv = "Name,Email,Phone,Company,Industry,Subscription,Wallet Balance,Status,Created At\n";

        foreach ($clients as $client) {
            $csv .= implode(',', [
                '"' . $client->name . '"',
                $client->email,
                $client->phone,
                '"' . $client->company_name . '"',
                '"' . ($client->industry?->name ?? 'N/A') . '"',
                '"' . ($client->activeSubscription?->package?->name ?? 'None') . '"',
                $client->wallet_balance,
                $client->is_active ? 'Active' : 'Inactive',
                $client->created_at->format('Y-m-d'),
            ]) . "\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename=clients_' . now()->format('Y-m-d') . '.csv');
    }

    protected function ensureClient(User $client): void
    {
        if (!$client->isClient()) {
            abort(404);
        }
    }
}