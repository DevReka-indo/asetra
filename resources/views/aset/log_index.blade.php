@extends('layouts.app')

@section('title', 'Laporan Monitoring Aset')

@section('content')
<div class="container-fluid px-1 py-0 mt-0">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-4">
        <h3 class="fw-bold mb-0">Riwayat Monitoring Aset</h3>
        <ul class="breadcrumbs d-flex align-items-center p-0 m-0" style="list-style: none;"> 
            <li class="nav-home d-flex align-items-center">
                <a href="{{ route('dashboard') }}" class="text-muted text-decoration-none d-flex align-items-center">
                    <i class="fas fa-home me-2" style="font-size: 15px;"></i>
                    <span style="font-size: 14px; font-weight: 500; position: relative; top: 2px;">Dashboard</span>                    
                </a>                
            </li>
            <li class="separator text-muted d-flex align-items-center px-2">
                <span style="font-size: 14px; position: relative; top: 2px;">-</span>
            </li>
            <li class="nav-item d-flex align-items-center">
                <span class="text-muted" style="font-size: 14px; font-weight: 500; position: relative; top: 2px;">
                    Riwayat Monitoring
                </span>
            </li>
        </ul>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('log-aset.index') }}">
                <input type="hidden" name="sort_by" value="{{ request('sort_by') }}">
                <input type="hidden" name="order_by" value="{{ request('order_by') }}">
                
                <div class="row g-2 align-items-end">
                    {{-- Entries --}}
                    <div class="col-6 col-sm-auto mb-2 mb-sm-0">
                        <label class="form-label fw-bold small text-muted text-uppercase" style="font-size: 0.7rem;">Entries</label>
                        <select name="per_page" class="form-select form-select-sm rounded-3 w-100" style="min-width: 75px;" onchange="this.form.submit()">
                            <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>

                    {{-- Filter Kondisi --}}
                    <div class="col-6 col-sm-auto mb-2 mb-sm-0">
                        <label class="form-label fw-bold small text-muted text-uppercase" style="font-size: 0.7rem;">Kondisi Aset</label>
                        <select name="kondisi" class="form-select form-select-sm rounded-3 w-100" onchange="this.form.submit()">
                            <option value="">Semua Kondisi</option>
                            <option value="Baik" {{ request('kondisi') == 'Baik' ? 'selected' : '' }}>Baik</option>
                            <option value="Rusak" {{ request('kondisi') == 'Rusak' ? 'selected' : '' }}>Rusak</option>
                            <option value="Bongkar" {{ request('kondisi') == 'Bongkar' ? 'selected' : '' }}>Bongkar</option>
                            <option value="Tidak Terpakai" {{ request('kondisi') == 'Tidak Terpakai' ? 'selected' : '' }}>Tidak Terpakai</option>
                            <option value="Hilang" {{ request('kondisi') == 'Hilang' ? 'selected' : '' }}>Hilang</option>
                            <option value="Tidak Teridentifikasi" {{ request('kondisi') == 'Tidak Teridentifikasi' ? 'selected' : '' }}>Tidak Teridentifikasi</option>
                        </select>
                    </div>

                    {{-- Filter Status --}}
                    <div class="col-6 col-sm-auto mb-2 mb-sm-0">
                        <label class="form-label fw-bold small text-muted text-uppercase" style="font-size: 0.7rem;">Status Aset</label>
                        <select name="status_aset" class="form-select form-select-sm rounded-3 w-100" onchange="this.form.submit()">
                            <option value="">Semua Status</option>
                            <option value="Aktif" {{ request('status_aset') == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="Tidak Aktif" {{ request('status_aset') == 'Tidak Aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                            <option value="Dalam Perbaikan" {{ request('status_aset') == 'Dalam Perbaikan' ? 'selected' : '' }}>Dalam Perbaikan</option>
                            <option value="Dipinjam" {{ request('status_aset') == 'Dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                            <option value="Hilang" {{ request('status_aset') == 'Hilang' ? 'selected' : '' }}>Hilang</option>
                        </select>
                    </div>

                    {{-- Filter Lokasi --}}
                    @if(auth()->user()->role_id_role == 1 || auth()->user()->isBagianUmum())
                    <div class="col-6 col-sm-auto mb-2 mb-sm-0">
                        <label class="form-label fw-bold small text-muted text-uppercase" style="font-size: 0.7rem;">Lokasi Aset</label>
                        <select name="lokasi" class="form-select form-select-sm rounded-3 w-100" onchange="this.form.submit()">
                            <option value="">Semua Lokasi</option>
                            @foreach($lokasis as $lokasi)
                                <option value="{{ $lokasi->lokasi_id }}" {{ request('lokasi') == $lokasi->lokasi_id ? 'selected' : '' }}>{{ $lokasi->nama_lokasi ?? $lokasi->nm_lokasi_aset }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    {{-- Pencarian --}}
                    <div class="col-12 col-sm-4 col-md-3 mb-2 mb-sm-0 ms-sm-auto">
                        <label class="form-label fw-bold small text-muted text-uppercase" style="font-size: 0.7rem;">Pencarian</label>
                        <div class="input-group input-group-sm input-group-focus rounded-3">
                            <span class="input-group-text bg-white border-0 text-muted"><i class="fas fa-search"></i></span>
                            <input type="text" name="search" class="form-control border-0 shadow-none bg-transparent" placeholder="Cari nomor atau nama aset..." value="{{ request('search') }}">
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            @php
                $sortBy = request('sort_by', 'created_at');
                $orderBy = request('order_by', 'desc');
            @endphp
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 d-none d-md-table-cell">No</th>
                            <th>
                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'tanggal_cek', 'order_by' => ($sortBy == 'tanggal_cek' && $orderBy == 'asc') ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark d-flex align-items-center justify-content-between">
                                    Tanggal Pengecekan
                                    <span class="d-inline-block position-relative ms-2" style="width: 20px; height: 18px; vertical-align: middle;">
                                        <span style="position: absolute; right: 10px; bottom: 0.1em; font-size: 15px; font-weight: normal; opacity: {{ ($sortBy == 'tanggal_cek' && $orderBy == 'asc') ? '0.9' : '0.3' }}; color: {{ ($sortBy == 'tanggal_cek' && $orderBy == 'asc') ? '#253070' : '#888888' }};">↑</span>
                                        <span style="position: absolute; right: 2px; bottom: 0.1em; font-size: 15px; font-weight: normal; opacity: {{ ($sortBy == 'tanggal_cek' && $orderBy == 'desc') ? '0.9' : '0.3' }}; color: {{ ($sortBy == 'tanggal_cek' && $orderBy == 'desc') ? '#253070' : '#888888' }};">↓</span>
                                    </span>
                                </a>
                            </th>
                            <th>
                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'nama_aset', 'order_by' => ($sortBy == 'nama_aset' && $orderBy == 'asc') ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark d-flex align-items-center justify-content-between">
                                    Identitas Aset
                                    <span class="d-inline-block position-relative ms-2" style="width: 20px; height: 18px; vertical-align: middle;">
                                        <span style="position: absolute; right: 10px; bottom: 0.1em; font-size: 15px; font-weight: normal; opacity: {{ ($sortBy == 'nama_aset' && $orderBy == 'asc') ? '0.9' : '0.3' }}; color: {{ ($sortBy == 'nama_aset' && $orderBy == 'asc') ? '#253070' : '#888888' }};">↑</span>
                                        <span style="position: absolute; right: 2px; bottom: 0.1em; font-size: 15px; font-weight: normal; opacity: {{ ($sortBy == 'nama_aset' && $orderBy == 'desc') ? '0.9' : '0.3' }}; color: {{ ($sortBy == 'nama_aset' && $orderBy == 'desc') ? '#253070' : '#888888' }};">↓</span>
                                    </span>
                                </a>
                            </th>
                            <th>
                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'kondisi', 'order_by' => ($sortBy == 'kondisi' && $orderBy == 'asc') ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark d-flex align-items-center justify-content-between">
                                    Kondisi Fisik & Status
                                    <span class="d-inline-block position-relative ms-2" style="width: 20px; height: 18px; vertical-align: middle;">
                                        <span style="position: absolute; right: 10px; bottom: 0.1em; font-size: 15px; font-weight: normal; opacity: {{ ($sortBy == 'kondisi' && $orderBy == 'asc') ? '0.9' : '0.3' }}; color: {{ ($sortBy == 'kondisi' && $orderBy == 'asc') ? '#253070' : '#888888' }};">↑</span>
                                        <span style="position: absolute; right: 2px; bottom: 0.1em; font-size: 15px; font-weight: normal; opacity: {{ ($sortBy == 'kondisi' && $orderBy == 'desc') ? '0.9' : '0.3' }}; color: {{ ($sortBy == 'kondisi' && $orderBy == 'desc') ? '#253070' : '#888888' }};">↓</span>
                                    </span>
                                </a>
                            </th>
                            <th class="d-none d-md-table-cell">
                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'nama_lokasi', 'order_by' => ($sortBy == 'nama_lokasi' && $orderBy == 'asc') ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark d-flex align-items-center justify-content-between">
                                    Lokasi & Penempatan
                                    <span class="d-inline-block position-relative ms-2" style="width: 20px; height: 18px; vertical-align: middle;">
                                        <span style="position: absolute; right: 10px; bottom: 0.1em; font-size: 15px; font-weight: normal; opacity: {{ ($sortBy == 'nama_lokasi' && $orderBy == 'asc') ? '0.9' : '0.3' }}; color: {{ ($sortBy == 'nama_lokasi' && $orderBy == 'asc') ? '#253070' : '#888888' }};">↑</span>
                                        <span style="position: absolute; right: 2px; bottom: 0.1em; font-size: 15px; font-weight: normal; opacity: {{ ($sortBy == 'nama_lokasi' && $orderBy == 'desc') ? '0.9' : '0.3' }}; color: {{ ($sortBy == 'nama_lokasi' && $orderBy == 'desc') ? '#253070' : '#888888' }};">↓</span>
                                    </span>
                                </a>
                            </th>
                            <th class="d-none d-lg-table-cell">
                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'dicatat_oleh_name', 'order_by' => ($sortBy == 'dicatat_oleh_name' && $orderBy == 'asc') ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark d-flex align-items-center justify-content-between">
                                    Pelapor
                                    <span class="d-inline-block position-relative ms-2" style="width: 20px; height: 18px; vertical-align: middle;">
                                        <span style="position: absolute; right: 10px; bottom: 0.1em; font-size: 15px; font-weight: normal; opacity: {{ ($sortBy == 'dicatat_oleh_name' && $orderBy == 'asc') ? '0.9' : '0.3' }}; color: {{ ($sortBy == 'dicatat_oleh_name' && $orderBy == 'asc') ? '#253070' : '#888888' }};">↑</span>
                                        <span style="position: absolute; right: 2px; bottom: 0.1em; font-size: 15px; font-weight: normal; opacity: {{ ($sortBy == 'dicatat_oleh_name' && $orderBy == 'desc') ? '0.9' : '0.3' }}; color: {{ ($sortBy == 'dicatat_oleh_name' && $orderBy == 'desc') ? '#253070' : '#888888' }};">↓</span>
                                    </span>
                                </a>
                            </th>
                            <th class="d-none d-md-table-cell">Catatan & Perubahan</th>
                            <th class="text-center pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $index => $log)
                        <tr>
                            <td class="ps-4 text-muted d-none d-md-table-cell">{{ $logs->firstItem() + $index }}</td>
                            <td>
                                <span class="fw-semibold text-dark">{{ \Carbon\Carbon::parse($log->tanggal_cek)->format('d M Y') }}</span><br>
                                <small class="text-muted">{{ $log->created_at->format('H:i') }} WIB</small>
                            </td>
                            <td>
                                @if($log->aset)
                                    <span class="d-block fw-bold text-navy">{{ $log->aset->nama_aset }}</span>
                                    <small class="text-muted">{{ $log->aset->nomor_aset }}</small>
                                @else
                                    <span class="text-danger">Aset Dihapus</span>
                                @endif
                            </td>
                            <td>
                                @if($log->kondisi == 'Baik')
                                    <span class="badge bg-success rounded-pill px-3">{{ $log->kondisi }}</span>
                                @elseif(in_array($log->kondisi, ['Rusak', 'Hilang']))
                                    <span class="badge bg-danger rounded-pill px-3">{{ $log->kondisi }}</span>
                                @else
                                    <span class="badge bg-warning text-dark rounded-pill px-3">{{ $log->kondisi }}</span>
                                @endif
                                
                                @if($log->status_aset)
                                    <small class="d-block mt-1 text-muted"><i class="fas fa-tag me-1 text-secondary"></i>{{ $log->status_aset }}</small>
                                  @endif
                            </td>
                            <td class="d-none d-md-table-cell">
                                @if($log->lokasi || $log->organisasi_terikat !== 'Tanpa Organisasi')
                                    <small class="d-block text-dark fw-bold"><i class="fas fa-map-marker-alt text-danger me-1"></i>{{ $log->lokasi->nama_lokasi ?? '-' }}</small>
                                    <small class="d-block text-muted"><i class="fas fa-sitemap text-secondary me-1"></i>{{ $log->organisasi_terikat }}</small>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="d-none d-lg-table-cell">
                                <span class="badge bg-primary rounded-pill px-3">
                                    {{ $log->dicatatOleh->firstname ?? 'System' }} {{ $log->dicatatOleh->lastname ?? '' }}
                                </span>
                            </td>
                            <td class="d-none d-md-table-cell">
                                @if($log->flag_perubahan == 'Pengecekan Rutin')
                                    <span class="badge bg-light text-success border border-success rounded-pill px-2 py-1 mb-1" style="font-weight: 500; font-size: 0.7rem;"><i class="fas fa-check-circle me-1"></i>Pengecekan Rutin</span>
                                @elseif($log->flag_perubahan)
                                    <span class="badge bg-light text-danger border border-danger rounded-pill px-2 py-1 mb-1" style="font-weight: 500; font-size: 0.7rem;"><i class="fas fa-exclamation-circle me-1"></i>{{ $log->flag_perubahan }}</span>
                                @endif
                                @if($log->keterangan)
                                    <p class="text-muted small mb-0 mt-1">{{ $log->keterangan }}</p>
                                @endif
                            </td>
                            <td class="text-center pe-4">
                                @if($log->aset)
                                    <div class="d-flex justify-content-center align-items-center gap-2">
                                        <a href="{{ route('aset.show', $log->aset->id) }}" 
                                        class="btn btn-info btn-sm rounded-circle text-white border-0" 
                                        style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"
                                        title="Lihat">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                    </div>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="fas fa-clipboard-list fa-3x mb-3 text-light"></i><br>
                                Belum ada riwayat monitoring yang tercatat.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{-- Pagination --}}
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 px-4 py-3 border-top bg-light">
                <div class="text-muted small text-center text-md-start">
                    Menampilkan {{ $logs->firstItem() ?? 0 }} sampai {{ $logs->lastItem() ?? 0 }} dari {{ $logs->total() }} catatan
                </div>
                <div class="d-flex justify-content-center">
                    {{ $logs->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-refresh when search input is cleared
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
@endsection
