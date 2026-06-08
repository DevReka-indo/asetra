@extends('layouts.app')

@section('title', 'Mengelola Lokasi Aset')

@section('content')
<div class="container-fluid px-1 py-0 mt-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0">Mengelola Lokasi Aset</h3>
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
                <span class="text-muted" style="font-size: 14px; font-weight: 500; position: relative; top: 2px;">Mengelola Lokasi Aset</span>
            </li>
        </ul>
    </div>

    {{-- FILTER --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('lokasi-aset.index') }}" class="row g-2 align-items-end">
                {{-- Entries --}}
                <div class="col-md-1">
                    <label class="form-label fw-bold small text-muted text-uppercase" style="font-size: 0.7rem;">Entries</label>
                    <select name="per_page" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
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
                        <input type="search" name="search" class="form-control border-0 shadow-none bg-transparent" placeholder="Cari kode, nama, atau detail..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-auto ms-auto">
                    <div class="d-flex gap-2">

                        {{-- Import --}}
                        <button type="button" class="btn btn-warning px-4 rounded-3 d-flex align-items-center text-dark" title="Import Data"
                            data-bs-toggle="modal" data-bs-target="#modalImportLokasi">
                            <i class="fas fa-file-import me-1"></i> Import
                        </button>

                        {{-- Export --}}
                        <a href="{{ route('lokasi-aset.export', request()->query()) }}" class="btn btn-success px-4 rounded-3 d-flex align-items-center text-white" title="Export Data">
                            <i class="fas fa-file-excel me-1"></i> Export
                        </a>

                        {{-- Tombol Tambah --}}
                        <button type="button" class="btn btn-primary px-4 rounded-3 d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#modalTambah">
                            <i class="fas fa-plus me-1"></i> Tambah Lokasi Aset
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            @php
                $sortBy = request('sort_by', 'nama_lokasi');
                $orderBy = request('order_by', 'asc');
            @endphp
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="60" class="text-center">No</th>
                            <th>
                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'nama_lokasi', 'order_by' => ($sortBy == 'nama_lokasi' && $orderBy == 'asc') ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark d-flex align-items-center justify-content-between">
                                    Nama Lokasi
                                    <span class="d-inline-block position-relative ms-2" style="width: 20px; height: 18px; vertical-align: middle;">
                                        <span style="position: absolute; right: 10px; bottom: 0.1em; font-size: 15px; font-weight: normal; opacity: {{ ($sortBy == 'nama_lokasi' && $orderBy == 'asc') ? '0.9' : '0.3' }}; color: {{ ($sortBy == 'nama_lokasi' && $orderBy == 'asc') ? '#253070' : '#888888' }};">↑</span>
                                        <span style="position: absolute; right: 2px; bottom: 0.1em; font-size: 15px; font-weight: normal; opacity: {{ ($sortBy == 'nama_lokasi' && $orderBy == 'desc') ? '0.9' : '0.3' }}; color: {{ ($sortBy == 'nama_lokasi' && $orderBy == 'desc') ? '#253070' : '#888888' }};">↓</span>
                                    </span>
                                </a>
                            </th>
                            <th>
                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'kode_lokasi', 'order_by' => ($sortBy == 'kode_lokasi' && $orderBy == 'asc') ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark d-flex align-items-center justify-content-between">
                                    Kode Lokasi
                                    <span class="d-inline-block position-relative ms-2" style="width: 20px; height: 18px; vertical-align: middle;">
                                        <span style="position: absolute; right: 10px; bottom: 0.1em; font-size: 15px; font-weight: normal; opacity: {{ ($sortBy == 'kode_lokasi' && $orderBy == 'asc') ? '0.9' : '0.3' }}; color: {{ ($sortBy == 'kode_lokasi' && $orderBy == 'asc') ? '#253070' : '#888888' }};">↑</span>
                                        <span style="position: absolute; right: 2px; bottom: 0.1em; font-size: 15px; font-weight: normal; opacity: {{ ($sortBy == 'kode_lokasi' && $orderBy == 'desc') ? '0.9' : '0.3' }}; color: {{ ($sortBy == 'kode_lokasi' && $orderBy == 'desc') ? '#253070' : '#888888' }};">↓</span>
                                    </span>
                                </a>
                            </th>
                            <th>
                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'detail_lokasi', 'order_by' => ($sortBy == 'detail_lokasi' && $orderBy == 'asc') ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark d-flex align-items-center justify-content-between">
                                    Detail Lokasi
                                    <span class="d-inline-block position-relative ms-2" style="width: 20px; height: 18px; vertical-align: middle;">
                                        <span style="position: absolute; right: 10px; bottom: 0.1em; font-size: 15px; font-weight: normal; opacity: {{ ($sortBy == 'detail_lokasi' && $orderBy == 'asc') ? '0.9' : '0.3' }}; color: {{ ($sortBy == 'detail_lokasi' && $orderBy == 'asc') ? '#253070' : '#888888' }};">↑</span>
                                        <span style="position: absolute; right: 2px; bottom: 0.1em; font-size: 15px; font-weight: normal; opacity: {{ ($sortBy == 'detail_lokasi' && $orderBy == 'desc') ? '0.9' : '0.3' }}; color: {{ ($sortBy == 'detail_lokasi' && $orderBy == 'desc') ? '#253070' : '#888888' }};">↓</span>
                                    </span>
                                </a>
                            </th>
                            <th width="150" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data as $i => $row)
                            <tr>
                                <td class="text-center">{{ $data->firstItem() + $i }}</td>
                                <td>{{ $row->nama_lokasi }}</td>
                                <td class="fw-bold text-primary">{{ $row->kode_lokasi }}</td>
                                <td>{{ $row->detail_lokasi }}</td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center align-items-center gap-2">
                                        {{-- Tombol Detail / View (Info) --}}
                                        <button type="button" class="btn btn-info btn-sm rounded-circle text-white border-0" 
                                            style="width:32px; height:32px; display:flex; align-items:center; justify-content:center;"
                                            title="Lihat Detail" data-bs-toggle="modal" data-bs-target="#modalDetail{{ $row->getKey() }}">
                                            <i class="fas fa-eye"></i>
                                        </button>

                                        {{-- Tombol Edit (Warning) --}}
                                        <button type="button" class="btn btn-warning btn-sm rounded-circle text-white border-0" 
                                            style="width:32px; height:32px; display:flex; align-items:center; justify-content:center;"
                                            title="Edit" data-bs-toggle="modal" data-bs-target="#modalEdit{{ $row->getKey() }}">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        {{-- Tombol Hapus (Danger) --}}
                                        <button type="button" class="btn btn-danger btn-sm rounded-circle text-white border-0" 
                                            style="width:32px; height:32px; display:flex; align-items:center; justify-content:center;"
                                            title="Hapus" data-bs-toggle="modal" data-bs-target="#modalDelete{{ $row->getKey() }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fas fa-info-circle mb-2 d-block fa-2x"></i>
                                    Belum ada data lokasi aset
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4 d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Menampilkan {{ $data->firstItem() ?? 0 }} sampai {{ $data->lastItem() ?? 0 }} dari {{ $data->total() }} data
                </div>
                <div>
                    {{ $data->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            </div>
            
        </div>
    </div>
</div>

{{-- MODAL TAMBAH --}}
<div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('lokasi-aset.store') }}" method="POST" id="formTambahLokasi" class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" autocomplete="off" novalidate>
            @csrf
            <input type="hidden" name="form_type" value="tambah">

            <div class="modal-header border-0 pt-4 px-4 pb-3" style="background-color: #253070;">
                <h5 class="modal-title fw-bold text-white">
                    <i class="fas fa-plus-circle me-2"></i> Tambah Lokasi Aset
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4 bg-light">
                <div class="mb-4">
                    <label class="form-label fw-bold small flex-grow-1" style="color: #253070;">NAMA LOKASI <span class="text-danger">*</span></label>
                    <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-map-marker-alt"></i></span>
                        <input type="text" name="nama_lokasi" class="form-control border-start-0 fs-6 @error('nama_lokasi') is-invalid @enderror" value="{{ old('form_type') == 'tambah' ? old('nama_lokasi') : '' }}" placeholder="Contoh: Ruang Keuangan" required maxlength="100">
                    </div>
                    @if(old('form_type') == 'tambah')
                        @error('nama_lokasi') <div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div> @enderror
                    @endif
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold small flex-grow-1" style="color: #253070;">KODE LOKASI <span class="text-danger">*</span></label>
                    <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-barcode"></i></span>
                        <input type="text" name="kode_lokasi" class="form-control border-start-0 fs-6 @error('kode_lokasi') is-invalid @enderror" value="{{ old('form_type') == 'tambah' ? old('kode_lokasi') : '' }}" placeholder="Contoh: Keu" required maxlength="45"> 
                    </div>
                    @if(old('form_type') == 'tambah')
                        @error('kode_lokasi') <div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div> @enderror
                    @endif
                </div>

                <div class="mb-2">
                    <label class="form-label fw-bold small flex-grow-1" style="color: #253070;">DETAIL LOKASI</label>
                    <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus align-items-start">
                        <span class="input-group-text bg-white border-end-0 text-muted pt-3"><i class="fas fa-align-left"></i></span>
                        <textarea name="detail_lokasi" class="form-control border-start-0 fs-6" rows="3" placeholder="Contoh: Gedung pusat, Lt.1 Ruang Keuangan" maxlength="255">{{ old('form_type') == 'tambah' ? old('detail_lokasi') : '' }}</textarea>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-light border-top-0 pt-3 pb-4 px-4">
                <button type="button" class="btn btn-light rounded-pill px-4 fw-bold shadow-sm border" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn text-white rounded-pill px-4 fw-bold shadow-sm" style="background-color: #253070;">
                    <i class="fas fa-save me-1"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL IMPORT --}}
<div class="modal fade" id="modalImportLokasi" tabindex="-1" aria-labelledby="modalImportLokasiLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('lokasi-aset.import') }}" method="POST" enctype="multipart/form-data"
            class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            @csrf
            
            {{-- Header --}}
            <div class="modal-header border-0 pt-4 px-4 pb-3" style="background-color: #253070;">
                <h5 class="modal-title fw-bold text-white" id="modalImportLokasiLabel">
                    <i class="fas fa-file-import me-2"></i> Import Lokasi Aset
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- Body --}}
            <div class="modal-body p-4 bg-light">
                {{-- Panduan / Download Template --}}
                <div class="alert alert-info border-0 shadow-sm rounded-3 mb-4 d-flex align-items-start gap-2" style="background-color: #eef2fa; color: #253070;">
                    <i class="fas fa-info-circle fa-lg mt-1"></i>
                    <div>
                        <span class="fw-bold">Petunjuk Import:</span>
                        <p class="small mb-2 text-muted">
                            Gunakan template Excel standar yang disediakan. Pastikan data yang dimasukkan memenuhi kriteria berikut:
                        </p>
                        <ul class="mb-3 small text-muted ps-3">
                            <li>Baris pertama sebagai Judul (Heading Row).</li>
                            <li>Kolom A: <strong>nama_lokasi</strong> (wajib, unik, maks 100 karakter)</li>
                            <li>Kolom B: <strong>kode_lokasi</strong> (wajib, unik, maks 45 karakter)</li>
                            <li>Kolom C: <strong>detail_lokasi</strong> (opsional, maks 255 karakter)</li>
                        </ul>
                        <a href="{{ route('lokasi-aset.template') }}" class="btn btn-sm btn-navy text-white rounded-pill px-3 py-1 fw-bold border-0 shadow-sm" style="background-color: #253070;">
                            <i class="fas fa-download me-1"></i> Unduh Template Excel
                        </a>
                    </div>
                </div>

                {{-- File Input --}}
                <div class="mb-3">
                    <label class="form-label fw-bold small text-uppercase" style="color: #253070; font-size: 0.72rem;">
                        <i class="fas fa-file-excel me-1"></i> File Excel (.xlsx, .xls, .csv) <span class="text-danger">*</span>
                    </label>
                    <div class="input-group shadow-sm rounded-3 input-group-focus align-items-center bg-white border">
                        <span class="input-group-text bg-white border-0 text-muted"><i class="fas fa-upload"></i></span>
                        <input type="file" class="form-control border-0 shadow-none fs-6 bg-white" name="file" accept=".xlsx,.xls,.csv" required>
                    </div>
                    <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">Maksimal ukuran file adalah 2MB.</small>
                </div>
            </div>

            {{-- Footer --}}
            <div class="modal-footer bg-light border-top-0 pt-2 pb-4 px-4">
                <button type="button" class="btn btn-light rounded-pill px-4 fw-bold shadow-sm border" data-bs-dismiss="modal">
                    Batal
                </button>
                <button type="submit" class="btn text-white rounded-pill px-4 fw-bold shadow-sm" style="background-color: #253070;">
                    <i class="fas fa-upload me-1"></i> Import Data
                </button>
            </div>
        </form>
    </div>
</div>

@foreach($data as $row)
    <!-- Modal Detail -->
    <div class="modal fade" id="modalDetail{{ $row->getKey() }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header border-0 pt-4 px-4 pb-3" style="background-color: #253070;">
                    <h5 class="modal-title fw-bold text-white">
                        <i class="fas fa-info-circle me-2"></i> Detail Lokasi
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 bg-light">
                    <div class="bg-white rounded-4 p-4 shadow-sm border-0">
                        <div class="row align-items-center mb-3">
                            <div class="col-5 text-muted small text-uppercase fw-bold"><i class="fas fa-barcode me-2 text-primary"></i>Kode Lokasi</div>
                            <div class="col-7">
                                <span class="badge bg-primary px-3 py-2 fs-6 shadow-sm rounded-pill">{{ $row->kode_lokasi }}</span>
                            </div>
                        </div>
                        <hr class="text-muted opacity-25">
                        <div class="row align-items-center mb-3">
                            <div class="col-5 text-muted small text-uppercase fw-bold"><i class="fas fa-map-marker-alt me-2 text-primary"></i>Nama Lokasi</div>
                            <div class="col-7 fw-bold text-dark fs-6">{{ $row->nama_lokasi }}</div>
                        </div>
                        <hr class="text-muted opacity-25">
                        <div class="row align-items-start">
                            <div class="col-5 text-muted small text-uppercase fw-bold pt-1"><i class="fas fa-align-left me-2 text-primary"></i>Detail Lokasi</div>
                            <div class="col-7 text-dark fw-medium">{{ $row->detail_lokasi ?? 'Tidak ada keterangan detail' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit -->
    <div class="modal fade" id="modalEdit{{ $row->getKey() }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('lokasi-aset.update', $row->getKey()) }}" method="POST" id="formEditLokasi{{ $row->getKey() }}" class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" autocomplete="off" novalidate>
                @csrf
                @method('PUT')
                <input type="hidden" name="form_type" value="edit_{{ $row->getKey() }}">

                <div class="modal-header border-0 pt-4 px-4 pb-3" style="background-color: #253070;">
                    <h5 class="modal-title fw-bold text-white">
                        <i class="fas fa-edit me-2"></i> Edit Lokasi Aset
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4 bg-light">
                    <div class="mb-4">
                        <label class="form-label fw-bold small flex-grow-1" style="color: #253070;">KODE LOKASI <span class="text-danger">*</span></label>
                        <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-barcode"></i></span>
                            <input type="text" name="kode_lokasi" class="form-control border-start-0 fs-6 @error('kode_lokasi') is-invalid @enderror" value="{{ old('form_type') == 'edit_'.$row->getKey() ? old('kode_lokasi') : $row->kode_lokasi }}" required maxlength="45"> 
                        </div>
                        @if(old('form_type') == 'edit_'.$row->getKey())
                            @error('kode_lokasi') <div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div> @enderror
                        @endif
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small flex-grow-1" style="color: #253070;">NAMA LOKASI <span class="text-danger">*</span></label>
                        <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-map-marker-alt"></i></span>
                            <input type="text" name="nama_lokasi" class="form-control border-start-0 fs-6 @error('nama_lokasi') is-invalid @enderror" value="{{ old('form_type') == 'edit_'.$row->getKey() ? old('nama_lokasi') : $row->nama_lokasi }}" required maxlength="100">
                        </div>
                        @if(old('form_type') == 'edit_'.$row->getKey())
                            @error('nama_lokasi') <div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div> @enderror
                        @endif
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-bold small flex-grow-1" style="color: #253070;">DETAIL LOKASI</label>
                        <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus align-items-start">
                            <span class="input-group-text bg-white border-end-0 text-muted pt-3"><i class="fas fa-align-left"></i></span>
                            <textarea name="detail_lokasi" class="form-control border-start-0 fs-6" rows="3" maxlength="255">{{ old('form_type') == 'edit_'.$row->getKey() ? old('detail_lokasi') : $row->detail_lokasi }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light border-top-0 pt-3 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold shadow-sm border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn text-white rounded-pill px-4 fw-bold shadow-sm" style="background-color: #253070;">
                        <i class="fas fa-save me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Hapus -->
    <div class="modal fade" id="modalDelete{{ $row->getKey() }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-body p-5 text-center bg-light">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-4 mb-4" style="width: 80px; height: 80px; background-color: #f1f3f5;">
                        <i class="fas fa-exclamation-triangle fa-3x text-danger"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-2">Konfirmasi Hapus</h4>
                    <p class="text-muted mb-4" style="font-size: 1rem;">
                        Anda yakin ingin menghapus data <br>
                        <strong class="text-danger fs-5">{{ $row->kode_lokasi }} - {{ $row->nama_lokasi }}</strong>?
                    </p>
                    <div class="d-flex justify-content-center gap-3">
                        <form action="{{ route('lokasi-aset.destroy', $row->getKey()) }}" method="POST" class="w-100 d-flex justify-content-center gap-3">
                            @csrf 
                            @method('DELETE')
                            <button type="button" class="btn btn-light rounded-pill fw-bold py-2 shadow-sm border" style="width: 120px;" data-bs-dismiss="modal">Batalkan</button>
                            <button type="submit" class="btn btn-danger rounded-pill fw-bold py-2 shadow-sm" style="width: 140px;">Ya, Hapus</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endforeach

@endsection

@push('scripts')
    <script>
        window.addEventListener('load', function() {
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
            
            const swalConfig = {
                showConfirmButton: true,
                confirmButtonText: 'OK',
                confirmButtonColor: '#253070',
                customClass: { popup: 'rounded-4 shadow' }
            };

            @if (session('success'))
                Swal.fire({ ...swalConfig, icon: 'success', title: 'Berhasil!', text: '{{ session('success') }}' });
            @endif

            @if (session('error'))
                Swal.fire({ ...swalConfig, icon: 'error', title: 'Gagal!', text: '{{ session('error') }}' });
            @endif

            @if (session('warning'))
                Swal.fire({ ...swalConfig, icon: 'warning', title: 'Perhatian!', text: '{{ session('warning') }}' });
            @endif

            @if($errors->any())
                setTimeout(function() {
                    const formType = "{{ old('form_type') }}";
                    
                    if (formType === 'tambah') {
                        var modalTambah = new bootstrap.Modal(document.getElementById('modalTambah'));
                        modalTambah.show();
                    } else if (formType.startsWith('edit_')) {
                        const id = formType.split('_')[1];
                        var modalEdit = new bootstrap.Modal(document.getElementById('modalEdit' + id));
                        modalEdit.show();
                    }
                }, 200); 
            @endif

            const modalTambahEl = document.getElementById('modalTambah');
            if (modalTambahEl) {
                modalTambahEl.addEventListener('hidden.bs.modal', function () {
                    const form = this.querySelector('form');
                    if (form) {
                        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                        form.reset(); // Mengembalikan input ke kosong
                    }
                });
            }

            document.querySelectorAll('[id^="modalEdit"]').forEach(function(modalEditEl) {
                modalEditEl.addEventListener('hidden.bs.modal', function () {
                    const form = this.querySelector('form');
                    if (form) {
                        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                    }
                });
            });

            // Client-side Validation on Submit
            const formsToValidate = document.querySelectorAll('#formTambahLokasi, form[id^="formEditLokasi"]');
            formsToValidate.forEach(form => {
                form.addEventListener('submit', function(e) {
                    // Clear any old custom error messages and styles
                    form.querySelectorAll('.invalid-feedback-custom').forEach(el => el.remove());
                    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                    form.querySelectorAll('.input-group').forEach(el => {
                        el.classList.remove('border', 'border-danger');
                    });

                    let isValid = true;
                    let firstInvalidEl = null;

                    const fields = form.querySelectorAll('input, select, textarea');
                    fields.forEach(field => {
                        // Try to find the associated label text
                        let labelText = '';
                        const formGroup = field.closest('.mb-2, .mb-4');
                        if (formGroup) {
                            const labelEl = formGroup.querySelector('label');
                            if (labelEl) {
                                labelText = labelEl.textContent.replace('*', '').trim();
                            }
                        }
                        
                        // Fallback to name or placeholder
                        if (!labelText) {
                            labelText = field.getAttribute('placeholder') || field.getAttribute('name') || 'Kolom';
                        }

                        if (field.hasAttribute('required') && (!field.value || (field.tagName === 'SELECT' && field.value === ''))) {
                            isValid = false;
                            
                            // Style input field and group
                            field.classList.add('is-invalid');
                            const inputGroup = field.closest('.input-group');
                            if (inputGroup) {
                                inputGroup.classList.add('border', 'border-danger');
                            }

                            // Create inline error message element
                            const errorDiv = document.createElement('div');
                            errorDiv.className = 'text-danger small mt-1 fw-bold invalid-feedback-custom';
                            errorDiv.innerHTML = `<i class="fas fa-exclamation-circle me-1"></i>${labelText} wajib diisi.`;
                            
                            // Insert error message element
                            const targetAnchor = inputGroup || field;
                            targetAnchor.parentNode.insertBefore(errorDiv, targetAnchor.nextSibling);

                            if (!firstInvalidEl) {
                                firstInvalidEl = targetAnchor;
                            }
                        } else if (field.value) {
                            const maxLen = field.getAttribute('maxlength');
                            if (maxLen && field.value.length > parseInt(maxLen)) {
                                isValid = false;
                                
                                // Style input field and group
                                field.classList.add('is-invalid');
                                const inputGroup = field.closest('.input-group');
                                if (inputGroup) {
                                    inputGroup.classList.add('border', 'border-danger');
                                }

                                // Create inline error message element
                                const errorDiv = document.createElement('div');
                                errorDiv.className = 'text-danger small mt-1 fw-bold invalid-feedback-custom';
                                errorDiv.innerHTML = `<i class="fas fa-exclamation-circle me-1"></i>${labelText} tidak boleh lebih dari ${maxLen} karakter.`;
                                
                                // Insert error message element
                                const targetAnchor = inputGroup || field;
                                targetAnchor.parentNode.insertBefore(errorDiv, targetAnchor.nextSibling);

                                if (!firstInvalidEl) {
                                    firstInvalidEl = targetAnchor;
                                }
                            }
                        }
                    });

                    if (!isValid) {
                        e.preventDefault();
                        if (firstInvalidEl) {
                            firstInvalidEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    }
                });
            });

        });
    </script>
@endpush