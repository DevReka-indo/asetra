@extends('layouts.app')

@section('title', 'Manajemen Stock Opname')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/stock-opname.css') }}">
@endpush

@section('content')
<div class="container-fluid px-1 py-0 mt-0 page-stock-opname-index">

    {{-- HEADER --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3">
        <h3 class="fw-bold mb-0">Manajemen Stock Opname</h3>
        <ul class="breadcrumbs d-flex flex-wrap align-items-center p-0 m-0" style="list-style: none;">
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
                <button class="btn btn-light fw-bold px-4 rounded-pill shadow-sm" onclick="Swal.fire({ title: 'Perhatian', text: 'Gagal membuat jadwal baru. Harap selesaikan jadwal opname aktif terlebih dahulu.', icon: 'warning', confirmButtonColor: '#253070', confirmButtonText: 'OK', customClass: { popup: 'rounded-4 shadow' } })">
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
            <div class="row g-2 align-items-center mb-3">
                {{-- Entries --}}
                <div class="col-6 col-sm-auto mb-2 mb-sm-0">
                    <div class="d-flex align-items-center gap-2">
                        <label class="form-label fw-bold small text-muted text-uppercase mb-0" style="font-size: 0.7rem; white-space: nowrap;">Entries</label>
                        <select class="form-select form-select-sm rounded-3 custom-entries-select" style="width: 75px;">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>
                {{-- Pencarian --}}
                <div class="col-12 col-sm-4 col-md-3 ms-sm-auto mb-2 mb-sm-0">
                    <div class="input-group input-group-sm input-group-focus rounded-3" style="border: 1px solid #ced4da; background: #fff;">
                        <span class="input-group-text bg-white border-0 text-muted"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control border-0 shadow-none bg-transparent custom-search-input" placeholder="Cari...">
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="stockOpnameTable">
                    <thead class="table-light">
                        <tr>
                            <th width="5%" class="text-center d-none d-md-table-cell">No</th>
                            <th>Periode</th>
                            <th>Tanggal Pelaksanaan</th>
                            <th class="d-none d-lg-table-cell">Keterangan</th>
                            <th class="d-none d-md-table-cell">Dibuat Oleh</th>
                            <th class="text-center">Status</th>
                            <th width="150" class="text-center">Aksi</th>
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
                                <td class="text-center text-muted d-none d-md-table-cell">{{ $loop->iteration }}</td>
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
                                <td class="d-none d-lg-table-cell">
                                    <span class="text-muted">{{ \Illuminate\Support\Str::limit($session->keterangan ?: '—', 50) }}</span>
                                </td>
                                <td class="d-none d-md-table-cell">
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
                                    <div class="d-flex justify-content-center align-items-center gap-2">
                                        {{-- Tombol Detail / View --}}
                                        <a href="{{ route('stock-opname.show', $session->id) }}" class="btn btn-info btn-sm rounded-circle text-white border-0" 
                                            style="width:32px; height:32px; display:inline-flex; align-items:center; justify-content:center;"
                                            title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        {{-- Tombol Edit (Warning) --}}
                                        <button type="button" class="btn btn-warning btn-sm rounded-circle text-white border-0" 
                                            style="width:32px; height:32px; display:flex; align-items:center; justify-content:center;"
                                            title="Edit" data-bs-toggle="modal" data-bs-target="#editModal{{ $session->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        {{-- Tombol Hapus (Danger) --}}
                                        <button type="button" class="btn btn-danger btn-sm rounded-circle text-white border-0" 
                                            style="width:32px; height:32px; display:flex; align-items:center; justify-content:center;"
                                            title="Hapus" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $session->id }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
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
            <form action="{{ route('stock-opname.store') }}" method="POST" id="formCreateStockOpname">
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

{{-- MODALS EDIT & HAPUS --}}
@foreach($sessions as $session)
    <!-- Modal Edit -->
    <div class="modal fade so-modal" id="editModal{{ $session->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $session->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header border-0 pt-4 px-4 pb-3" style="background-color: #253070;">
                    <h5 class="modal-title fw-bold d-flex align-items-center gap-2 text-white" id="editModalLabel{{ $session->id }}" style="font-size: 1.05rem;">
                        <i class="fas fa-edit text-white"></i> Edit Jadwal Stock Opname
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('stock-opname.update', $session->id) }}" method="POST" id="formEditStockOpname{{ $session->id }}" autocomplete="off" novalidate>
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="form_type" value="edit_{{ $session->id }}">
                    <div class="modal-body p-4 bg-light">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase mb-1" style="color: #253070; font-size: 0.72rem;">
                                Periode
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus align-items-center bg-white border">
                                <span class="input-group-text bg-white border-0 text-muted"><i class="fas fa-tag"></i></span>
                                <input type="text" name="periode" class="form-control border-0 shadow-none fs-6" required placeholder="Contoh: Periode 2 2026" value="{{ old('form_type') == 'edit_'.$session->id ? old('periode') : $session->periode }}">
                            </div>
                            @if(old('form_type') == 'edit_'.$session->id)
                                @error('periode') <div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div> @enderror
                            @endif
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase mb-1" style="color: #253070; font-size: 0.72rem;">
                                    Tanggal Mulai
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus align-items-center bg-white border">
                                    <input type="date" name="tanggal_mulai" class="form-control border-0 shadow-none fs-6" required value="{{ old('form_type') == 'edit_'.$session->id ? old('tanggal_mulai') : ($session->tanggal_mulai ? $session->tanggal_mulai->format('Y-m-d') : '') }}">
                                </div>
                                @if(old('form_type') == 'edit_'.$session->id)
                                    @error('tanggal_mulai') <div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div> @enderror
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase mb-1" style="color: #253070; font-size: 0.72rem;">
                                    Tanggal Berakhir
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus align-items-center bg-white border">
                                    <input type="date" name="tanggal_berakhir" class="form-control border-0 shadow-none fs-6" required value="{{ old('form_type') == 'edit_'.$session->id ? old('tanggal_berakhir') : ($session->tanggal_berakhir ? $session->tanggal_berakhir->format('Y-m-d') : '') }}">
                                </div>
                                @if(old('form_type') == 'edit_'.$session->id)
                                    @error('tanggal_berakhir') <div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div> @enderror
                                @endif
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase mb-1" style="color: #253070; font-size: 0.72rem;">
                                Status
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus align-items-center bg-white border">
                                <span class="input-group-text bg-white border-0 text-muted"><i class="fas fa-toggle-on"></i></span>
                                <select name="status" class="form-select border-0 shadow-none fs-6" required>
                                    @php
                                        $currentStatus = old('form_type') == 'edit_'.$session->id ? old('status') : $session->status;
                                    @endphp
                                    @if($session->isActive())
                                        <option value="aktif" {{ $currentStatus == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                    @endif
                                    <option value="selesai" {{ $currentStatus == 'selesai' ? 'selected' : '' }}>Selesai</option>
                                </select>
                            </div>
                            @if(old('form_type') == 'edit_'.$session->id)
                                @error('status') <div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div> @enderror
                            @endif
                        </div>
                        <div class="mb-1">
                            <label class="form-label fw-bold small text-uppercase mb-1" style="color: #253070; font-size: 0.72rem;">
                                Keterangan
                            </label>
                            <textarea name="keterangan" class="form-control shadow-sm rounded-3 border-0" rows="3" placeholder="Catatan/instruksi tambahan untuk tim...">{{ old('form_type') == 'edit_'.$session->id ? old('keterangan') : $session->keterangan }}</textarea>
                            @if(old('form_type') == 'edit_'.$session->id)
                                @error('keterangan') <div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div> @enderror
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top-0 pt-2 pb-4 px-4">
                        <button type="button" class="btn btn-light rounded-pill px-4 fw-bold shadow-sm border" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn text-white rounded-pill px-4 fw-bold shadow-sm" style="background-color: #253070; border-color: #253070;">
                            <i class="fas fa-save me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Delete -->
    <div class="modal fade so-modal" id="deleteModal{{ $session->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-body p-5 text-center bg-light">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-4 mb-4" style="width: 80px; height: 80px; background-color: #f8d7da;">
                        <i class="fas fa-exclamation-triangle fa-3x text-danger"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-2">Konfirmasi Hapus</h4>
                    <p class="text-muted mb-4" style="font-size: 1rem;">
                        Anda yakin ingin menghapus jadwal Stock Opname periode <br>
                        <strong class="text-danger fs-5">{{ $session->periode }}</strong>?
                    </p>
                    <div class="alert alert-warning border-0 rounded-3 text-start small mb-4">
                        <i class="fas fa-exclamation-circle me-2 text-warning"></i>
                        <strong>Peringatan:</strong> Menghapus jadwal ini juga akan menghapus secara permanen semua data scan temuan aset, catatan kondisi, dan file foto yang terkait dengan periode ini.
                    </div>
                    <div class="d-flex justify-content-center gap-3">
                        <form action="{{ route('stock-opname.destroy', $session->id) }}" method="POST" class="w-100 d-flex justify-content-center gap-3">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-light rounded-pill fw-bold py-2 shadow-sm border" style="width: 120px;" data-bs-dismiss="modal">Batalkan</button>
                            <button type="submit" class="btn btn-danger rounded-pill fw-bold py-2 shadow-sm" style="width: 140px;">Ya, Hapus</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endforeach
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

        dt.on('order.dt search.dt', function () {
            dt.column(0, {search:'applied', order:'applied'}).nodes().each(function (cell, i) {
                cell.innerHTML = i + 1;
            });
        }).draw();

        // Wire custom Entries change
        $('.custom-entries-select').on('change', function() {
            dt.page.len(parseInt($(this).val())).draw();
        });

        // Wire custom Search input
        $('.custom-search-input').on('keyup', function() {
            dt.search($(this).val()).draw();
        });

        // Setup SweetAlert config
        const swalConfig = {
            showConfirmButton: true,
            confirmButtonText: 'OK',
            confirmButtonColor: '#253070',
            customClass: { popup: 'rounded-4 shadow' }
        };

        // Form Validation & AJAX Submit
        const formsToValidate = document.querySelectorAll('#formCreateStockOpname, form[id^="formEditStockOpname"]');
        formsToValidate.forEach(form => {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();

                // Clear previous errors
                form.querySelectorAll('.invalid-feedback-custom').forEach(el => el.remove());
                form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                form.querySelectorAll('.input-group').forEach(el => {
                    el.classList.remove('border', 'border-danger');
                });

                let isValid = true;
                let firstInvalidEl = null;

                const fields = form.querySelectorAll('input, select');
                fields.forEach(field => {
                    if (field.type === 'hidden') return;
                    
                    let labelText = '';
                    const formGroup = field.closest('.mb-3, .row, .mb-1');
                    if (formGroup) {
                        const labelEl = formGroup.querySelector('label');
                        if (labelEl) {
                            labelText = labelEl.textContent.replace('*', '').trim();
                        }
                    }
                    if (!labelText) {
                        labelText = field.getAttribute('placeholder') || field.getAttribute('name') || 'Kolom';
                    }

                    if (field.hasAttribute('required') && (!field.value || (field.tagName === 'SELECT' && field.value === ''))) {
                        isValid = false;
                        field.classList.add('is-invalid');
                        const inputGroup = field.closest('.input-group');
                        if (inputGroup) {
                            inputGroup.classList.add('border', 'border-danger');
                        }

                        const errorDiv = document.createElement('div');
                        errorDiv.className = 'text-danger small mt-1 fw-bold invalid-feedback-custom';
                        errorDiv.innerHTML = `<i class="fas fa-exclamation-circle me-1"></i>${labelText} wajib diisi.`;
                        
                        const targetAnchor = inputGroup || field;
                        targetAnchor.parentNode.insertBefore(errorDiv, targetAnchor.nextSibling);

                        if (!firstInvalidEl) {
                            firstInvalidEl = targetAnchor;
                        }
                    }
                });

                // Custom validation: End Date must be after or equal to Start Date
                const tglMulai = form.querySelector('[name="tanggal_mulai"]');
                const tglAkhir = form.querySelector('[name="tanggal_berakhir"]');
                if (tglMulai && tglAkhir && tglMulai.value && tglAkhir.value) {
                    if (new Date(tglAkhir.value) < new Date(tglMulai.value)) {
                        isValid = false;
                        tglAkhir.classList.add('is-invalid');
                        const inputGroup = tglAkhir.closest('.input-group');
                        if (inputGroup) {
                            inputGroup.classList.add('border', 'border-danger');
                        }

                        const errorDiv = document.createElement('div');
                        errorDiv.className = 'text-danger small mt-1 fw-bold invalid-feedback-custom';
                        errorDiv.innerHTML = `<i class="fas fa-exclamation-circle me-1"></i>Tanggal berakhir harus setelah atau sama dengan tanggal mulai.`;
                        
                        const targetAnchor = inputGroup || tglAkhir;
                        targetAnchor.parentNode.insertBefore(errorDiv, targetAnchor.nextSibling);

                        if (!firstInvalidEl) {
                            firstInvalidEl = targetAnchor;
                        }
                    }
                }

                if (!isValid) {
                    if (firstInvalidEl) {
                        firstInvalidEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                    return;
                }

                // AJAX submission
                Swal.fire({
                    title: 'Menyimpan...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                try {
                    const formData = new FormData(form);
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    const responseData = await response.json();
                    Swal.close();

                    if (response.ok) {
                        // Close the Bootstrap modal immediately
                        const modalEl = form.closest('.modal');
                        if (modalEl) {
                            const modalInstance = bootstrap.Modal.getInstance(modalEl);
                            if (modalInstance) {
                                modalInstance.hide();
                            } else if (window.jQuery || window.$) {
                                $(modalEl).modal('hide');
                            }
                        }

                        await Swal.fire({
                            ...swalConfig,
                            icon: 'success',
                            title: 'Berhasil!',
                            text: responseData.message || 'Data berhasil disimpan.'
                        });
                        location.reload();
                    } else if (response.status === 422) {
                        const errors = responseData.errors;
                        if (errors) {
                            Object.keys(errors).forEach(fieldName => {
                                const field = form.querySelector(`[name="${fieldName}"]`);
                                if (field) {
                                    field.classList.add('is-invalid');
                                    const inputGroup = field.closest('.input-group');
                                    if (inputGroup) {
                                        inputGroup.classList.add('border', 'border-danger');
                                    }

                                    const errorDiv = document.createElement('div');
                                    errorDiv.className = 'text-danger small mt-1 fw-bold invalid-feedback-custom';
                                    errorDiv.innerHTML = `<i class="fas fa-exclamation-circle me-1"></i>${errors[fieldName].join(', ')}`;

                                    const targetAnchor = inputGroup || field;
                                    targetAnchor.parentNode.insertBefore(errorDiv, targetAnchor.nextSibling);
                                }
                            });
                        } else {
                            Swal.fire({
                                ...swalConfig,
                                icon: 'error',
                                title: 'Gagal!',
                                text: responseData.message || 'Terjadi kesalahan validasi.'
                            });
                        }
                    } else {
                        Swal.fire({
                            ...swalConfig,
                            icon: 'error',
                            title: 'Gagal!',
                            text: responseData.message || 'Terjadi kesalahan pada server.'
                        });
                    }
                } catch (err) {
                    Swal.close();
                    Swal.fire({
                        ...swalConfig,
                        icon: 'error',
                        title: 'Gagal!',
                        text: 'Tidak dapat menghubungi server. Silakan coba lagi.'
                    });
                }
            });
        });

        // Add clear error class on modal hide
        document.querySelectorAll('.modal').forEach(modalEl => {
            modalEl.addEventListener('hidden.bs.modal', function() {
                const form = this.querySelector('form');
                if (form) {
                    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                    form.querySelectorAll('.invalid-feedback-custom').forEach(el => el.remove());
                    form.querySelectorAll('.input-group').forEach(el => {
                        el.classList.remove('border', 'border-danger');
                    });
                    form.reset();
                }
            });
        });
    });
</script>
@endpush
