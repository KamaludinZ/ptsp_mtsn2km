<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;

class ServiceCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = ServiceCategory::query();
        
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        $categories = $query->with('parent')->orderBy('sort_order')->latest()->paginate(15);
        
        return view('admin.service_categories.index', compact('categories'));
    }

    public function create()
    {
        $categories = ServiceCategory::whereNull('parent_id')->get();
        return view('admin.service_categories.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:service_categories',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
            'parent_id' => 'nullable|exists:service_categories,id',
            'is_active' => 'boolean',
        ]);

        ServiceCategory::create($request->all());

        return redirect()->route('suadmin.service-categories.index')->with('success', 'Kategori layanan berhasil ditambahkan.');
    }

    public function show(ServiceCategory $serviceCategory)
    {
        return view('admin.service_categories.show', compact('serviceCategory'));
    }

    public function edit(ServiceCategory $serviceCategory)
    {
        $categories = ServiceCategory::where('id', '!=', $serviceCategory->id)->whereNull('parent_id')->get();
        return view('admin.service_categories.edit', compact('serviceCategory', 'categories'));
    }

    public function update(Request $request, ServiceCategory $serviceCategory)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:service_categories,slug,' . $serviceCategory->id,
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
            'parent_id' => 'nullable|exists:service_categories,id|not_in:' . $serviceCategory->id,
            'is_active' => 'boolean',
        ]);

        $serviceCategory->update($request->all());

        return redirect()->route('suadmin.service-categories.index')->with('success', 'Kategori layanan berhasil diperbarui.');
    }

    public function destroy(ServiceCategory $serviceCategory)
    {
        $serviceCategory->delete();

        return redirect()->route('suadmin.service-categories.index')->with('success', 'Kategori layanan berhasil dihapus.');
    }
}