@extends('layouts.app')

@section('title', 'Mengelola Jenis Kategori Aset')

@section('content')
<!-- Pickr CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@simonwep/pickr/dist/themes/nano.min.css"/>

<div class="container-fluid px-1 py-0 mt-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0">Mengelola Jenis Kategori Aset</h3>
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
                <span class="text-muted" style="font-size: 14px; font-weight: 500; position: relative; top: 2px;">Mengelola Jenis Kategori Aset</span>
            </li>
        </ul>
    </div>

    {{-- FILTER & TOOLBAR --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('jenis-kategori.index') }}" class="row g-2 align-items-end">
                <div class="col-md-1">
                    <label class="form-label fw-bold small text-muted text-uppercase" style="font-size: 0.7rem;">Entries</label>
                    <select name="per_page" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                        <option value="10"  {{ request('per_page') == 10  ? 'selected' : '' }}>10</option>
                        <option value="25"  {{ request('per_page') == 25  ? 'selected' : '' }}>25</option>
                        <option value="50"  {{ request('per_page') == 50  ? 'selected' : '' }}>50</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small text-muted text-uppercase" style="font-size: 0.7rem;">Pencarian</label>
                    <div class="input-group input-group-sm input-group-focus rounded-3">
                        <span class="input-group-text bg-white border-0 text-muted"><i class="fas fa-search"></i></span>
                        <input type="search" name="search" class="form-control border-0 shadow-none bg-transparent"
                            placeholder="Cari kode awalan atau nama jenis..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-auto ms-auto">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-warning px-4 rounded-3 d-flex align-items-center text-dark"
                            data-bs-toggle="modal" data-bs-target="#modalImportJenis">
                            <i class="fas fa-file-import me-1"></i> Import
                        </button>
                        <a href="{{ route('jenis-kategori.export', request()->query()) }}" class="btn btn-success px-4 rounded-3 d-flex align-items-center text-white" title="Export Data">
                            <i class="fas fa-file-excel me-1"></i> Export
                        </a>
                        <button type="button" class="btn btn-primary px-4 rounded-3 d-flex align-items-center"
                            data-bs-toggle="modal" data-bs-target="#modalTambahJenis">
                            <i class="fas fa-plus me-1"></i> Tambah Jenis Kategori
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- TABEL DATA --}}
    <div class="card shadow-sm border-0">
         <div class="card-body">
            @php
                $sortBy = request('sort_by', 'nama_jenis');
                $orderBy = request('order_by', 'asc');
            @endphp
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="60" class="text-center">No</th>
                            <th width="200">
                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'nama_jenis', 'order_by' => ($sortBy == 'nama_jenis' && $orderBy == 'asc') ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark d-flex align-items-center justify-content-between">
                                    Nama Jenis
                                    <span class="d-inline-block position-relative ms-2" style="width: 20px; height: 18px; vertical-align: middle;">
                                        <span style="position: absolute; right: 10px; bottom: 0.1em; font-size: 15px; font-weight: normal; opacity: {{ ($sortBy == 'nama_jenis' && $orderBy == 'asc') ? '0.9' : '0.3' }}; color: {{ ($sortBy == 'nama_jenis' && $orderBy == 'asc') ? '#253070' : '#888888' }};">↑</span>
                                        <span style="position: absolute; right: 2px; bottom: 0.1em; font-size: 15px; font-weight: normal; opacity: {{ ($sortBy == 'nama_jenis' && $orderBy == 'desc') ? '0.9' : '0.3' }}; color: {{ ($sortBy == 'nama_jenis' && $orderBy == 'desc') ? '#253070' : '#888888' }};">↓</span>
                                    </span>
                                </a>
                            </th>
                            <th width="150">
                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'kode_awalan', 'order_by' => ($sortBy == 'kode_awalan' && $orderBy == 'asc') ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark d-flex align-items-center justify-content-between">
                                    Kode Awalan
                                    <span class="d-inline-block position-relative ms-2" style="width: 20px; height: 18px; vertical-align: middle;">
                                        <span style="position: absolute; right: 10px; bottom: 0.1em; font-size: 15px; font-weight: normal; opacity: {{ ($sortBy == 'kode_awalan' && $orderBy == 'asc') ? '0.9' : '0.3' }}; color: {{ ($sortBy == 'kode_awalan' && $orderBy == 'asc') ? '#253070' : '#888888' }};">↑</span>
                                        <span style="position: absolute; right: 2px; bottom: 0.1em; font-size: 15px; font-weight: normal; opacity: {{ ($sortBy == 'kode_awalan' && $orderBy == 'desc') ? '0.9' : '0.3' }}; color: {{ ($sortBy == 'kode_awalan' && $orderBy == 'desc') ? '#253070' : '#888888' }};">↓</span>
                                    </span>
                                </a>
                            </th>
                            <th width="120" class="text-center">Warna Label</th>
                            <th width="180" class="text-center">
                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'kategori_aset_count', 'order_by' => ($sortBy == 'kategori_aset_count' && $orderBy == 'asc') ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark d-flex align-items-center justify-content-center">
                                    Jumlah Kategori
                                    <span class="d-inline-block position-relative ms-2" style="width: 20px; height: 18px; vertical-align: middle;">
                                        <span style="position: absolute; right: 10px; bottom: 0.1em; font-size: 15px; font-weight: normal; opacity: {{ ($sortBy == 'kategori_aset_count' && $orderBy == 'asc') ? '0.9' : '0.3' }}; color: {{ ($sortBy == 'kategori_aset_count' && $orderBy == 'asc') ? '#253070' : '#888888' }};">↑</span>
                                        <span style="position: absolute; right: 2px; bottom: 0.1em; font-size: 15px; font-weight: normal; opacity: {{ ($sortBy == 'kategori_aset_count' && $orderBy == 'desc') ? '0.9' : '0.3' }}; color: {{ ($sortBy == 'kategori_aset_count' && $orderBy == 'desc') ? '#253070' : '#888888' }};">↓</span>
                                    </span>
                                </a>
                            </th>
                            <th width="150" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data as $i => $item)
                            <tr>
                                <td class="text-center">{{ $data->firstItem() + $i }}</td>
                                <td class="fw-semibold">{{ $item->nama_jenis }}</td>
                                <td>
                                    <span class="fw-bold fs-5 text-primary">{{ $item->kode_awalan }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="d-inline-block rounded-circle shadow-sm border" style="width: 24px; height: 24px; background-color: {{ $item->warna_label ?? '#FF5E9B' }};" title="{{ $item->warna_label ?? '#FF5E9B' }}"></div>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('kategori-aset.index', ['jenis_kategori_id' => $item->id]) }}"
                                        class="badge bg-light text-dark border text-decoration-none"
                                        title="Lihat kategori dengan jenis ini">
                                        {{ $item->kategori_aset_count }} kategori
                                        <i class="fas fa-external-link-alt ms-1 small"></i>
                                    </a>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center align-items-center gap-2">
                                        <button type="button"
                                            class="btn btn-info btn-sm rounded-circle text-white border-0"
                                            style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"
                                            title="Detail"
                                            data-bs-toggle="modal" data-bs-target="#detailModal{{ $item->id }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button"
                                            class="btn btn-warning btn-sm rounded-circle text-white border-0"
                                            style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"
                                            title="Edit"
                                            data-bs-toggle="modal" data-bs-target="#editModal{{ $item->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button"
                                            class="btn btn-danger btn-sm rounded-circle text-white border-0"
                                            style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"
                                            title="Hapus"
                                            data-bs-toggle="modal" data-bs-target="#deleteModal{{ $item->id }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fas fa-layer-group fa-3x mb-3 d-block opacity-25"></i>
                                    Belum ada data Jenis Kategori
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3 d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Menampilkan {{ $data->firstItem() ?? 0 }} sampai {{ $data->lastItem() ?? 0 }} dari {{ $data->total() }} data
                </div>
                <div>{{ $data->appends(request()->query())->links('pagination::bootstrap-5') }}</div>
            </div>
        </div>
    </div>
</div>


{{-- MODAL TAMBAH --}}
    <div class="modal fade" id="modalTambahJenis" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('jenis-kategori.store') }}" method="POST" id="formTambahJenis" class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" autocomplete="off" novalidate>
                @csrf
                <input type="hidden" name="form_type" value="tambah">

                <div class="modal-header border-0 pt-4 px-4 pb-3" style="background-color: #253070;">
                    <h5 class="modal-title fw-bold text-white">
                        <i class="fas fa-plus-circle me-2"></i> Tambah Jenis Kategori
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4 bg-light">

                    {{-- Nama Jenis --}}
                    <div class="mb-2">
                        <label class="form-label fw-bold small" style="color: #253070;">NAMA JENIS <span class="text-danger">*</span></label>
                        <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-tag"></i></span>
                            <input type="text" name="nama_jenis"
                                class="form-control border-start-0 fs-6 @error('nama_jenis') is-invalid @enderror"
                                placeholder="Contoh: Aset Kendaraan"
                                value="{{ old('form_type') == 'tambah' ? old('nama_jenis') : '' }}" required maxlength="100">
                        </div>
                        @if(old('form_type') == 'tambah') @error('nama_jenis') <div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div> @enderror @endif
                    </div>
                    {{-- Kode Awalan --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold small" style="color: #253070;">KODE AWALAN <span class="text-danger">*</span></label>
                        <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-hashtag"></i></span>
                            <input type="text" name="kode_awalan" maxlength="10"
                                class="form-control border-start-0 fs-6 @error('kode_awalan') is-invalid @enderror"
                                placeholder="Contoh: 3"
                                value="{{ old('form_type') == 'tambah' ? old('kode_awalan') : '' }}" required>
                        </div>
                        <small class="text-muted mt-1 d-block">Digit pertama kode kategori. Contoh: <strong>1</strong> = Aset Tetap, <strong>2</strong> = Inventaris</small>
                        @if(old('form_type') == 'tambah') @error('kode_awalan') <div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div> @enderror @endif
                    </div>
                    {{-- Warna Label --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold small" style="color: #253070;">WARNA LABEL <span class="text-danger">*</span></label>
                        <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus align-items-center bg-white border py-1 pe-2">
                            <span class="input-group-text bg-transparent border-0 text-muted ps-3"><i class="fas fa-palette"></i></span>
                            <!-- Tempat render Pickr -->
                            <div class="ms-2">
                                <div class="color-picker-tambah"></div>
                            </div>
                            <!-- Input text untuk dikirim ke backend dan bisa diedit manual -->
                            <input type="text" name="warna_label" id="warna_label_tambah" 
                                class="form-control border-0 bg-transparent fs-6 ms-2 shadow-none fw-bold text-uppercase"
                                placeholder="#FF5E9B" maxlength="7"
                                value="{{ old('form_type') == 'tambah' ? old('warna_label', '#FF5E9B') : '#FF5E9B' }}" required>
                        </div>
                        <small class="text-muted mt-2 d-block">Warna ini akan digunakan pada banner dan border saat mencetak label aset jenis ini.</small>
                        @if(old('form_type') == 'tambah') @error('warna_label') <div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div> @enderror @endif
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
<div class="modal fade" id="modalImportJenis" tabindex="-1" aria-labelledby="modalImportJenisLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('jenis-kategori.import') }}" method="POST" enctype="multipart/form-data"
            class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            @csrf

            {{-- Header --}}
            <div class="modal-header border-0 pt-4 px-4 pb-3" style="background-color: #253070;">
                <h5 class="modal-title fw-bold text-white" id="modalImportJenisLabel">
                    <i class="fas fa-file-import me-2"></i> Import Jenis Kategori
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
                            <li>Kolom A: <strong>nama_jenis</strong> (wajib, maks 100 karakter)</li>
                            <li>Kolom B: <strong>kode_awalan</strong> (wajib, unik, maks 10 karakter)</li>
                            <li>Kolom C: <strong>warna_label</strong> (opsional, kode hex misal #FF5E9B, maks 7 karakter)</li>
                        </ul>
                        <a href="{{ route('jenis-kategori.template') }}" class="btn btn-sm btn-navy text-white rounded-pill px-3 py-1 fw-bold border-0 shadow-sm" style="background-color: #253070;">
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
    
@foreach($data as $item)
{{-- MODAL DETAIL --}}
<div class="modal fade" id="detailModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 pt-4 px-4 pb-3" style="background-color: #253070;">
                <h5 class="modal-title fw-bold text-white">
                    <i class="fas fa-info-circle me-2"></i> Detail Jenis Kategori
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                    <div class="card-body p-0">
                        <table class="table table-borderless mb-0">
                            <tbody>
                                <tr class="border-bottom">
                                    <th class="bg-white px-4 py-3 text-muted small text-uppercase" width="160">Nama Jenis</th>
                                    <td class="px-4 py-3 fw-bold text-dark">{{ $item->nama_jenis }}</td>
                                </tr>
                                <tr class="border-bottom">
                                    <th class="bg-white px-4 py-3 text-muted small text-uppercase">Kode Awalan</th>
                                    <td class="px-4 py-3 fw-bold text-primary fs-5">{{ $item->kode_awalan }}</td>
                                </tr>
                                <tr class="border-bottom">
                                    <th class="bg-white px-4 py-3 text-muted small text-uppercase">Warna Label</th>
                                    <td class="px-4 py-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle shadow-sm border" style="width: 24px; height: 24px; background-color: {{ $item->warna_label ?? '#FF5E9B' }};"></div>
                                            <span class="fw-bold text-dark">{{ strtoupper($item->warna_label ?? '#FF5E9B') }}</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="border-bottom">
                                    <th class="bg-white px-4 py-3 text-muted small text-uppercase">Jumlah Kategori</th>
                                    <td class="px-4 py-3">
                                        <span class="badge bg-light text-dark border">
                                            {{ $item->kategori_aset_count }} kategori terdaftar
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('jenis-kategori.update', $item->id) }}" method="POST" id="formEditJenis{{ $item->id }}"
            class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" autocomplete="off" novalidate>
            @csrf @method('PUT')
            <input type="hidden" name="form_type" value="edit_{{ $item->id }}">

            <div class="modal-header border-0 pt-4 px-4 pb-3" style="background-color: #253070;">
                <h5 class="modal-title fw-bold text-white">
                    <i class="fas fa-edit me-2"></i> Edit Jenis Kategori
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4 bg-light">
                <div class="mb-2">
                    <label class="form-label fw-bold small" style="color: #253070;">NAMA JENIS <span class="text-danger">*</span></label>
                    <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-tag"></i></span>
                        <input type="text" name="nama_jenis"
                            class="form-control border-start-0 fs-6 @error('nama_jenis') is-invalid @enderror"
                            value="{{ old('form_type') == 'edit_'.$item->id ? old('nama_jenis') : $item->nama_jenis }}" required maxlength="100">
                    </div>
                    @if(old('form_type') == 'edit_'.$item->id) @error('nama_jenis') <div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div> @enderror @endif
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold small" style="color: #253070;">KODE AWALAN <span class="text-danger">*</span></label>
                    <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-hashtag"></i></span>
                        <input type="text" name="kode_awalan" maxlength="10"
                            class="form-control border-start-0 fs-6 @error('kode_awalan') is-invalid @enderror"
                            value="{{ old('form_type') == 'edit_'.$item->id ? old('kode_awalan') : $item->kode_awalan }}" required>
                    </div>
                    @if(old('form_type') == 'edit_'.$item->id) @error('kode_awalan') <div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div> @enderror @endif
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold small" style="color: #253070;">WARNA LABEL <span class="text-danger">*</span></label>
                    <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus align-items-center bg-white border py-1 pe-2">
                        <span class="input-group-text bg-transparent border-0 text-muted ps-3"><i class="fas fa-palette"></i></span>
                        <!-- Tempat render Pickr -->
                        <div class="ms-2">
                            <div class="color-picker-edit-{{ $item->id }}"></div>
                        </div>
                        <!-- Input text untuk dikirim ke backend dan bisa diedit manual -->
                        <input type="text" name="warna_label" id="warna_label_edit_{{ $item->id }}" 
                            class="form-control border-0 bg-transparent fs-6 ms-2 shadow-none fw-bold text-uppercase"
                            placeholder="#FF5E9B" maxlength="7"
                            value="{{ old('form_type') == 'edit_'.$item->id ? old('warna_label') : ($item->warna_label ?? '#FF5E9B') }}" required>
                    </div>
                    @if(old('form_type') == 'edit_'.$item->id) @error('warna_label') <div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div> @enderror @endif
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

<div class="modal fade" id="deleteModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-body p-5 text-center bg-light">
                <div class="d-inline-flex align-items-center justify-content-center rounded-4 mb-4"
                    style="width: 80px; height: 80px; background-color: #f1f3f5;">
                    <i class="fas fa-exclamation-triangle fa-3x text-danger"></i>
                </div>
                <h4 class="fw-bold text-dark mb-2">Konfirmasi Hapus</h4>
                <p class="text-muted mb-1">Hapus jenis kategori</p>
                <p class="fw-bold text-danger fs-5 mb-3">{{ $item->nama_jenis }} (Awalan: {{ $item->kode_awalan }})?</p>
                @if($item->kategori_aset_count > 0)
                    <div class="alert alert-warning border-0 rounded-3 small text-start">
                        <i class="fas fa-exclamation-circle me-1"></i>
                        Jenis ini masih memiliki <strong>{{ $item->kategori_aset_count }}</strong> kategori aset aktif dan tidak bisa dihapus.
                    </div>
                @endif
                <div class="d-flex justify-content-center gap-3 mt-3">
                    @if($item->kategori_aset_count == 0)
                    <form action="{{ route('jenis-kategori.destroy', $item->id) }}" method="POST"
                        class="w-100 d-flex justify-content-center gap-3">
                        @csrf @method('DELETE')
                        <button type="button" class="btn btn-light rounded-pill fw-bold py-2 shadow-sm border" style="width: 120px;" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger rounded-pill fw-bold py-2 shadow-sm" style="width: 140px;">Ya, Hapus</button>
                    </form>
                    @else
                    <button type="button" class="btn btn-secondary rounded-pill fw-bold py-2 px-4" data-bs-dismiss="modal">Tutup</button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endforeach
@endsection

@push('scripts')
<!-- Pickr JS -->
<script src="https://cdn.jsdelivr.net/npm/@simonwep/pickr/dist/pickr.min.js"></script>
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
            showConfirmButton: true, confirmButtonText: 'OK',
            confirmButtonColor: '#253070', customClass: { popup: 'rounded-4 shadow' }
        };
        @if (session('success')) Swal.fire({ ...swalConfig, icon: 'success', title: 'Berhasil!', text: '{{ session('success') }}' }); @endif
        @if (session('error'))   Swal.fire({ ...swalConfig, icon: 'error',   title: 'Gagal!',    text: '{{ session('error') }}'   }); @endif

        @if($errors->any())
            setTimeout(function() {
                const formType = "{{ old('form_type') }}";
                if (formType === 'tambah') {
                    new bootstrap.Modal(document.getElementById('modalTambahJenis')).show();
                } else if (formType.startsWith('edit_')) {
                    const id = formType.split('_')[1];
                    new bootstrap.Modal(document.getElementById('editModal' + id)).show();
                }
            }, 200);
        @endif

        // Konfigurasi Umum Pickr
        const pickrConfig = {
            theme: 'nano',
            swatches: [
                '#FF5E9B', '#253070', '#e30613', '#28a745', '#ffc107', '#17a2b8', '#6c757d', '#343a40'
            ],
            components: {
                preview: true,
                opacity: false,
                hue: true,
                interaction: {
                    hex: false,
                    input: false,
                    save: true
                }
            }
        };

        // Inisialisasi Pickr untuk Modal Tambah
        const elTambah = document.querySelector('.color-picker-tambah');
        const inputTambah = document.getElementById('warna_label_tambah');
        if (elTambah && inputTambah) {
            const pickrTambah = Pickr.create({
                ...pickrConfig,
                el: elTambah,
                default: inputTambah.value
            });
            pickrTambah.on('change', (color, source, instance) => {
                if (source === 'slider' || source === 'swatch') {
                    inputTambah.value = color.toHEXA().toString();
                }
            }).on('save', (color, instance) => {
                inputTambah.value = color.toHEXA().toString();
                pickrTambah.hide();
            });
            inputTambah.addEventListener('change', function() {
                pickrTambah.setColor(this.value);
            });
        }

        // Inisialisasi Pickr untuk Modal Edit (Loop Data)
        @foreach($data as $item)
            const elEdit{{ $item->id }} = document.querySelector('.color-picker-edit-{{ $item->id }}');
            const inputEdit{{ $item->id }} = document.getElementById('warna_label_edit_{{ $item->id }}');
            if (elEdit{{ $item->id }} && inputEdit{{ $item->id }}) {
                const pickrEdit{{ $item->id }} = Pickr.create({
                    ...pickrConfig,
                    el: elEdit{{ $item->id }},
                    default: inputEdit{{ $item->id }}.value
                });
                pickrEdit{{ $item->id }}.on('change', (color, source, instance) => {
                    if (source === 'slider' || source === 'swatch') {
                        inputEdit{{ $item->id }}.value = color.toHEXA().toString();
                    }
                }).on('save', (color, instance) => {
                    inputEdit{{ $item->id }}.value = color.toHEXA().toString();
                    pickrEdit{{ $item->id }}.hide();
                });
                inputEdit{{ $item->id }}.addEventListener('change', function() {
                    pickrEdit{{ $item->id }}.setColor(this.value);
                });
            }
        @endforeach

        // Client-side Validation on Submit
        const formsToValidate = document.querySelectorAll('#formTambahJenis, form[id^="formEditJenis"]');
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
