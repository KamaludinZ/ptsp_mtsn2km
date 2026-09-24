<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Service::query();

        if ($request->filled('category')) {
            $query->where('service_category_id', $request->category);
        }

        if ($request->filled('search')) {
            $query->where('name', 'ilike', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('mode')) {
            $query->where('mode', $request->mode);
        }

        if ($request->filled('approval')) {
            $query->where('approval_required', $request->approval === 'required');
        }

        $services = $query->with('categories')->latest()->paginate(15);
        $categories = ServiceCategory::all();
        
        // Additional metrics for the stats cards
        $totalServices = Service::count();
        $activeServices = Service::where('is_active', true)->count();
        $onlineServices = Service::whereIn('mode', ['online', 'hybrid'])->count();
        // No numeric duration column exists on services (only the free-text
        // "processing_time" field), so there is nothing to average here.
        $avgEstimatedDays = 0;
        
        return view('admin.services.index', compact(
            'services', 
            'categories',
            'totalServices',
            'activeServices',
            'onlineServices',
            'avgEstimatedDays'
        ));
    }

    public function create()
    {
        $categories = ServiceCategory::all();
        return view('admin.services.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:services',
            'service_category_id' => 'nullable|exists:service_categories,id',
            'description' => 'nullable|string',
            'mode' => 'required|in:online,offline,hybrid', // Updated to include hybrid
            'is_active' => 'boolean',
            'is_digital_product' => 'boolean',
            'estimated_days' => 'nullable|integer|min:1',
            'approval_required' => 'boolean',
            'approval_roles' => 'array',
            'approval_users' => 'array',
            'approval_instructions' => 'nullable|string',
            // 14 Komponen Standar Pelayanan fields
            'dasar_hukum' => 'nullable|string',
            'persyaratan' => 'nullable|string',
            'mekanisme' => 'nullable|string',
            'jangka_waktu' => 'nullable|string',
            'biaya_tarif' => 'nullable|string',
            'produk_layanan' => 'nullable|string',
            'sarpras' => 'nullable|string',
            'kompetensi_pelaksana' => 'nullable|string',
            'pengawasan_internal' => 'nullable|string',
            'penanganan_pengaduan' => 'nullable|string',
            'jumlah_pelaksana' => 'nullable|string',
            'jaminan_pelayanan' => 'nullable|string',
            'jaminan_keamanan' => 'nullable|string',
            'evaluasi_kinerja' => 'nullable|string',
        ]);

        $service = Service::create([
            'name' => $request->name,
            'slug' => $request->slug,
            'service_category_id' => $request->service_category_id,
            'description' => $request->description,
            'mode' => $request->mode,
            'is_active' => $request->is_active ?? false,
            'is_digital_product' => $request->is_digital_product ?? false,
            'estimated_days' => $request->estimated_days,
            'approval_required' => $request->approval_required ?? true,
            'approval_roles' => $request->approval_roles,
            'approval_users' => $request->approval_users,
            'approval_instructions' => $request->approval_instructions,
            // 14 Komponen Standar Pelayanan fields
            'dasar_hukum' => $request->dasar_hukum,
            'persyaratan' => $request->persyaratan,
            'mekanisme' => $request->mekanisme,
            'jangka_waktu' => $request->jangka_waktu,
            'biaya_tarif' => $request->biaya_tarif,
            'produk_layanan' => $request->produk_layanan,
            'sarpras' => $request->sarpras,
            'kompetensi_pelaksana' => $request->kompetensi_pelaksana,
            'pengawasan_internal' => $request->pengawasan_internal,
            'penanganan_pengaduan' => $request->penanganan_pengaduan,
            'jumlah_pelaksana' => $request->jumlah_pelaksana,
            'jaminan_pelayanan' => $request->jaminan_pelayanan,
            'jaminan_keamanan' => $request->jaminan_keamanan,
            'evaluasi_kinerja' => $request->evaluasi_kinerja,
        ]);

        return redirect()->route('admin.services.index')->with('success', 'Layanan berhasil ditambahkan.');
    }

    public function show(Service $service)
    {
        return view('admin.services.show', compact('service'));
    }

    public function edit(Service $service)
    {
        $categories = ServiceCategory::all();
        return view('admin.services.edit', compact('service', 'categories'));
    }

    public function update(Request $request, Service $service)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:services,slug,' . $service->id,
            'service_category_id' => 'nullable|exists:service_categories,id',
            'description' => 'nullable|string',
            'mode' => 'required|in:online,offline,hybrid', // Updated to include hybrid
            'is_active' => 'boolean',
            'is_digital_product' => 'boolean',
            'estimated_days' => 'nullable|integer|min:1',
            'approval_required' => 'boolean',
            'approval_roles' => 'array',
            'approval_users' => 'array',
            'approval_instructions' => 'nullable|string',
            // 14 Komponen Standar Pelayanan fields
            'dasar_hukum' => 'nullable|string',
            'persyaratan' => 'nullable|string',
            'mekanisme' => 'nullable|string',
            'jangka_waktu' => 'nullable|string',
            'biaya_tarif' => 'nullable|string',
            'produk_layanan' => 'nullable|string',
            'sarpras' => 'nullable|string',
            'kompetensi_pelaksana' => 'nullable|string',
            'pengawasan_internal' => 'nullable|string',
            'penanganan_pengaduan' => 'nullable|string',
            'jumlah_pelaksana' => 'nullable|string',
            'jaminan_pelayanan' => 'nullable|string',
            'jaminan_keamanan' => 'nullable|string',
            'evaluasi_kinerja' => 'nullable|string',
        ]);

        $service->update([
            'name' => $request->name,
            'slug' => $request->slug,
            'service_category_id' => $request->service_category_id,
            'description' => $request->description,
            'mode' => $request->mode,
            'is_active' => $request->is_active ?? false,
            'is_digital_product' => $request->is_digital_product ?? false,
            'estimated_days' => $request->estimated_days,
            'approval_required' => $request->approval_required ?? true,
            'approval_roles' => $request->approval_roles,
            'approval_users' => $request->approval_users,
            'approval_instructions' => $request->approval_instructions,
            // 14 Komponen Standar Pelayanan fields
            'dasar_hukum' => $request->dasar_hukum,
            'persyaratan' => $request->persyaratan,
            'mekanisme' => $request->mekanisme,
            'jangka_waktu' => $request->jangka_waktu,
            'biaya_tarif' => $request->biaya_tarif,
            'produk_layanan' => $request->produk_layanan,
            'sarpras' => $request->sarpras,
            'kompetensi_pelaksana' => $request->kompetensi_pelaksana,
            'pengawasan_internal' => $request->pengawasan_internal,
            'penanganan_pengaduan' => $request->penanganan_pengaduan,
            'jumlah_pelaksana' => $request->jumlah_pelaksana,
            'jaminan_pelayanan' => $request->jaminan_pelayanan,
            'jaminan_keamanan' => $request->jaminan_keamanan,
            'evaluasi_kinerja' => $request->evaluasi_kinerja,
        ]);

        return redirect()->route('admin.services.index')->with('success', 'Layanan berhasil diperbarui.');
    }

    public function destroy(Service $service)
    {
        $service->delete();

        return redirect()->route('admin.services.index')->with('success', 'Layanan berhasil dihapus.');
    }
}