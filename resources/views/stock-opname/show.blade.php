@extends('layouts.app')

@section('title', 'Dashboard Stock Opname')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/stock-opname.css') }}">
@endpush

@section('content')
<div class="container-fluid px-1 py-0 mt-0 page-stock-opname-show">

    {{-- Breadcrumb --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold mb-0">Detail Stock Opname</h3>
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
                <a href="{{ route('stock-opname.user-index') }}" class="text-muted text-decoration-none d-flex align-items-center">
                    <span style="font-size: 14px; font-weight: 500; position: relative; top: 2px;">Stock Opname</span>
                </a>
            </li>
            <li class="separator text-muted d-flex align-items-center px-2">
                <span style="font-size: 14px; position: relative; top: 2px;">-</span>
            </li>
            <li class="nav-item">
                <span class="text-muted" style="font-size: 13px; font-weight: 500;">{{ $session->periode }}</span>
            </li>
        </ul>
    </div>


    {{-- HEADER PERIODE --}}
    <div class="so-header-card">
        <div class="so-header-content row align-items-center g-3">
            <div class="col-md-7">
                <div class="d-flex align-items-center gap-2 mb-2">
                    @if($session->status == 'aktif')
                        <span class="badge-soft-light" style="background: rgba(40,167,69,.25); border-color: rgba(255,255,255,.4);">
                            <i class="fas fa-circle me-1" style="font-size:.5rem; vertical-align: middle;"></i> Sedang Berjalan
                        </span>
                    @else
                        <span class="badge-soft-light"><i class="fas fa-flag-checkered me-1"></i> Telah Selesai</span>
                    @endif
                </div>
                <h3 class="fw-bold mb-2">{{ $session->periode }}</h3>
                <div class="d-flex flex-wrap gap-3 opacity-90">
                    <span><i class="far fa-calendar-alt me-1"></i> 
                        {{ \Carbon\Carbon::parse($session->tanggal_mulai)->format('d M Y') }} 
                        <span class="mx-1">→</span>
                        {{ \Carbon\Carbon::parse($session->tanggal_berakhir)->format('d M Y') }}
                    </span>
                    @if(!empty($session->keterangan))
                        <span><i class="fas fa-sticky-note me-1"></i> {{ \Illuminate\Support\Str::limit($session->keterangan, 70) }}</span>
                    @endif
                </div>
            </div>
            <div class="col-md-5 text-md-end">
                <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                    <a href="{{ route('stock-opname.export', $session->id) }}"
                       class="btn btn-light fw-semibold rounded-pill px-3 shadow-sm">
                        <i class="fas fa-file-excel me-2 text-success"></i> Export Laporan
                    </a>
                    <form id="syncForm" action="{{ route('stock-opname.sync', $session->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="button" class="btn btn-warning fw-semibold rounded-pill px-3 shadow-sm text-dark" id="btnSync">
                            <i class="fas fa-sync-alt me-2"></i> Sinkronkan ke Master
                        </button>
                    </form>
                    @if($session->status == 'aktif')
                    <form id="closePeriodForm" action="{{ route('stock-opname.update-status', $session->id) }}" method="POST" class="d-inline">
                        @csrf @method('PUT')
                        <input type="hidden" name="status" value="selesai">
                        <button type="button" class="btn btn-danger fw-semibold rounded-pill px-3 shadow-sm" id="btnClosePeriod">
                            <i class="fas fa-lock me-2"></i> Tutup Periode
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- STAT CARDS --}}
    @php
        $progressPct = $totalAset > 0 ? round(($totalChecked/$totalAset)*100) : 0;
        $deptDone = collect($deptStats)->where('progress', 100)->count();
        $deptTotal = count($deptStats);
        $totalAnomali = count($anomaliLokasi) + count($anomaliKondisi);
    @endphp

    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex align-items-start gap-3 mb-3">
                    <span class="stat-icon primary"><i class="fas fa-clipboard-check"></i></span>
                    <div class="flex-grow-1">
                        <div class="stat-label">Total Aset Dicek</div>
                        <div class="d-flex align-items-baseline gap-1">
                            <div class="stat-number">{{ $totalChecked }}</div>
                            <div class="stat-sub">/ {{ $totalAset }}</div>
                        </div>
                    </div>
                </div>
                <div class="progress progress-thin mb-2">
                    <div class="progress-bar" style="width: {{ $progressPct }}%; background: linear-gradient(90deg,#253070,#48abf7);"></div>
                </div>
                <div class="d-flex justify-content-between">
                    <small class="text-muted">Progres pengecekan</small>
                    <small class="fw-bold text-primary">{{ $progressPct }}%</small>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex align-items-start gap-3 mb-3">
                    <span class="stat-icon success"><i class="fas fa-building-shield"></i></span>
                    <div class="flex-grow-1">
                        <div class="stat-label">Departemen Selesai</div>
                        <div class="d-flex align-items-baseline gap-1">
                            <div class="stat-number">{{ $deptDone }}</div>
                            <div class="stat-sub">/ {{ $deptTotal }}</div>
                        </div>
                    </div>
                </div>
                <div class="stat-sub">{{ $deptTotal > 0 ? round(($deptDone/$deptTotal)*100) : 0 }}% departemen rampung</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex align-items-start gap-3 mb-3">
                    <span class="stat-icon warning"><i class="fas fa-map-marker-alt"></i></span>
                    <div class="flex-grow-1">
                        <div class="stat-label">Anomali Lokasi</div>
                        <div class="stat-number" style="color:#ff9f1c;">{{ count($anomaliLokasi) }}</div>
                    </div>
                </div>
                <div class="stat-sub">Lokasi fisik berbeda dari sistem</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex align-items-start gap-3 mb-3">
                    <span class="stat-icon danger"><i class="fas fa-exclamation-triangle"></i></span>
                    <div class="flex-grow-1">
                        <div class="stat-label">Penurunan Kondisi</div>
                        <div class="stat-number" style="color:#dc3545;">{{ count($anomaliKondisi) }}</div>
                    </div>
                </div>
                <div class="stat-sub">Aset rusak/hilang temuan baru</div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- KIRI: Progress per Departemen --}}
        <div class="col-lg-8">
            <div class="panel-card mb-3">
                <div class="panel-head">
                    <div class="section-title mb-0">
                        <span class="dot"></span> Progres Per Departemen
                    </div>
                    <div>
                        <span class="summary-pill success"><i class="fas fa-check"></i> {{ $deptDone }} Selesai</span>
                        <span class="summary-pill warn ms-1"><i class="fas fa-spinner"></i> {{ max(0, $deptTotal - $deptDone) }} Berjalan</span>
                    </div>
                </div>
                <div class="panel-body">
                    @if(empty($deptStats))
                        <div class="empty-state py-5">
                            <i class="fas fa-building d-block mb-2"></i>
                            <p class="mb-0">Belum ada data departemen.</p>
                        </div>
                    @else
                        <div class="row g-3">
                            @foreach($deptStats as $dept)
                                @php
                                    $isComplete = $dept['progress'] == 100;
                                    $progressColor = $isComplete ? 'background:linear-gradient(90deg,#28a745,#48d971);' : 'background:linear-gradient(90deg,#253070,#48abf7);';
                                @endphp
                                <div class="col-md-6">
                                    <div class="dept-card p-3">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="dept-icon"><i class="fas fa-building"></i></span>
                                                <div>
                                                    <div class="dept-name">{{ \Illuminate\Support\Str::limit($dept['name'], 28) }}</div>
                                                    <small class="text-muted">{{ $dept['total'] }} Aset Total</small>
                                                </div>
                                            </div>
                                            @if($isComplete)
                                                <span class="badge-progress selesai"><i class="fas fa-check-circle"></i> Selesai</span>
                                            @elseif($dept['progress'] == 0)
                                                <span class="badge-progress kosong">Belum Mulai</span>
                                            @else
                                                <span class="badge-progress aktif"><i class="fas fa-bolt"></i> Berjalan</span>
                                            @endif
                                        </div>
                                        
                                        <div class="d-flex align-items-end justify-content-between mb-2 mt-3">
                                            <div class="progress-pct {{ $isComplete ? 'complete' : '' }}">{{ $dept['progress'] }}%</div>
                                            <small class="text-muted">{{ $dept['checked'] }} dari {{ $dept['total'] }} dicek</small>
                                        </div>
                                        <div class="progress progress-thin">
                                            <div class="progress-bar" style="width: {{ $dept['progress'] }}%; {{ $progressColor }}"></div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- KANAN: Atensi Khusus Bagian Umum --}}
        <div class="col-lg-4">
            <div class="panel-card mb-3">
                <div class="panel-head">
                    <div class="section-title mb-0">
                        <span class="dot" style="background:#dc3545;"></span> Informasi Perubahan
                    </div>
                    @if($totalAnomali > 0)
                        <span class="badge bg-danger rounded-pill px-3 py-2">{{ $totalAnomali }}</span>
                    @endif
                </div>
                <div class="panel-body">
                    @if(count($anomaliLokasi) > 0)
                        <h6 class="small fw-bold text-uppercase text-muted mb-3" style="letter-spacing:.04em;">
                            <i class="fas fa-map-marker-alt text-warning me-1"></i> Lokasi Tidak Sesuai
                        </h6>
                        @foreach(collect($anomaliLokasi)->take(5) as $item)
                            <div class="anomali-item lokasi">
                                <div class="anomali-icon"><i class="fas fa-arrow-right-arrow-left"></i></div>
                                <div class="flex-grow-1">
                                    <div class="anomali-aset">
                                        <a href="{{ route('aset.show', $item->aset->id) }}" class="text-decoration-none" style="color: #253070;">
                                            {{ $item->aset->nomor_aset }}
                                        </a>
                                    </div>
                                    <div class="anomali-meta">
                                        Sistem: <strong>{{ $item->aset->lokasi->nama_lokasi ?? '-' }}</strong><br>
                                        Fisik:&nbsp; <strong class="text-warning">{{ is_numeric($item->lokasi_temuan) ? (\App\Models\LokasiAset::find($item->lokasi_temuan)->nama_lokasi ?? $item->lokasi_temuan) : $item->lokasi_temuan }}</strong>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        @if(count($anomaliLokasi) > 5)
                            <small class="text-muted d-block mt-2">+{{ count($anomaliLokasi) - 5 }} perubahan lokasi lainnya</small>
                        @endif
                    @endif

                    @if(count($anomaliKondisi) > 0)
                        <h6 class="small fw-bold text-uppercase text-muted {{ count($anomaliLokasi) > 0 ? 'mt-4' : '' }} mb-3" style="letter-spacing:.04em;">
                            <i class="fas fa-exclamation-triangle text-danger me-1"></i> Perubahan Kondisi
                        </h6>
                        @foreach(collect($anomaliKondisi)->take(5) as $item)
                            <div class="anomali-item">
                                <div class="anomali-icon"><i class="fas fa-tools"></i></div>
                                <div class="flex-grow-1">
                                    <div class="anomali-aset">
                                        <a href="{{ route('aset.show', $item->aset->id) }}" class="text-decoration-none" style="color: #253070;">
                                            {{ $item->aset->nomor_aset }}
                                        </a>
                                    </div>
                                    <div class="anomali-meta">
                                        Status: <span class="text-danger fw-bold">{{ $item->kondisi_temuan }}</span> 
                                        <span class="text-muted">(sebelumnya Baik)</span>
                                    </div>
                                </div>
                                @if($item->foto_temuan)
                                    <img src="{{ Storage::url($item->foto_temuan) }}" class="img-preview-sm" onclick="window.open(this.src)">
                                @endif
                            </div>
                        @endforeach
                        @if(count($anomaliKondisi) > 5)
                            <small class="text-muted d-block mt-2">+{{ count($anomaliKondisi) - 5 }} perubahan kondisi lainnya</small>
                        @endif
                    @endif

                    @if(count($anomaliLokasi) == 0 && count($anomaliKondisi) == 0)
                        <div class="empty-state py-4">
                            <i class="fas fa-check-circle text-success d-block mb-2"></i>
                            <p class="mb-0 fw-semibold text-dark">Tidak Ada Perubahan</p>
                            <small class="text-muted">Semua temuan sesuai dengan data master.</small>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Card: Belum Dicek --}}
            <div class="panel-card">
                <div class="panel-body">
                    <div class="belum-dicek-card">
                        <div class="stat-icon danger mb-2 mx-auto"><i class="fas fa-search-minus"></i></div>
                        <h2 class="fw-bold text-danger mb-0">{{ $belumDicek->count() }}</h2>
                        <p class="text-muted small mb-3">Aset Belum Dilaporkan
                            <br><small>(Berpotensi Hilang/Belum Discan)</small>
                        </p>
                        @if($belumDicek->count() > 0 && $session->status == 'aktif')
                            <button class="btn so-btn-outline so-action-btn w-100" data-bs-toggle="modal" data-bs-target="#belumDicekModal">
                                <i class="fas fa-list me-1"></i> Lihat Daftar Aset
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Belum Dicek --}}
@php
    $uniqueDepts = $belumDicek->map(fn($a) => $a->resolved_department_name)->filter()->unique();
@endphp
<div class="modal fade" id="belumDicekModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 1rem;">
            <div class="modal-header text-white" style="background: linear-gradient(135deg,#dc3545,#e85563); border-radius: 1rem 1rem 0 0;">
                <h5 class="modal-title fw-bold"><i class="fas fa-search-minus me-2"></i> Aset Belum Dilaporkan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1) brightness(2);"></button>
            </div>
            <div class="modal-body p-0">
                {{-- Search & Filter Bar --}}
                <div class="px-4 py-3 bg-light border-bottom">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-7">
                            <div class="input-group input-group-sm rounded-3 overflow-hidden border bg-white">
                                <span class="input-group-text bg-white border-0 text-muted"><i class="fas fa-search"></i></span>
                                <input type="text" id="modalSearchInput" class="form-control border-0 shadow-none bg-transparent" placeholder="Cari nomor atau nama aset...">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="input-group input-group-sm rounded-3 overflow-hidden border bg-white">
                                <span class="input-group-text bg-white border-0 text-muted"><i class="fas fa-building"></i></span>
                                <select id="modalDeptSelect" class="form-select border-0 shadow-none bg-transparent">
                                    <option value="">Semua Departemen</option>
                                    @foreach($uniqueDepts as $deptName)
                                        <option value="{{ $deptName }}">{{ $deptName }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive" style="max-height: 480px;">
                    <table class="table table-hover mb-0">
                        <thead class="table-light sticky-top" style="z-index: 10;">
                            <tr>
                                <th style="width:5%;">No</th>
                                <th>Nomor Aset</th>
                                <th>Nama Aset</th>
                                <th>Lokasi Sistem</th>
                                <th>Departemen</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($belumDicek as $aset)
                                <tr data-number="{{ $aset->nomor_aset }}" data-name="{{ $aset->nama_aset }}" data-dept="{{ $aset->resolved_department_name }}">
                                    <td class="row-number">{{ $loop->iteration }}</td>
                                    <td class="fw-bold text-dark">{{ $aset->nomor_aset }}</td>
                                    <td>{{ $aset->nama_aset }}</td>
                                    <td><i class="fas fa-map-marker-alt text-muted me-1"></i> {{ $aset->lokasi->nama_lokasi ?? '-' }}</td>
                                    <td>{{ $aset->resolved_department_name }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        // Konfigurasi SweetAlert Standard Aplikasi (seperti di Edit Data Aset)
        const swalConfig = {
            showConfirmButton: true,
            confirmButtonText: 'OK',
            confirmButtonColor: '#253070',
            customClass: { popup: 'rounded-4 shadow' }
        };

        // Success / Error session flash notifications
        @if(session('success'))
            Swal.fire({
                ...swalConfig,
                icon: 'success',
                title: 'Berhasil!',
                text: "{{ session('success') }}"
            });
        @endif

        @if(session('error'))
            Swal.fire({
                ...swalConfig,
                icon: 'error',
                title: 'Gagal!',
                text: "{{ session('error') }}"
            });
        @endif

        // Sync Confirmation
        $('#btnSync').on('click', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Sinkronkan Data?',
                text: "Aksi ini akan menyelaraskan temuan Stock Opname ke data master aset secara permanen dan tidak dapat dibatalkan.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ff9f1c',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Sinkronkan!',
                cancelButtonText: 'Batal',
                customClass: {
                    popup: 'rounded-4 shadow',
                    confirmButton: 'rounded-pill px-4 fw-bold',
                    cancelButton: 'rounded-pill px-4 fw-bold'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Memproses...',
                        text: 'Sedang melakukan sinkronisasi data master.',
                        allowOutsideClick: false,
                        customClass: { popup: 'rounded-4 shadow' },
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    $('#syncForm').submit();
                }
            });
        });

        // Close Period Confirmation
        $('#btnClosePeriod').on('click', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Tutup Periode Opname?',
                text: "Setelah ditutup, status periode ini akan berubah menjadi Selesai. Pengisian data scan/opname akan dikunci sepenuhnya.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Tutup Periode!',
                cancelButtonText: 'Batal',
                customClass: {
                    popup: 'rounded-4 shadow',
                    confirmButton: 'rounded-pill px-4 fw-bold',
                    cancelButton: 'rounded-pill px-4 fw-bold'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Menutup Sesi...',
                        text: 'Sedang menyelesaikan sesi stock opname.',
                        allowOutsideClick: false,
                        customClass: { popup: 'rounded-4 shadow' },
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    $('#closePeriodForm').submit();
                }
            });
        });

        // Live Filter for "Aset Belum Dilaporkan" Modal
        const modalSearchInput = document.getElementById('modalSearchInput');
        const modalDeptSelect = document.getElementById('modalDeptSelect');
        const modalTableRows = document.querySelectorAll('#belumDicekModal tbody tr');

        function filterModalTable() {
            const searchTerm = modalSearchInput.value.toLowerCase().trim();
            const selectedDept = modalDeptSelect.value;
            let visibleCount = 0;

            modalTableRows.forEach(row => {
                if (row.id === 'modalEmptyRow') return;

                const numberAset = row.getAttribute('data-number').toLowerCase();
                const nameAset = row.getAttribute('data-name').toLowerCase();
                const dept = row.getAttribute('data-dept');

                const matchesSearch = numberAset.includes(searchTerm) || nameAset.includes(searchTerm);
                const matchesDept = !selectedDept || dept === selectedDept;

                if (matchesSearch && matchesDept) {
                    row.style.display = '';
                    visibleCount++;
                    row.querySelector('.row-number').textContent = visibleCount;
                } else {
                    row.style.display = 'none';
                }
            });

            // Handle empty state
            const emptyRow = document.getElementById('modalEmptyRow');
            if (visibleCount === 0) {
                if (!emptyRow) {
                    const tbody = document.querySelector('#belumDicekModal tbody');
                    const tr = document.createElement('tr');
                    tr.id = 'modalEmptyRow';
                    tr.innerHTML = `<td colspan="5" class="text-center text-muted py-4"><i class="fas fa-search me-1"></i> Tidak ada aset yang cocok.</td>`;
                    tbody.appendChild(tr);
                }
            } else {
                if (emptyRow) emptyRow.remove();
            }
        }

        if (modalSearchInput && modalDeptSelect) {
            modalSearchInput.addEventListener('input', filterModalTable);
            modalDeptSelect.addEventListener('change', filterModalTable);
        }
    });
</script>
@endpush
