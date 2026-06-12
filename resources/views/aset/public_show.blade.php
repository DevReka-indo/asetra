@extends('layouts.auth')

@section('title', 'ASETRA - ' . $aset->nama_aset)

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --primary-navy: #253070;
            --primary-navy-light: #3a4792;
            --danger-red: #c0392b;
            --danger-red-light: #da4f41;
            --surface: #ffffff;
            --bg-color: #f0f4f8;
            --text-dark: #1e293b;
            --text-muted: #64748b;
        }

        body {
            background-color: var(--bg-color);
            background-image: radial-gradient(circle at 100% 0%, rgba(37,48,112,0.04) 0%, rgba(37,48,112,0) 50%),
                              radial-gradient(circle at 0% 100%, rgba(192,57,43,0.03) 0%, rgba(192,57,43,0) 50%);
            font-family: 'Public Sans', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .public-card {
            background: var(--surface);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.05), 0 5px 15px rgba(0,0,0,0.03);
            width: 100%;
            max-width: 420px;
            overflow: hidden;
            position: relative;
            border: 1px solid rgba(255, 255, 255, 0.8);
        }

        .brand-header {
            text-align: center;
            padding: 18px 20px 15px;
            color: var(--primary-navy);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: linear-gradient(180deg, rgba(255,255,255,1) 0%, rgba(255,255,255,0.9) 100%);
            border-bottom: 1px solid rgba(0,0,0,0.03);
        }

        .brand-header .title-wrapper {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 16px;
            font-weight: 800;
            letter-spacing: 0.5px;
        }

        .brand-header .subtitle {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 4px;
        }

        .brand-header i {
            font-size: 18px;
            color: var(--primary-navy);
        }

        .public-img-container {
            width: 100%;
            height: 260px;
            background-color: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        .public-img-container::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 40px;
            background: linear-gradient(0deg, rgba(255,255,255,1) 0%, rgba(255,255,255,0) 100%);
        }

        .public-img-container img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            transition: transform 0.5s ease;
        }

        .public-card:hover .public-img-container img {
            transform: scale(1.03);
        }

        .public-content {
            padding: 10px 24px 30px;
        }

        .aset-title {
            font-size: 22px;
            font-weight: 800;
            color: var(--text-dark);
            margin-bottom: 6px;
            line-height: 1.3;
        }

        .aset-no {
            font-size: 13px;
            font-weight: 600;
            color: var(--primary-navy);
            background: rgba(37, 48, 112, 0.08);
            padding: 5px 12px;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 24px;
        }

        .info-wrapper {
            background: #f8fafc;
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 28px;
            border: 1px solid #f1f5f9;
        }

        .info-row {
            display: flex;
            align-items: center;
        }

        .info-row:not(:last-child) {
            margin-bottom: 16px;
            padding-bottom: 16px;
            border-bottom: 1px solid #e2e8f0;
        }

        .info-icon {
            width: 36px;
            height: 36px;
            background: #ffffff;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-navy);
            margin-right: 14px;
            font-size: 16px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.04);
        }

        .info-text {
            flex: 1;
        }

        .info-label {
            font-size: 11px;
            color: var(--text-muted);
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }

        .info-value {
            font-size: 15px;
            color: var(--text-dark);
            font-weight: 600;
        }

        .actions-container {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .btn-action {
            padding: 14px 16px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 700;
            text-align: center;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .btn-action::after {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(255,255,255,0.1);
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: -1;
        }

        .btn-action:hover::after {
            opacity: 1;
        }

        .btn-monitoring {
            background: linear-gradient(135deg, var(--primary-navy) 0%, var(--primary-navy-light) 100%);
            color: white;
            box-shadow: 0 6px 15px rgba(37, 48, 112, 0.25);
        }

        .btn-monitoring:hover {
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(37, 48, 112, 0.35);
        }

        .btn-perbaikan {
            background: linear-gradient(135deg, var(--danger-red) 0%, var(--danger-red-light) 100%);
            color: white;
            box-shadow: 0 6px 15px rgba(192, 57, 43, 0.25);
        }

        .btn-perbaikan:hover {
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(192, 57, 43, 0.35);
        }

        .footer-note {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        
        .footer-note i {
            font-size: 11px;
            color: #94a3b8;
        }
    </style>
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
