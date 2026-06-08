@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid px-1 py-0 mt-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="fw-bold mb-0 text-dark">Dashboard</h3>
        </div>
    </div>

    {{-- Welcome Card --}}
    <div class="card border-0 mb-4 overflow-hidden" style="border-radius: 1rem; background: linear-gradient(135deg, #1A2355 0%, #2A367C 100%); box-shadow: 0 8px 24px rgba(26, 35, 85, 0.12); color: #ffffff;">
        <div class="card-body p-4 d-flex align-items-center position-relative">
            <div class="d-flex align-items-center w-100 position-relative" style="z-index: 1;">
                <div class="me-3 d-none d-md-flex align-items-center justify-content-center bg-white bg-opacity-10 rounded-circle" style="width: 55px; height: 55px; backdrop-filter: blur(5px);">
                    <span class="fs-3">👋</span>
                </div>
                <div>
                    <h4 class="fw-bold mb-1" style="font-family: 'Public Sans', sans-serif;">Selamat Datang, {{ auth()->user()->firstname }} {{ auth()->user()->lastname }}!</h4>
                    <p class="mb-0 opacity-90 small" style="line-height: 1.5;">
                        Selamat datang di <strong>Sistem Informasi Manajemen Aset</strong>. Anda login sebagai 
                        <span class="badge bg-white text-navy fw-bold px-2.5 py-1.5 ms-1 rounded-pill" style="color: #1A2355 !important; font-size: 0.75rem; letter-spacing: 0.5px;">{{ auth()->user()->role_id_role == 1 ? 'Superadmin' : (auth()->user()->role_id_role == 3 ? 'Admin' : 'User') }}</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- TOP CARDS: METRIKS --}}
    <div class="row g-3 mb-4">
        {{-- Card 1: Aset Departemen --}}
        <div class="col-md-4">
            <div class="card glass-card h-100 p-3">
                <div class="d-flex align-items-center">
                    <div class="metric-icon icon-primary me-3">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 small fw-bold text-uppercase">Aset Divisi</p>
                        <h3 class="fw-bold mb-0 text-dark">{{ number_format($totalAsetDept) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 2: Aset PIC Saya --}}
        <div class="col-md-4">
            <div class="card glass-card h-100 p-3">
                <div class="d-flex align-items-center">
                    <div class="metric-icon icon-success me-3">
                        <i class="fas fa-user-tag"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 small fw-bold text-uppercase">Aset PIC Saya</p>
                        <h3 class="fw-bold mb-0 text-dark">{{ number_format($totalAsetPic) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 3: Pengajuan Perbaikan --}}
        <div class="col-md-4">
            <div class="card glass-card h-100 p-3">
                <div class="d-flex align-items-center">
                    <div class="metric-icon icon-warning me-3" style="background: rgba(220, 53, 69, 0.1); color: #dc3545;">
                        <i class="fas fa-tools"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 small fw-bold text-uppercase">Pengajuan Perbaikan</p>
                        <h3 class="fw-bold mb-0 text-dark">{{ number_format($totalPerbaikan) }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        {{-- BAGIAN KIRI: PROGRESS & MONITORING --}}
        <div class="col-lg-8">
            

            {{-- Monitoring Terbaru --}}
            <div class="card glass-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold text-success mb-0"><i class="fas fa-history me-2"></i>Aktivitas Monitoring Terbaru Departemen</h6>
                    <a href="{{ route('log-aset.index') }}" class="small text-decoration-none text-success fw-bold">Lihat Semua</a>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-borderless table-hover align-middle mb-0">
                        <thead class="bg-light rounded-3">
                            <tr class="text-muted small text-uppercase">
                                <th class="ps-3">Aset</th>
                                <th>Dicatat Oleh</th>
                                <th>Tanggal</th>
                                <th class="text-center">Kondisi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($monitoringTerbaru as $log)
                                <tr style="border-bottom: 1px solid rgba(0,0,0,.05);">
                                    <td class="ps-3 py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-light rounded-3 d-flex align-items-center justify-content-center text-success me-2" style="width: 35px; height: 35px;">
                                                <i class="fas fa-clipboard-list"></i>
                                            </div>
                                            <div>
                                                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">{{ Str::limit($log->aset->nama_aset ?? 'Aset Dihapus', 30) }}</h6>
                                                <small class="text-muted" style="font-size: 0.75rem;">No: {{ $log->aset->nomor_aset ?? '-' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $log->dicatatOleh->fullname ?? 'Sistem' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($log->tanggal_cek)->format('d M Y') }}</td>
                                    <td class="text-center">
                                        @if($log->kondisi == 'Baik')
                                            <span class="badge bg-success rounded-pill px-2" style="font-size: 0.7rem;">Baik</span>
                                        @elseif($log->kondisi == 'Rusak')
                                            <span class="badge bg-danger rounded-pill px-2" style="font-size: 0.7rem;">Rusak</span>
                                        @else
                                            <span class="badge bg-secondary rounded-pill px-2" style="font-size: 0.7rem;">{{ ucfirst($log->kondisi) }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-5">
                                        <i class="fas fa-history text-muted fs-3 mb-2"></i><br>
                                        Belum ada riwayat aktivitas monitoring di departemen Anda.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        {{-- BAGIAN KANAN: QUICK ACCESS & PERBAIKAN SAYA --}}
        <div class="col-lg-4">
            
            {{-- Quick Access --}}
            <h6 class="fw-bold text-dark mb-3"><i class="fas fa-bolt text-warning me-2"></i>Akses Cepat</h6>
            <div class="row g-2 mb-4">
                <div class="col-6">
                    <a href="{{ route('aset.scanner') }}" class="quick-access-btn shadow-sm">
                        <i class="fas fa-qrcode"></i>
                        <span class="fw-bold small text-center">Scan Aset</span>
                    </a>
                </div>
                <div class="col-6">
                    <a href="{{ route('aset.index') }}" class="quick-access-btn shadow-sm">
                        <i class="fas fa-box-open"></i>
                        <span class="fw-bold small text-center">Data Aset</span>
                    </a>
                </div>
                <div class="col-6">
                    <a href="{{ route('aset.pic') }}" class="quick-access-btn shadow-sm">
                        <i class="fas fa-user-tag"></i>
                        <span class="fw-bold small text-center">Aset PIC Saya</span>
                    </a>
                </div>
                <div class="col-6">
                    <a href="{{ route('perbaikan.index') }}" class="quick-access-btn shadow-sm">
                        <i class="fas fa-tools"></i>
                        <span class="fw-bold small text-center">Perbaikan</span>
                    </a>
                </div>
            </div>

            {{-- Pengajuan Perbaikan Saya --}}
            <div class="card glass-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold text-danger mb-0"><i class="fas fa-tools me-2"></i>Perbaikan Terbaru Anda</h6>
                    <a href="{{ route('perbaikan.index') }}" class="small text-decoration-none text-danger fw-bold">Lihat Semua</a>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-borderless table-hover align-middle mb-0">
                        <tbody>
                            @forelse($perbaikanTerbaru as $pb)
                                <tr style="border-bottom: 1px solid rgba(0,0,0,.05);">
                                    <td class="px-0 py-2">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-light rounded-3 d-flex align-items-center justify-content-center text-danger me-2" style="width: 35px; height: 35px;">
                                                <i class="fas fa-wrench"></i>
                                            </div>
                                            <div>
                                                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">{{ Str::limit($pb->aset->nama_aset ?? 'Aset Dihapus', 22) }}</h6>
                                                <small class="text-muted" style="font-size: 0.75rem;">{{ $pb->created_at->diffForHumans() }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end px-0 py-2">
                                        @if($pb->status == 'menunggu')
                                            <span class="badge bg-warning text-dark rounded-pill px-2" style="font-size: 0.7rem;">Menunggu</span>
                                        @elseif($pb->status == 'diproses')
                                            <span class="badge bg-info rounded-pill px-2" style="font-size: 0.7rem;">Diproses</span>
                                        @else
                                            <span class="badge bg-success rounded-pill px-2" style="font-size: 0.7rem;">{{ ucfirst($pb->status) }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-4">
                                        <i class="fas fa-check-circle text-success fs-4 mb-2"></i><br>
                                        Tidak ada riwayat pengajuan perbaikan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
