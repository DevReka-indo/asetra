@extends('layouts.app')

@section('title', 'Manajemen Stock Opname')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/stock-opname.css') }}">
@endpush

@section('content')
<div class="container-fluid px-1 py-0 mt-0 page-stock-opname-index">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold mb-0">Manajemen Stock Opname</h3>
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
                <span class="text-muted" style="font-size: 14px; font-weight: 500; position: relative; top: 2px;">Stock Opname</span>
            </li>
        </ul>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- HERO BANNER --}}
    @php
        $totalSesi = $sessions->count();
        $sesiAktif = $sessions->where('status','aktif')->count();
        $sesiSelesai = $sessions->where('status','selesai')->count();
        $sesiTerakhir = $sessions->first();
    @endphp
    <div class="so-hero d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div class="so-hero-content">
            <h4 class="fw-bold mb-1">Kelola Periode &amp; Pantau Progres Pengecekan Aset</h4>
            
        </div>
        <div class="so-hero-content">
            @if($sesiAktif > 0)
                <button class="btn btn-light fw-bold px-4 rounded-pill shadow-sm" onclick="Swal.fire('Perhatian', 'Gagal membuat jadwal baru. Harap selesaikan jadwal opname aktif terlebih dahulu.', 'warning')">
                    <i class="fas fa-plus-circle me-2 text-warning"></i> Buat Jadwal Baru
                </button>
            @else
                <button class="btn btn-light fw-bold px-4 rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#createModal">
                    <i class="fas fa-plus-circle me-2 text-primary"></i> Buat Jadwal Baru
                </button>
            @endif
        </div>
    </div>

    {{-- STAT CARDS --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="so-stat d-flex align-items-center gap-3">
                <span class="so-stat-ico so-ico-blue"><i class="fas fa-layer-group"></i></span>
                <div>
                    <div class="text-muted small fw-semibold text-uppercase" style="letter-spacing:.04em;">Total Periode</div>
                    <div class="fs-4 fw-bold text-dark">{{ $totalSesi }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="so-stat d-flex align-items-center gap-3">
                <span class="so-stat-ico so-ico-green"><i class="fas fa-bolt"></i></span>
                <div>
                    <div class="text-muted small fw-semibold text-uppercase" style="letter-spacing:.04em;">Sedang Aktif</div>
                    <div class="fs-4 fw-bold text-dark">{{ $sesiAktif }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="so-stat d-flex align-items-center gap-3">
                <span class="so-stat-ico so-ico-grey"><i class="fas fa-flag-checkered"></i></span>
                <div>
                    <div class="text-muted small fw-semibold text-uppercase" style="letter-spacing:.04em;">Telah Selesai</div>
                    <div class="fs-4 fw-bold text-dark">{{ $sesiSelesai }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="so-stat d-flex align-items-center gap-3">
                <span class="so-stat-ico so-ico-orange"><i class="fas fa-calendar-day"></i></span>
                <div class="flex-grow-1">
                    <div class="text-muted small fw-semibold text-uppercase" style="letter-spacing:.04em;">Periode Terbaru</div>
                    <div class="fs-6 fw-bold text-dark">{{ $sesiTerakhir->periode ?? '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- TABEL PERIODE --}}
    <div class="card so-card-table border-0 shadow-sm">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-2 px-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h5 class="card-title fw-bold text-dark mb-1">Daftar Jadwal Stock Opname</h5>
                </div>
            </div>
        </div>
        <div class="card-body px-4 pt-3 pb-4">
            {{-- Custom Filter Toolbar to match Kelola Pages --}}
            <div class="row g-2 align-items-end mb-3">
                {{-- Entries --}}
                <div style="width: 100px;">
                    <label class="form-label fw-bold small text-muted text-uppercase" style="font-size: 0.7rem; margin-bottom: 0.25rem; display: block;">Entries</label>
                    <select class="form-select form-select-sm rounded-3 custom-entries-select">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
                {{-- Pencarian --}}
                <div class="col-md-3 ms-auto">
                    <label class="form-label fw-bold small text-muted text-uppercase" style="font-size: 0.7rem; margin-bottom: 0.25rem; display: block;">Pencarian</label>
                    <div class="input-group input-group-sm input-group-focus rounded-3" style="border: 1px solid #ced4da; background: #fff;">
                        <span class="input-group-text bg-white border-0 text-muted"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control border-0 shadow-none bg-transparent custom-search-input" placeholder="Cari periode...">
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="stockOpnameTable">
                    <thead class="table-light">
                        <tr>
                            <th width="5%" class="text-center">No</th>
                            <th>Periode</th>
                            <th>Tanggal Pelaksanaan</th>
                            <th>Keterangan</th>
                            <th>Dibuat Oleh</th>
                            <th class="text-center">Status</th>
                            <th width="13%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sessions as $session)
                            @php
                                $start = \Carbon\Carbon::parse($session->tanggal_mulai);
                                $end   = \Carbon\Carbon::parse($session->tanggal_berakhir);
                                $durasi = $start->diffInDays($end) + 1;
                            @endphp
                            <tr>
                                <td class="text-center text-muted">{{ $loop->iteration }}</td>
                                <td>
                                    <div class="so-period-name">
                                        <i class="fas fa-calendar-check me-1 text-primary"></i> {{ $session->periode }}
                                    </div>
                                    <small class="text-muted">Durasi {{ $durasi }} hari</small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="far fa-calendar-alt so-meta-icon"></i>
                                        <span>
                                            <span class="fw-semibold text-dark">{{ $start->format('d M') }}</span>
                                            <span class="text-muted mx-1">→</span>
                                            <span class="fw-semibold text-dark">{{ $end->format('d M Y') }}</span>
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-muted">{{ \Illuminate\Support\Str::limit($session->keterangan ?: '—', 50) }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="so-stat-ico" style="width:34px;height:34px;background:rgba(37,48,112,.08);color:#253070;font-size:.8rem;">
                                            {{ strtoupper(substr($session->createdBy->firstname ?? 'X', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold small text-dark">{{ $session->createdBy->firstname ?? '-' }} {{ $session->createdBy->lastname ?? '' }}</div>
                                            <small class="text-muted">{{ $session->created_at?->format('d M Y') }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if($session->status == 'aktif')
                                        <span class="badge-status-aktif">Aktif</span>
                                    @else
                                        <span class="badge-status-selesai">Selesai</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('stock-opname.show', $session->id) }}" class="btn so-action-btn so-btn-primary text-white">
                                        <i class="fas fa-chart-line me-1"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach

                        @if($sessions->isEmpty())
                        <tr>
                            <td colspan="7">
                                <div class="so-empty">
                                    <div class="so-empty-ico"><i class="fas fa-clipboard-list"></i></div>
                                    <h5 class="fw-bold text-dark mb-1">Belum Ada Jadwal Stock Opname</h5>
                                    <p class="text-muted mb-3">Mulai dengan membuat jadwal periode opname pertama Anda.</p>
                                    <button class="btn so-btn-primary text-white rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#createModal">
                                        <i class="fas fa-plus me-1"></i> Buat Jadwal Sekarang
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Create -->
<div class="modal fade so-modal" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 pt-4 px-4 pb-3" style="background-color: #253070;">
                <h5 class="modal-title fw-bold d-flex align-items-center gap-2 text-white" id="createModalLabel" style="font-size: 1.05rem;">
                    <i class="fas fa-calendar-plus text-white"></i> Buat Jadwal Stock Opname
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('stock-opname.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4 bg-light">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase mb-1" style="color: #253070; font-size: 0.72rem;">
                            Periode
                            <span class="text-danger">*</span>
                        </label>
                        <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus align-items-center bg-white border">
                            <span class="input-group-text bg-white border-0 text-muted"><i class="fas fa-tag"></i></span>
                            <input type="text" name="periode" class="form-control border-0 shadow-none fs-6" required placeholder="Contoh: Periode 2 2026">
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-uppercase mb-1" style="color: #253070; font-size: 0.72rem;">
                                Tanggal Mulai
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus align-items-center bg-white border">
                                <input type="date" name="tanggal_mulai" class="form-control border-0 shadow-none fs-6" required value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-uppercase mb-1" style="color: #253070; font-size: 0.72rem;">
                                Tanggal Berakhir
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus align-items-center bg-white border">
                                <input type="date" name="tanggal_berakhir" class="form-control border-0 shadow-none fs-6" required value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                    </div>
                    <div class="mb-1">
                        <label class="form-label fw-bold small text-uppercase mb-1" style="color: #253070; font-size: 0.72rem;">
                            Keterangan <span class="text-muted small"></span>
                        </label>
                        <textarea name="keterangan" class="form-control shadow-sm rounded-3 border-0" rows="3" placeholder="Catatan/instruksi tambahan untuk tim..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0 pt-2 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold shadow-sm border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn text-white rounded-pill px-4 fw-bold shadow-sm" style="background-color: #253070; border-color: #253070;">
                        <i class="fas fa-save me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        var dt = $('#stockOpnameTable').DataTable({
            "pageLength": 10,
            "order": [],
            "dom": "rtip", // Hides the default length menu 'l' and search box 'f'
            "language": {
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                "infoEmpty": "",
                "zeroRecords": "Tidak ada data ditemukan",
                "paginate": {
                    "first": "«",
                    "last": "»",
                    "next": "›",
                    "previous": "‹"
                }
            }
        });

        // Wire custom Entries change
        $('.custom-entries-select').on('change', function() {
            dt.page.len(parseInt($(this).val())).draw();
        });

        // Wire custom Search input
        $('.custom-search-input').on('keyup', function() {
            dt.search($(this).val()).draw();
        });
    });
</script>
@endpush
