@extends('layouts.app')

@section('title', 'Edit Data Aset')

@section('content')
<div class="container-fluid px-1 py-0 mt-0">
    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold mb-0">Edit Data Aset</h3>
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
                <div class="alert alert-danger border-0 shadow-sm rounded-3">
                    <ul class="mb-0 small">
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
                                <label class="form-label fw-bold text-navy mb-1 small">Nomor Aset Saat Ini</label>
                                <div class="input-group">
                                    <input type="text" id="nomor_aset_display" class="form-control bg-light text-primary fw-bold border-0 shadow-none rounded-3 px-3 py-2" 
                                           value="{{ $aset->nomor_aset }}" disabled style="cursor: not-allowed; opacity: 1;">
                                    <span class="input-group-text bg-light border-0 rounded-3 text-muted small"><i class="fas fa-info-circle"></i></span>
                                </div>
                                <small class="text-muted" style="font-size: 0.7rem;">Nomor aset akan diperbarui otomatis jika klasifikasi, lokasi, atau tahun diubah.</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-navy mb-1 small">Kategori Aset <span class="text-danger">*</span></label>
                                <div class="input-group input-group-focus rounded-3">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-layer-group"></i></span>
                                    <select name="kategori_id" id="kategori_id" class="form-select border-start-0 ps-0 shadow-none" required>
                                        <option value="" disabled>-- Pilih Kategori Aset --</option>
                                        <optgroup label="KATEGORI ASET TETAP">
                                            @foreach($kategoriTetap as $kt)
                                                <option value="{{ $kt->id }}" data-kode="{{ $kt->kode }}" {{ (old('kategori_id', $aset->kategori_id) == $kt->id) ? 'selected' : '' }}>
                                                    {{ $kt->kode }} - {{ $kt->nama }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                        <optgroup label="KATEGORI ASET EC">
                                            @foreach($kategoriInventaris as $ki)
                                                <option value="{{ $ki->id }}" data-kode="{{ $ki->kode }}" {{ (old('kategori_id', $aset->kategori_id) == $ki->id) ? 'selected' : '' }}>
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
                                    <input type="text" name="nama_aset" class="form-control border-start-0 ps-0 shadow-none" value="{{ old('nama_aset', $aset->nama_aset) }}" placeholder="Contoh: Gedung Kantor Utama" required>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold text-navy mb-1 small">Tahun Kapitalisasi <span class="text-danger">*</span></label>
                                <div class="input-group input-group-focus rounded-3">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-calendar-alt"></i></span>
                                    <select name="tahun_kapitalisasi" id="id_tahun" class="form-select border-start-0 ps-0 shadow-none" required>
                                        <option value="" disabled>-- Tahun --</option>
                                        @for ($year = date('Y'); $year >= 1900; $year--)
                                            <option value="{{ $year }}" {{ old('tahun_kapitalisasi', $aset->tahun_kapitalisasi) == $year ? 'selected' : '' }}>{{ $year }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold text-navy mb-1 small">Merk Aset <span class="text-danger">*</span></label>
                                <div class="input-group input-group-focus rounded-3">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-tag"></i></span>
                                    <input type="text" name="merek" class="form-control border-start-0 ps-0 shadow-none" value="{{ old('merek', $aset->merek) }}" placeholder="Lenovo / Honda" required>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold text-navy mb-1 small">Nomor BAST (Opsional)</label>
                                <div class="input-group input-group-focus rounded-3">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-file-signature"></i></span>
                                    <input type="text" name="bast" class="form-control border-start-0 ps-0 shadow-none" value="{{ old('bast', $aset->bast) }}" placeholder="Contoh: 001/BAST/2023">
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold text-navy mb-1 small">Deskripsi Aset <span class="text-danger">*</span></label>
                                <div class="input-group input-group-focus rounded-3">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-align-left"></i></span>
                                    <textarea name="deskripsi" class="form-control border-start-0 ps-0 shadow-none" rows="2" placeholder="Rincian detail aset..." required>{{ old('deskripsi', $aset->deskripsi) }}</textarea>
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

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-navy mb-1 small text-muted">Detail Lokasi</label>
                                <input type="text" id="input_detail_lokasi" class="form-control bg-light text-muted border-0 shadow-none rounded-3 px-3 py-2" 
                                       disabled placeholder="Otomatis dari lokasi terpilih" style="cursor: not-allowed; opacity: 0.8;"
                                       value="{{ $aset->lokasi->detail_lokasi ?? '' }}">
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
                            <div class="col-md-12 mt-2" id="keterangan_kondisi_wrapper" style="{{ old('status_kondisi', $aset->status_kondisi) == 'Lainnya' ? 'display: block;' : 'display: none;' }}">
                                <label class="form-label fw-bold text-navy mb-1 small">Spesifikasikan Kondisi Lainnya <span class="text-danger">*</span></label>
                                <div class="input-group input-group-focus rounded-3">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-pen"></i></span>
                                    <input type="text" name="keterangan_kondisi" id="keterangan_kondisi" class="form-control border-start-0 ps-0 shadow-none" value="{{ old('keterangan_kondisi', $aset->keterangan_kondisi) }}" placeholder="Tulis rincian kondisinya disini...">
                                </div>
                            </div>

                            <div class="col-md-3">
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

                            <div class="col-md-3">
                                <label class="form-label fw-bold text-navy mb-1 small">PIC Aset <span class="text-danger">*</span></label>
                                <div class="input-group input-group-focus rounded-3">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-user-tie"></i></span>
                                    <select name="pic_id" class="form-select border-start-0 ps-0 shadow-none" required>
                                        <option value="" selected disabled>-- Pilih User --</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ old('pic_id', $aset->pic_id) == $user->id ? 'selected' : '' }}>{{ $user->firstname }} {{ $user->lastname }}</option>
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
                                            <option value="{{ $user->id }}" {{ old('penanggung_jawab_id', $aset->penanggung_jawab_id) == $user->id ? 'selected' : '' }}>{{ $user->firstname }} {{ $user->lastname }}</option>
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
                                            if (!function_exists('renderOrgOptions')) {
                                                function renderOrgOptions($node, $currentKey, &$seen = [], $level = 0) {
                                                    $indent = str_repeat('&nbsp;', $level * 4);
                                                    $prefix = $level > 0 ? '→ ' : '';
                                                    
                                                    $val = null;
                                                    $type = null;
                                                    $label = null;

                                                    if (isset($node->name_director)) { $val = $node->id_director; $type = 'director'; $label = "Direktur: {$node->name_director}"; }
                                                    elseif (isset($node->nm_divisi)) { $val = $node->id_divisi; $type = 'divisi'; $label = "Divisi: {$node->nm_divisi}"; }
                                                    elseif (isset($node->name_department)) { $val = $node->id_department; $type = 'department'; $label = "Departemen: {$node->name_department}"; }
                                                    elseif (isset($node->name_section)) { $val = $node->id_section; $type = 'section'; $label = "Bagian: {$node->name_section}"; }
                                                    elseif (isset($node->name_unit)) { $val = $node->id_unit; $type = 'unit'; $label = "Unit: {$node->name_unit}"; }

                                                    if ($type && $val) {
                                                        $key = $type . '_' . $val;
                                                        if (isset($seen[$key])) return;
                                                        $seen[$key] = true;

                                                        $selected = ($currentKey == $key) ? 'selected' : '';
                                                        echo "<option value='{$key}' data-label='{$label}' {$selected}>{$indent}{$prefix}{$label}</option>";
                                                    }

                                                    if (isset($node->subDirectors)) foreach ($node->subDirectors as $s) renderOrgOptions($s, $currentKey, $seen, $level + 1);
                                                    if (isset($node->divisi)) foreach ($node->divisi as $d) renderOrgOptions($d, $currentKey, $seen, $level + 1);
                                                    if (isset($node->department)) foreach ($node->department as $dp) renderOrgOptions($dp, $currentKey, $seen, $level + 1);
                                                    if (isset($node->section)) foreach ($node->section as $sc) renderOrgOptions($sc, $currentKey, $seen, $level + 1);
                                                    if (isset($node->unit)) foreach ($node->unit as $u) renderOrgOptions($u, $currentKey, $seen, $level + 1);
                                                }
                                            }
                                            
                                            // Tentukan key saat ini
                                            $currentOrgKey = null;
                                            if ($aset->id_unit) $currentOrgKey = "unit_{$aset->id_unit}";
                                            elseif ($aset->id_section) $currentOrgKey = "section_{$aset->id_section}";
                                            elseif ($aset->id_department) $currentOrgKey = "department_{$aset->id_department}";
                                            elseif ($aset->id_divisi) $currentOrgKey = "divisi_{$aset->id_divisi}";
                                            elseif ($aset->id_director) $currentOrgKey = "director_{$aset->id_director}";

                                            $seenArray = [];
                                            if (isset($mainDirector)) renderOrgOptions($mainDirector, old('kode_organisasi', $currentOrgKey), $seenArray);
                                        @endphp
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- DOKUMENTASI FOTO --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white pt-3 pb-2 border-bottom-0">
                        <h6 class="mb-0 fw-semibold text-primary"><i class="fas fa-camera me-2"></i> Foto Aset</h6>
                    </div>
                    <div class="card-body">
                        {{-- List foto saat ini --}}
                        @if($aset->foto->count() > 0)
                            <div class="row g-2 mb-3">
                                @foreach($aset->foto as $f)
                                    <div class="col-md-2 position-relative">
                                        <div class="rounded-3 overflow-hidden shadow-sm" style="height: 100px;">
                                            <img src="{{ asset('storage/' . $f->path) }}" class="w-100 h-100 object-fit-cover" alt="Foto Aset">
                                        </div>
                                        <div class="form-check position-absolute top-0 end-0 m-1 bg-white rounded px-1 shadow-sm">
                                            <input class="form-check-input" type="checkbox" name="hapus_foto[]" value="{{ $f->id }}" id="f{{ $f->id }}">
                                            <label class="form-check-label text-danger small cursor-pointer" for="f{{ $f->id }}">
                                                <i class="fas fa-trash"></i>
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <p class="text-muted small"><i class="fas fa-info-circle me-1"></i> Centang foto yang ingin dihapus.</p>
                        @endif

                        <label class="form-label fw-bold text-navy mb-1 small">Tambah Foto Baru</label>
                        <div class="input-group input-group-focus rounded-3">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-plus"></i></span>
                            <input type="file" name="foto_baru[]" class="form-control border-start-0 ps-0 shadow-none" accept="image/*" multiple>
                        </div>
                        <small class="text-muted mt-2 d-block">Maks 4MB per foto.</small>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('aset.index') }}" class="btn btn-outline-secondary px-4 bg-white rounded-pill border">Batal</a>
                    <button type="submit" class="btn text-white rounded-pill px-4 fw-bold shadow-sm" style="background-color: #253070;">
                        <i class="fas fa-save me-1"></i> Simpan Perubahan
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
        
        // No Urut tetap dari ID aset (diformat 5 digit)
        const assetIdFormatted = "{{ str_pad($aset->id, 5, '0', STR_PAD_LEFT) }}"; 

        function updateNomor() {
            // Kode Kategori
            const optKat = selectKategori.options[selectKategori.selectedIndex];
            const kKat = (selectKategori.value && optKat.getAttribute('data-kode')) ? optKat.getAttribute('data-kode') : 'XXX';
            
            // Kode Lokasi
            const optLok = selectLok.options[selectLok.selectedIndex];
            const kLok = (selectLok.value && optLok.getAttribute('data-kode')) ? optLok.getAttribute('data-kode') : 'LOK';
            
            // Tahun
            const thn = (selectThn && selectThn.value) ? selectThn.value : new Date().getFullYear();

            // Hasil: [KODE]/[ID]/[LOKASI]/[TAHUN]
            const finalResult = `${kKat}/${assetIdFormatted}/${kLok}/${thn}`;
            inputDisplay.value = finalResult;
        }

        [selectKategori, selectLok, selectThn].forEach(el => {
            if(el) el.addEventListener('change', updateNomor);
        });

        if(selectLok) {
            selectLok.addEventListener('change', function() {
                const opt = this.options[this.selectedIndex];
                inputDetail.value = (opt && opt.value !== "") ? (opt.getAttribute('data-detail') || '') : '';
            });
        }

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
        }
    });
</script>
@endpush
