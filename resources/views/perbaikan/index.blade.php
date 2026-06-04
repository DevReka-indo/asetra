@extends('layouts.app')

@section('title', 'Pengajuan Perbaikan Aset')

@section('content')
<div class="container-fluid px-1 py-0 mt-0">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold mb-0">Pengajuan Perbaikan Aset</h3>
        <ul class="breadcrumbs d-flex align-items-center p-0 m-0" style="list-style: none;">
            <li class="nav-home d-flex align-items-center">
                <a href="{{ route('superadmin.dashboard') }}" class="text-muted text-decoration-none d-flex align-items-center">
                    <i class="fas fa-home me-2" style="font-size: 15px;"></i>
                    <span style="font-size: 14px; font-weight: 500; position: relative; top: 2px;">Dashboard</span>
                </a>
            </li>
            <li class="separator text-muted d-flex align-items-center px-2">
                <span style="font-size: 14px; position: relative; top: 2px;">-</span>
            </li>
            <li class="nav-item d-flex align-items-center">
                <span class="text-muted" style="font-size: 14px; font-weight: 500; position: relative; top: 2px;">Pengajuan Perbaikan</span>
            </li>
        </ul>
    </div>

    {{-- ALERT --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- FILTER CARD --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('perbaikan.index') }}" id="filterForm">
                {{-- Keep sort fields in form so sorting isn't lost on filter submit --}}
                <input type="hidden" name="sort_by" value="{{ request('sort_by') }}">
                <input type="hidden" name="order_by" value="{{ request('order_by') }}">

                <div class="row g-2 align-items-end">
                    {{-- Entries --}}
                    <div class="col-md-1">
                        <label class="form-label fw-bold small text-muted text-uppercase" style="font-size: 0.7rem;">Entries</label>
                        <select name="per_page" class="form-select form-select-sm rounded-3 w-100" onchange="this.form.submit()">
                            <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>

                    {{-- Pencarian --}}
                    <div class="col-md-3">
                        <label class="form-label fw-bold small text-muted text-uppercase" style="font-size: 0.7rem;">Pencarian</label>
                        <div class="input-group input-group-sm input-group-focus rounded-3">
                            <span class="input-group-text bg-white border-0 text-muted"><i class="fas fa-search"></i></span>
                            <input type="search" name="search" class="form-control border-0 shadow-none bg-transparent" placeholder="Cari nomor atau nama aset..." value="{{ request('search') }}">
                        </div>
                    </div>

                    {{-- Filter Urgensi --}}
                    <div class="col-md-2">
                        <label class="form-label fw-bold small text-muted text-uppercase" style="font-size: 0.7rem;">Urgensi</label>
                        <select name="urgensi" class="form-select form-select-sm rounded-3 w-100" onchange="this.form.submit()">
                            <option value="">Semua Urgensi</option>
                            <option value="rendah" {{ request('urgensi') == 'rendah' ? 'selected' : '' }}>Rendah</option>
                            <option value="sedang" {{ request('urgensi') == 'sedang' ? 'selected' : '' }}>Sedang</option>
                            <option value="tinggi" {{ request('urgensi') == 'tinggi' ? 'selected' : '' }}>Tinggi</option>
                        </select>
                    </div>

                    {{-- Filter Status --}}
                    <div class="col-md-2">
                        <label class="form-label fw-bold small text-muted text-uppercase" style="font-size: 0.7rem;">Status Pengajuan</label>
                        <select name="status" class="form-select form-select-sm rounded-3 w-100" onchange="this.form.submit()">
                            <option value="">Semua Status</option>
                            <option value="menunggu" {{ request('status') == 'menunggu' ? 'selected' : '' }}>Menunggu Review</option>
                            <option value="disetujui" {{ request('status') == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                            <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                            <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- TABLE CARD --}}
    <div class="card shadow-sm border-0">
        <div class="card-body">

            @php
                $sortBy = request('sort_by', 'created_at');
                $orderBy = request('order_by', 'desc');
            @endphp
            {{-- TABEL --}}
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="60">No</th>
                            <th>
                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'nama_aset', 'order_by' => ($sortBy == 'nama_aset' && $orderBy == 'asc') ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark d-flex align-items-center justify-content-between">
                                    Aset
                                    <span class="d-inline-block position-relative ms-2" style="width: 20px; height: 18px; vertical-align: middle;">
                                        <span style="position: absolute; right: 10px; bottom: 0.1em; font-size: 15px; font-weight: normal; opacity: {{ ($sortBy == 'nama_aset' && $orderBy == 'asc') ? '0.9' : '0.3' }}; color: {{ ($sortBy == 'nama_aset' && $orderBy == 'asc') ? '#253070' : '#888888' }};">↑</span>
                                        <span style="position: absolute; right: 2px; bottom: 0.1em; font-size: 15px; font-weight: normal; opacity: {{ ($sortBy == 'nama_aset' && $orderBy == 'desc') ? '0.9' : '0.3' }}; color: {{ ($sortBy == 'nama_aset' && $orderBy == 'desc') ? '#253070' : '#888888' }};">↓</span>
                                    </span>
                                </a>
                            </th>
                            <th>
                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'pengaju_name', 'order_by' => ($sortBy == 'pengaju_name' && $orderBy == 'asc') ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark d-flex align-items-center justify-content-between">
                                    Pengaju
                                    <span class="d-inline-block position-relative ms-2" style="width: 20px; height: 18px; vertical-align: middle;">
                                        <span style="position: absolute; right: 10px; bottom: 0.1em; font-size: 15px; font-weight: normal; opacity: {{ ($sortBy == 'pengaju_name' && $orderBy == 'asc') ? '0.9' : '0.3' }}; color: {{ ($sortBy == 'pengaju_name' && $orderBy == 'asc') ? '#253070' : '#888888' }};">↑</span>
                                        <span style="position: absolute; right: 2px; bottom: 0.1em; font-size: 15px; font-weight: normal; opacity: {{ ($sortBy == 'pengaju_name' && $orderBy == 'desc') ? '0.9' : '0.3' }}; color: {{ ($sortBy == 'pengaju_name' && $orderBy == 'desc') ? '#253070' : '#888888' }};">↓</span>
                                    </span>
                                </a>
                            </th>
                            <th>
                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'tingkat_urgensi', 'order_by' => ($sortBy == 'tingkat_urgensi' && $orderBy == 'asc') ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark d-flex align-items-center justify-content-between">
                                    Urgensi
                                    <span class="d-inline-block position-relative ms-2" style="width: 20px; height: 18px; vertical-align: middle;">
                                        <span style="position: absolute; right: 10px; bottom: 0.1em; font-size: 15px; font-weight: normal; opacity: {{ ($sortBy == 'tingkat_urgensi' && $orderBy == 'asc') ? '0.9' : '0.3' }}; color: {{ ($sortBy == 'tingkat_urgensi' && $orderBy == 'asc') ? '#253070' : '#888888' }};">↑</span>
                                        <span style="position: absolute; right: 2px; bottom: 0.1em; font-size: 15px; font-weight: normal; opacity: {{ ($sortBy == 'tingkat_urgensi' && $orderBy == 'desc') ? '0.9' : '0.3' }}; color: {{ ($sortBy == 'tingkat_urgensi' && $orderBy == 'desc') ? '#253070' : '#888888' }};">↓</span>
                                    </span>
                                </a>
                            </th>
                            <th>
                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'tanggal_pengajuan', 'order_by' => ($sortBy == 'tanggal_pengajuan' && $orderBy == 'asc') ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark d-flex align-items-center justify-content-between">
                                    Tanggal Pengajuan
                                    <span class="d-inline-block position-relative ms-2" style="width: 20px; height: 18px; vertical-align: middle;">
                                        <span style="position: absolute; right: 10px; bottom: 0.1em; font-size: 15px; font-weight: normal; opacity: {{ ($sortBy == 'tanggal_pengajuan' && $orderBy == 'asc') ? '0.9' : '0.3' }}; color: {{ ($sortBy == 'tanggal_pengajuan' && $orderBy == 'asc') ? '#253070' : '#888888' }};">↑</span>
                                        <span style="position: absolute; right: 2px; bottom: 0.1em; font-size: 15px; font-weight: normal; opacity: {{ ($sortBy == 'tanggal_pengajuan' && $orderBy == 'desc') ? '0.9' : '0.3' }}; color: {{ ($sortBy == 'tanggal_pengajuan' && $orderBy == 'desc') ? '#253070' : '#888888' }};">↓</span>
                                    </span>
                                </a>
                            </th>
                            <th>
                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'status', 'order_by' => ($sortBy == 'status' && $orderBy == 'asc') ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark d-flex align-items-center justify-content-between">
                                    Status
                                    <span class="d-inline-block position-relative ms-2" style="width: 20px; height: 18px; vertical-align: middle;">
                                        <span style="position: absolute; right: 10px; bottom: 0.1em; font-size: 15px; font-weight: normal; opacity: {{ ($sortBy == 'status' && $orderBy == 'asc') ? '0.9' : '0.3' }}; color: {{ ($sortBy == 'status' && $orderBy == 'asc') ? '#253070' : '#888888' }};">↑</span>
                                        <span style="position: absolute; right: 2px; bottom: 0.1em; font-size: 15px; font-weight: normal; opacity: {{ ($sortBy == 'status' && $orderBy == 'desc') ? '0.9' : '0.3' }}; color: {{ ($sortBy == 'status' && $orderBy == 'desc') ? '#253070' : '#888888' }};">↓</span>
                                    </span>
                                </a>
                            </th>
                            <th>Dokumentasi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pengajuans as $p)
                        <tr>
                            <td class="text-muted small">{{ $loop->iteration + ($pengajuans->currentPage() - 1) * $pengajuans->perPage() }}</td>
                            <td>
                                <div class="fw-bold" style="font-size: 13px;">
                                    {{ $p->aset->nama_aset ?? '-' }}
                                </div>
                                <small class="text-muted">{{ $p->aset->nomor_aset ?? '-' }}</small>
                            </td>
                            <td>
                                <small>{{ $p->pengaju ? $p->pengaju->firstname . ' ' . $p->pengaju->lastname : '-' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-{{ $p->urgensi_badge }} rounded-pill px-3">
                                    {{ ucfirst($p->tingkat_urgensi) }}
                                </span>
                            </td>
                            <td>
                                <small>{{ $p->tanggal_pengajuan ? $p->tanggal_pengajuan->format('d M Y') : '-' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-{{ $p->status_badge }} rounded-pill px-3">
                                    {{ $p->status_label }}
                                </span>
                            </td>
                            <td>
                                @if($p->foto_kerusakan)
                                    <a href="{{ asset('storage/' . $p->foto_kerusakan) }}" target="_blank" class="btn btn-sm btn-outline-info rounded-pill py-1 px-3">
                                        <i class="fas fa-image me-1"></i>Lihat Foto
                                    </a>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('perbaikan.show', $p->id) }}"
                                   class="btn btn-info btn-sm rounded-circle text-white border-0" 
                                    style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"
                                    title="Lihat">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                Belum ada data pengajuan perbaikan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            <div class="mt-3 d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Menampilkan {{ $pengajuans->firstItem() ?? 0 }} sampai {{ $pengajuans->lastItem() ?? 0 }} dari {{ $pengajuans->total() }} data
                </div>
                <div>
                    {{ $pengajuans->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            </div>

        </div>
    </div>

</div>

@push('scripts')
<script>
// Auto-refresh
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            if (this.value.trim() === '') {
                this.form.submit();
            }
        });
        searchInput.addEventListener('search', function() {
            if (this.value.trim() === '') {
                this.form.submit();
            }
        });
    }
});
</script>
@endpush

@endsection
