@extends('layouts.app')

@section('title', 'Scan Barcode Aset')


@section('content')
<div class="container-fluid px-1 py-0 mt-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold mb-0">Scanner Barcode Aset</h3>
        <ul class="breadcrumbs d-flex align-items-center p-0 m-0" style="list-style: none;"> 
            <li class="nav-home d-flex align-items-center">
                <a href="{{ route('dashboard') }}" class="text-muted text-decoration-none d-flex align-items-center">
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
        <div class="col-12 mx-auto" style="max-width: 560px;">
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show mb-4 rounded-3 border-0 shadow-sm" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            @php
                $defaultMode = request('mode') === 'opname' ? 'opname' : 'normal';
            @endphp

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

                <!-- Mode Selector-->
                <div class="px-4 pt-3 pb-0 d-none">
                    <div class="form-group mb-0">
                        <label class="form-label fw-bold">Mode Scan</label>
                        <select id="scanMode" class="form-select form-control">
                            <option value="normal" {{ $defaultMode == 'normal' ? 'selected' : '' }}>Scan Normal (Lihat Detail Aset)</option>
                            <option value="opname" {{ $defaultMode == 'opname' ? 'selected' : '' }}>Stock Opname (Pengecekan Fisik)</option>
                        </select>
                    </div>
                    
                    <div id="opnameSessionContainer" class="form-group mt-3 {{ ($defaultMode == 'opname' && !request('session_id')) ? '' : 'd-none' }}">
                        <label class="form-label fw-bold">Pilih Jadwal Stock Opname</label>
                        <select id="opnameSession" class="form-select form-control">
                            <option value="">-- Pilih Sesi --</option>
                            @foreach($activeOpnames as $opname)
                                <option value="{{ $opname->id }}" {{ request('session_id') == $opname->id ? 'selected' : '' }}>{{ $opname->periode }} ({{ \Carbon\Carbon::parse($opname->tanggal_mulai)->format('d M') }} - {{ \Carbon\Carbon::parse($opname->tanggal_berakhir)->format('d M Y') }})</option>
                            @endforeach
                        </select>
                    </div>

                    @if(request('manual_id'))
                        <div class="alert alert-info mt-3 py-2 border-info shadow-sm">
                            <i class="fas fa-info-circle me-1"></i> Mode Cek Manual aktif. Tekan tombol Scanner di bawah untuk mulai mengisi data.
                            <input type="hidden" id="manualAsetId" value="{{ request('manual_id') }}">
                        </div>
                    @endif
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
                    
                    <!-- Form Normal Scan -->
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
                        <div class="manual-input-group">
                            <input type="text" id="manual_nomor_aset" class="manual-input-field" placeholder="Contoh: 0001/REKA/IT-A/..." required>
                            <button type="button" id="btnManualSubmit" class="btn-search-manual">
                                <i class="fas fa-search"></i> Cari Aset
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Stock Opname Input -->
<div class="modal fade" id="stockOpnameModal" tabindex="-1" aria-labelledby="stockOpnameModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 pt-4 px-4 pb-3" style="background-color: #253070;">
                <h5 class="modal-title fw-bold text-white" id="stockOpnameModalLabel">
                    <i class="fas fa-clipboard-check me-2"></i> Form Temuan Stock Opname
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" id="btnCloseOpnameModal"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <div class="alert alert-info border-0 shadow-sm rounded-3 mb-3">
                    <small>Memproses Aset: <strong id="scanned_aset_display"></strong></small>
                </div>
                
                <form id="stockOpnameForm">
                    @csrf
                    <input type="hidden" id="so_session_id" name="stock_opname_id">
                    <input type="hidden" id="so_aset_id" name="aset_id">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold small" style="color: #253070;">Kondisi Fisik Saat Ini <span class="text-danger">*</span></label>
                        <select name="kondisi_temuan" id="so_kondisi" class="form-select shadow-sm rounded-3" required>
                            <option value="">-- Pilih Kondisi --</option>
                            <option value="Baik">Baik</option>
                            <option value="Rusak">Rusak</option>
                            <option value="Bongkar">Bongkar</option>
                            <option value="Tidak Terpakai">Tidak Terpakai</option>
                            <option value="Hilang">Hilang</option>
                            <option value="Tidak Teridentifikasi">Tidak Teridentifikasi</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small" style="color: #253070;">Lokasi Fisik Saat Ini <span class="text-danger">*</span></label>
                        <select name="lokasi_temuan" id="so_lokasi" class="form-select shadow-sm rounded-3" required>
                            <option value="">-- Pilih Lokasi --</option>
                            @foreach($lokasis as $lokasi)
                                <option value="{{ $lokasi->lokasi_id }}">{{ $lokasi->nama_lokasi }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small" style="color: #253070;">Foto Bukti Fisik <span class="text-danger">*</span></label>
                        <input type="file" name="foto_temuan" id="so_foto" class="form-control shadow-sm rounded-3" accept="image/*" capture="environment" required>
                        <small class="text-muted mt-1 d-block">Langsung dari kamera atau pilih file.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small" style="color: #253070;">Keterangan (Opsional)</label>
                        <textarea name="keterangan" id="so_keterangan" class="form-control shadow-sm rounded-3" rows="2" placeholder="Tambahkan catatan jika perlu..."></textarea>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary btn-lg rounded-pill fw-bold shadow-sm" id="btnSubmitOpname" style="background-color: #253070; border-color: #253070;">
                            <i class="fas fa-save me-2"></i> Simpan Temuan & Lanjut Scan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        let html5QrCode;
        try {
            html5QrCode = new Html5Qrcode("reader");
        } catch(e) {
            console.error(e);
        }

        const scanForm = document.getElementById('scanForm');
        const inputSet = document.getElementById('nomor_aset_input');
        const scannerOverlay = document.getElementById('scanner-overlay');
        
        // Inisialisasi awal
        const scanMode = document.getElementById('scanMode');
        const opnameSessionContainer = document.getElementById('opnameSessionContainer');
        const opnameSession = document.getElementById('opnameSession');
        const manualAsetIdInput = document.getElementById('manualAsetId');
        
        let isScanned = false;

        // Function to toggle inputs when kondisi is 'Tidak Teridentifikasi'
        const soKondisi = document.getElementById('so_kondisi');
        const soLokasi = document.getElementById('so_lokasi');
        const soFoto = document.getElementById('so_foto');

        function handleKondisiChange() {
            if (!soKondisi) return;
            const isUnidentified = soKondisi.value === 'Tidak Teridentifikasi';
            
            if (isUnidentified) {
                // Disable and remove required
                soLokasi.disabled = true;
                soLokasi.required = false;
                soLokasi.value = '';
                
                soFoto.disabled = true;
                soFoto.required = false;
                soFoto.value = '';
                
                // Hide red asterisk *
                const lokasiLabelAsterisk = soLokasi.closest('.mb-3').querySelector('.text-danger');
                if (lokasiLabelAsterisk) lokasiLabelAsterisk.classList.add('d-none');
                
                const fotoLabelAsterisk = soFoto.closest('.mb-3').querySelector('.text-danger');
                if (fotoLabelAsterisk) fotoLabelAsterisk.classList.add('d-none');
            } else {
                // Enable and add required
                soLokasi.disabled = false;
                soLokasi.required = true;
                
                soFoto.disabled = false;
                soFoto.required = true;
                
                // Show red asterisk *
                const lokasiLabelAsterisk = soLokasi.closest('.mb-3').querySelector('.text-danger');
                if (lokasiLabelAsterisk) lokasiLabelAsterisk.classList.remove('d-none');
                
                const fotoLabelAsterisk = soFoto.closest('.mb-3').querySelector('.text-danger');
                if (fotoLabelAsterisk) fotoLabelAsterisk.classList.remove('d-none');
            }
        }

        if (soKondisi) {
            soKondisi.addEventListener('change', handleKondisiChange);
        }
        
        scanMode.addEventListener('change', function() {
            if(this.value === 'opname') {
                opnameSessionContainer.classList.remove('d-none');
            } else {
                opnameSessionContainer.classList.add('d-none');
            }
        });

        // Trigger manual scan
        if (scanMode.value === 'opname' && manualAsetIdInput && manualAsetIdInput.value) {
            const manualId = manualAsetIdInput.value;
            const sessionId = opnameSession.value;
            
            if(sessionId) {
                setTimeout(() => {
                    document.getElementById('scanned_aset_display').innerText = "Manual ID: " + manualId;
                    document.getElementById('so_session_id').value = sessionId;
                    document.getElementById('so_aset_id').value = manualId;
                    
                    document.getElementById('stockOpnameForm').reset();
                    handleKondisiChange();
                    var modal = new bootstrap.Modal(document.getElementById('stockOpnameModal'));
                    modal.show();
                }, 500);
            }
        }

        // --- Logika Scanner HTML5-QRCode ---
        function extractIdFromScan(inputData) {
            if (inputData.startsWith('http')) {
                const parts = inputData.split('/');
                return parts[parts.length - 1]; // Ambil segmen terakhir 
            }
            return inputData; 
        }

        const qrCodeSuccessCallback = (decodedText, decodedResult) => {
            if(!isScanned) {
                
                const mode = scanMode.value;
                
                if (mode === 'opname') {
                    if (!opnameSession.value) {
                        Swal.fire('Perhatian', 'Silakan pilih Jadwal Stock Opname terlebih dahulu!', 'warning');
                        return;
                    }
                    
                    isScanned = true; // Kunci scanner
                    
                    // Pause scanner
                    if(html5QrCode.getState() === Html5QrcodeScannerState.SCANNING) {
                        html5QrCode.pause(true);
                    }
                    
                    const asetIdOrNomor = extractIdFromScan(decodedText);
                    
                    // Coba cari id di database (di sistem QR Code berisi URL /aset/{id})
                    // Untuk Stock Opname, butuh ID.
                    let extractedId = asetIdOrNomor;
                    
                    // Set Data Modal
                    document.getElementById('scanned_aset_display').innerText = decodedText;
                    document.getElementById('so_session_id').value = opnameSession.value;
                    document.getElementById('so_aset_id').value = extractedId; // Kita asumsikan itu ID dari URL
                    
                    // Reset Form
                    document.getElementById('stockOpnameForm').reset();
                    handleKondisiChange();
                    
                    // Tampilkan Modal
                    var modal = new bootstrap.Modal(document.getElementById('stockOpnameModal'));
                    modal.show();

                } else {
                    isScanned = true;
                    // Scan Normal (Redirect)
                    if(html5QrCode) {
                        html5QrCode.stop().then(() => {
                            inputSet.value = decodedText;
                            scanForm.submit();
                        }).catch((err) => {
                            console.error("Failed to stop scanner", err);
                            inputSet.value = decodedText;
                            scanForm.submit();
                        });
                    } else {
                        inputSet.value = decodedText;
                        scanForm.submit();
                    }
                }
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

        if(html5QrCode) {
            html5QrCode.start({ facingMode: "environment" }, config, qrCodeSuccessCallback)
            .then(() => {
                if(scannerOverlay) {
                    scannerOverlay.classList.remove('d-none');
                }
            })
            .catch(err => {
                console.error("Camera access failed:", err);
                document.getElementById('manualInputContainer').classList.remove('d-none');
            });
        }

        // Handle Close Modal -> Resume Scanner
        document.getElementById('stockOpnameModal').addEventListener('hidden.bs.modal', function () {
            isScanned = false;
            if(html5QrCode && html5QrCode.getState() === Html5QrcodeScannerState.PAUSED) {
                html5QrCode.resume();
            }
        });

        // Handle Manual Input Submit
        document.getElementById('btnManualSubmit').addEventListener('click', function() {
            const val = document.getElementById('manual_nomor_aset').value;
            if(!val) return;
            
            const mode = scanMode.value;
            if(mode === 'opname') {
                if (!opnameSession.value) {
                    Swal.fire('Perhatian', 'Silakan pilih Jadwal Stock Opname terlebih dahulu!', 'warning');
                    return;
                }
                
                document.getElementById('scanned_aset_display').innerText = val;
                document.getElementById('so_session_id').value = opnameSession.value;
                document.getElementById('so_aset_id').value = val; // nomor aset, controller handle pencarian ID berdasarkan nomor aset
                
                document.getElementById('stockOpnameForm').reset();
                handleKondisiChange();
                var modal = new bootstrap.Modal(document.getElementById('stockOpnameModal'));
                modal.show();
            } else {
                inputSet.value = val;
                scanForm.submit();
            }
        });

        // Toggle Manual Container
        document.getElementById('manualInputBtn').addEventListener('click', function() {
            const manualContainer = document.getElementById('manualInputContainer');
            if(manualContainer.classList.contains('d-none')) {
                manualContainer.classList.remove('d-none');
                this.classList.add('active');
            } else {
                manualContainer.classList.add('d-none');
                this.classList.remove('active');
            }
        });

        // Submit Form Stock Opname via AJAX
        document.getElementById('stockOpnameForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const btnSubmit = document.getElementById('btnSubmitOpname');
            const originalText = btnSubmit.innerHTML;
            
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Menyimpan...';
            
            fetch("{{ route('stock-opname.scanStore') }}", {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json().then(data => ({status: response.status, body: data})))
            .then(result => {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = originalText;
                
                if (result.status === 200 && result.body.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: result.body.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                    // Close modal 
                    bootstrap.Modal.getInstance(document.getElementById('stockOpnameModal')).hide();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: result.body.message || 'Terjadi kesalahan'
                    });
                }
            })
            .catch(error => {
                console.error(error);
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = originalText;
                Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
            });
        });
    });
</script>
@endpush
