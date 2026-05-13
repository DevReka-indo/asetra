@extends('layouts.app')

@section('title', 'Tambah Data Aset')

@section('content')
<div class="container-fluid px-1 py-0 mt-0">
    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold mb-0">Tambah Data Aset Baru</h3>
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
                <span class="text-muted" style="font-size: 14px; font-weight: 500; position: relative; top: 2px;">Tambah Data Aset</span>
            </li>
        </ul>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger border-0 shadow-sm rounded-3">
                    <ul class="mb-0 small">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <form action="{{ route('aset.store') }}" method="POST" enctype="multipart/form-data" id="formAset" autocomplete="off">
                @csrf

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
                                <label class="form-label fw-bold text-navy mb-1 small">Pratinjau Nomor Aset</label>
                                <div class="input-group">
                                    <input type="text" id="nomor_aset_display" class="form-control bg-light text-primary fw-bold border-0 shadow-none rounded-3 px-3 py-2" 
                                           value="Memuat..." disabled style="cursor: not-allowed; opacity: 1;">
                                    <span class="input-group-text bg-light border-0 rounded-3 text-muted small"><i class="fas fa-info-circle"></i></span>
                                </div>
                                <small class="text-muted" style="font-size: 0.7rem;">Format Baru: [KODE]/[NO_URUT]/[LOKASI]/[TAHUN]</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-navy mb-1 small">Kategori Aset <span class="text-danger">*</span></label>
                                <div class="input-group input-group-focus rounded-3">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-layer-group"></i></span>
                                    <select name="kategori_id" id="kategori_id" class="form-select border-start-0 ps-0 shadow-none" required>
                                        <option value="" selected disabled>-- Pilih Kategori Aset --</option>
                                        <optgroup label="KATEGORI ASET TETAP">
                                            @foreach($kategoriTetap as $kt)
                                                <option value="{{ $kt->id }}" data-kode="{{ $kt->kode }}" data-nama="{{ $kt->nama }}" {{ old('kategori_id') == $kt->id ? 'selected' : '' }}>
                                                    {{ $kt->kode }} - {{ $kt->nama }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                        <optgroup label="KATEGORI ASET EC">
                                            @foreach($kategoriInventaris as $ki)
                                                <option value="{{ $ki->id }}" data-kode="{{ $ki->kode }}" data-nama="{{ $ki->nama }}" {{ old('kategori_id') == $ki->id ? 'selected' : '' }}>
                                                    {{ $ki->kode }} - {{ $ki->nama }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-navy mb-1 small">Nama Aset <span class="text-danger">*</span></label>
                                <div class="input-group input-group-focus rounded-3">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-box-open"></i></span>
                                    <input type="text" name="nama_aset" id="nama_aset" class="form-control border-start-0 ps-0 shadow-none" value="{{ old('nama_aset') }}" placeholder="Contoh: Gedung Kantor Utama" required>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold text-navy mb-1 small">Tahun Kapitalisasi <span class="text-danger">*</span></label>
                                <div class="input-group input-group-focus rounded-3">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-calendar-alt"></i></span>
                                    <select name="tahun_kapitalisasi" id="id_tahun" class="form-select border-start-0 ps-0 shadow-none" required>
                                        <option value="" selected disabled>-- Tahun --</option>
                                        @for ($year = date('Y'); $year >= 1900; $year--)
                                            <option value="{{ $year }}" {{ old('tahun_kapitalisasi') == $year ? 'selected' : '' }}>{{ $year }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold text-navy mb-1 small">Merk Aset <span class="text-danger">*</span></label>
                                <div class="input-group input-group-focus rounded-3">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-tag"></i></span>
                                    <input type="text" name="merek" class="form-control border-start-0 ps-0 shadow-none" value="{{ old('merek') }}" placeholder="Lenovo / Honda" required>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold text-navy mb-1 small">Nomor BAST (Opsional)</label>
                                <div class="input-group input-group-focus rounded-3">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-file-signature"></i></span>
                                    <input type="text" name="bast" class="form-control border-start-0 ps-0 shadow-none" value="{{ old('bast') }}" placeholder="Contoh: 001/BAST/2023">
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold text-navy mb-1 small">Deskripsi Aset <span class="text-danger">*</span></label>
                                <div class="input-group input-group-focus rounded-3">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-align-left"></i></span>
                                    <textarea name="deskripsi" class="form-control border-start-0 ps-0 shadow-none" rows="2" placeholder="Rincian detail aset..." required>{{ old('deskripsi') }}</textarea>
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
                                <label class="form-label fw-bold text-navy mb-1 small">Lokasi Aset <span class="text-danger">*</span></label>
                                <div class="input-group input-group-focus rounded-3">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-map-marker-alt"></i></span>
                                    <select name="lokasi_id" id="dropdown_lokasi" class="form-select border-start-0 ps-0 shadow-none" required>
                                        <option value="" selected disabled>-- Pilih Lokasi --</option>
                                        @foreach($lokasi as $lok)
                                            <option value="{{ $lok->lokasi_id }}" 
                                                    data-detail="{{ $lok->detail_lokasi ?? '' }}"
                                                    data-kode="{{ $lok->kode_lokasi ?? 'LOK' }}"
                                                    {{ old('lokasi_id') == $lok->lokasi_id ? 'selected' : '' }}>
                                                {{ $lok->nama_lokasi ?? $lok->nm_lokasi_aset }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
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
                            <div class="col-md-3">
                                <label class="form-label fw-bold text-navy mb-1 small">Kondisi Saat Ini <span class="text-danger">*</span></label>
                                <div class="input-group input-group-focus rounded-3">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-info-circle"></i></span>
                                    <select name="status_kondisi" id="status_kondisi" class="form-select border-start-0 ps-0 shadow-none" required>
                                        <option value="" selected disabled>-- Pilih Kondisi --</option>
                                        <option value="Baik" {{ old('status_kondisi') == 'Baik' ? 'selected' : '' }}>Baik</option>
                                        <option value="Rusak" {{ old('status_kondisi') == 'Rusak' ? 'selected' : '' }}>Rusak</option>
                                        <option value="Bongkar" {{ old('status_kondisi') == 'Bongkar' ? 'selected' : '' }}>Bongkar</option>
                                        <option value="Tidak Terpakai" {{ old('status_kondisi') == 'Tidak Terpakai' ? 'selected' : '' }}>Tidak Terpakai</option>
                                        <option value="Hilang" {{ old('status_kondisi') == 'Hilang' ? 'selected' : '' }}>Hilang</option>
                                        <option value="Tidak Teridentifikasi" {{ old('status_kondisi') == 'Tidak Teridentifikasi' ? 'selected' : '' }}>Tidak Teridentifikasi</option>
                                        <option value="Lainnya" {{ old('status_kondisi') == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                                    </select>
                                </div>
                            </div>
                            
                            {{-- Field Keterangan Lainnya --}}
                            <div class="col-md-12 mt-2" id="keterangan_kondisi_wrapper" style="display: none;">
                                <label class="form-label fw-bold text-navy mb-1 small">Spesifikasikan Kondisi Lainnya <span class="text-danger">*</span></label>
                                <div class="input-group input-group-focus rounded-3">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-pen"></i></span>
                                    <input type="text" name="keterangan_kondisi" id="keterangan_kondisi" class="form-control border-start-0 ps-0 shadow-none" value="{{ old('keterangan_kondisi') }}" placeholder="Tulis rincian kondisinya disini...">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold text-navy mb-1 small">Status Aset <span class="text-danger">*</span></label>
                                <div class="input-group input-group-focus rounded-3">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-cog"></i></span>
                                    <select name="status_aset" class="form-select border-start-0 ps-0 shadow-none" required>
                                        <option value="" selected disabled>-- Pilih Status --</option>
                                        <option value="Aktif" {{ old('status_aset') == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                                        <option value="Dalam Perbaikan" {{ old('status_aset') == 'Dalam Perbaikan' ? 'selected' : '' }}>Dalam Perbaikan</option>
                                        <option value="Tidak Aktif" {{ old('status_aset') == 'Tidak Aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                                        <option value="Dipinjam" {{ old('status_aset') == 'Dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                                        <option value="Hilang" {{ old('status_aset') == 'Hilang' ? 'selected' : '' }}>Hilang</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold text-navy mb-1 small">PIC Aset <span class="text-danger">*</span></label>
                                <div class="input-group input-group-focus rounded-3">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-user-tie"></i></span>
                                    <select name="pic_id" class="form-select border-start-0 ps-0 shadow-none" required>
                                        <option value="" selected disabled>-- Pilih User --</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ old('pic_id') == $user->id ? 'selected' : '' }}>{{ $user->firstname }} {{ $user->lastname }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold text-navy mb-1 small">Penanggung Jawab Aset <span class="text-danger">*</span></label>
                                <div class="input-group input-group-focus rounded-3">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-user-shield"></i></span>
                                    <select name="penanggung_jawab_id" class="form-select border-start-0 ps-0 shadow-none" required>
                                        <option value="" selected disabled>-- Pilih User --</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ old('penanggung_jawab_id') == $user->id ? 'selected' : '' }}>{{ $user->firstname }} {{ $user->lastname }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold text-navy mb-1 small">Struktur Organisasi <span class="text-danger">*</span></label>
                                <div class="input-group input-group-focus rounded-3">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-sitemap"></i></span>
                                    <select class="form-select border-start-0 ps-0 shadow-none" id="kode_organisasi" name="kode_organisasi" required>
                                        <option value="" selected disabled>-- Pilih Organisasi --</option>
                                        @php
                                            if (!function_exists('renderOrgOptions')) {
                                                function renderOrgOptions($node, &$seen = [], $level = 0) {
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

                                                        $selected = old('kode_organisasi') == $key ? 'selected' : '';
                                                        echo "<option value='{$key}' data-label='{$label}' {$selected}>{$indent}{$printLabel}</option>";
                                                    }

                                                    if (isset($node->subDirectors)) foreach ($node->subDirectors as $s) renderOrgOptions($s, $seen, $level + 1);
                                                    if (isset($node->divisi)) foreach ($node->divisi as $d) renderOrgOptions($d, $seen, $level + 1);
                                                    if (isset($node->department)) foreach ($node->department as $dp) renderOrgOptions($dp, $seen, $level + 1);
                                                    if (isset($node->section)) foreach ($node->section as $sc) renderOrgOptions($sc, $seen, $level + 1);
                                                    if (isset($node->unit)) foreach ($node->unit as $u) renderOrgOptions($u, $seen, $level + 1);
                                                }
                                            }
                                            $seenArray = [];
                                            if (isset($mainDirector)) renderOrgOptions($mainDirector, $seenArray);
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
                        <h6 class="mb-0 fw-semibold text-primary"><i class="fas fa-camera me-2"></i> Foto Aset</h6>
                    </div>
                    <div class="card-body">
                        <div class="input-group input-group-focus rounded-3">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-image"></i></span>
                            <input type="file" name="foto[]" class="form-control border-start-0 ps-0 shadow-none" accept="image/*" multiple required>
                        </div>
                        <small class="text-muted mt-2 d-block">Bisa upload lebih dari 1 foto sekaligus. Format: JPG, PNG. Maks 4MB per foto.</small>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <button type="reset" class="btn btn-outline-secondary px-4 bg-white rounded-pill border">Batal</button>
                    <button type="submit" class="btn text-white rounded-pill px-4 fw-bold shadow-sm" style="background-color: #253070;">
                        <i class="fas fa-save me-1"></i> Simpan Data Aset
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const inputDisplay = document.getElementById('nomor_aset_display');
        const inputDetail = document.getElementById('input_detail_lokasi');
        
        const selectKategori = document.getElementById('kategori_id');
        const selectLok = document.getElementById('dropdown_lokasi');
        const selectThn = document.getElementById('id_tahun');
        const statusKondisi = document.getElementById('status_kondisi');
        const keteranganWrapper = document.getElementById('keterangan_kondisi_wrapper');
        const inputKeterangan = document.getElementById('keterangan_kondisi');
        const inputNamaAset = document.getElementById('nama_aset');
        
        const nextId = "{{ $nextId }}"; 

        function updateNomor() {
            // Kode Kategori
            const optKat = selectKategori.options[selectKategori.selectedIndex];
            const kKat = (selectKategori.value && optKat.getAttribute('data-kode')) ? optKat.getAttribute('data-kode') : 'XXX';
            
            // Kode Lokasi
            const optLok = selectLok.options[selectLok.selectedIndex];
            const kLok = (selectLok.value && optLok.getAttribute('data-kode')) ? optLok.getAttribute('data-kode') : 'LOK';
            
            // Tahun
            const thn = (selectThn && selectThn.value) ? selectThn.value : new Date().getFullYear();

            // Set hasil akhir: [KODE]/[ID]/[LOKASI]/[TAHUN]
            const finalResult = `${kKat}/${nextId}/${kLok}/${thn}`;
            inputDisplay.value = finalResult;
        }

        [selectKategori, selectLok, selectThn].forEach(el => {
            if(el) el.addEventListener('change', updateNomor);
        });

        // Detail Lokasi & Auto-fill Nama Aset
        if(selectLok) {
            selectLok.addEventListener('change', function() {
                const opt = this.options[this.selectedIndex];
                inputDetail.value = (opt && opt.value !== "") ? (opt.getAttribute('data-detail') || '') : '';
            });
        }
        
        if(selectKategori) {
            selectKategori.addEventListener('change', function() {
                const opt = this.options[this.selectedIndex];
                if(opt && opt.value !== "" && inputNamaAset.value === "") {
                    inputNamaAset.value = opt.getAttribute('data-nama') || '';
                }
                updateNomor();
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
            if(statusKondisi.value === 'Lainnya') {
                keteranganWrapper.style.display = 'block';
                inputKeterangan.setAttribute('required', 'required');
            }
        }

        // Jalankan pratinjau pertama kali
        setTimeout(updateNomor, 100);
    });
</script>
@endpush