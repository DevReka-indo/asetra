@extends('layouts.auth')

@section('title', 'ASETRA - ' . $aset->nama_aset)

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/public-aset.css') }}">
@endpush

@section('content')
    <div class="public-card">
        <div class="brand-header">
            <div class="title-wrapper">
                <i class="fas fa-qrcode"></i> ASETRA
            </div>
            <div class="subtitle">Detail Aset</div>
        </div>
        
        <div class="public-img-container">
            @php
                $latestLogPhoto = $aset->logAset->whereNotNull('foto_bukti')->sortByDesc('created_at')->first();
            @endphp

            @if ($aset->foto && $aset->foto->count() > 0)
                @php
                    $firstFoto = $aset->foto->sortBy('urutan')->first();
                @endphp
                <img src="{{ Storage::url($firstFoto->path_foto) }}" alt="Foto Aset">
            @elseif ($latestLogPhoto)
                <img src="{{ Storage::url($latestLogPhoto->foto_bukti) }}" alt="Foto Monitoring Terbaru">
                <div style="position: absolute; top: 15px; background: rgba(192, 57, 43, 0.85); color: white; padding: 6px 12px; font-size: 11px; font-weight: 600; border-radius: 20px; z-index: 10; box-shadow: 0 4px 10px rgba(0,0,0,0.15); border: 1px solid rgba(255,255,255,0.2);">
                    <i class="fas fa-exclamation-circle me-1"></i> Tidak ada foto utama, menampilkan dari log terbaru
                </div>
            @else
                <div class="text-center" style="color: #cbd5e1;">
                    <i class="fas fa-image fa-4x mb-3" style="opacity: 0.7;"></i>
                    <p class="mb-0 fw-medium" style="font-size: 14px;">Tidak ada foto aset</p>
                </div>
            @endif
        </div>

        <div class="public-content">
            <div class="text-center">
                <h1 class="aset-title">{{ $aset->nama_aset }}</h1>
                <div class="aset-no">
                    <i class="fas fa-qrcode"></i> 
                    <span>{{ $aset->nomor_aset }}</span>
                </div>
            </div>

            <div class="info-wrapper">
                <div class="info-row">
                    <div class="info-icon"><i class="fas fa-tag"></i></div>
                    <div class="info-text">
                        <div class="info-label">Merk / Model</div>
                        <div class="info-value">{{ $aset->merek ?? '-' }}</div>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-icon"><i class="fas fa-map-marker-alt"></i></div>
                    <div class="info-text">
                        <div class="info-label">Lokasi Penempatan</div>
                        <div class="info-value">{{ $aset->lokasi ? $aset->lokasi->nama_lokasi : '-' }}</div>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-icon"><i class="fas fa-building"></i></div>
                    <div class="info-text">
                        <div class="info-label">Departemen / Divisi</div>
                        <div class="info-value">
                            {{ $aset->organisasi_terikat }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="actions-container">
                <a href="{{ route('aset.action', ['id' => $aset->id, 'action' => 'monitoring']) }}" class="btn-action btn-monitoring">
                    <i class="fas fa-shield-alt"></i> Monitoring Aset
                </a>
                <a href="{{ route('aset.action', ['id' => $aset->id, 'action' => 'perbaikan']) }}" class="btn-action btn-perbaikan">
                    <i class="fas fa-wrench"></i> Ajukan Perbaikan
                </a>
            </div>
            
            <div class="footer-note">
                <i class="fas fa-lock"></i>
                <span>Tindakan membutuhkan proses Login</span>
            </div>
        </div>
    </div>
@endsection
