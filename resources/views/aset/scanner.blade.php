@extends('layouts.app')

@section('title', 'Scan Barcode Aset')


@section('content')
<div class="container-fluid px-1 py-0 mt-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold mb-0">Pemindai Barcode Aset</h3>
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
                    <span style="font-size: 14px; font-weight: 500; position: relative; top: 2px;">Data Aset</span>
                </a>
            </li>
            <li class="separator text-muted d-flex align-items-center px-2">
                <span style="font-size: 14px; position: relative; top: 2px;">-</span>
            </li>
            <li class="nav-item d-flex align-items-center">
                <span class="text-muted" style="font-size: 14px; font-weight: 500; position: relative; top: 2px;">Scanner</span>
            </li>
        </ul>
    </div>

    <div class="row pt-2">
        <div class="col-lg-6 col-md-8 mx-auto">
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show mb-4 rounded-3 border-0 shadow-sm" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="scanner-card">
                <div class="scanner-header">
                    <div class="scanner-header-left">
                        <div class="scanner-icon-box">
                            <i class="fas fa-camera"></i>
                        </div>
                        <div>
                            <h4 class="scanner-title">Scan Barcode Aset</h4>
                            <p class="scanner-subtitle">Arahkan kamera ke barcode untuk memindai aset secara otomatis</p>
                        </div>
                    </div>
                </div>
                
                <div class="scanner-body text-center">
                    <!-- Scanner Reader -->
                    <div class="scanner-wrapper" id="scanner-wrapper">
                        <div id="reader"></div>
                        <div class="scanner-overlay d-none" id="scanner-overlay">
                            <div class="scanner-overlay-corners"></div>
                            <div class="scanner-laser"></div>
                        </div>
                    </div>
                    
                    <form id="scanForm" action="{{ route('aset.scanProses') }}" method="POST" class="d-none">
                        @csrf
                        <input type="hidden" name="nomor_aset" id="nomor_aset_input">
                    </form>

                    <!-- Info Box -->
                    <div class="info-box text-start">
                        <div class="info-icon">
                            <i class="fas fa-lightbulb"></i>
                        </div>
                        <p class="info-text">Pastikan pencahayaan cukup dan izinkan akses kamera pada browser Anda. Jaga jarak kamera sekitar 15-30 cm dari barcode.</p>
                    </div>

                    <!-- Divider -->
                    <div class="divider-container">
                        <span>ATAU INPUT MANUAL</span>
                    </div>

                    <!-- Manual Input -->
                    <div class="text-center mt-3">
                        <button type="button" id="manualInputBtn" class="btn-manual-primary">
                            <i class="fas fa-keyboard"></i> Input Manual Nomor Aset
                        </button>
                    </div>

                    {{-- Form Manual --}}
                    <div id="manualInputContainer" class="manual-input-box d-none">
                        <div class="manual-input-title">
                            <i class="fas fa-barcode"></i> NOMOR ASET
                        </div>
                        <form action="{{ route('aset.scanProses') }}" method="POST">
                            @csrf
                            <div class="manual-input-group">
                                <input type="text" name="nomor_aset" class="manual-input-field" placeholder="Contoh: 0001/REKA/IT-A/MONITOR/SERVER/2026" required>
                                <button type="submit" class="btn-search-manual">
                                    <i class="fas fa-search"></i> Cari Aset
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const html5QrCode = new Html5Qrcode("reader");
        const scanForm = document.getElementById('scanForm');
        const inputSet = document.getElementById('nomor_aset_input');
        const statusBadge = document.getElementById('scannerStatusBadge');
        const statusText = document.getElementById('scannerStatusText');
        const statusDot = document.querySelector('.status-dot');
        const scannerOverlay = document.getElementById('scanner-overlay');
        
        let isScanned = false; 

        const qrCodeSuccessCallback = (decodedText, decodedResult) => {
            if(!isScanned) {
                isScanned = true;
                
                if(statusBadge && statusText && statusDot) {
                    statusText.innerText = "Barcode Terdeteksi!";
                    statusBadge.style.backgroundColor = "rgba(34, 197, 94, 0.2)";
                    statusBadge.style.borderColor = "rgba(34, 197, 94, 0.4)";
                }

                if(scannerOverlay) {
                    // Turn laser
                    const laser = scannerOverlay.querySelector('.scanner-laser');
                    if(laser) {
                        laser.style.animation = 'none';
                        laser.style.top = '50%';
                        laser.style.opacity = '1';
                    }
                }

                html5QrCode.stop().then(() => {
                    inputSet.value = decodedText;
                    scanForm.submit();
                }).catch((err) => {
                    console.error("Failed to stop scanner", err);
                });
            }
        };
        
        const config = { 
            fps: 20, 
            disableFlip: false,
            qrbox: function(viewfinderWidth, viewfinderHeight) {
                return {
                    width: viewfinderWidth * 0.85,
                    height: viewfinderHeight * 0.85
                };
            }
        };

        html5QrCode.start({ facingMode: "environment" }, config, qrCodeSuccessCallback)
        .then(() => {
            // Show overlay 
            if(scannerOverlay) {
                scannerOverlay.classList.remove('d-none');
            }
        })
        .catch(err => {
            console.error("Camera access failed:", err);
            
            if(statusBadge && statusText && statusDot) {
                statusText.innerText = "Kamera Tidak Aktif";
                statusBadge.style.backgroundColor = "rgba(239, 68, 68, 0.2)";
                statusBadge.style.borderColor = "rgba(239, 68, 68, 0.4)";
                statusDot.style.backgroundColor = "#ef4444";
                statusDot.style.boxShadow = "0 0 8px rgba(239, 68, 68, 0.6)";
            }
            
            document.getElementById('manualInputContainer').classList.remove('d-none');
        });

        document.getElementById('manualInputBtn').addEventListener('click', function() {
            const manualContainer = document.getElementById('manualInputContainer');
            if(manualContainer.classList.contains('d-none')) {
                manualContainer.classList.remove('d-none');
                manualContainer.classList.add('fade-in');
                this.classList.add('active');
            } else {
                manualContainer.classList.add('d-none');
                manualContainer.classList.remove('fade-in');
                this.classList.remove('active');
            }
        });
    });
</script>
@endpush
