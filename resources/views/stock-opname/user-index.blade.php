@extends('layouts.app')

@section('title', 'Pelaksanaan Stock Opname')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/stock-opname.css') }}">
@endpush

@section('content')
<div class="container-fluid px-1 py-0 mt-0 page-stock-opname-user-index">
    {{-- Breadcrumb --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold mb-0">Pelaksanaan Stock Opname</h3>
        <ul class="breadcrumbs d-flex align-items-center p-0 m-0" style="list-style: none;">
            <li class="nav-home d-flex align-items-center">
                <a href="{{ url('dashboard') }}" class="text-muted text-decoration-none d-flex align-items-center">
                    <i class="fas fa-home me-2" style="font-size: 15px;"></i>
                    <span style="font-size: 14px; font-weight: 500; position: relative; top: 2px;">Dashboard</span>
                </a>
            </li>
            <li class="separator text-muted d-flex align-items-center px-2">
                <span style="font-size: 14px; position: relative; top: 2px;">-</span>
            </li>
            <li class="nav-item d-flex align-items-center">
                <span class="text-muted" style="font-size: 14px; font-weight: 500; position: relative; top: 2px;"> Pelaksanaan Stock Opname</span>
            </li>
        </ul>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- HERO --}}
    <div class="so-user-hero d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div class="so-user-hero-content">
            <h4 class="fw-bold mt-2 mb-1">Halo, {{ auth()->user()->firstname }}!</h4>
            <p class="mb-0 opacity-90">Pilih jadwal opname yang sedang berjalan untuk mulai memeriksa fisik aset di unit kerja Anda.</p>
        </div>
        <div class="so-user-hero-content text-md-end">
            <div class="d-flex align-items-center gap-3 justify-content-md-end">
                <div class="text-md-end">
                    <div class="opacity-75 small">Jadwal Aktif</div>
                    <h2 class="fw-bold mb-0 text-white">{{ $sessions->count() }}</h2>
                </div>
                <div class="d-flex align-items-center justify-content-center" style="width:60px; height:60px; background:rgba(255,255,255,.18); border-radius: 50%;">
                    <i class="fas fa-calendar-check fa-2x"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- KIRI: Daftar Jadwal --}}
        <div class="col-lg-8">

            <div class="row g-3">
                @forelse($sessions as $session)
                    @php
                        $start = \Carbon\Carbon::parse($session->tanggal_mulai);
                        $end = \Carbon\Carbon::parse($session->tanggal_berakhir);
                        $today = now();
                        $sisaHari = $today->lessThan($end) ? $today->startOfDay()->diffInDays($end->startOfDay()) : 0;
                        $durasi = $start->diffInDays($end) + 1;
                    @endphp
                    <div class="col-md-6">
                        <div class="session-card">
                            <div class="card-banner">
                                <div class="card-banner-content d-flex justify-content-between align-items-start">
                                    <div>
                                        <h5>{{ $session->periode }}</h5>
                                        <small><i class="far fa-clock me-1"></i> Durasi {{ $durasi }} hari</small>
                                    </div>
                                    <span class="badge-aktif">Aktif</span>
                                </div>
                            </div>
                            <div class="card-body-inner">
                                <div class="meta-row">
                                    <i class="far fa-calendar-alt"></i>
                                    <span>{{ $start->format('d M Y') }} <span class="mx-1 text-muted">→</span> {{ $end->format('d M Y') }}</span>
                                </div>
                                <div class="meta-row">
                                    <i class="far fa-hourglass"></i>
                                    @if($sisaHari > 0)
                                        <span>Sisa <strong class="text-success">{{ $sisaHari }} hari</strong> lagi</span>
                                    @else
                                        <span class="text-warning fw-semibold">Hari terakhir!</span>
                                    @endif
                                </div>
                                <div class="meta-row mb-3">
                                    <i class="fas fa-sticky-note"></i>
                                    <span>{{ \Illuminate\Support\Str::limit($session->keterangan ?: 'Tidak ada keterangan tambahan.', 60) }}</span>
                                </div>

                                <a href="{{ route('stock-opname.user-show', $session->id) }}" class="so-btn-primary w-100 text-decoration-none">
                                    <i class="fas fa-clipboard-check"></i> Mulai Pengecekan
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="empty-state-card">
                            <div class="empty-ico"><i class="fas fa-calendar-times"></i></div>
                            <h5 class="fw-bold text-dark mb-1">Belum Ada Jadwal Aktif</h5>
                            <p class="text-muted mb-0">Saat ini tidak ada jadwal Stock Opname yang sedang berjalan.<br>Silakan tunggu pemberitahuan dari General Affairs.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</div>
@endsection
