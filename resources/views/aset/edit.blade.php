@extends('layouts.app')

@section('title', 'Edit Data Aset - ' . $aset->nomor_aset)

@section('content')
<div class="container-fluid px-1 py-0 mt-0">
    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold mb-0">Edit Data Aset: {{ $aset->nomor_aset }}</h3>
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
                <a href="{{ route('aset.index') }}" class="text-muted text-decoration-none d-flex align-items-center">
                    <span style="font-size: 14px; font-weight: 500; position: relative; top: 2px;">Data Aset Perusahaan</span>
                </a>
            </li>
                <li class="separator text-muted d-flex align-items-center px-2">
                <span style="font-size: 14px; position: relative; top: 2px;">-</span>
            </li>
            
            <li class="nav-item d-flex align-items-center">
                <span class="text-muted" style="font-size: 14px; font-weight: 500; position: relative; top: 2px;">Edit Data Aset</span>
            </li>
        </ul>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <form action="{{ route('aset.update', $aset->id) }}" method="POST" enctype="multipart/form-data" id="formAset" autocomplete="off">
                @csrf
                @method('PUT')

                {{-- INFORMASI DATA ASET --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white pt-3 pb-2 border-bottom-0">
                        <h6 class="mb-0 fw-semibold text-primary">
                            <i class="fas fa-box-open me-2"></i> Informasi Data Aset
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-navy mb-1 small">Nomor Aset (Otomatis)</label>
                                <input type="text" id="nomor_aset_display" name="nomor_aset" class="form-control bg-light text-muted border-0 shadow-none rounded-3 px-3 py-2" 
                                       value="{{ $aset->nomor_aset }}" disabled style="cursor: not-allowed; opacity: 0.8;">
                                <small class="text-muted mt-1 d-block" style="font-size: 0.7rem;">Format: [ID]/[SUMBER]/[UMUM]-[KHUSUS]/[LOKASI]/[KATEGORI]/[TAHUN]</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-navy mb-1 small">Jenis Aset Umum <span class="text-danger">*</span></label>
                                <div class="input-group input-group-focus rounded-3">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-layer-group"></i></span>
                                    <select id="id_jenis_aset_umum" class="form-select border-start-0 ps-0 shadow-none" required>
                                        <option value="" disabled>-- Pilih Jenis Aset Umum --</option>
                                        @foreach($jenisUmum as $umum)
                                            <option value="{{ $umum->id }}" data-kode="{{ $umum->kode_umum }}"
                                                {{ (old('jenis_aset_umum_id', $aset->jenisAsetKhusus->jenis_aset_umum_id ?? '') == $umum->id) ? 'selected' : '' }}>
                                                {{ $umum->kode_umum }} - {{ $umum->jenis_aset }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-navy mb-1 small">Jenis Aset Khusus <span class="text-danger">*</span></label>
                                <div class="input-group input-group-focus rounded-3">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-layer-group"></i></span>
                                    <select name="jenis_aset_khusus_id" id="id_jenis_aset" class="form-select border-start-0 ps-0 shadow-none" required>
                                        <option value="" disabled>-- Pilih Jenis Umum Dahulu --</option>
                                        @foreach($jenisKhusus as $jenis)
                                            <option value="{{ $jenis->id }}" 
                                                    data-parent="{{ $jenis->jenis_aset_umum_id }}"
                                                    data-kode="{{ $jenis->kode_khusus }}" 
                                                    data-nama="{{ $jenis->jenis_aset }}" 
                                                    {{ old('jenis_aset_khusus_id', $aset->jenis_aset_khusus_id) == $jenis->id ? 'selected' : '' }}>
                                                {{ $jenis->kode_khusus }} - {{ $jenis->jenis_aset }} 
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-navy mb-1 small">Kategori Aset <span class="text-danger">*</span></label>
                                <div class="input-group input-group-focus rounded-3">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-tags"></i></span>
                                    <select name="kategori_id" id="id_kategori" class="form-select border-start-0 ps-0 shadow-none" required>
                                        <option value="" disabled>-- Pilih Kategori --</option>
                                        @foreach($kategori as $kat)
                                            <option value="{{ $kat->kategori_id }}" data-kode="{{ $kat->kode }}" {{ old('kategori_id', $aset->kategori_id) == $kat->kategori_id ? 'selected' : '' }}>
                                                {{ $kat->kode }} - {{ $kat->nama_kategori }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-navy mb-1 small">Nama Aset <span class="text-danger">*</span></label>
                                <div class="input-group input-group-focus rounded-3">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-box-open"></i></span>
                                    <input type="text" name="nama_aset" class="form-control border-start-0 ps-0 shadow-none" value="{{ old('nama_aset', $aset->nama_aset) }}" placeholder="Contoh: Gedung Kantor Utama" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-navy mb-1 small">Merk Aset</label>
                                <div class="input-group input-group-focus rounded-3">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-tag"></i></span>
                                    <input type="text" name="merek" class="form-control border-start-0 ps-0 shadow-none" value="{{ old('merek', $aset->merek) }}" placeholder="Contoh: Lenovo / Honda">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-navy mb-1 small">Tahun Kapitalisasi</label>
                                <div class="input-group input-group-focus rounded-3">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-calendar-alt"></i></span>
                                    <select name="tahun_kapitalisasi" id="id_tahun" class="form-select border-start-0 ps-0 shadow-none">
                                        <option value="" disabled>-- Pilih Tahun --</option>
                                        @for ($year = date('Y'); $year >= 1900; $year--)
                                            <option value="{{ $year }}" {{ old('tahun_kapitalisasi', $aset->tahun_kapitalisasi) == $year ? 'selected' : '' }}>{{ $year }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-navy mb-1 small">Deskripsi Singkat</label>
                                <div class="input-group input-group-focus rounded-3">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-align-left"></i></span>
                                    <textarea name="deskripsi" class="form-control border-start-0 ps-0 shadow-none" rows="1" placeholder="Opsional detail aset...">{{ old('deskripsi', $aset->deskripsi) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- PENEMPATAN ASET --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white pt-3 pb-2 border-bottom-0">
                        <h6 class="mb-0 fw-semibold text-primary">
                            <i class="fas fa-map-marker-alt me-2"></i> Penempatan Aset
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-navy mb-1 small">Sumber Kepemilikan <span class="text-danger">*</span></label>
                                <div class="input-group input-group-focus rounded-3">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-hand-holding-usd"></i></span>
                                    <select name="sumber_kepemilikan_id" id="sumber_kepemilikan_id" class="form-select border-start-0 ps-0 shadow-none" required>
                                        <option value="" disabled>-- Pilih Sumber Kepemilikan --</option>
                                        @foreach($sumberKepemilikan as $sk)
                                            <option value="{{ $sk->id }}"
                                                    data-kode="{{ $sk->kode ?? 'REKA' }}"
                                                    {{ old('sumber_kepemilikan_id', $aset->sumber_kepemilikan_id) == $sk->id ? 'selected' : '' }}>
                                                {{ $sk->kode }} — {{ $sk->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-navy mb-1 small">Lokasi Aset <span class="text-danger">*</span></label>
                                <div class="input-group input-group-focus rounded-3">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-map-marker-alt"></i></span>
                                    <select name="lokasi_id" id="dropdown_lokasi" class="form-select border-start-0 ps-0 shadow-none" required>
                                        <option value="" disabled>-- Pilih Lokasi --</option>
                                        @foreach($lokasi as $lok)
                                            <option value="{{ $lok->lokasi_id }}" 
                                                    data-detail="{{ $lok->detail_lokasi ?? '' }}"
                                                    data-kode="{{ $lok->kode_lokasi ?? 'LOK' }}"
                                                    {{ old('lokasi_id', $aset->lokasi_id) == $lok->lokasi_id ? 'selected' : '' }}>
                                                {{ $lok->nama_lokasi ?? $lok->nm_lokasi_aset }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold text-navy mb-1 small text-muted">Detail Lokasi</label>
                                <input type="text" id="input_detail_lokasi" class="form-control bg-light text-muted border-0 shadow-none rounded-3 px-3 py-2" 
                                       disabled placeholder="Otomatis dari lokasi terpilih" style="cursor: not-allowed; opacity: 0.8;">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- STATUS & STRUKTUR --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white pt-3 pb-2 border-bottom-0">
                        <h6 class="mb-0 fw-semibold text-primary"><i class="fas fa-shield-alt me-2"></i> Kondisi & Struktur</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-navy mb-1 small">Kondisi Saat Ini <span class="text-danger">*</span></label>
                                <div class="input-group input-group-focus rounded-3">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-info-circle"></i></span>
                                    <select name="status_kondisi" id="status_kondisi" class="form-select border-start-0 ps-0 shadow-none" required>
                                        <option value="" disabled>-- Pilih Kondisi --</option>
                                        <option value="Baik" {{ old('status_kondisi', $aset->status_kondisi) == 'Baik' ? 'selected' : '' }}>Baik</option>
                                        <option value="Rusak" {{ old('status_kondisi', $aset->status_kondisi) == 'Rusak' ? 'selected' : '' }}>Rusak</option>
                                        <option value="Bongkar" {{ old('status_kondisi', $aset->status_kondisi) == 'Bongkar' ? 'selected' : '' }}>Bongkar</option>
                                        <option value="Tidak Terpakai" {{ old('status_kondisi', $aset->status_kondisi) == 'Tidak Terpakai' ? 'selected' : '' }}>Tidak Terpakai</option>
                                        <option value="Hilang" {{ old('status_kondisi', $aset->status_kondisi) == 'Hilang' ? 'selected' : '' }}>Hilang</option>
                                        <option value="Tidak Teridentifikasi" {{ old('status_kondisi', $aset->status_kondisi) == 'Tidak Teridentifikasi' ? 'selected' : '' }}>Tidak Teridentifikasi</option>
                                        <option value="Lainnya" {{ old('status_kondisi', $aset->status_kondisi) == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                                    </select>
                                </div>
                            </div>
                            
                            {{-- Field Keterangan Lainnya --}}
                            <div class="col-md-12 mt-2" id="keterangan_kondisi_wrapper" style="display: none;">
                                <label class="form-label fw-bold text-navy mb-1 small">Spesifikasikan Kondisi Lainnya <span class="text-danger">*</span></label>
                                <div class="input-group input-group-focus rounded-3">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-pen"></i></span>
                                    <input type="text" name="keterangan_kondisi" id="keterangan_kondisi" class="form-control border-start-0 ps-0 shadow-none" value="{{ old('keterangan_kondisi', $aset->keterangan_kondisi) }}" placeholder="Tulis rincian kondisinya disini...">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-navy mb-1 small">Status Aset <span class="text-danger">*</span></label>
                                <div class="input-group input-group-focus rounded-3">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-cog"></i></span>
                                    <select name="status_aset" class="form-select border-start-0 ps-0 shadow-none" required>
                                        <option value="" disabled>-- Pilih Status --</option>
                                        <option value="Aktif" {{ old('status_aset', $aset->status_aset) == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                                        <option value="Dalam Perbaikan" {{ old('status_aset', $aset->status_aset) == 'Dalam Perbaikan' ? 'selected' : '' }}>Dalam Perbaikan</option>
                                        <option value="Tidak Aktif" {{ old('status_aset', $aset->status_aset) == 'Tidak Aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                                        <option value="Dipinjam" {{ old('status_aset', $aset->status_aset) == 'Dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                                        <option value="Hilang" {{ old('status_aset', $aset->status_aset) == 'Hilang' ? 'selected' : '' }}>Hilang</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-navy mb-1 small">PIC Aset</label>
                                <div class="input-group input-group-focus rounded-3">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-user-tie"></i></span>
                                    <select name="pic_id" class="form-select border-start-0 ps-0 shadow-none">
                                        <option value="">-- Kosong / Tidak Ada --</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ old('pic_id', $aset->pic_id) == $user->id ? 'selected' : '' }}>{{ $user->firstname }} {{ $user->lastname }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-bold text-navy mb-1 small">Struktur Organisasi <span class="text-danger">*</span></label>
                                <div class="input-group input-group-focus rounded-3">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-sitemap"></i></span>
                                    <select class="form-select border-start-0 ps-0 shadow-none" id="kode_organisasi" name="kode_organisasi" required>
                                        <option value="" disabled>-- Pilih Organisasi --</option>
                                        @php
                                            $currentOrgKode = $aset->getKodeOrganisasiAttribute();
                                            if (!function_exists('renderOrgOptionsEdit')) {
                                                function renderOrgOptionsEdit($node, $currentVal, &$seen = [], $level = 0) {
                                                    $indent = str_repeat('&nbsp;', $level * 4);
                                                    $prefix = $level > 0 ? '→ ' : '';
                                                    
                                                    $val = null;
                                                    $type = null;
                                                    $label = null;

                                                    if (isset($node->name_director)) {
                                                        $val = $node->id_director;
                                                        $type = 'director';
                                                        $label = "Direktur: {$node->name_director}";
                                                        $printLabel = "Direktur: {$node->name_director}";
                                                    } elseif (isset($node->nm_divisi)) {
                                                        $val = $node->id_divisi;
                                                        $type = 'divisi';
                                                        $label = "Divisi: {$node->nm_divisi}";
                                                        $printLabel = "{$prefix}Divisi: {$node->nm_divisi}";
                                                    } elseif (isset($node->name_department)) {
                                                        $val = $node->id_department;
                                                        $type = 'department';
                                                        $label = "Departemen: {$node->name_department}";
                                                        $printLabel = "{$prefix}Departemen: {$node->name_department}";
                                                    } elseif (isset($node->name_section)) {
                                                        $val = $node->id_section;
                                                        $type = 'section';
                                                        $label = "Bagian: {$node->name_section}";
                                                        $printLabel = "{$prefix}Bagian: {$node->name_section}";
                                                    } elseif (isset($node->name_unit)) {
                                                        $val = $node->id_unit;
                                                        $type = 'unit';
                                                        $label = "Unit: {$node->name_unit}";
                                                        $printLabel = "{$prefix}Unit: {$node->name_unit}";
                                                    }

                                                    if ($type && $val) {
                                                        $key = $type . '_' . $val;
                                                        if (isset($seen[$key])) return; // Skip duplicate
                                                        $seen[$key] = true;

                                                        $sel = ($key == $currentVal) ? 'selected' : '';
                                                        echo "<option value='{$key}' data-label='{$label}' {$sel}>{$indent}{$printLabel}</option>";
                                                    }

                                                    if (isset($node->subDirectors)) foreach ($node->subDirectors as $s) renderOrgOptionsEdit($s, $currentVal, $seen, $level + 1);
                                                    if (isset($node->divisi)) foreach ($node->divisi as $d) renderOrgOptionsEdit($d, $currentVal, $seen, $level + 1);
                                                    if (isset($node->department)) foreach ($node->department as $dp) renderOrgOptionsEdit($dp, $currentVal, $seen, $level + 1);
                                                    if (isset($node->section)) foreach ($node->section as $sc) renderOrgOptionsEdit($sc, $currentVal, $seen, $level + 1);
                                                    if (isset($node->unit)) foreach ($node->unit as $u) renderOrgOptionsEdit($u, $currentVal, $seen, $level + 1);
                                                }
                                            }
                                            $seenArray = [];
                                            if (isset($mainDirector)) renderOrgOptionsEdit($mainDirector, old('kode_organisasi', $currentOrgKode), $seenArray);
                                        @endphp
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- DOKUMENTASI MULTI FOTO --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white pt-3 pb-2 border-bottom-0">
                        <h6 class="mb-0 fw-semibold text-primary"><i class="fas fa-camera me-2"></i> Edit Foto Aset</h6>
                    </div>
                    <div class="card-body">
                        @if($aset->foto->count() > 0)
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Foto Saat Ini (Centang untuk menghapus)</label>
                                <div class="row g-3">
                                    @foreach($aset->foto as $f)
                                        <div class="col-md-3 col-sm-4 text-center">
                                            <div class="position-relative d-inline-block border rounded p-1 mb-2">
                                                <img src="{{ asset('storage/' . $f->path_foto) }}" 
                                                     class="img-fluid rounded" 
                                                     style="max-height: 120px; cursor: zoom-in;" 
                                                     alt="Aset Foto"
                                                     data-bs-toggle="modal"
                                                     data-bs-target="#imagePreviewModal"
                                                     onclick="document.getElementById('previewImage').src=this.src;">
                                            </div>
                                            <br>
                                            <div class="form-check d-inline-block">
                                                <input class="form-check-input" type="checkbox" name="hapus_foto[]" value="{{ $f->id }}" id="hapus_{{ $f->id }}">
                                                <label class="form-check-label text-danger" for="hapus_{{ $f->id }}">
                                                    Hapus
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="mt-2">
                            <label class="form-label fw-bold text-navy mb-1 small">Tambah Foto Baru</label>
                            <div class="input-group input-group-focus rounded-3">
                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-image"></i></span>
                                <input type="file" name="foto_baru[]" class="form-control border-start-0 ps-0 shadow-none" accept="image/*" multiple>
                            </div>
                            <small class="text-muted mt-2 d-block">Bisa upload lebih dari 1 foto sekaligus. Format: JPG, PNG. Maks 4MB per foto.</small>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('aset.index') }}" class="btn btn-outline-secondary px-4 bg-white">Batal</a>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL PREVIEW FOTO --}}
<div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-transparent border-0 shadow-none">
            <div class="modal-header border-0 p-0 justify-content-end" style="position: absolute; right: 0; top: -40px; z-index: 1055;">
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="background-color: rgba(255,255,255,0.2); padding: 10px; border-radius: 50%; opacity: 1;"></button>
            </div>
            <div class="modal-body text-center p-0 position-relative">
                <img id="previewImage" src="" class="img-fluid rounded shadow-lg" style="max-height: 85vh; width: auto; object-fit: contain;">
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const inputDisplay = document.getElementById('nomor_aset_display');
        const inputDetail = document.getElementById('input_detail_lokasi');
        
        const selectKep = document.getElementById('sumber_kepemilikan_id');
        const selectUmum = document.getElementById('id_jenis_aset_umum');
        const selectJen = document.getElementById('id_jenis_aset');
        const selectKat = document.getElementById('id_kategori');
        const selectLok = document.getElementById('dropdown_lokasi');
        const selectThn = document.getElementById('id_tahun');
        
        const statusKondisi = document.getElementById('status_kondisi');
        const keteranganWrapper = document.getElementById('keterangan_kondisi_wrapper');
        const inputKeterangan = document.getElementById('keterangan_kondisi');
        
        const currentAsetId = "{{ str_pad($aset->id, 3, '0', STR_PAD_LEFT) }}";

        function updateNomor() {
            // Helper untuk ambil data-kode
            const getK = (id, fallback) => {
                const el = document.getElementById(id);
                if (!el || el.selectedIndex < 0) return fallback;
                const opt = el.options[el.selectedIndex];
                const kodeAttr = opt.getAttribute('data-kode');
                return (opt.value !== "" && kodeAttr) ? kodeAttr : fallback;
            };

            const kKep = getK('sumber_kepemilikan_id', 'XXXX');
            
            const optUmum = selectUmum.options[selectUmum.selectedIndex];
            const kUmum = (selectUmum.value && optUmum.getAttribute('data-kode')) ? optUmum.getAttribute('data-kode') : 'XX';
            
            const optKhusus = selectJen.options[selectJen.selectedIndex];
            const kKhusus = (selectJen.value && optKhusus.getAttribute('data-kode')) ? optKhusus.getAttribute('data-kode') : 'XXX';
            
            const kJen = `${kUmum}-${kKhusus}`;
            
            const kLok = getK('dropdown_lokasi', 'LOK');
            const kKat = getK('id_kategori', 'XX');
            
            // Ambil tahun
            const selectThn = document.getElementById('id_tahun');
            const thn = (selectThn && selectThn.value) ? selectThn.value : new Date().getFullYear();

            // Set hasil akhir
            const finalResult = `${currentAsetId}/${kKep}/${kJen}/${kLok}/${kKat}/${thn}`;
            document.getElementById('nomor_aset_display').value = finalResult;
        }

        // Listener Perubahan
        [selectKep, selectUmum, selectJen, selectKat, selectLok, selectThn].forEach(el => {
            if(el) el.addEventListener('change', updateNomor);
        });

        // Chained Dropdown Logic
        if(selectUmum) {
            function filterKhusus() {
                const umumId = selectUmum.value;
                const options = selectJen.querySelectorAll('option');
                
                let hasVisible = false;
                options.forEach(opt => {
                    if(opt.value === "") return;
                    if(opt.getAttribute('data-parent') === umumId) {
                        opt.style.display = 'block';
                        hasVisible = true;
                    } else {
                        opt.style.display = 'none';
                    }
                });

                if(!hasVisible && umumId !== "") {
                    // Do not reset value if we are initial loading and it matches
                    // Actually, for edit, we should keep the value if it's correct
                }
            }

            selectUmum.addEventListener('change', function() {
                const umumId = this.value;
                const options = selectJen.querySelectorAll('option');
                
                options.forEach(opt => {
                    if(opt.value === "") return;
                    if(opt.getAttribute('data-parent') === umumId) {
                        opt.style.display = 'block';
                    } else {
                        opt.style.display = 'none';
                    }
                });

                selectJen.value = "";
                updateNomor();
            });

            // Initial Filter
            filterKhusus();
        }

        // Detail Lokasi Fill
        if(selectLok) {
            selectLok.addEventListener('change', function() {
                const opt = this.options[this.selectedIndex];
                inputDetail.value = (opt && opt.value !== "") ? (opt.getAttribute('data-detail') || '') : '';
            });
            // trigger first time
            selectLok.dispatchEvent(new Event('change'));
        }

        // Auto-fill Nama Aset (jika merubah dropdown jenis)
        if(selectJen) {
            selectJen.addEventListener('change', function() {
                const opt = this.options[this.selectedIndex];
                const inputNamaAset = document.querySelector('input[name="nama_aset"]');
                if(opt && opt.value !== "") {
                    inputNamaAset.value = opt.getAttribute('data-nama') || '';
                    inputNamaAset.dispatchEvent(new Event('input'));
                }
            });
        }

        // Logic Keterangan Kondisi
        if(statusKondisi) {
            statusKondisi.addEventListener('change', function() {
                if(this.value === 'Lainnya') {
                    keteranganWrapper.style.display = 'block';
                    inputKeterangan.setAttribute('required', 'required');
                } else {
                    keteranganWrapper.style.display = 'none';
                    inputKeterangan.removeAttribute('required');
                    inputKeterangan.value = '';
                }
            });
            // initial check for old() or database value
            if(statusKondisi.value === 'Lainnya') {
                keteranganWrapper.style.display = 'block';
                inputKeterangan.setAttribute('required', 'required');
            }
        }

    });
</script>
@endpush
