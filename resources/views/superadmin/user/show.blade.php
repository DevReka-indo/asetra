@extends('layouts.app')

@section('title', 'Detail Pengguna - ' . $user->firstname . ' ' . $user->lastname)

@section('content')
<div class="container-fluid px-1 py-0 mt-0">

    {{-- Page Header + Breadcrumb --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold mb-0">Detail Pengguna</h3>
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
                <a href="{{ route('user.manage') }}" class="text-muted text-decoration-none d-flex align-items-center">
                    <span style="font-size: 14px; font-weight: 500; position: relative; top: 2px;">Manajemen Pengguna</span>
                </a>
            </li>
            <li class="separator text-muted d-flex align-items-center px-2">
                <span style="font-size: 14px; position: relative; top: 2px;">-</span>
            </li>
            <li class="nav-item d-flex align-items-center">
                <span class="text-muted" style="font-size: 14px; font-weight: 500; position: relative; top: 2px;">Detail Pengguna</span>
            </li>
        </ul>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4 p-md-5">

            {{-- Informasi Akun --}}
            <div class="mb-4">
                <h6 class="fw-bold text-navy border-bottom pb-2 mb-3"><i class="fas fa-id-card me-2"></i> Informasi Akun</h6>
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-navy mb-1 small"><i class="fas fa-hashtag me-1"></i> ID Pengguna</label>
                        <div class="input-group rounded-3 overflow-hidden">
                            <span class="input-group-text border-0 text-navy" style="background: #f1f3f5;"><i class="fas fa-fingerprint"></i></span>
                            <input type="text" class="form-control border-0 text-muted shadow-none" style="background: #f1f3f5; cursor: default;" value="{{ $user->id }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-navy mb-1 small"><i class="fas fa-envelope me-1"></i> Email</label>
                        <div class="input-group rounded-3 overflow-hidden">
                            <span class="input-group-text border-0 text-navy" style="background: #f1f3f5;"><i class="fas fa-at"></i></span>
                            <input type="email" class="form-control border-0 text-muted shadow-none" style="background: #f1f3f5; cursor: default;" value="{{ $user->email }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-navy mb-1 small"><i class="fas fa-shield-alt me-1"></i> Status Akun</label>
                        @php
                            $status      = $user->deleted_at ? 'Non-Aktif' : 'Aktif';
                            $statusClass = $user->deleted_at ? 'danger' : 'success';
                            $statusIcon  = $user->deleted_at ? 'times-circle' : 'check-circle';
                        @endphp
                        <div class="input-group rounded-3 overflow-hidden">
                            <span class="input-group-text border-0" style="background: #f1f3f5;">
                                <i class="fas fa-{{ $statusIcon }} text-{{ $statusClass }}"></i>
                            </span>
                            <input type="text" class="form-control border-0 shadow-none fw-bold text-{{ $statusClass }}" style="background: #f1f3f5; cursor: default;" value="{{ $status }}" readonly>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Data Pribadi --}}
            <div class="mb-4 mt-5">
                <h6 class="fw-bold text-navy border-bottom pb-2 mb-3"><i class="fas fa-user me-2"></i> Data Pribadi</h6>
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-navy mb-1 small"><i class="fas fa-user-edit me-1"></i> Nama Depan</label>
                        <div class="input-group rounded-3 overflow-hidden">
                            <span class="input-group-text border-0 text-navy" style="background: #f1f3f5;"><i class="fas fa-signature"></i></span>
                            <input type="text" class="form-control border-0 text-muted shadow-none" style="background: #f1f3f5; cursor: default;" value="{{ $user->firstname }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-navy mb-1 small"><i class="fas fa-user-edit me-1"></i> Nama Belakang</label>
                        <div class="input-group rounded-3 overflow-hidden">
                            <span class="input-group-text border-0 text-navy" style="background: #f1f3f5;"><i class="fas fa-signature"></i></span>
                            <input type="text" class="form-control border-0 text-muted shadow-none" style="background: #f1f3f5; cursor: default;" value="{{ $user->lastname ?? '-' }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-navy mb-1 small"><i class="fas fa-id-card me-1"></i> NIP</label>
                        <div class="input-group rounded-3 overflow-hidden">
                            <span class="input-group-text border-0 text-navy" style="background: #f1f3f5;"><i class="fas fa-id-badge"></i></span>
                            <input type="text" class="form-control border-0 text-muted shadow-none" style="background: #f1f3f5; cursor: default;" value="{{ $user->nip }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-navy mb-1 small"><i class="fas fa-phone-alt me-1"></i> No. Telepon</label>
                        <div class="input-group rounded-3 overflow-hidden">
                            <span class="input-group-text border-0 text-navy" style="background: #f1f3f5;"><i class="fas fa-mobile-alt"></i></span>
                            <input type="text" class="form-control border-0 text-muted shadow-none" style="background: #f1f3f5; cursor: default;" value="{{ $user->phone_number }}" readonly>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Organisasi & Posisi --}}
            <div class="mb-4 mt-5">
                <h6 class="fw-bold text-navy border-bottom pb-2 mb-3"><i class="fas fa-building me-2"></i> Organisasi &amp; Posisi</h6>
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-navy mb-1 small"><i class="fas fa-sitemap me-1"></i> Organisasi</label>
                        @php
                            $orgName = '-';
                            if ($user->unit) {
                                $orgName = 'Unit: ' . $user->unit->name_unit;
                            } elseif ($user->section) {
                                $orgName = 'Bagian: ' . $user->section->name_section;
                            } elseif ($user->department) {
                                $orgName = 'Departemen: ' . $user->department->name_department;
                            } elseif ($user->divisi) {
                                $orgName = 'Divisi: ' . $user->divisi->nm_divisi;
                            } elseif ($user->director) {
                                $orgName = 'Direktur: ' . $user->director->name_director;
                            }
                        @endphp
                        <div class="input-group rounded-3 overflow-hidden">
                            <span class="input-group-text border-0 text-navy" style="background: #f1f3f5;"><i class="fas fa-network-wired"></i></span>
                            <input type="text" class="form-control border-0 text-muted shadow-none" style="background: #f1f3f5; cursor: default;" value="{{ $orgName }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-navy mb-1 small"><i class="fas fa-briefcase me-1"></i> Posisi</label>
                        <div class="input-group rounded-3 overflow-hidden">
                            <span class="input-group-text border-0 text-navy" style="background: #f1f3f5;"><i class="fas fa-user-tie"></i></span>
                            <input type="text" class="form-control border-0 text-muted shadow-none" style="background: #f1f3f5; cursor: default;" value="{{ $user->position->nm_position ?? '-' }}" readonly>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Hak Akses & Area Kerja --}}
            <div class="mb-4 mt-5">
                <h6 class="fw-bold text-navy border-bottom pb-2 mb-3"><i class="fas fa-shield-alt me-2"></i> Hak Akses &amp; Area Kerja</h6>
                <div class="row g-4">

                    {{-- Hak Akses --}}
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-navy mb-1 small">
                            <i class="fas fa-user-shield me-1"></i> Hak Akses
                        </label>
                        @php
                            switch ($user->role_id_role) {
                                case 1: $roleName = 'Superadmin'; $roleColorStyle = ''; $roleIcon = 'star'; break;
                                case 2: $roleName = 'User';       $roleColorStyle = 'color: #4da3ff;'; $roleIcon = 'user'; break;
                                case 3: $roleName = 'Admin';      $roleColorStyle = ''; $roleIcon = 'cog'; break;
                                default:$roleName = '-';          $roleColorStyle = ''; $roleIcon = 'question';
                            }
                        @endphp
                        <div class="input-group rounded-3 overflow-hidden">
                            <span class="input-group-text border-0" style="background: #f1f3f5;">
                                <i class="fas fa-{{ $roleIcon }}" style="{{ $roleColorStyle }}"></i>
                            </span>
                            <input type="text" class="form-control border-0 shadow-none" style="background: #f1f3f5; cursor: default; {{ $roleColorStyle }}" value="{{ $roleName }}" readonly>
                        </div>
                    </div>

                    {{-- Kode Bagian --}}
                    <div class="col-md-12">
                        <label class="form-label fw-bold text-navy mb-1 small">
                            <i class="fas fa-tag me-1"></i> Kode Bagian
                        </label>
                        @php
                            $kodeBagianArray = $user->kode_bagian
                                ? array_filter(array_map('trim', explode(';', $user->kode_bagian)))
                                : [];
                        @endphp
                        <div class="w-100 rounded-3 overflow-hidden text-start" style="background: #f1f3f5; border: 1px solid #e9ecef;">
                            @if(count($kodeBagianArray) > 0)
                                {{-- Baris 1: Header Info --}}
                                <div class="px-3 py-2 border-bottom d-flex align-items-center gap-2" style="background: #e9ecef;">
                                    <i class="fas fa-check-circle text-success"></i>
                                    <span class="text-muted fw-semibold" style="font-size: 0.9rem;">
                                        Total: <span class="badge bg-primary">{{ count($kodeBagianArray) }}</span> bagian ditugaskan
                                    </span>
                                </div>

                                {{-- Baris 2 dan seterusnya: List Data --}}
                                <div class="w-100" style="max-height: 250px; overflow-y: auto;">
                                    @foreach($kodeBagianArray as $index => $kode)
                                        @php $bagian = $bagianKerja->firstWhere('kode_bagian', $kode); @endphp
                                        
                                        {{-- Container per baris aset --}}
                                        <div class="d-flex align-items-center w-100 px-3 py-2" style="{{ $index > 0 ? 'border-top: 1px solid #e9ecef;' : '' }}">
                                            
                                            {{-- Kiri: Badge Kode --}}
                                            <div class="me-3 flex-shrink-0">
                                                <span class="badge bg-secondary text-white rounded-1 text-center" style="min-width: 65px; padding: 8px 12px; font-size: 0.85rem; letter-spacing: 0.5px;">
                                                    {{ $kode }}
                                                </span>
                                            </div>
                                            
                                            {{-- Kanan: Teks --}}
                                            <div class="text-muted fw-medium text-wrap" style="font-size: 0.9rem;">
                                                {{ $bagian->nama_bagian ?? 'tes' }}
                                            </div>
                                            
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="px-3 py-3 d-flex align-items-center gap-2 text-muted">
                                    <i class="fas fa-inbox"></i>
                                    <small>Tidak ada kode bagian yang ditugaskan</small>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-end">
                <a href="{{ route('user.manage') }}" class="btn btn-secondary shadow-sm rounded-pill px-4">Kembali</a>
                <a href="{{ route('user-manage.edit', $user->id) }}" class="btn btn-primary shadow-sm rounded-pill px-4 ms-2">Edit Pengguna</a>
            </div>
        </div>
    </div>

</div>
@endsection
