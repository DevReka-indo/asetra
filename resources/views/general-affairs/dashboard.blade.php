@extends('layouts.app')

@section('title', 'Dashboard Bagian Umum')

@section('content')
    <div class="container-fluid px-1 py-0 mt-0">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="fw-bold mb-0">Dashboard Bagian Umum</h3>
            <ul class="breadcrumbs d-flex align-items-center p-0 m-0" style="list-style: none;"> 
                <li class="nav-home d-flex align-items-center">
                    <a href="{{ route('general-affairs.dashboard') }}" class="text-muted text-decoration-none d-flex align-items-center">
                        <i class="fas fa-home me-2" style="font-size: 15px;"></i>
                        <span style="font-size: 14px; font-weight: 500; position: relative; top: 2px;">Dashboard Bagian Umum</span>                    
                    </a>                
                </li>
            </ul>
        </div>

        <div class="row">
            <!-- Welcome Card -->
            <div class="col-md-12">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        <h5 class="card-title fw-bold">Selamat Datang, {{ Auth::user()->firstname }}</h5>
                        <p class="card-text text-muted">Anda login sebagai staff Bagian Umum (Sumber Daya Manusia & Umum)</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats Row -->
        <div class="row">
            <div class="col-md-6 col-lg-3 mb-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body d-flex align-items-center">
                        <div style="background-color: #E8F4F8; width: 60px; height: 60px; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-right: 15px;">
                            <i class="fas fa-box" style="font-size: 28px; color: #1b53a7;"></i>
                        </div>
                        <div>
                            <p class="text-muted mb-1" style="font-size: 14px;">Total Aset</p>
                            <h5 class="fw-bold mb-0">--</h5>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3 mb-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body d-flex align-items-center">
                        <div style="background-color: #FFF3E0; width: 60px; height: 60px; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-right: 15px;">
                            <i class="fas fa-tools" style="font-size: 28px; color: #FF9800;"></i>
                        </div>
                        <div>
                            <p class="text-muted mb-1" style="font-size: 14px;">Pengajuan Perbaikan</p>
                            <h5 class="fw-bold mb-0">--</h5>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3 mb-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body d-flex align-items-center">
                        <div style="background-color: #F3E5F5; width: 60px; height: 60px; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-right: 15px;">
                            <i class="fas fa-map-marker-alt" style="font-size: 28px; color: #9C27B0;"></i>
                        </div>
                        <div>
                            <p class="text-muted mb-1" style="font-size: 14px;">Lokasi Aset</p>
                            <h5 class="fw-bold mb-0">--</h5>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3 mb-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body d-flex align-items-center">
                        <div style="background-color: #E8F5E9; width: 60px; height: 60px; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-right: 15px;">
                            <i class="fas fa-clipboard-check" style="font-size: 28px; color: #4CAF50;"></i>
                        </div>
                        <div>
                            <p class="text-muted mb-1" style="font-size: 14px;">Monitoring</p>
                            <h5 class="fw-bold mb-0">--</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
