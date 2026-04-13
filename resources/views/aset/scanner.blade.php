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
        <div class="col-md-8 mx-auto">
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white pt-3 pb-2 text-center">
                    <h5 class="fw-bold text-primary mb-0"><i class="fas fa-camera me-2"></i> Arahkan Barcode ke Kamera</h5>
                </div>
                <div class="card-body p-4 text-center">
                    <div id="reader" style="width: 100%; max-width: 600px; margin: 0 auto; border-radius: 8px; overflow: hidden;" class="shadow-sm border"></div>
                    <form id="scanForm" action="{{ route('aset.scanProses') }}" method="POST" class="d-none">
                        @csrf
                        <input type="hidden" name="nomor_aset" id="nomor_aset_input">
                    </form>
                    <div class="mt-4">
                        <p class="text-muted small">Pastikan pencahayaan cukup dan izinkan akses kamera pada browser Anda.</p>
                        <button type="button" id="manualInputBtn" class="btn btn-outline-secondary btn-sm mt-2">
                            <i class="fas fa-keyboard me-1"></i> Input Manual Nomor Aset
                        </button>
                    </div>
                    
                    {{-- Form Manual (Hidden by default) --}}
                    <div id="manualInputContainer" class="mt-3 d-none">
                        <form action="{{ route('aset.scanProses') }}" method="POST" class="d-flex justify-content-center">
                            @csrf
                            <input type="text" name="nomor_aset" class="form-control form-control-sm w-50 me-2" placeholder="Masukkan nomor aset..." required>
                            <button type="submit" class="btn btn-primary btn-sm">Cari</button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const html5QrCode = new Html5Qrcode("reader");
        const scanForm = document.getElementById('scanForm');
        const inputSet = document.getElementById('nomor_aset_input');
        
        let isScanned = false; 

        const qrCodeSuccessCallback = (decodedText, decodedResult) => {
            if(!isScanned) {
                isScanned = true;
                html5QrCode.stop().then(() => {
                    inputSet.value = decodedText;
                    scanForm.submit();
                }).catch((err) => {
                    console.error("Failed to stop scanner", err);
                });
            }
        };
        
        const config = { fps: 10, qrbox: { width: 250, height: 250 } };

        html5QrCode.start({ facingMode: "environment" }, config, qrCodeSuccessCallback)
        .catch(err => {
            console.error("Camera access failed:", err);
            document.getElementById('manualInputContainer').classList.remove('d-none');
            alert("Tidak dapat mengakses kamera. Pastikan memberikan izin kamera atau URL menggunakan HTTPS/localhost.");
        });

        document.getElementById('manualInputBtn').addEventListener('click', function() {
            const manualContainer = document.getElementById('manualInputContainer');
            manualContainer.classList.toggle('d-none');
        });
    });
</script>
@endpush
