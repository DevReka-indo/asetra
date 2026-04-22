@extends('layouts.app')

@section('title', 'Laporan Monitoring Aset')

@section('content')
<div class="container-fluid px-1 py-0 mt-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0">Riwayat Monitoring Aset</h3>
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
                            <input type="text" name="search" class="form-control border-0 shadow-none bg-transparent" placeholder="Cari nomor atau nama aset..." value="{{ request('search') }}">
                        </div>
                    </div>

                    {{-- Filter Kondisi --}}
                    <div class="col-md-2">
                        <label class="form-label fw-bold small text-muted text-uppercase" style="font-size: 0.7rem;">Kondisi Aset</label>
                        <select name="kondisi" class="form-select form-select-sm rounded-3 w-100" onchange="this.form.submit()">
                            <option value="">Semua Kondisi</option>
                            <option value="Baik" {{ request('kondisi') == 'Baik' ? 'selected' : '' }}>Baik</option>
                            <option value="Rusak" {{ request('kondisi') == 'Rusak' ? 'selected' : '' }}>Rusak</option>
                            <option value="Bongkar" {{ request('kondisi') == 'Bongkar' ? 'selected' : '' }}>Bongkar</option>
                            <option value="Tidak Terpakai" {{ request('kondisi') == 'Tidak Terpakai' ? 'selected' : '' }}>Tidak Terpakai</option>
                            <option value="Hilang" {{ request('kondisi') == 'Hilang' ? 'selected' : '' }}>Hilang</option>
                            <option value="Lainnya" {{ request('kondisi') == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                    </div>

                    {{-- Filter Status --}}
                    <div class="col-md-2">
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
                    <div class="col-md-2">
                        <label class="form-label fw-bold small text-muted text-uppercase" style="font-size: 0.7rem;">Lokasi Aset</label>
                        <select name="lokasi" class="form-select form-select-sm rounded-3 w-100" onchange="this.form.submit()">
                            <option value="">Semua Lokasi</option>
                            @foreach($lokasis as $lokasi)
                                <option value="{{ $lokasi->lokasi_id }}" {{ request('lokasi') == $lokasi->lokasi_id ? 'selected' : '' }}>{{ $lokasi->nama_lokasi ?? $lokasi->nm_lokasi_aset }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Tombol Reset --}}
                    <div class="col-auto ms-auto d-flex gap-2">
                        <a href="{{ route('log-aset.index') }}" class="btn px-4 rounded-3 d-flex align-items-center text-white" style="background-color: #1b53a7; border-color: #48abf7;" title="Reset Filter">
                            <i class="fas fa-undo me-1"></i> Reset
                        </a>
                    </div>
                </div>

            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">No</th>
                            <th>Tanggal Pengecekan</th>
                            <th>Identitas Aset</th>
                            <th>Kondisi Fisik & Status</th>
                            <th>Lokasi & Penempatan</th>
                            <th>Pelapor</th>
                            <th>Keterangan</th>
                            <th class="text-center pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $index => $log)
                        <tr>
                            <td class="ps-4 text-muted">{{ $logs->firstItem() + $index }}</td>
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
                            <td>
                                @if($log->lokasi || $log->organisasi_terikat !== 'Tanpa Organisasi')
                                    <small class="d-block text-dark fw-bold"><i class="fas fa-map-marker-alt text-danger me-1"></i>{{ $log->lokasi->nama_lokasi ?? '-' }}</small>
                                    <small class="d-block text-muted"><i class="fas fa-sitemap text-secondary me-1"></i>{{ $log->organisasi_terikat }}</small>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-primary rounded-pill px-3">
                                    {{ $log->dicatatOleh->firstname ?? 'System' }} {{ $log->dicatatOleh->lastname ?? '' }}
                                </span>
                            </td>
                            <td>
                                <p class="text-muted small mb-0">{{ $log->keterangan ?? 'Tanpa catatan' }}</p>
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
            <div class="d-flex justify-content-between align-items-center px-4 py-3 border-top bg-light">
                <div class="text-muted small">
                    Menampilkan {{ $logs->firstItem() ?? 0 }} sampai {{ $logs->lastItem() ?? 0 }} dari {{ $logs->total() }} catatan
                </div>
                <div>
                    {{ $logs->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
