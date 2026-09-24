@extends('layouts.admin')

@section('title', 'Kategori Layanan')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Manajemen Kategori Layanan</h4>
                    <a href="{{ route('admin.service-categories.create') }}" class="btn btn-light">
                        <i class="fas fa-plus"></i> Tambah Kategori
                    </a>
                </div>
                <div class="card-body">
                    <!-- Filter -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <label class="form-label">Cari Kategori</label>
                            <input type="text" class="form-control" id="searchInput" placeholder="Cari nama kategori..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button class="btn btn-primary w-100" id="applyFilters">Terapkan Filter</button>
                        </div>
                    </div>

                    <!-- Categories Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Nama</th>
                                    <th>Slug</th>
                                    <th>Deskripsi</th>
                                    <th>Parent</th>
                                    <th>Urutan</th>
                                    <th>Aktif</th>
                                    <th>Dibuat</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($categories as $category)
                                    <tr>
                                        <td>
                                            @if($category->parent)
                                                <span class="ms-{{ $category->depth * 2 }}"></span>— 
                                            @endif
                                            {{ $category->name }}
                                        </td>
                                        <td>{{ $category->slug }}</td>
                                        <td>{{ Str::limit($category->description, 50) }}</td>
                                        <td>{{ $category->parent->name ?? 'N/A' }}</td>
                                        <td>{{ $category->sort_order }}</td>
                                        <td>
                                            @if($category->is_active)
                                                <span class="badge bg-success">Ya</span>
                                            @else
                                                <span class="badge bg-danger">Tidak</span>
                                            @endif
                                        </td>
                                        <td>{{ $category->created_at->format('d M Y') }}</td>
                                        <td>
                                            <a href="{{ route('admin.service-categories.show', $category) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.service-categories.edit', $category) }}" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.service-categories.destroy', $category) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus kategori ini?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">Tidak ada kategori ditemukan</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $categories->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('applyFilters').addEventListener('click', function() {
        const search = document.getElementById('searchInput').value;
        
        let url = '{{ route('admin.service-categories.index') }}';
        const params = [];
        
        if (search) params.push('search=' + encodeURIComponent(search));
        
        if (params.length > 0) {
            url += '?' + params.join('&');
        }
        
        window.location.href = url;
    });
    
    // Trigger filter when pressing Enter in search input
    document.getElementById('searchInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            document.getElementById('applyFilters').click();
        }
    });
});
</script>
@endsection