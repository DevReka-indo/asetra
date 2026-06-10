@extends('layouts.app')

@section('title', 'Daftar Aset Opname')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/stock-opname.css') }}">
@endpush

@section('content')
<div class="container-fluid px-1 py-0 mt-0 page-stock-opname-user-show">

    {{-- BREADCRUMB --}}
    {{-- HEADER --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3">
        <h3 class="fw-bold mb-0">Pelaksanaan Stock Opname</h3>
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
                    <a href="{{ route('stock-opname.user-index') }}" class="text-muted text-decoration-none d-flex align-items-center">
                    <span style="font-size: 14px; font-weight: 500; position: relative; top: 2px;"> Pelaksanaan Stock Opname</span>
                </a>
            </li>
            <li class="separator text-muted d-flex align-items-center px-2">
                <span style="font-size: 14px; position: relative; top: 2px;">-</span>
            </li>
            <li class="nav-item">
                <span style="font-size: 14px; position: relative; top: 2px;">{{ $session->periode }}</span>
            </li>
        </ul>
    </div>
    
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- HERO PERIODE + PROGRESS --}}
    @php
        $totalScope = $belumDicek->count() + $telahDicek->count();
        $progressUser = $totalScope > 0 ? round(($telahDicek->count() / $totalScope) * 100) : 0;
        $circumference = 2 * 3.14159 * 50; // r=50
        $offset = $circumference - ($progressUser / 100 * $circumference);
    @endphp

    <div class="so-detail-hero">
        <div class="so-detail-hero-content row align-items-center g-3">
            <div class="col-md-7">
                <h4 class="fw-bold mt-2 mb-1">
                    @if($isAdmin)
                        Daftar Aset Seluruh Departemen
                    @else
                        Daftar Aset di Unit Anda
                    @endif
                </h4>
                <p class="mb-2 opacity-90">
                    <i class="far fa-calendar-alt me-1"></i>
                    {{ \Carbon\Carbon::parse($session->tanggal_mulai)->format('d M') }}
                    <span class="mx-1">→</span>
                    {{ \Carbon\Carbon::parse($session->tanggal_berakhir)->format('d M Y') }}
                </p>
            </div>
            <div class="col-md-5">
                <div class="d-flex align-items-center justify-content-md-end gap-3">
                    <div class="progress-ring">
                        <svg viewBox="0 0 120 120">
                            <circle cx="60" cy="60" r="50" class="ring-track"></circle>
                            <circle cx="60" cy="60" r="50" class="ring-bar hero-progress-circle"
                                stroke-dasharray="{{ $circumference }}"
                                stroke-dashoffset="{{ $offset }}"></circle>
                        </svg>
                        <div class="ring-text">
                            <div class="ring-pct hero-progress-percent">{{ $progressUser }}%</div>
                            <div class="ring-sub">Selesai</div>
                        </div>
                    </div>
                    <div>
                        <div class="opacity-75 small">Progres Anda</div>
                        <h3 class="fw-bold mb-0 text-white"><span class="hero-progress-telah">{{ $telahDicek->count() }}</span> <small style="font-size:.9rem; opacity:.75;">/ <span class="hero-progress-total">{{ $totalScope }}</span></small></h3>
                        <small class="opacity-75">Aset terdata</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- STAT CHIPS --}}
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="so-stat-chip d-flex align-items-center gap-3">
                <span class="so-stat-icon primary"><i class="fas fa-boxes"></i></span>
                <div>
                    <div class="so-stat-lbl">
                        @if($isAdmin)
                            Total Aset Perusahaan
                        @else
                            Total Aset Anda
                        @endif
                    </div>
                    <div class="so-stat-num so-stat-num-total">{{ $totalScope }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="so-stat-chip d-flex align-items-center gap-3">
                <span class="so-stat-icon danger"><i class="fas fa-search"></i></span>
                <div>
                    <div class="so-stat-lbl">Perlu Dicek</div>
                    <div class="so-stat-num text-danger so-stat-num-belum">{{ $belumDicek->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="so-stat-chip d-flex align-items-center gap-3">
                <span class="so-stat-icon success"><i class="fas fa-check-circle"></i></span>
                <div>
                    <div class="so-stat-lbl">Telah Dicek</div>
                    <div class="so-stat-num text-success so-stat-num-telah">{{ $telahDicek->count() }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- SCANNER CTA --}}
    <div class="scanner-cta d-flex flex-column flex-md-row align-items-md-center gap-3 mb-4">
        <div class="scanner-cta-icon"><i class="fas fa-qrcode"></i></div>
        <div class="flex-grow-1">
            <h6 class="fw-bold mb-1 text-dark">Mulai Pindai Aset</h6>
            <p class="mb-0 small text-muted">Buka scanner untuk memindai QR code aset, atau gunakan "Cek Manual" jika label sulit dipindai.</p>
        </div>
        <a href="{{ route('aset.scanner') }}?mode=opname&session_id={{ $session->id }}" class="scanner-btn d-inline-flex align-items-center justify-content-center gap-2 w-100 w-md-auto">
            <i class="fas fa-camera"></i> Buka Scanner
        </a>
    </div>

    {{-- TABEL DATA --}}
    <div class="panel-card">
        <div class="panel-head">
            <ul class="nav nav-tabs-pills" id="pills-tab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link tab-danger active" id="pills-belum-tab" data-bs-toggle="pill" data-bs-target="#pills-belum" type="button" role="tab">
                        <i class="fas fa-search"></i> Perlu Dicek
                        <span class="count-badge count-badge-belum">{{ $belumDicek->count() }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pills-ditemukan-tab" data-bs-toggle="pill" data-bs-target="#pills-ditemukan" type="button" role="tab">
                        <i class="fas fa-check-circle"></i> Telah Dicek
                        <span class="count-badge count-badge-telah">{{ $telahDicek->count() }}</span>
                    </button>
                </li>
            </ul>
        </div>
        <div class="panel-body">
            <div class="tab-content" id="pills-tabContent">

                {{-- TAB: BELUM DICEK --}}
                <div class="tab-pane fade show active" id="pills-belum" role="tabpanel">
                    @if($belumDicek->isEmpty())
                        <div class="empty-row">
                            <i class="fas fa-check-double text-success d-block mb-2"></i>
                            <h5 class="fw-bold text-dark mt-2 mb-1">Semua Aset Sudah Dicek!</h5>
                            <p class="text-muted small mb-0">Bagus, tidak ada aset yang tertinggal di scope Anda.</p>
                        </div>
                    @else
                        {{-- Custom Filter Toolbar to match Kelola Pages --}}
                        <div class="row g-2 align-items-center mb-3">
                            {{-- Entries --}}
                            <div class="col-6 col-sm-auto mb-2 mb-sm-0">
                                <div class="d-flex align-items-center gap-2">
                                    <label class="form-label fw-bold small text-muted text-uppercase mb-0" style="font-size: 0.7rem; white-space: nowrap;">Entries</label>
                                    <select class="form-select form-select-sm rounded-3 custom-entries-select" data-table="dtBelum" style="width: 75px;">
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                </div>
                            </div>
                            {{-- Filter Departemen (Only for Admin/GA) --}}
                            @if($isAdmin)
                            @php
                                $userResolvedDivisi = auth()->user()->divisi->nm_divisi ?? (auth()->user()->department->divisi->nm_divisi ?? (auth()->user()->section->department->divisi->nm_divisi ?? (auth()->user()->unit->section->department->divisi->nm_divisi ?? '')));
                            @endphp
                            <div class="col-6 col-sm-4 col-md-3 mb-2 mb-sm-0">
                                <select class="form-select form-select-sm rounded-3 custom-dept-filter" data-table="dtBelum">
                                    <option value="">-- Semua Divisi --</option>
                                    @foreach($availableDivisis as $divisiName)
                                        <option value="{{ $divisiName }}" {{ $userResolvedDivisi == $divisiName ? 'selected' : '' }}>{{ $divisiName }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                            {{-- Pencarian --}}
                            <div class="col-12 col-sm-4 col-md-3 ms-sm-auto mb-2 mb-sm-0">
                                <div class="input-group input-group-sm input-group-focus rounded-3" style="border: 1px solid #ced4da; background: #fff;">
                                    <span class="input-group-text bg-white border-0 text-muted"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control border-0 shadow-none bg-transparent custom-search-input" data-table="dtBelum" placeholder="Cari...">
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table data-table mb-0" id="tableBelum">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%" class="text-center d-none d-md-table-cell">No</th>
                                        <th>Aset</th>
                                        <th class="d-none d-md-table-cell">Kategori</th>
                                        <th>Lokasi Terakhir</th>
                                        @if($isAdmin)
                                            <th class="col-dept d-none d-lg-table-cell">Divisi</th>
                                        @endif
                                        <th>Kondisi</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($belumDicek as $aset)
                                        <tr data-dept="{{ $aset->resolved_divisi_name }}">
                                            <td class="text-center text-muted d-none d-md-table-cell">{{ $loop->iteration }}</td>
                                            <td>
                                                <div class="aset-cell">
                                                    <div class="aset-thumb"><i class="fas fa-box"></i></div>
                                                    <div class="aset-info">
                                                        <div class="nomor">{{ $aset->nomor_aset }}</div>
                                                        <div class="nama">{{ $aset->nama_aset }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="d-none d-md-table-cell">
                                                <span class="text-dark small">{{ $aset->kategoriAset->nama ?? '-' }}</span>
                                            </td>
                                            <td>
                                                <i class="fas fa-map-marker-alt text-muted me-1"></i>
                                                <span class="text-dark small">{{ $aset->lokasi->nama_lokasi ?? '-' }}</span>
                                            </td>
                                            @if($isAdmin)
                                                <td class="col-dept d-none d-lg-table-cell"><span class="text-dark small">{{ $aset->resolved_divisi_name }}</span></td>
                                            @endif
                                            <td>
                                                @php
                                                    $kondisi = $aset->status_kondisi;
                                                @endphp
                                                @if($kondisi == 'Baik')
                                                    <span class="badge bg-success rounded-pill px-3">Baik</span>
                                                @elseif($kondisi == 'Rusak')
                                                    <span class="badge bg-danger rounded-pill px-3">Rusak</span>
                                                @elseif($kondisi == 'Bongkar')
                                                    <span class="badge bg-warning text-white rounded-pill px-3">Bongkar</span>
                                                @elseif($kondisi == 'Tidak Terpakai')
                                                    <span class="badge bg-secondary rounded-pill px-3">Tidak Terpakai</span>
                                                @elseif($kondisi == 'Hilang')
                                                    <span class="badge bg-dark rounded-pill px-3">Hilang</span>
                                                @elseif($kondisi == 'Tidak Teridentifikasi')
                                                    <span class="badge bg-dark rounded-pill px-3">Tidak Teridentifikasi</span>
                                                @else
                                                    <span class="badge bg-light text-white border rounded-pill px-3">{{ $kondisi ?? 'Lainnya' }}</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <button type="button" 
                                                    class="btn so-btn-outline so-action-btn btn-cek-manual" 
                                                    data-aset-id="{{ $aset->id }}"
                                                    data-aset-nomor="{{ $aset->nomor_aset }}"
                                                    data-aset-nama="{{ $aset->nama_aset }}"
                                                    title="Input temuan manual untuk aset ini">
                                                    <i class="fas fa-pen-to-square me-1"></i> <span class="d-none d-sm-inline">Cek Manual</span>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                {{-- TAB: DITEMUKAN --}}
                <div class="tab-pane fade" id="pills-ditemukan" role="tabpanel">
                    @if($telahDicek->isEmpty())
                        <div class="empty-row">
                            <i class="fas fa-clipboard-list d-block mb-2"></i>
                            <h6 class="fw-bold text-dark mt-2 mb-1">Belum Ada Temuan</h6>
                            <p class="text-muted small mb-0">Aset yang berhasil discan akan tampil di tab ini.</p>
                        </div>
                    @else
                        {{-- Custom Filter Toolbar to match Kelola Pages --}}
                        <div class="row g-2 align-items-center mb-3">
                            {{-- Entries --}}
                            <div class="col-6 col-sm-auto mb-2 mb-sm-0">
                                <div class="d-flex align-items-center gap-2">
                                    <label class="form-label fw-bold small text-muted text-uppercase mb-0" style="font-size: 0.7rem; white-space: nowrap;">Entries</label>
                                    <select class="form-select form-select-sm rounded-3 custom-entries-select" data-table="dtDitemukan" style="width: 75px;">
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                </div>
                            </div>
                            {{-- Filter Divisi (Only for Admin/GA) --}}
                            @if($isAdmin)
                            @php
                                $userResolvedDivisi = auth()->user()->divisi->nm_divisi ?? (auth()->user()->department->divisi->nm_divisi ?? (auth()->user()->section->department->divisi->nm_divisi ?? (auth()->user()->unit->section->department->divisi->nm_divisi ?? '')));
                            @endphp
                            <div class="col-6 col-sm-4 col-md-3 mb-2 mb-sm-0">
                                <select class="form-select form-select-sm rounded-3 custom-dept-filter" data-table="dtDitemukan">
                                    <option value="">-- Semua Divisi --</option>
                                    @foreach($availableDivisis as $divisiName)
                                        <option value="{{ $divisiName }}" {{ $userResolvedDivisi == $divisiName ? 'selected' : '' }}>{{ $divisiName }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                            {{-- Pencarian --}}
                            <div class="col-12 col-sm-4 col-md-3 ms-sm-auto mb-2 mb-sm-0">
                                <div class="input-group input-group-sm input-group-focus rounded-3" style="border: 1px solid #ced4da; background: #fff;">
                                    <span class="input-group-text bg-white border-0 text-muted"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control border-0 shadow-none bg-transparent custom-search-input" data-table="dtDitemukan" placeholder="Cari...">
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table data-table mb-0" id="tableDitemukan">
                                <thead class="table-light">
                                    <tr>
                                        <th>Aset</th>
                                        <th>Kondisi Temuan</th>
                                        <th>Lokasi Temuan</th>
                                        @if($isAdmin)
                                            <th class="col-dept d-none d-lg-table-cell">Divisi</th>
                                        @endif
                                        <th>Foto</th>
                                        <th class="d-none d-md-table-cell">Dicek Oleh</th>
                                        <th class="d-none d-md-table-cell">Waktu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($telahDicek as $detail)
                                        @php
                                            $kondisiBerubah = $detail->aset && $detail->kondisi_temuan != $detail->aset->status_kondisi;
                                            $kondisiBuruk = in_array($detail->kondisi_temuan, ['Rusak','Hilang','Bongkar','Tidak Teridentifikasi']);
                                            $lokasiTemuanNama = $detail->lokasi_temuan;
                                            if(is_numeric($detail->lokasi_temuan)) {
                                                $lokasiObj = \App\Models\LokasiAset::find($detail->lokasi_temuan);
                                                if($lokasiObj) $lokasiTemuanNama = $lokasiObj->nama_lokasi;
                                            }
                                            $lokasiBerubah = is_numeric($detail->lokasi_temuan) && $detail->aset && $detail->lokasi_temuan != $detail->aset->lokasi_id;
                                        @endphp
                                        <tr data-dept="{{ $detail->aset->resolved_divisi_name ?? '-' }}">
                                            <td>
                                                <div class="aset-cell">
                                                    <div class="aset-thumb" style="background:rgba(40,167,69,.10); color:#28a745;"><i class="fas fa-check"></i></div>
                                                    <div class="aset-info">
                                                        <div class="nomor">{{ $detail->aset->nomor_aset ?? 'N/A' }}</div>
                                                        <div class="nama">{{ $detail->aset->nama_aset ?? '-' }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @php
                                                    $kondisi = $detail->kondisi_temuan;
                                                @endphp
                                                @if($kondisi == 'Baik')
                                                    <span class="badge bg-success rounded-pill px-3">Baik</span>
                                                @elseif($kondisi == 'Rusak')
                                                    <span class="badge bg-danger rounded-pill px-3">
                                                        <i class="fas fa-exclamation-triangle me-1"></i>Rusak
                                                    </span>
                                                @elseif($kondisi == 'Bongkar')
                                                    <span class="badge bg-warning text-white rounded-pill px-3">
                                                        <i class="fas fa-exclamation-triangle me-1"></i>Bongkar
                                                    </span>
                                                @elseif($kondisi == 'Tidak Terpakai')
                                                    <span class="badge bg-secondary rounded-pill px-3">Tidak Terpakai</span>
                                                @elseif($kondisi == 'Hilang')
                                                    <span class="badge bg-dark rounded-pill px-3">
                                                        <i class="fas fa-exclamation-triangle me-1"></i>Hilang
                                                    </span>
                                                @elseif($kondisi == 'Tidak Teridentifikasi')
                                                    <span class="badge bg-dark rounded-pill px-3">
                                                        <i class="fas fa-exclamation-triangle me-1"></i>Tidak Teridentifikasi
                                                    </span>
                                                @else
                                                    <span class="badge bg-light text-white border rounded-pill px-3">
                                                        @if($kondisiBuruk)<i class="fas fa-exclamation-triangle me-1"></i>@endif{{ $kondisi ?? 'Lainnya' }}
                                                    </span>
                                                @endif
                                                @if($kondisiBerubah && !$kondisiBuruk)
                                                    <small class="d-block text-muted mt-1">Sebelumnya: {{ $detail->aset->status_kondisi }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($lokasiBerubah)
                                                    <span class="text-warning fw-bold small">
                                                        <i class="fas fa-map-marker-alt"></i> {{ $lokasiTemuanNama }}
                                                    </span>
                                                    <small class="d-block text-muted">Beda dari sistem</small>
                                                @else
                                                    <span class="text-dark small">
                                                        <i class="fas fa-map-marker-alt text-muted"></i> {{ $lokasiTemuanNama }}
                                                    </span>
                                                @endif
                                            </td>
                                            @if($isAdmin)
                                                <td class="col-dept d-none d-lg-table-cell"><span class="text-dark small">{{ $detail->aset->resolved_divisi_name ?? '-' }}</span></td>
                                            @endif
                                            <td>
                                                @if($detail->foto_temuan)
                                                    <img src="{{ Storage::url($detail->foto_temuan) }}" class="img-temuan" onclick="window.open(this.src)">
                                                @else
                                                    <span class="text-muted small"><i class="fas fa-image"></i> -</span>
                                                @endif
                                            </td>
                                            <td class="d-none d-md-table-cell">
                                                <small class="fw-semibold text-dark">{{ $detail->dicekOleh->firstname ?? '-' }} {{ $detail->dicekOleh->lastname ?? '' }}</small>
                                            </td>
                                            <td class="d-none d-md-table-cell">
                                                <small class="text-muted">
                                                    <i class="far fa-clock me-1"></i>
                                                    {{ \Carbon\Carbon::parse($detail->created_at)->format('d M, H:i') }}
                                                </small>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
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
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <div class="alert alert-info border-0 shadow-sm rounded-3 mb-3">
                    <small>Memproses Aset: <strong id="scanned_aset_display"></strong></small>
                </div>
                
                <form id="stockOpnameForm">
                    @csrf
                    <input type="hidden" id="so_session_id" name="stock_opname_id" value="{{ $session->id }}">
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

                </div>
                <div class="modal-footer bg-white border-top-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold shadow-sm border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn text-white rounded-pill px-4 fw-bold shadow-sm" id="btnSubmitOpname" style="background-color: #253070 !important; border-color: #253070 !important;">
                        <i class="fas fa-save me-2"></i> Simpan Temuan
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
        var dtBelum = $('#tableBelum').DataTable({
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

        var dtDitemukan = $('#tableDitemukan').DataTable({
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

        const tables = {
            dtBelum: dtBelum,
            dtDitemukan: dtDitemukan
        };

        // Wire custom Entries change
        $('.custom-entries-select').on('change', function() {
            const tableName = $(this).data('table');
            const dtInstance = tables[tableName];
            if (dtInstance) {
                dtInstance.page.len(parseInt($(this).val())).draw();
            }
        });

        $('.custom-search-input').on('keyup', function() {
            const tableName = $(this).data('table');
            const dtInstance = tables[tableName];
            if (dtInstance) {
                dtInstance.search($(this).val()).draw();
            }
        });

        function updateStatsForDept(selectedDept) {
            let countBelum = 0;
            let countTelah = 0;

            const trBelum = tables.dtBelum ? $(tables.dtBelum.rows().nodes()) : $();
            const trTelah = tables.dtDitemukan ? $(tables.dtDitemukan.rows().nodes()) : $();

            if (selectedDept === '') {
                countBelum = trBelum.filter('[data-dept]').length;
                countTelah = trTelah.filter('[data-dept]').length;
            } else {
                countBelum = trBelum.filter(`[data-dept="${selectedDept}"]`).length;
                countTelah = trTelah.filter(`[data-dept="${selectedDept}"]`).length;
            }

            const total = countBelum + countTelah;
            const progressPercent = total > 0 ? Math.round((countTelah / total) * 100) : 0;

            // 1. Update the Stat Cards below the banner
            $('.so-stat-num-total').text(total);
            $('.so-stat-num-belum').text(countBelum);
            $('.so-stat-num-telah').text(countTelah);

            // 2. Update the Tab Badges
            $('.count-badge-belum').text(countBelum);
            $('.count-badge-telah').text(countTelah);

            // 3. Update the Hero Progress Circle & Text
            $('.ring-pct.hero-progress-percent').text(progressPercent + '%');
            $('.hero-progress-telah').text(countTelah);
            $('.hero-progress-total').text(total);
            
            // Adjust SVG circle dashoffset
            const circumference = 2 * 3.14159 * 50; // r=50
            const offset = circumference - (progressPercent / 100 * circumference);
            $('.ring-bar.hero-progress-circle').css('stroke-dashoffset', offset);
        }

        $('.custom-dept-filter').on('change', function() {
            const val = $(this).val();
            
            // Sync all department select filters
            $('.custom-dept-filter').val(val);

            // Filter both tables
            if (tables.dtBelum) {
                if (val === '') {
                    tables.dtBelum.column('.col-dept').search('').draw();
                } else {
                    tables.dtBelum.column('.col-dept').search('^' + $.fn.dataTable.util.escapeRegex(val) + '$', true, false).draw();
                }
            }
            if (tables.dtDitemukan) {
                if (val === '') {
                    tables.dtDitemukan.column('.col-dept').search('').draw();
                } else {
                    tables.dtDitemukan.column('.col-dept').search('^' + $.fn.dataTable.util.escapeRegex(val) + '$', true, false).draw();
                }
            }

            // Dynamically update stats based on selected department
            updateStatsForDept(val);
        });

        // Trigger initial department filter for Admin/GA
        const initialDept = $('.custom-dept-filter').val();
        if (initialDept !== undefined) {
            $('.custom-dept-filter').first().trigger('change');
        }

        // Recalculate column widths when switching tabs
        $('button[data-bs-toggle="pill"]').on('shown.bs.tab', function () {
            $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
        });

        // Function to toggle inputs when kondisi is 'Tidak Teridentifikasi'
        function handleKondisiChange() {
            const kondisi = $('#so_kondisi').val();
            const isUnidentified = kondisi === 'Tidak Teridentifikasi' || kondisi === 'Hilang';
            
            const soLokasi = $('#so_lokasi');
            const soFoto = $('#so_foto');
            
            if (isUnidentified) {
                // Disable and remove required
                soLokasi.prop('disabled', true).prop('required', false).val('');
                soFoto.prop('disabled', true).prop('required', false).val('');
                
                // Hide red asterisk *
                soLokasi.closest('.mb-3').find('.text-danger').addClass('d-none');
                soFoto.closest('.mb-3').find('.text-danger').addClass('d-none');
            } else {
                // Enable and add required
                soLokasi.prop('disabled', false).prop('required', true);
                soFoto.prop('disabled', false).prop('required', true);
                
                // Show red asterisk *
                soLokasi.closest('.mb-3').find('.text-danger').removeClass('d-none');
                soFoto.closest('.mb-3').find('.text-danger').removeClass('d-none');
            }
        }
        
        // Listen to change
        $('#so_kondisi').on('change', handleKondisiChange);

        // Handle Cek Manual Button click
        $(document).on('click', '.btn-cek-manual', function() {
            const asetId = $(this).data('aset-id');
            const asetNomor = $(this).data('aset-nomor');
            const asetNama = $(this).data('aset-nama');
            
            // Set Modal display
            $('#scanned_aset_display').text(`${asetNomor} - ${asetNama}`);
            $('#so_aset_id').val(asetId);
            
            // Reset and show modal
            $('#stockOpnameForm')[0].reset();
            handleKondisiChange();
            var modal = new bootstrap.Modal(document.getElementById('stockOpnameModal'));
            modal.show();
        });

        // Submit Form Stock Opname via AJAX
        $('#stockOpnameForm').on('submit', function(e) {
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
                    // Close bootstrap modal immediately
                    $('#stockOpnameModal').modal('hide');
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: result.body.message,
                        showConfirmButton: true,
                        confirmButtonColor: '#253070'
                    }).then(() => {
                        // Reload page to reflect changes
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: result.body.message || 'Terjadi kesalahan',
                        confirmButtonColor: '#253070'
                    });
                }
            })
            .catch(error => {
                console.error(error);
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = originalText;
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Terjadi kesalahan sistem.',
                    confirmButtonColor: '#253070'
                });
            });
        });
    });
</script>
@endpush
