@extends('layouts.app')

@section('title', 'Mengelola Kategori Aset')

@section('content')
<div class="container-fluid px-1 py-0 mt-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0">
            Mengelola Kategori Aset
            @if($jenisAktif)
                <span class="badge bg-{{ $jenisAktif->warna_badge_safe }} ms-2 fs-6">{{ $jenisAktif->nama_jenis }}</span>
            @endif
        </h3>
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
                <span class="text-muted" style="font-size: 14px; font-weight: 500; position: relative; top: 2px;">Mengelola Kategori Aset</span>
            </li>
        </ul>
    </div>

    {{-- FILTER & TOOLBAR --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('kategori-aset.index') }}" class="row g-2 align-items-end">
                {{-- Entries --}}
                <div class="col-md-1">
                    <label class="form-label fw-bold small text-muted text-uppercase" style="font-size: 0.7rem;">Entries</label>
                    <select name="per_page" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                        <option value="10"  {{ request('per_page') == 10  ? 'selected' : '' }}>10</option>
                        <option value="25"  {{ request('per_page') == 25  ? 'selected' : '' }}>25</option>
                        <option value="50"  {{ request('per_page') == 50  ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>

                {{-- Filter Jenis --}}
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-muted text-uppercase" style="font-size: 0.7rem;">Filter Jenis Kategori</label>
                    <select name="jenis_kategori_id" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                        <option value="">-- Semua Jenis --</option>
                        @foreach($jenisList as $jenis)
                            <option value="{{ $jenis->id }}" {{ request('jenis_kategori_id') == $jenis->id ? 'selected' : '' }}>
                                {{ $jenis->kode_awalan }} - {{ $jenis->nama_jenis }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Pencarian --}}
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-muted text-uppercase" style="font-size: 0.7rem;">Pencarian</label>
                    <div class="input-group input-group-sm input-group-focus rounded-3">
                        <span class="input-group-text bg-white border-0 text-muted"><i class="fas fa-search"></i></span>
                        <input type="search" name="search" class="form-control border-0 shadow-none bg-transparent"
                            placeholder="Cari kode atau nama..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-auto ms-auto">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-warning px-4 rounded-3 d-flex align-items-center text-dark"
                            data-bs-toggle="modal" data-bs-target="#modalImportKategori">
                            <i class="fas fa-file-import me-1"></i> Import
                        </button>
                        <a href="{{ route('kategori-aset.export', request()->query()) }}" class="btn btn-success px-4 rounded-3 d-flex align-items-center text-white" title="Export Data">
                            <i class="fas fa-file-excel me-1"></i> Export
                        </a>
                        <button type="button" class="btn btn-primary px-4 rounded-3 d-flex align-items-center"
                            data-bs-toggle="modal" data-bs-target="#modalTambahKategori">
                            <i class="fas fa-plus me-1"></i> Tambah Kategori
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
                $sortBy = request('sort_by', 'kode');
                $orderBy = request('order_by', 'asc');
            @endphp
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="60" class="text-center">No</th>
                            <th width="200">
                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'nama', 'order_by' => ($sortBy == 'nama' && $orderBy == 'asc') ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark d-flex align-items-center justify-content-between">
                                    Nama Kategori
                                    <span class="d-inline-block position-relative ms-2" style="width: 20px; height: 18px; vertical-align: middle;">
                                        <span style="position: absolute; right: 10px; bottom: 0.1em; font-size: 15px; font-weight: normal; opacity: {{ ($sortBy == 'nama' && $orderBy == 'asc') ? '0.9' : '0.3' }}; color: {{ ($sortBy == 'nama' && $orderBy == 'asc') ? '#253070' : '#888888' }};">↑</span>
                                        <span style="position: absolute; right: 2px; bottom: 0.1em; font-size: 15px; font-weight: normal; opacity: {{ ($sortBy == 'nama' && $orderBy == 'desc') ? '0.9' : '0.3' }}; color: {{ ($sortBy == 'nama' && $orderBy == 'desc') ? '#253070' : '#888888' }};">↓</span>
                                    </span>
                                </a>
                            </th>
                            <th width="130">
                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'kode', 'order_by' => ($sortBy == 'kode' && $orderBy == 'asc') ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark d-flex align-items-center justify-content-between">
                                    Kode
                                    <span class="d-inline-block position-relative ms-2" style="width: 20px; height: 18px; vertical-align: middle;">
                                        <span style="position: absolute; right: 10px; bottom: 0.1em; font-size: 15px; font-weight: normal; opacity: {{ ($sortBy == 'kode' && $orderBy == 'asc') ? '0.9' : '0.3' }}; color: {{ ($sortBy == 'kode' && $orderBy == 'asc') ? '#253070' : '#888888' }};">↑</span>
                                        <span style="position: absolute; right: 2px; bottom: 0.1em; font-size: 15px; font-weight: normal; opacity: {{ ($sortBy == 'kode' && $orderBy == 'desc') ? '0.9' : '0.3' }}; color: {{ ($sortBy == 'kode' && $orderBy == 'desc') ? '#253070' : '#888888' }};">↓</span>
                                    </span>
                                </a>
                            </th>
                            <th width="160" class="text-center">
                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'jenis_kategori_id', 'order_by' => ($sortBy == 'jenis_kategori_id' && $orderBy == 'asc') ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark d-flex align-items-center justify-content-center">
                                    Jenis Kategori
                                    <span class="d-inline-block position-relative ms-2" style="width: 20px; height: 18px; vertical-align: middle;">
                                        <span style="position: absolute; right: 10px; bottom: 0.1em; font-size: 15px; font-weight: normal; opacity: {{ ($sortBy == 'jenis_kategori_id' && $orderBy == 'asc') ? '0.9' : '0.3' }}; color: {{ ($sortBy == 'jenis_kategori_id' && $orderBy == 'asc') ? '#253070' : '#888888' }};">↑</span>
                                        <span style="position: absolute; right: 2px; bottom: 0.1em; font-size: 15px; font-weight: normal; opacity: {{ ($sortBy == 'jenis_kategori_id' && $orderBy == 'desc') ? '0.9' : '0.3' }}; color: {{ ($sortBy == 'jenis_kategori_id' && $orderBy == 'desc') ? '#253070' : '#888888' }};">↓</span>
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
                                <td>{{ $item->nama }}</td>
                                <td class="fw-bold text-primary">{{ $item->kode }}</td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $item->tipe_badge_color }}">
                                        {{ $item->tipe_label }}
                                    </span>
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
                                    <i class="fas fa-boxes fa-3x mb-3 d-block opacity-25"></i>
                                    Belum ada data Kategori Aset
                                    @if(request('jenis_kategori_id'))
                                        untuk jenis kategori ini
                                    @endif
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
                <div>
                    {{ $data->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL IMPORT --}}
    <div class="modal fade" id="modalImportKategori" tabindex="-1" aria-labelledby="modalImportKategoriLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('kategori-aset.import') }}" method="POST" enctype="multipart/form-data"
                class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                @csrf
                
                {{-- Header --}}
                <div class="modal-header border-0 pt-4 px-4 pb-3" style="background-color: #253070;">
                    <h5 class="modal-title fw-bold text-white" id="modalImportKategoriLabel">
                        <i class="fas fa-file-import me-2"></i> Import Kategori Aset
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
                                <li>Kolom A: <strong>nama</strong> (wajib, nama kategori aset)</li>
                                <li>Kolom B: <strong>kode</strong> (wajib, awalan kode harus sesuai jenis kategori)</li>
                            </ul>
                            <a href="{{ route('kategori-aset.template') }}" class="btn btn-sm btn-navy text-white rounded-pill px-3 py-1 fw-bold border-0 shadow-sm" style="background-color: #253070;">
                                <i class="fas fa-download me-1"></i> Unduh Template Excel
                            </a>
                        </div>
                    </div>

                    {{-- Jenis Kategori (Dropdown) --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase" style="color: #253070; font-size: 0.72rem;">
                            <i class="fas fa-layer-group me-1"></i> Jenis Kategori (Opsional)
                        </label>
                        <div class="input-group shadow-sm rounded-3 input-group-focus align-items-center bg-white border">
                            <span class="input-group-text bg-white border-0 text-muted"><i class="fas fa-folder"></i></span>
                            <select name="jenis_kategori_id" class="form-select border-0 shadow-none fs-6 bg-white">
                                <option value="" selected>-- Semua Jenis (Otomatis Mendeteksi dari Awalan Kode) --</option>
                                @foreach($jenisList as $jenis)
                                    <option value="{{ $jenis->id }}">{{ $jenis->kode_awalan }} - {{ $jenis->nama_jenis }}</option>
                                @endforeach
                            </select>
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

{{-- MODAL TAMBAH --}}
<div class="modal fade" id="modalTambahKategori" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('kategori-aset.store') }}" method="POST" id="formTambahKategori"
            class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" autocomplete="off" novalidate>
            @csrf
            <input type="hidden" name="form_type" value="tambah">

            <div class="modal-header border-0 pt-4 px-4 pb-3" style="background-color: #253070;">
                <h5 class="modal-title fw-bold text-white">
                    <i class="fas fa-plus-circle me-2"></i> Tambah Kategori Aset
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4 bg-light">
                {{-- Dropdown Jenis Kategori --}}
                <div class="mb-4">
                    <label class="form-label fw-bold small" style="color: #253070;">JENIS KATEGORI ASET <span class="text-danger">*</span></label>
                    <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-layer-group"></i></span>
                        <select name="jenis_kategori_id" id="addJenisSelect"
                            class="form-select border-start-0 fs-6 @error('jenis_kategori_id') is-invalid @enderror" required>
                            <option value="" disabled selected>-- Pilih Jenis Kategori --</option>
                            @foreach($jenisList as $jenis)
                                <option value="{{ $jenis->id }}"
                                    data-awalan="{{ $jenis->kode_awalan }}"
                                    {{ (old('form_type') == 'tambah' && old('jenis_kategori_id') == $jenis->id) || (request('jenis_kategori_id') == $jenis->id) ? 'selected' : '' }}>
                                    {{ $jenis->kode_awalan }} – {{ $jenis->nama_jenis }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @if(old('form_type') == 'tambah') @error('jenis_kategori_id') <div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div> @enderror @endif
                </div>

                {{-- Nama --}}
                <div class="mb-2">
                    <label class="form-label fw-bold small" style="color: #253070;">NAMA KATEGORI ASET <span class="text-danger">*</span></label>
                    <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-tag"></i></span>
                        <input type="text" name="nama"
                            class="form-control border-start-0 fs-6 @error('nama') is-invalid @enderror"
                            placeholder="Contoh: Tanah" value="{{ old('form_type') == 'tambah' ? old('nama') : '' }}" required maxlength="100">
                    </div>
                    @if(old('form_type') == 'tambah') @error('nama') <div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div> @enderror @endif
                </div>

                {{-- Kode --}}
                <div class="mb-4">
                    <label class="form-label fw-bold small" style="color: #253070;">KODE KATEGORI ASET <span class="text-danger">*</span></label>
                    <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-barcode"></i></span>
                        <input type="text" name="kode" id="addKodeInput"
                            class="form-control border-start-0 fs-6 @error('kode') is-invalid @enderror"
                            placeholder="Contoh: 101" maxlength="10"
                            value="{{ old('form_type') == 'tambah' ? old('kode') : '' }}" required>
                    </div>
                    <small class="text-muted mt-1 d-block" id="addKodeHint">Pilih Jenis Kategori dulu untuk melihat awalan kode yang benar</small>
                    @if(old('form_type') == 'tambah') @error('kode') <div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div> @enderror @endif
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

{{-- MODAL EDIT & HAPUS per item --}}
@foreach($data as $item)
{{-- MODAL DETAIL --}}
<div class="modal fade" id="detailModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 pt-4 px-4 pb-3" style="background-color: #253070;">
                <h5 class="modal-title fw-bold text-white">
                    <i class="fas fa-info-circle me-2"></i> Detail Kategori Aset
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                    <div class="card-body p-0">
                        <table class="table table-borderless mb-0">
                            <tbody>
                                <tr class="border-bottom">
                                    <th class="bg-white px-4 py-3 text-muted small text-uppercase">Jenis Kategori Aset</th>
                                    <td class="px-4 py-3">
                                        <span class="badge bg-{{ $item->tipe_badge_color }}">
                                            {{ $item->tipe_label }}
                                        </span>
                                    </td>
                                </tr>
                                <tr class="border-bottom">
                                    <th class="bg-white px-4 py-3 text-muted small text-uppercase">Nama Kategori Aset</th>
                                    <td class="px-4 py-3 fw-bold text-dark">{{ $item->nama }}</td>
                                </tr>
                                <tr class="border-bottom">
                                    <th class="bg-white px-4 py-3 text-muted small text-uppercase" width="160">Kode Kategori Aset</th>
                                    <td class="px-4 py-3 fw-bold text-primary fs-5">{{ $item->kode }}</td>
                                </tr>
                                
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-top-0 pt-0 pb-4 px-4">
                <button type="button" class="btn btn-secondary rounded-pill px-4 fw-bold shadow-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL EDIT --}}
<div class="modal fade" id="editModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('kategori-aset.update', $item->id) }}" method="POST" id="formEditKategori{{ $item->id }}"
            class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" autocomplete="off" novalidate>
            @csrf @method('PUT')
            <input type="hidden" name="form_type_edit" value="{{ $item->id }}">

            <div class="modal-header border-0 pt-4 px-4 pb-3" style="background-color: #253070;">
                <h5 class="modal-title fw-bold text-white">
                    <i class="fas fa-edit me-2"></i> Edit Kategori Aset
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4 bg-light">
                {{-- Dropdown Jenis --}}
                <div class="mb-4">
                    <label class="form-label fw-bold small" style="color: #253070;">JENIS KATEGORI ASET <span class="text-danger">*</span></label>
                    <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-layer-group"></i></span>
                        <select name="jenis_kategori_id"
                            class="form-select border-start-0 fs-6 edit-jenis-select"
                            data-item-id="{{ $item->id }}" required>
                            <option value="" disabled>-- Pilih Jenis --</option>
                            @foreach($jenisList as $jenis)
                                <option value="{{ $jenis->id }}"
                                    data-awalan="{{ $jenis->kode_awalan }}"
                                    {{ (session('form_type_edit') == $item->id && old('jenis_kategori_id') == $jenis->id) || $item->jenis_kategori_id == $jenis->id ? 'selected' : '' }}>
                                    {{ $jenis->kode_awalan }} – {{ $jenis->nama_jenis }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Nama --}}
                <div class="mb-2">
                    <label class="form-label fw-bold small" style="color: #253070;">NAMA KATEGORI ASET <span class="text-danger">*</span></label>
                    <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-tag"></i></span>
                        <input type="text" name="nama"
                            class="form-control border-start-0 fs-6 @error('nama') is-invalid @enderror"
                            value="{{ session('form_type_edit') == $item->id ? old('nama') : $item->nama }}" required maxlength="100">
                    </div>
                    @if(session('form_type_edit') == $item->id) @error('nama') <div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div> @enderror @endif
                </div>

                {{-- Kode --}}
                <div class="mb-4">
                    <label class="form-label fw-bold small" style="color: #253070;">KODE KATEGORI ASET <span class="text-danger">*</span></label>
                    <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-barcode"></i></span>
                        <input type="text" name="kode" maxlength="10"
                            class="form-control border-start-0 fs-6 @error('kode') is-invalid @enderror"
                            value="{{ session('form_type_edit') == $item->id ? old('kode') : $item->kode }}" required>
                    </div>
                    <small class="text-muted mt-1 d-block edit-kode-hint-{{ $item->id }}">
                        Awalan kode harus sesuai jenis: <strong>{{ $item->jenisKategori?->kode_awalan }}</strong>
                    </small>
                    @if(session('form_type_edit') == $item->id) @error('kode') <div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div> @enderror @endif
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

{{-- MODAL HAPUS --}}
<div class="modal fade" id="deleteModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-body p-5 text-center bg-light">
                <div class="d-inline-flex align-items-center justify-content-center rounded-4 mb-4"
                    style="width: 80px; height: 80px; background-color: #f1f3f5;">
                    <i class="fas fa-exclamation-triangle fa-3x text-danger"></i>
                </div>
                <h4 class="fw-bold text-dark mb-2">Konfirmasi Hapus</h4>
                <p class="text-muted mb-3">
                    Hapus kategori <br>
                    <strong class="text-danger fs-5">{{ $item->kode }} - {{ $item->nama }}</strong>?
                </p>
                <div class="d-flex justify-content-center gap-3">
                    <form action="{{ route('kategori-aset.destroy', $item->id) }}" method="POST"
                        class="w-100 d-flex justify-content-center gap-3">
                        @csrf @method('DELETE')
                        <button type="button" class="btn btn-light rounded-pill fw-bold py-2 shadow-sm border"
                            style="width: 120px;" data-bs-dismiss="modal">Batalkan</button>
                        <button type="submit" class="btn btn-danger rounded-pill fw-bold py-2 shadow-sm"
                            style="width: 140px;">Ya, Hapus</button>
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
            showConfirmButton: true, confirmButtonText: 'OK',
            confirmButtonColor: '#253070', customClass: { popup: 'rounded-4 shadow' }
        };

        @if (session('success')) Swal.fire({ ...swalConfig, icon: 'success', title: 'Berhasil!', text: '{{ session('success') }}' }); @endif
        @if (session('error'))   Swal.fire({ ...swalConfig, icon: 'error',   title: 'Gagal!',    text: '{{ session('error') }}'   }); @endif

        // Hint awalan kode pada modal Tambah
        const addJenisSelect = document.getElementById('addJenisSelect');
        const addKodeHint    = document.getElementById('addKodeHint');
        if (addJenisSelect) {
            function updateAddHint() {
                const opt = addJenisSelect.options[addJenisSelect.selectedIndex];
                const awalan = opt ? opt.getAttribute('data-awalan') : null;
                addKodeHint.innerHTML = awalan
                    ? `Kode harus diawali dengan angka <strong>${awalan}</strong>`
                    : 'Pilih Jenis Kategori dulu untuk melihat awalan kode yang benar';
            }
            addJenisSelect.addEventListener('change', updateAddHint);
            updateAddHint();
        }

        // Hint awalan kode pada setiap modal Edit
        document.querySelectorAll('.edit-jenis-select').forEach(function(sel) {
            sel.addEventListener('change', function() {
                const itemId = this.getAttribute('data-item-id');
                const opt    = this.options[this.selectedIndex];
                const awalan = opt ? opt.getAttribute('data-awalan') : null;
                const hint   = document.querySelector('.edit-kode-hint-' + itemId);
                if (hint) {
                    hint.innerHTML = awalan
                        ? `Awalan kode harus sesuai jenis: <strong>${awalan}</strong>`
                        : 'Pilih jenis terlebih dahulu';
                }
            });
        });

        // Buka kembali modal jika ada error validasi
        @if($errors->any())
            setTimeout(function() {
                const formType = "{{ old('form_type') }}";
                const formTypeEdit = "{{ session('form_type_edit') }}";
                if (formType === 'tambah') {
                    new bootstrap.Modal(document.getElementById('modalTambahKategori')).show();
                } else if (formTypeEdit) {
                    const modalEl = document.getElementById('editModal' + formTypeEdit);
                    if (modalEl) new bootstrap.Modal(modalEl).show();
                }
            }, 200);
        @endif

        // Client-side Validation on Submit
        const formsToValidate = document.querySelectorAll('#formTambahKategori, form[id^="formEditKategori"]');
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

                const requiredFields = form.querySelectorAll('[required]');
                requiredFields.forEach(field => {
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
                        labelText = field.getAttribute('placeholder') || field.getAttribute('name') || 'Kolom Wajib';
                    }

                    if (!field.value || (field.tagName === 'SELECT' && field.value === '')) {
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
                    } else {
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
