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
                <div class="alert alert-danger">
                    <ul class="mb-0">
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
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-hashtag text-muted me-1"></i> Nomor Aset (Otomatis)
                                </label>
                                <input type="text" id="nomor_aset_display" name="nomor_aset" class="form-control bg-light" 
                                       value="Mencari format..." readonly>
                                <small class="text-muted">Format: [ID]/[KEPEMILIKAN]/[KODE]/[LOKASI]/[TAHUN]</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-boxes text-muted me-1"></i> Jenis Aset <span class="text-danger">*</span>
                                </label>
                                <select name="jenis_aset_khusus_id" id="id_jenis_aset" class="form-select @error('jenis_aset_khusus_id') is-invalid @enderror" required>
                                    <option value="" selected disabled>-- Pilih Jenis Aset --</option>
                                    @foreach($jenisKhusus as $jenis)
                                        <option value="{{ $jenis->id }}" data-kode="{{ $jenis->full_kode }}" {{ old('jenis_aset_khusus_id') == $jenis->id ? 'selected' : '' }}>
                                            {{ $jenis->jenis_aset }} 
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nama Aset <span class="text-danger">*</span></label>
                                <input type="text" name="nama_aset" class="form-control" value="{{ old('nama_aset') }}" placeholder="Contoh: Gedung Kantor Utama" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Merk Aset</label>
                                <input type="text" name="merek" class="form-control" value="{{ old('merek') }}" placeholder="Contoh: Lenovo / Honda">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tahun Kapitalisasi</label>
                                <select name="tahun_kapitalisasi" id="id_tahun" class="form-select">
                                    <option value="" selected disabled>-- Pilih Tahun --</option>
                                    @for ($year = date('Y'); $year >= 1900; $year--)
                                        <option value="{{ $year }}" {{ old('tahun_kapitalisasi') == $year ? 'selected' : '' }}>{{ $year }}</option>
                                    @endfor
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Deskripsi</label>
                                <textarea name="deskripsi" class="form-control" rows="1">{{ old('deskripsi') }}</textarea>
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
                                <label class="form-label fw-semibold">Sumber Kepemilikan <span class="text-danger">*</span></label>
                                <select name="sumber_kepemilikan_id" id="sumber_kepemilikan_id" class="form-select" required>
                                    <option value="" selected disabled>-- Pilih Sumber Kepemilikan --</option>
                                    @foreach($sumberKepemilikan as $sk)
                                        <option value="{{ $sk->id }}"
                                                data-kode="{{ $sk->kode ?? 'REKA' }}"
                                                {{ old('sumber_kepemilikan_id') == $sk->id ? 'selected' : '' }}>
                                            {{ $sk->kode }} — {{ $sk->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Lokasi Aset <span class="text-danger">*</span></label>
                                <select name="lokasi_id" id="dropdown_lokasi" class="form-select" required>
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

                            <div class="col-md-12">
                                <label class="form-label fw-semibold text-muted">Detail Lokasi</label>
                                <input type="text" id="input_detail_lokasi" class="form-control bg-light" readonly placeholder="Otomatis dari database...">
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
                                <label class="form-label fw-semibold">Kondisi Saat Ini <span class="text-danger">*</span></label>
                                <select name="status_kondisi" class="form-select" required>
                                    <option value="" selected disabled>-- Pilih Kondisi --</option>
                                    <option value="Baik" {{ old('status_kondisi') == 'Baik' ? 'selected' : '' }}>Baik</option>
                                    <option value="Rusak" {{ old('status_kondisi') == 'Rusak' ? 'selected' : '' }}>Rusak</option>
                                    <option value="Bongkar" {{ old('status_kondisi') == 'Bongkar' ? 'selected' : '' }}>Bongkar</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Status Aset <span class="text-danger">*</span></label>
                                <select name="status_aset" class="form-select" required>
                                    <option value="" selected disabled>-- Pilih Status --</option>
                                    <option value="Aktif" {{ old('status_aset') == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                                    <option value="Dalam Perbaikan" {{ old('status_aset') == 'Dalam Perbaikan' ? 'selected' : '' }}>Dalam Perbaikan</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">PIC Aset</label>
                                <select name="pic_id" class="form-select">
                                    <option value="" selected disabled>-- Pilih User --</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->firstname }} {{ $user->lastname }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Struktur Organisasi <span class="text-danger">*</span></label>
                                <select class="form-select" id="id_divisi" name="id_divisi" required>
                                    <option value="" selected disabled>-- Pilih Organisasi --</option>
                                    @php
                                        if (!function_exists('renderOrgOptions')) {
                                            function renderOrgOptions($node, $level = 0) {
                                                $indent = str_repeat('&nbsp;', $level * 4);
                                                $prefix = $level > 0 ? '→ ' : '';
                                                if (isset($node->name_director)) {
                                                    echo "<option value='{$node->id_director}' data-label='Direktur: {$node->name_director}'>{$indent}Direktur: {$node->name_director}</option>";
                                                } elseif (isset($node->nm_divisi)) {
                                                    echo "<option value='{$node->id_divisi}' data-label='Divisi: {$node->nm_divisi}'>{$indent}{$prefix}Divisi: {$node->nm_divisi}</option>";
                                                } elseif (isset($node->name_department)) {
                                                    echo "<option value='{$node->id_department}' data-label='Departemen: {$node->name_department}'>{$indent}{$prefix}Departemen: {$node->name_department}</option>";
                                                } elseif (isset($node->name_section)) {
                                                    echo "<option value='{$node->id_section}' data-label='Bagian: {$node->name_section}'>{$indent}{$prefix}Bagian: {$node->name_section}</option>";
                                                } elseif (isset($node->name_unit)) {
                                                    echo "<option value='{$node->id_unit}' data-label='Unit: {$node->name_unit}'>{$indent}{$prefix}Unit: {$node->name_unit}</option>";
                                                }
                                                if (isset($node->subDirectors)) foreach ($node->subDirectors as $s) renderOrgOptions($s, $level + 1);
                                                if (isset($node->divisi)) foreach ($node->divisi as $d) renderOrgOptions($d, $level + 1);
                                                if (isset($node->department)) foreach ($node->department as $dp) renderOrgOptions($dp, $level + 1);
                                                if (isset($node->section)) foreach ($node->section as $sc) renderOrgOptions($sc, $level + 1);
                                                if (isset($node->unit)) foreach ($node->unit as $u) renderOrgOptions($u, $level + 1);
                                            }
                                        }
                                        if (isset($mainDirector)) renderOrgOptions($mainDirector);
                                    @endphp
                                </select>
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
                        <input type="file" name="foto[]" class="form-control" accept="image/*" multiple>
                        <small class="text-muted">Bisa upload lebih dari 1 foto. Format: JPG, PNG. Maks 4MB per foto.</small>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <button type="reset" class="btn btn-outline-secondary px-4 bg-white">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm">Simpan Data Aset</button>
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
        
        const selectKep = document.getElementById('sumber_kepemilikan_id');
        const selectJen = document.getElementById('id_jenis_aset');
        const selectLok = document.getElementById('dropdown_lokasi');
        const selectThn = document.getElementById('id_tahun');
        const orgSelect = document.getElementById('id_divisi');
        
        const nextId = "{{ $nextId }}"; 

        function updateNomor() {
            // Ambil ID 3 Digit
            const nextId = "{{ $nextId }}"; 

            // Helper untuk ambil data-kode
            const getK = (id, fallback) => {
                const el = document.getElementById(id);
                if (!el || el.selectedIndex < 0) return fallback;
                const opt = el.options[el.selectedIndex];
                
                const kodeAttr = opt.getAttribute('data-kode');
                
                // DEBUG
                console.log(`Pengecekan ID ${id}: terpilih ${opt.text}, kode: ${kodeAttr}`);

                return (opt.value !== "" && kodeAttr) ? kodeAttr : fallback;
            };

            const kKep = getK('sumber_kepemilikan_id', 'XXXX');
            const kJen = getK('id_jenis_aset', 'XXXX');
            const kLok = getK('dropdown_lokasi', 'LOK');
            
            // Ambil tahun dari dropdown atau default ke tahun sekarang
            const selectThn = document.getElementById('id_tahun');
            const thn = (selectThn && selectThn.value) ? selectThn.value : new Date().getFullYear();

            // Set hasil akhir
            const finalResult = `${nextId}/${kKep}/${kJen}/${kLok}/${thn}`;
            document.getElementById('nomor_aset_display').value = finalResult;
        }

        [selectKep, selectJen, selectLok, selectThn].forEach(el => {
            if(el) el.addEventListener('change', updateNomor);
        });

        // Detail Lokasi 
        if(selectLok) {
            selectLok.addEventListener('change', function() {
                const opt = this.options[this.selectedIndex];
                inputDetail.value = (opt && opt.value !== "") ? (opt.getAttribute('data-detail') || '') : '';
            });
        }
        if(orgSelect) {
            orgSelect.addEventListener('change', function() {
                const opt = this.options[this.selectedIndex];
                const lbl = opt.getAttribute('data-label');
                if(lbl) opt.text = lbl;
            });
        }

        setTimeout(updateNomor, 200);
    });

    window.onpageshow = function(event) {
        if (event.persisted) window.location.reload();
    };
</script>
@endpush