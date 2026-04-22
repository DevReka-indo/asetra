@extends('layouts.app')

@section('title', 'Detail Aset - ' . $aset->nomor_aset)

@section('content')
<div class="container-fluid px-1 py-0 mt-0">
    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold mb-0">Detail Aset - {{ $aset->nomor_aset }}</h3>
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
                <span class="text-muted" style="font-size: 14px; font-weight: 500; position: relative; top: 2px;">Detail Aset</span>
            </li>
        </ul>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">

            <div class="row">
                {{-- SISI KIRI: QR CODE & FOTO --}}
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm mb-4 text-center">
                        <div class="card-header bg-white pt-3 pb-2">
                            <h6 class="fw-bold text-primary mb-0">Label QR Aset</h6>
                        </div>
                        <div class="card-body pt-3">
                            <div class="d-inline-block p-3 bg-white border rounded mb-3">
                                {!! QrCode::size(180)->generate(url('/aset/'.$aset->id)) !!}
                            </div>
                            <h5 class="fw-bold mb-1">{{ $aset->nomor_aset }}</h5>
                            <p class="text-muted small mb-0">{{ $aset->nama_aset }}</p>
                        </div>
                        <div class="card-footer bg-light border-0">
                            <button class="btn btn-sm btn-primary w-100" onclick="window.print()">
                                <i class="fas fa-print me-2"></i>Cetak Label Aset
                            </button>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white pt-3 pb-2">
                            <h6 class="fw-bold text-primary mb-0">Dokumentasi Aset</h6>
                        </div>
                        <div class="card-body p-0 text-center">
                            @if($aset->foto->isNotEmpty())
                                @foreach($aset->foto as $foto)
                                    <img src="{{ asset('storage/' . $foto->path_foto) }}"
                                         class="img-fluid {{ !$loop->last ? 'border-bottom' : 'rounded-bottom' }}"
                                         alt="Foto Aset"
                                         style="cursor: zoom-in;"
                                         data-bs-toggle="modal"
                                         data-bs-target="#imagePreviewModal"
                                         onclick="document.getElementById('previewImage').src=this.src;">
                                @endforeach
                            @else
                                <div class="py-5 bg-light text-muted">
                                    <i class="fas fa-camera fa-3x mb-2"></i><br>
                                    Tidak ada foto
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- SISI KANAN: DETAIL INFORMASI --}}
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm mb-4">
                        
                        {{-- HEADER DISAMAKAN PERSIS DENGAN SISI KIRI --}}
                        <div class="card-header bg-white pt-3 pb-2">
                            <h6 class="fw-bold text-primary mb-0 text-center">Informasi Aset</h6>
                        </div>
                        
                        <div class="card-body px-4 pb-4 pt-3">
                            {{-- Row 1: Highlight Data Utama --}}
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <div class="info-box">
                                        <span class="info-label">Nama Aset</span>
                                        <span class="info-value">{{ $aset->nama_aset }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-box ">
                                        <span class="info-label">Merk / Model</span>
                                        <span class="info-value">{{ $aset->merek ?? 'Tidak ada data' }}</span>
                                    </div>
                                </div>
                            </div>

                            <h6 class="fw-bold mb-3 text-navy border-bottom pb-2">Spesifikasi Detail</h6>

                            {{-- Row 2: Detail --}}
                            <div class="row g-4">
                                <div class="col-md-6 d-flex align-items-start">
                                    <div class="icon-wrapper text-navy me-3">
                                        <i class="fas fa-tags"></i>
                                    </div>
                                    <div>
                                        <span class="info-label">Sumber Kepemilikan</span>
                                        <span class="info-value">{{ $aset->sumberKepemilikan->nama ?? '-' }}</span>
                                    </div>
                                </div>

                                <div class="col-md-6 d-flex align-items-start">
                                    <div class="icon-wrapper text-navy me-3">
                                        <i class="fas fa-tags"></i>
                                    </div>
                                    <div>
                                        <span class="info-label">Jenis Aset</span>
                                        <span class="badge bg-navy text-white px-3 py-2 rounded-pill">
                                            {{ $aset->jenisAsetKhusus->jenis_aset ?? '-' }}
                                        </span>
                                    </div>
                                </div>

                                <div class="col-md-6 d-flex align-items-start">
                                    <div class="icon-wrapper text-navy me-3">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                    <div>
                                        <span class="info-label">Tahun Kapitalisasi</span>
                                        <span class="info-value">{{ $aset->tahun_kapitalisasi ?? '-' }}</span>
                                    </div>
                                </div>

                                <div class="col-md-6 d-flex align-items-start">
                                    <div class="icon-wrapper text-navy me-3">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </div>
                                    <div>
                                        <span class="info-label">Lokasi Penempatan</span>
                                        <span class="info-value">{{ $aset->lokasi->nama_lokasi ?? '-' }}</span>
                                    </div>
                                </div>

                                <div class="col-md-6 d-flex align-items-start">
                                    <div class="icon-wrapper text-navy me-3">
                                        <i class="fas fa-info-circle"></i>
                                    </div>
                                    <div>
                                        <span class="info-label">Kondisi Aset</span>
                                        @if($aset->status_kondisi == 'Baik')
                                            <span class="badge bg-success px-3 py-2 rounded-pill"><i class="fas fa-check-circle me-1"></i> {{ $aset->status_kondisi }}</span>
                                        @else
                                            <span class="badge bg-danger px-3 py-2 rounded-pill"><i class="fas fa-exclamation-triangle me-1"></i> {{ $aset->status_kondisi ?? '-' }}</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-6 d-flex align-items-start">
                                    <div class="icon-wrapper text-navy me-3">
                                        <i class="fas fa-info-circle"></i>
                                    </div>
                                    <div>
                                        <span class="info-label">Status Aset</span>
                                        @if($aset->status_aset == 'Aktif')
                                            <span class="badge bg-success px-3 py-2 rounded-pill"><i class="fas fa-check-circle me-1"></i> {{ $aset->status_aset }}</span>
                                        @else
                                            <span class="badge bg-secondary px-3 py-2 rounded-pill"><i class="fas fa-times-circle me-1"></i> {{ $aset->status_aset ?? '-' }}</span>
                                        @endif
                                    </div>
                                </div>  
                                
                                <div class="col-md-6 d-flex align-items-start">
                                    <div class="icon-wrapper text-navy me-3">
                                        <i class="fas fa-sitemap"></i>
                                    </div>
                                    <div>
                                        <span class="info-label">Penempatan Organisasi</span>
                                        <span class="info-value">{{ $aset->organisasi_terikat }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6 d-flex align-items-start">
                                    <div class="icon-wrapper text-navy me-3">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div>
                                        <span class="info-label">Penanggung Jawab</span>
                                        <span class="info-value">{{ $aset->pic ? $aset->pic->firstname . ' ' . $aset->pic->lastname : '-' }}</span>
                                    </div>
                                </div>

                            {{-- Row 3: Deskripsi Full Width --}}
                            <div class="row mt-4 pt-3 border-top">
                                <div class="col-12">
                                    <div class="d-flex align-items-start">
                                        <div class="icon-wrapper text-navy me-3">
                                            <i class="fas fa-align-left"></i>
                                        </div>
                                        <div>
                                            <span class="info-label">Deskripsi Aset</span>
                                            <p class="text-secondary mb-0 mt-1" style="line-height: 1.6;">
                                                {{ $aset->deskripsi ?? 'Tidak ada deskripsi tambahan untuk aset ini.' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            {{-- FOOTER INFO --}}
            <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                <small class="text-muted">Data awal ditambahkan pada: {{ $aset->created_at->format('d M Y, H:i') }}</small>

                <div class="d-flex gap-2">
                    {{-- Tombol Ajukan Perbaikan --}}
                    @php
                        $adaPengajuanAktif = $aset->pengajuanPerbaikanAktif()->exists();
                    @endphp

                    @if($adaPengajuanAktif)
                        <button type="button" class="btn btn-warning d-flex align-items-center rounded-pill" disabled>
                            <i class="fas fa-tools me-2"></i> Pengajuan Sedang Diproses
                        </button>
                    @else
                        <button type="button" class="btn d-flex align-items-center rounded-pill"
                                style="background-color: #c0392b; color: white;"
                                data-bs-toggle="modal" data-bs-target="#modalPerbaikan">
                            <i class="fas fa-tools me-2"></i> Ajukan Perbaikan
                        </button>
                    @endif

                    {{-- TOMBOL TRIGGER MODAL MONITORING --}}
                    <button type="button" class="btn btn-navy d-flex align-items-center rounded-pill" style="background-color: #253070; color: white;" data-bs-toggle="modal" data-bs-target="#modalMonitoring">
                        <i class="fas fa-search-plus me-2"></i> Update Monitoring Log
                    </button>
                </div>
            </div>

            {{-- BAGIAN TABEL RIWAYAT MONITORING (LOG ASET) --}}
            <div class="mt-5">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <h5 class="fw-bold text-navy mb-0"><i class="fas fa-history me-2"></i> Riwayat Monitoring & Kondisi</h5>
                    <small class="text-muted">{{ $aset->logAset->count() }} catatan</small>
                </div>
                
                <div class="table-responsive" style="max-height: 500px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 0.375rem;">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light sticky-top" style="top: 0; z-index: 10;">
                            <tr>
                                <th>Tanggal Cek</th>
                                <th>Kondisi Fisik </th>
                                <th>Status Terkini</th>
                                <th>Lokasi / Divisi Tercatat</th>
                                <th>Dicatat Oleh</th>
                                <th>Dokumentasi</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($aset->logAset as $log)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($log->tanggal_cek)->format('d M Y') }}</td>
                                <td>
                                    @if($log->kondisi == 'Baik')
                                        <span class="badge bg-success">{{ $log->kondisi }}</span>
                                    @else
                                        <span class="badge bg-danger">{{ $log->kondisi }}</span>
                                    @endif
                                </td>
                                <td>{{ $log->status_aset ?? '-' }}</td>
                                <td>
                                    @if($log->lokasi || $log->organisasi_terikat !== 'Tanpa Organisasi')
                                        <small class="d-block text-navy fw-bold">{{ $log->lokasi->nama_lokasi ?? '-' }}</small>
                                        <small class="d-block text-muted">{{ $log->organisasi_terikat }}</small>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $log->dicatatOleh->firstname ?? '-' }}</td>
                                <td>
                                    @if($log->foto_bukti)
                                        <a href="{{ asset('storage/' . $log->foto_bukti) }}" target="_blank" class="btn btn-sm btn-outline-info rounded-pill py-1 px-2" title="Lihat Foto Bukti">
                                            <i class="fas fa-image me-1"></i> Lihat
                                        </a>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td><small>{{ $log->keterangan ?? '-' }}</small></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-3">Belum ada data monitoring (log) untuk aset ini.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- MODAL FORM MONITORING --}}
<div class="modal fade" id="modalMonitoring" tabindex="-1" aria-labelledby="modalMonitoringLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-navy text-white text-center d-block position-relative border-0 shadow-sm" style="background-color: #253070;">
                <h5 class="modal-title fw-bold text-white mb-0" id="modalMonitoringLabel">
                    <i class="fas fa-clipboard-check me-2"></i> Update Monitoring Log / Kondisi Aset
                </h5>
                <button type="button" class="btn-close btn-close-white position-absolute top-50 translate-middle-y end-0 me-3" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('log-aset.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4 bg-light">
                    
                    {{-- Alert Note --}}
                    <div class="alert alert-info border-info d-flex align-items-center py-2 px-3 mb-4 rounded-3 shadow-sm" role="alert">
                        <i class="fas fa-info-circle fa-lg me-3 text-info"></i>
                        <span class="small m-0 text-dark">Laporan ini akan memperbarui <strong>status aset, lokasi, dan divisi utama</strong> pada master data secara otomatis. Cukup kosongkan *Keterangan* jika tidak ada keluhan.</span>
                    </div>

                    <input type="hidden" name="aset_id" value="{{ $aset->id }}">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-navy mb-1 small">Kondisi Aset Saat Ini <span class="text-danger">*</span></label>
                            <select name="kondisi" class="form-select bg-white border-0 shadow-sm" required>
                                <option value="Baik" {{ $aset->status_kondisi == 'Baik' ? 'selected' : '' }}>🟢 Baik</option>
                                <option value="Rusak" {{ $aset->status_kondisi == 'Rusak' ? 'selected' : '' }}>🔴 Rusak</option>
                                <option value="Bongkar" {{ $aset->status_kondisi == 'Bongkar' ? 'selected' : '' }}>🔨 Bongkar</option>
                                <option value="Tidak Terpakai" {{ $aset->status_kondisi == 'Tidak Terpakai' ? 'selected' : '' }}>⚪ Tidak Terpakai</option>
                                <option value="Hilang" {{ $aset->status_kondisi == 'Hilang' ? 'selected' : '' }}>❌ Hilang</option>
                                <option value="Lainnya" {{ $aset->status_kondisi == 'Lainnya' ? 'selected' : '' }}>📝 Lainnya</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-navy mb-1 small">Status Aset</label>
                            <select name="status_aset" class="form-select bg-white border-0 shadow-sm">
                                <option value="Aktif" {{ $aset->status_aset == 'Aktif' ? 'selected' : '' }}>🟢 Aktif (Sedang digunakan)</option>
                                <option value="Tidak Aktif" {{ $aset->status_aset == 'Tidak Aktif' ? 'selected' : '' }}>⚪ Tidak Aktif</option>
                                <option value="Dalam Perbaikan" {{ $aset->status_aset == 'Dalam Perbaikan' ? 'selected' : '' }}>🛠️ Dalam Perbaikan</option>
                                <option value="Dipinjam" {{ $aset->status_aset == 'Dipinjam' ? 'selected' : '' }}>🔁 Dipinjam</option>
                                <option value="Hilang" {{ $aset->status_aset == 'Hilang' ? 'selected' : '' }}>❓ Hilang</option>
                            </select>
                        </div>

                        {{-- Perubahan Ruangan/Lokasi --}}
                        <div class="col-md-6 mt-3">
                            <label class="form-label fw-bold text-navy mb-1 small">Update Lokasi Ditemukan</label>
                            <select name="lokasi_id" class="form-select bg-white border-0 shadow-sm">
                                <option value="">-- Tetap di ({{ $aset->lokasi->nama_lokasi ?? 'Tanpa Lokasi' }}) --</option>
                                @foreach($lokasi as $lok)
                                    <option value="{{ $lok->lokasi_id }}" {{ $aset->lokasi_id == $lok->lokasi_id ? 'selected' : '' }}>
                                        {{ $lok->nama_lokasi }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mt-3">
                            <label class="form-label fw-bold text-navy mb-1 small">Update Penempatan Divisi</label>
                            <select name="kode_organisasi" class="form-select bg-white border-0 shadow-sm">
                                <option value="">-- Tetap di ({{ $aset->organisasi_terikat }}) --</option>
                                @php
                                    $currentOrgKode = $aset->getKodeOrganisasiAttribute();
                                    if (!function_exists('renderOrgOptionsShow')) {
                                        function renderOrgOptionsShow($node, $currentVal, &$seen = [], $level = 0) {
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

                                            if (isset($node->subDirectors)) foreach ($node->subDirectors as $s) renderOrgOptionsShow($s, $currentVal, $seen, $level + 1);
                                            if (isset($node->divisi)) foreach ($node->divisi as $d) renderOrgOptionsShow($d, $currentVal, $seen, $level + 1);
                                            if (isset($node->department)) foreach ($node->department as $dp) renderOrgOptionsShow($dp, $currentVal, $seen, $level + 1);
                                            if (isset($node->section)) foreach ($node->section as $sc) renderOrgOptionsShow($sc, $currentVal, $seen, $level + 1);
                                            if (isset($node->unit)) foreach ($node->unit as $u) renderOrgOptionsShow($u, $currentVal, $seen, $level + 1);
                                        }
                                    }
                                    $seenArray = [];
                                    if (isset($mainDirector)) renderOrgOptionsShow($mainDirector, $currentOrgKode, $seenArray);
                                @endphp
                            </select>
                        </div>

                        {{-- Dokumentasi & Catatan --}}
                        <div class="col-12 mt-3">
                            <label class="form-label fw-bold text-navy mb-1 small">Upload Foto Kondisi Lapangan</label>
                            <input type="file" name="foto_bukti" class="form-control bg-white border-0 shadow-sm" accept="image/*" capture="environment">
                            <small class="text-muted d-block mt-1"><i class="fas fa-mobile-alt me-1"></i> Jika diakses dari HP, bisa langsung klik untuk membuka kamera.</small>
                        </div>

                        <div class="col-12 mt-3">
                            <label class="form-label fw-bold text-navy mb-1 small">Keterangan / Temuan Spesifik</label>
                            <textarea name="keterangan" rows="3" class="form-control bg-white border-0 shadow-sm" placeholder="Contoh: Layar lecet di ujung, kabel data hilang, dipindahkan ke gudang, dll."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white border-top-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-light px-4 rounded-pill fw-bold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-navy px-4 rounded-pill fw-bold text-white shadow-sm" style="background-color: #253070;">
                        <i class="fas fa-save me-2"></i> Simpan Catatan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL FORM PENGAJUAN PERBAIKAN --}}
<div class="modal fade" id="modalPerbaikan" tabindex="-1" aria-labelledby="modalPerbaikanLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white text-center d-block position-relative border-0 shadow-sm"
                 style="background-color: #c0392b;">
                <h5 class="modal-title fw-bold text-white mb-0" id="modalPerbaikanLabel">
                    <i class="fas fa-tools me-2"></i> Pengajuan Perbaikan Aset
                </h5>
                <button type="button" class="btn-close btn-close-white position-absolute top-50 translate-middle-y end-0 me-3"
                        data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('perbaikan.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="aset_id" value="{{ $aset->id }}">

                <div class="modal-body p-4 bg-light">

                    {{-- Info Aset --}}
                    <div class="alert alert-secondary d-flex align-items-center py-2 px-3 mb-4 rounded-3 shadow-sm" role="alert">
                        <i class="fas fa-box fa-lg me-3 text-secondary"></i>
                        <span class="small m-0 text-dark">
                            Mengajukan perbaikan untuk aset:
                            <strong>{{ $aset->nama_aset }}</strong>
                            — <code>{{ $aset->nomor_aset }}</code>
                        </span>
                    </div>

                    <div class="row g-3">

                        {{-- Tingkat Urgensi --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-navy mb-1 small">
                                Tingkat Urgensi <span class="text-danger">*</span>
                            </label>
                            <select name="tingkat_urgensi" class="form-select bg-white border-0 shadow-sm" required>
                                <option value="rendah">🟢 Rendah — Dapat ditunda</option>
                                <option value="sedang" selected>🟡 Sedang — Perlu segera ditangani</option>
                                <option value="tinggi">🔴 Tinggi — Mendesak / Berbahaya</option>
                            </select>
                        </div>

                        {{-- Upload Foto --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-navy mb-1 small">Foto Kerusakan</label>
                            <input type="file" name="foto_kerusakan" class="form-control bg-white border-0 shadow-sm"
                                   accept="image/*" capture="environment">
                            <small class="text-muted d-block mt-1">
                                <i class="fas fa-mobile-alt me-1"></i>Jika dari HP, bisa langsung buka kamera.
                            </small>
                        </div>

                        {{-- Deskripsi Kerusakan --}}
                        <div class="col-12">
                            <label class="form-label fw-bold text-navy mb-1 small">
                                Deskripsi Kerusakan <span class="text-danger">*</span>
                            </label>
                            <textarea name="deskripsi_kerusakan" rows="4"
                                      class="form-control bg-white border-0 shadow-sm"
                                      placeholder="Jelaskan kerusakan secara detail. Contoh: Layar mati total, tidak bisa dinyalakan. Sudah dicoba charge selama 2 jam namun tidak ada respons..."
                                      required></textarea>
                        </div>

                    </div>
                </div>

                <div class="modal-footer bg-white border-top-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-light px-4 rounded-pill fw-bold"
                            data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn px-4 rounded-pill fw-bold text-white shadow-sm"
                            style="background-color: #c0392b;">
                        <i class="fas fa-paper-plane me-2"></i>Kirim Pengajuan
                    </button>
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