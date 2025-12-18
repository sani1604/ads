<?php
// app/Http/Controllers/Admin/ServiceCategoryController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;

class ServiceCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function index()
    {
        $categories = ServiceCategory::withCount(['packages' => fn($q) => $q->active()])
            ->ordered()
            ->paginate(20);

        return view('admin.service-categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.service-categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:service_categories,slug',
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:20',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        ServiceCategory::create([
            'name' => $request->name,
            'slug' => $request->slug,
            'description' => $request->description,
            'icon' => $request->icon,
            'color' => $request->color ?? '#3B82F6',
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.service-categories.index')
            ->with('success', 'Service category created successfully.');
    }

    public function edit(ServiceCategory $serviceCategory)
    {
        return view('admin.service-categories.edit', compact('serviceCategory'));
    }

    public function update(Request $request, ServiceCategory $serviceCategory)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:service_categories,slug,' . $serviceCategory->id,
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:20',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $serviceCategory->update([
            'name' => $request->name,
            'slug' => $request->slug,
            'description' => $request->description,
            'icon' => $request->icon,
            'color' => $request->color ?? '#3B82F6',
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.service-categories.index')
            ->with('success', 'Service category updated successfully.');
    }

    public function destroy(ServiceCategory $serviceCategory)
    {
        if ($serviceCategory->packages()->exists()) {
            return back()->with('error', 'Cannot delete category with packages.');
        }

        $serviceCategory->delete();

        return redirect()->route('admin.service-categories.index')
            ->with('success', 'Service category deleted successfully.');
    }

    public function toggleStatus(ServiceCategory $serviceCategory)
    {
        $serviceCategory->update(['is_active' => !$serviceCategory->is_active]);

        return back()->with('success', 'Status updated successfully.');
    }
}