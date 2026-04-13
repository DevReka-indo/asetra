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
                                         alt="Foto Aset">
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

                            {{-- Row 2: Grid Detail dengan Ikon --}}
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
                                        <span class="info-label">Divisi Aset</span>
                                        <span class="info-value">{{ $aset->divisi->nm_divisi ?? '-' }}</span>
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
            <div class="mt-3 text-end">
                <small class="text-muted">Data ditambahkan pada: {{ $aset->created_at->format('d M Y, H:i') }}</small>
            </div>
        </div>
    </div>
</div>
@endsection