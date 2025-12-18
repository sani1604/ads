<?php
// app/Http/Controllers/Admin/PackageController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Industry;
use App\Models\Package;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin,manager']);
    }

    /**
     * List all packages
     */
    public function index(Request $request)
    {
        $query = Package::with(['serviceCategory', 'industry']);

        // Filter by category
        if ($request->filled('category')) {
            $query->where('service_category_id', $request->category);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $packages = $query->ordered()->paginate(20);

        $categories = ServiceCategory::active()->ordered()->get();

        $stats = [
            'total' => Package::count(),
            'active' => Package::where('is_active', true)->count(),
            'subscriptions' => Package::withCount(['subscriptions' => fn($q) => $q->active()])->get()->sum('subscriptions_count'),
        ];

        return view('admin.packages.index', compact('packages', 'categories', 'stats'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        $categories = ServiceCategory::active()->ordered()->get();
        $industries = Industry::active()->ordered()->get();

        return view('admin.packages.create', compact('categories', 'industries'));
    }

    /**
     * Store new package
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_category_id' => 'required|exists:service_categories,id',
            'industry_id' => 'nullable|exists:industries,id',
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:packages,slug',
            'description' => 'nullable|string|max:2000',
            'short_description' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0|gte:price',
            'billing_cycle' => 'required|in:monthly,quarterly,yearly',
            'billing_cycle_days' => 'required|integer|min:1',
            'features' => 'nullable|array',
            'features.*' => 'string|max:255',
            'deliverables' => 'nullable|array',
            'deliverables.*' => 'string|max:255',
            'max_creatives_per_month' => 'required|integer|min:1',
            'max_revisions' => 'required|integer|min:1',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['features'] = $request->features ? json_encode(array_filter($request->features)) : null;
        $validated['deliverables'] = $request->deliverables ? json_encode(array_filter($request->deliverables)) : null;
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_active'] = $request->boolean('is_active', true);

        $package = Package::create($validated);

        return redirect()->route('admin.packages.index')
            ->with('success', 'Package created successfully.');
    }

    /**
     * Show package details
     */
    public function show(Package $package)
    {
        $package->load(['serviceCategory', 'industry']);

        $activeSubscriptions = $package->subscriptions()->active()->with('user')->get();
        $totalRevenue = $package->subscriptions()
            ->whereHas('transactions', fn($q) => $q->completed())
            ->with('transactions')
            ->get()
            ->pluck('transactions')
            ->flatten()
            ->sum('total_amount');

        return view('admin.packages.show', compact('package', 'activeSubscriptions', 'totalRevenue'));
    }

    /**
     * Show edit form
     */
    public function edit(Package $package)
    {
        $categories = ServiceCategory::active()->ordered()->get();
        $industries = Industry::active()->ordered()->get();

        return view('admin.packages.edit', compact('package', 'categories', 'industries'));
    }

    /**
     * Update package
     */
    public function update(Request $request, Package $package)
    {
        $validated = $request->validate([
            'service_category_id' => 'required|exists:service_categories,id',
            'industry_id' => 'nullable|exists:industries,id',
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:packages,slug,' . $package->id,
            'description' => 'nullable|string|max:2000',
            'short_description' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0|gte:price',
            'billing_cycle' => 'required|in:monthly,quarterly,yearly',
            'billing_cycle_days' => 'required|integer|min:1',
            'features' => 'nullable|array',
            'features.*' => 'string|max:255',
            'deliverables' => 'nullable|array',
            'deliverables.*' => 'string|max:255',
            'max_creatives_per_month' => 'required|integer|min:1',
            'max_revisions' => 'required|integer|min:1',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['features'] = $request->features ? json_encode(array_filter($request->features)) : null;
        $validated['deliverables'] = $request->deliverables ? json_encode(array_filter($request->deliverables)) : null;
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_active'] = $request->boolean('is_active', true);

        $package->update($validated);

        return redirect()->route('admin.packages.index')
            ->with('success', 'Package updated successfully.');
    }

    /**
     * Toggle package status
     */
    public function toggleStatus(Package $package)
    {
        $package->update(['is_active' => !$package->is_active]);

        $status = $package->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Package {$status} successfully.");
    }

    /**
     * Toggle featured status
     */
    public function toggleFeatured(Package $package)
    {
        $package->update(['is_featured' => !$package->is_featured]);

        return back()->with('success', 'Package featured status updated.');
    }

    /**
     * Duplicate package
     */
    public function duplicate(Package $package)
    {
        $newPackage = $package->replicate();
        $newPackage->name = $package->name . ' (Copy)';
        $newPackage->slug = $package->slug . '-copy-' . time();
        $newPackage->is_active = false;
        $newPackage->save();

        return redirect()->route('admin.packages.edit', $newPackage)
            ->with('success', 'Package duplicated. Please update the details.');
    }

    /**
     * Delete package
     */
    public function destroy(Package $package)
    {
        // Check if package has active subscriptions
        if ($package->subscriptions()->active()->exists()) {
            return back()->with('error', 'Cannot delete package with active subscriptions.');
        }

        $package->delete();

        return redirect()->route('admin.packages.index')
            ->with('success', 'Package deleted successfully.');
    }

    /**
     * Update sort order (AJAX)
     */
    public function updateOrder(Request $request)
    {
        $request->validate([
            'packages' => 'required|array',
            'packages.*.id' => 'required|exists:packages,id',
            'packages.*.order' => 'required|integer|min:0',
        ]);

        foreach ($request->packages as $item) {
            Package::where('id', $item['id'])->update(['sort_order' => $item['order']]);
        }

        return response()->json(['success' => true]);
    }
}