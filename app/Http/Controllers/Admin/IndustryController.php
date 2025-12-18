<?php
// app/Http/Controllers/Admin/IndustryController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Industry;
use Illuminate\Http\Request;

class IndustryController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function index()
    {
        $industries = Industry::withCount('users')
            ->ordered()
            ->paginate(20);

        return view('admin.industries.index', compact('industries'));
    }

    public function create()
    {
        return view('admin.industries.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:industries,slug',
            'description' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        Industry::create($request->only([
            'name', 'slug', 'description', 'icon', 'sort_order'
        ]) + ['is_active' => $request->boolean('is_active', true)]);

        return redirect()->route('admin.industries.index')
            ->with('success', 'Industry created successfully.');
    }

    public function edit(Industry $industry)
    {
        return view('admin.industries.edit', compact('industry'));
    }

    public function update(Request $request, Industry $industry)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:industries,slug,' . $industry->id,
            'description' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $industry->update($request->only([
            'name', 'slug', 'description', 'icon', 'sort_order'
        ]) + ['is_active' => $request->boolean('is_active', true)]);

        return redirect()->route('admin.industries.index')
            ->with('success', 'Industry updated successfully.');
    }

    public function destroy(Industry $industry)
    {
        if ($industry->users()->exists()) {
            return back()->with('error', 'Cannot delete industry with associated clients.');
        }

        $industry->delete();

        return redirect()->route('admin.industries.index')
            ->with('success', 'Industry deleted successfully.');
    }

    public function toggleStatus(Industry $industry)
    {
        $industry->update(['is_active' => !$industry->is_active]);

        return back()->with('success', 'Status updated successfully.');
    }
}