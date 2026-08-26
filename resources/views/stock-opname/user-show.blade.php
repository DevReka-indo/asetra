@extends('layouts.app')

@section('title', 'Daftar Aset Opname')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/stock-opname.css') }}">
@endpush

@section('content')
    <div class="container-fluid px-1 py-0 mt-0 page-stock-opname-user-show">

        {{-- BREADCRUMB --}}
        {{-- HEADER --}}
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
                    <a href="{{ route('stock-opname.user-index') }}"
                        class="text-muted text-decoration-none d-flex align-items-center">
                        <span style="font-size: 14px; font-weight: 500; position: relative; top: 2px;"> Pelaksanaan Stock
                            Opname</span>
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

        @if (session('success'))
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
            $offset = $circumference - ($progressUser / 100) * $circumference;
        @endphp

        <div class="so-detail-hero">
            <div class="so-detail-hero-content row align-items-center g-3">
                <div class="col-md-7">
                    <h4 class="fw-bold mt-2 mb-1">
                        @if ($isAdmin)
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
                            <svg width="120" height="120">
                                <circle cx="60" cy="60" r="50" class="ring-track"></circle>
                                <circle cx="60" cy="60" r="50" class="ring-bar hero-progress-circle"
                                    stroke-dasharray="{{ $circumference }}" stroke-dashoffset="{{ $offset }}">
                                </circle>
                            </svg>
                            <div class="ring-text">
                                <div class="ring-pct hero-progress-percent">{{ $progressUser }}%</div>
                                <div class="ring-sub">Selesai</div>
                            </div>
                        </div>
                        <div>
                            <div class="opacity-75 small">Progres Anda</div>
                            <h3 class="fw-bold mb-0 text-white"><span
                                    class="hero-progress-telah">{{ $telahDicek->count() }}</span> <small
                                    style="font-size:.9rem; opacity:.75;">/ <span
                                        class="hero-progress-total">{{ $totalScope }}</span></small></h3>
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
                            @if ($isAdmin)
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
                <p class="mb-0 small text-muted">Buka scanner untuk memindai QR code aset, atau gunakan "Cek Manual" jika
                    label sulit dipindai.</p>
            </div>
            <a href="{{ route('aset.scanner') }}?mode=opname&session_id={{ $session->id }}"
                class="scanner-btn d-none d-md-inline-flex align-items-center gap-2">
                <i class="fas fa-camera"></i> Buka Scanner
            </a>
        </div>

        {{-- TABEL DATA --}}
        <div class="panel-card">
            <div class="panel-head">
                <ul class="nav nav-tabs-pills" id="pills-tab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link tab-danger active" id="pills-belum-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-belum" type="button" role="tab">
                            <i class="fas fa-search"></i> Perlu Dicek
                            <span class="count-badge count-badge-belum">{{ $belumDicek->count() }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-ditemukan-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-ditemukan" type="button" role="tab">
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
                        @if ($belumDicek->isEmpty())
                            <div class="empty-row">
                                <i class="fas fa-check-double text-success d-block mb-2"></i>
                                <h5 class="fw-bold text-dark mt-2 mb-1">Semua Aset Sudah Dicek!</h5>
                                <p class="text-muted small mb-0">Bagus, tidak ada aset yang tertinggal di scope Anda.</p>
                            </div>
                        @else
                            {{-- Custom Filter Toolbar to match Kelola Pages --}}
                            <div class="row g-2 align-items-end mb-3">
                                {{-- Entries --}}
                                <div style="width: 100px;">
                                    <label class="form-label fw-bold small text-muted text-uppercase"
                                        style="font-size: 0.7rem; margin-bottom: 0.25rem; display: block;">Entries</label>
                                    <select class="form-select form-select-sm rounded-3 custom-entries-select"
                                        data-table="dtBelum">
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                </div>
                                {{-- Filter Divisi (Only for Admin/GA) --}}
                                @if ($isAdmin)
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold small text-muted text-uppercase"
                                            style="font-size: 0.7rem; margin-bottom: 0.25rem; display: block;">Divisi</label>
                                        <select class="form-select form-select-sm rounded-3 custom-divisi-filter"
                                            data-table="dtBelum">
                                            <option value="">-- Semua Divisi --</option>
                                            @foreach ($availableDivisis as $divisiName)
                                                <option value="{{ $divisiName }}">{{ $divisiName }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    {{-- <div class="col-md-3">
                                        <label class="form-label fw-bold small text-muted text-uppercase"
                                            style="font-size: 0.7rem; margin-bottom: 0.25rem; display: block;">Departemen</label>
                                        <select class="form-select form-select-sm rounded-3 custom-dept-filter"
                                            data-table="dtBelum">
                                            <option value="">-- Semua Departemen --</option>
                                            @foreach ($availableDepts as $deptName)
                                                <option value="{{ $deptName }}">{{ $deptName }}</option>
                                            @endforeach
                                        </select>
                                    </div> --}}
                                @endif
                                {{-- Pencarian --}}
                                <div class="col-md-3 ms-auto">
                                    <label class="form-label fw-bold small text-muted text-uppercase"
                                        style="font-size: 0.7rem; margin-bottom: 0.25rem; display: block;">Pencarian</label>
                                    <div class="input-group input-group-sm input-group-focus rounded-3"
                                        style="border: 1px solid #ced4da; background: #fff;">
                                        <span class="input-group-text bg-white border-0 text-muted"><i
                                                class="fas fa-search"></i></span>
                                        <input type="text"
                                            class="form-control border-0 shadow-none bg-transparent custom-search-input"
                                            data-table="dtBelum" placeholder="Cari nomor atau nama...">
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table data-table mb-0" id="tableBelum">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="5%" class="text-center">No</th>
                                            <th>Aset</th>
                                            <th>Kategori</th>
                                            <th>Lokasi Terakhir</th>
                                            @if ($isAdmin)
                                                <th class="col-divisi">Divisi</th>
                                                <th class="col-dept">Departemen/Unit</th>
                                            @endif
                                            <th>Kondisi</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($belumDicek as $aset)
                                            <tr data-divisi="{{ $aset->resolved_divisi_name }}"
                                                data-dept="{{ $aset->resolved_department_name }}">
                                                <td class="text-center text-muted">{{ $loop->iteration }}</td>
                                                <td>
                                                    <div class="aset-cell">
                                                        <div class="aset-thumb"><i class="fas fa-box"></i></div>
                                                        <div class="aset-info">
                                                            <div class="nomor">{{ $aset->nomor_aset }}</div>
                                                            <div class="nama">{{ $aset->nama_aset }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span
                                                        class="text-dark small">{{ $aset->kategoriAset->nama ?? '-' }}</span>
                                                </td>
                                                <td>
                                                    <i class="fas fa-map-marker-alt text-muted me-1"></i>
                                                    <span
                                                        class="text-dark small">{{ $aset->lokasi->nama_lokasi ?? '-' }}</span>
                                                </td>
                                                @if ($isAdmin)
                                                    <td class="col-divisi"><span
                                                            class="text-dark small">{{ $aset->resolved_divisi_name }}</span>
                                                    </td>
                                                    <td class="col-dept"><span
                                                            class="text-dark small">{{ $aset->resolved_department_name }}</span>
                                                    </td>
                                                @endif
                                                <td>
                                                    @php
                                                        $kondisi = $aset->status_kondisi;
                                                    @endphp
                                                    @if ($kondisi == 'Baik')
                                                        <span class="badge bg-success rounded-pill px-3">Baik</span>
                                                    @elseif($kondisi == 'Rusak')
                                                        <span class="badge bg-danger rounded-pill px-3">Rusak</span>
                                                    @elseif($kondisi == 'Bongkar')
                                                        <span
                                                            class="badge bg-warning text-white rounded-pill px-3">Bongkar</span>
                                                    @elseif($kondisi == 'Tidak Terpakai')
                                                        <span class="badge bg-secondary rounded-pill px-3">Tidak
                                                            Terpakai</span>
                                                    @elseif($kondisi == 'Hilang')
                                                        <span class="badge bg-dark rounded-pill px-3">Hilang</span>
                                                    @elseif($kondisi == 'Tidak Teridentifikasi')
                                                        <span class="badge bg-dark rounded-pill px-3">Tidak
                                                            Teridentifikasi</span>
                                                    @else
                                                        <span
                                                            class="badge bg-light text-white border rounded-pill px-3">{{ $kondisi ?? 'Lainnya' }}</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <button type="button"
                                                        class="btn so-btn-outline so-action-btn btn-cek-manual"
                                                        data-aset-id="{{ $aset->id }}"
                                                        data-aset-nomor="{{ $aset->nomor_aset }}"
                                                        data-aset-nama="{{ $aset->nama_aset }}"
                                                        title="Input temuan manual untuk aset ini">
                                                        <i class="fas fa-pen-to-square me-1"></i> Cek Manual
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
                        @if ($telahDicek->isEmpty())
                            <div class="empty-row">
                                <i class="fas fa-clipboard-list d-block mb-2"></i>
                                <h6 class="fw-bold text-dark mt-2 mb-1">Belum Ada Temuan</h6>
                                <p class="text-muted small mb-0">Aset yang berhasil discan akan tampil di tab ini.</p>
                            </div>
                        @else
                            {{-- Custom Filter Toolbar to match Kelola Pages --}}
                            <div class="row g-2 align-items-end mb-3">
                                {{-- Entries --}}
                                <div style="width: 100px;">
                                    <label class="form-label fw-bold small text-muted text-uppercase"
                                        style="font-size: 0.7rem; margin-bottom: 0.25rem; display: block;">Entries</label>
                                    <select class="form-select form-select-sm rounded-3 custom-entries-select"
                                        data-table="dtDitemukan">
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                </div>
                                {{-- Filter Divisi (Only for Admin/GA) --}}
                                @if ($isAdmin)
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold small text-muted text-uppercase"
                                            style="font-size: 0.7rem; margin-bottom: 0.25rem; display: block;">Divisi</label>
                                        <select class="form-select form-select-sm rounded-3 custom-divisi-filter"
                                            data-table="dtDitemukan">
                                            <option value="">-- Semua Divisi --</option>
                                            @foreach ($availableDivisis as $divisiName)
                                                <option value="{{ $divisiName }}">{{ $divisiName }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold small text-muted text-uppercase"
                                            style="font-size: 0.7rem; margin-bottom: 0.25rem; display: block;">Departemen</label>
                                        <select class="form-select form-select-sm rounded-3 custom-dept-filter"
                                            data-table="dtDitemukan">
                                            <option value="">-- Semua Departemen --</option>
                                            @foreach ($availableDepts as $deptName)
                                                <option value="{{ $deptName }}">{{ $deptName }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                                {{-- Pencarian --}}
                                <div class="col-md-3 ms-auto">
                                    <label class="form-label fw-bold small text-muted text-uppercase"
                                        style="font-size: 0.7rem; margin-bottom: 0.25rem; display: block;">Pencarian</label>
                                    <div class="input-group input-group-sm input-group-focus rounded-3"
                                        style="border: 1px solid #ced4da; background: #fff;">
                                        <span class="input-group-text bg-white border-0 text-muted"><i
                                                class="fas fa-search"></i></span>
                                        <input type="text"
                                            class="form-control border-0 shadow-none bg-transparent custom-search-input"
                                            data-table="dtDitemukan" placeholder="Cari nomor atau nama...">
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
                                            @if ($isAdmin)
                                                <th class="col-divisi">Divisi</th>
                                                <th class="col-dept">Departemen/Unit</th>
                                            @endif
                                            <th>Foto</th>
                                            <th>Dicek Oleh</th>
                                            <th>Waktu</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($telahDicek as $detail)
                                            @php
                                                $kondisiBerubah =
                                                    $detail->aset &&
                                                    $detail->kondisi_temuan != $detail->aset->status_kondisi;
                                                $kondisiBuruk = in_array($detail->kondisi_temuan, [
                                                    'Rusak',
                                                    'Hilang',
                                                    'Bongkar',
                                                    'Tidak Teridentifikasi',
                                                ]);
                                                $lokasiTemuanNama = $detail->lokasi_temuan;
                                                if (is_numeric($detail->lokasi_temuan)) {
                                                    $lokasiObj = \App\Models\LokasiAset::find($detail->lokasi_temuan);
                                                    if ($lokasiObj) {
                                                        $lokasiTemuanNama = $lokasiObj->nama_lokasi;
                                                    }
                                                }
                                                $lokasiBerubah =
                                                    is_numeric($detail->lokasi_temuan) &&
                                                    $detail->aset &&
                                                    $detail->lokasi_temuan != $detail->aset->lokasi_id;
                                            @endphp
                                            <tr data-divisi="{{ $detail->aset->resolved_divisi_name ?? '-' }}"
                                                data-dept="{{ $detail->aset->resolved_department_name ?? '-' }}">
                                                <td>
                                                    <div class="aset-cell">
                                                        <div class="aset-thumb"
                                                            style="background:rgba(40,167,69,.10); color:#28a745;"><i
                                                                class="fas fa-check"></i></div>
                                                        <div class="aset-info">
                                                            <div class="nomor">{{ $detail->aset->nomor_aset ?? 'N/A' }}
                                                            </div>
                                                            <div class="nama">{{ $detail->aset->nama_aset ?? '-' }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    @php
                                                        $kondisi = $detail->kondisi_temuan;
                                                    @endphp
                                                    @if ($kondisi == 'Baik')
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
                                                        <span class="badge bg-secondary rounded-pill px-3">Tidak
                                                            Terpakai</span>
                                                    @elseif($kondisi == 'Hilang')
                                                        <span class="badge bg-dark rounded-pill px-3">
                                                            <i class="fas fa-exclamation-triangle me-1"></i>Hilang
                                                        </span>
                                                    @elseif($kondisi == 'Tidak Teridentifikasi')
                                                        <span class="badge bg-dark rounded-pill px-3">
                                                            <i class="fas fa-exclamation-triangle me-1"></i>Tidak
                                                            Teridentifikasi
                                                        </span>
                                                    @else
                                                        <span class="badge bg-light text-white border rounded-pill px-3">
                                                            @if ($kondisiBuruk)
                                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                            @endif{{ $kondisi ?? 'Lainnya' }}
                                                        </span>
                                                    @endif
                                                    @if ($kondisiBerubah && !$kondisiBuruk)
                                                        <small class="d-block text-muted mt-1">Sebelumnya:
                                                            {{ $detail->aset->status_kondisi }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($lokasiBerubah)
                                                        <span class="text-warning fw-bold small">
                                                            <i class="fas fa-map-marker-alt"></i> {{ $lokasiTemuanNama }}
                                                        </span>
                                                        <small class="d-block text-muted">Beda dari sistem</small>
                                                    @else
                                                        <span class="text-dark small">
                                                            <i class="fas fa-map-marker-alt text-muted"></i>
                                                            {{ $lokasiTemuanNama }}
                                                        </span>
                                                    @endif
                                                </td>
                                                @if ($isAdmin)
                                                    <td class="col-divisi"><span
                                                            class="text-dark small">{{ $detail->aset->resolved_divisi_name ?? '-' }}</span>
                                                    </td>
                                                    <td class="col-dept"><span
                                                            class="text-dark small">{{ $detail->aset->resolved_department_name ?? '-' }}</span>
                                                    </td>
                                                @endif
                                                <td>
                                                    @if ($detail->foto_temuan)
                                                        <img src="{{ Storage::url($detail->foto_temuan) }}"
                                                            class="img-temuan" onclick="window.open(this.src)">
                                                    @else
                                                        <span class="text-muted small"><i class="fas fa-image"></i>
                                                            -</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <small
                                                        class="fw-semibold text-dark">{{ $detail->dicekOleh->firstname ?? '-' }}
                                                        {{ $detail->dicekOleh->lastname ?? '' }}</small>
                                                </td>
                                                <td>
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

    {{-- Floating Button untuk Mobile --}}
    <a href="{{ route('aset.scanner') }}?mode=opname&session_id={{ $session->id }}"
        class="floating-scanner-btn d-md-none" title="Buka Scanner">
        <i class="fas fa-qrcode"></i>
    </a>

    <!-- Modal Stock Opname Input -->
    <div class="modal fade" id="stockOpnameModal" tabindex="-1" aria-labelledby="stockOpnameModalLabel"
        aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header border-0 pt-4 px-4 pb-3" style="background-color: #253070;">
                    <h5 class="modal-title fw-bold text-white" id="stockOpnameModalLabel">
                        <i class="fas fa-clipboard-check me-2"></i> Form Temuan Stock Opname
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
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
                            <label class="form-label fw-bold small" style="color: #253070;">Kondisi Fisik Saat Ini <span
                                    class="text-danger">*</span></label>
                            <select name="kondisi_temuan" id="so_kondisi" class="form-select shadow-sm rounded-3"
                                required>
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
                            <label class="form-label fw-bold small" style="color: #253070;">Lokasi Fisik Saat Ini <span
                                    class="text-danger">*</span></label>
                            <select name="lokasi_temuan" id="so_lokasi" class="form-select shadow-sm rounded-3" required>
                                <option value="">-- Pilih Lokasi --</option>
                                @foreach ($lokasis as $lokasi)
                                    <option value="{{ $lokasi->lokasi_id }}">{{ $lokasi->nama_lokasi }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small" style="color: #253070;">Foto Bukti Fisik <span
                                    class="text-danger">*</span></label>
                            <input type="file" name="foto_temuan" id="so_foto"
                                class="form-control shadow-sm rounded-3" accept="image/*" capture="environment" required>
                            <small class="text-muted mt-1 d-block">Langsung dari kamera atau pilih file.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small" style="color: #253070;">Keterangan (Opsional)</label>
                            <textarea name="keterangan" id="so_keterangan" class="form-control shadow-sm rounded-3" rows="2"
                                placeholder="Tambahkan catatan jika perlu..."></textarea>
                        </div>

                </div>
                <div class="modal-footer bg-white border-top-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold shadow-sm border"
                        data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn text-white rounded-pill px-4 fw-bold shadow-sm"
                        id="btnSubmitOpname" style="background-color: #253070;">
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

            /*
            |--------------------------------------------------------------------------
            | DATATABLE: BELUM DICEK
            |--------------------------------------------------------------------------
            */
            let dtBelum = null;

            if ($('#tableBelum').length) {
                dtBelum = $('#tableBelum').DataTable({
                    pageLength: 10,
                    order: [],
                    dom: 'rtip',

                    language: {
                        info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ entri',
                        infoEmpty: '',
                        zeroRecords: 'Tidak ada data ditemukan',
                        paginate: {
                            first: 'Awal',
                            last: 'Akhir',
                            next: 'Lanjut',
                            previous: 'Kembali'
                        }
                    }
                });
            }

            /*
            |--------------------------------------------------------------------------
            | DATATABLE: TELAH DICEK
            |--------------------------------------------------------------------------
            */
            let dtDitemukan = null;

            if ($('#tableDitemukan').length) {
                dtDitemukan = $('#tableDitemukan').DataTable({
                    pageLength: 10,
                    order: [],
                    dom: 'rtip',

                    language: {
                        info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ entri',
                        infoEmpty: '',
                        zeroRecords: 'Tidak ada data ditemukan',
                        paginate: {
                            first: 'Awal',
                            last: 'Akhir',
                            next: 'Lanjut',
                            previous: 'Kembali'
                        }
                    }
                });
            }

            /*
            |--------------------------------------------------------------------------
            | DATATABLE REFERENCES
            |--------------------------------------------------------------------------
            */
            const tables = {
                dtBelum: dtBelum,
                dtDitemukan: dtDitemukan
            };

            /*
            |--------------------------------------------------------------------------
            | TABLE CONFIG
            |--------------------------------------------------------------------------
            |
            | Jangan hardcode index Divisi / Departemen karena posisi kolom
            | antara tableBelum dan tableDitemukan berbeda.
            |
            */
            const stockOpnameTables = {
                dtBelum: {
                    instance: dtBelum,
                    selector: '#tableBelum'
                },

                dtDitemukan: {
                    instance: dtDitemukan,
                    selector: '#tableDitemukan'
                }
            };

            /*
            |--------------------------------------------------------------------------
            | GET COLUMN INDEX
            |--------------------------------------------------------------------------
            */
            function getColumnIndex(tableSelector, columnClass) {
                const table = $(tableSelector);

                if (!table.length) {
                    return -1;
                }

                return table
                    .find('thead th.' + columnClass)
                    .index();
            }

            /*
            |--------------------------------------------------------------------------
            | CUSTOM ENTRIES
            |--------------------------------------------------------------------------
            */
            $('.custom-entries-select').on('change', function() {
                const tableName = $(this).data('table');
                const dtInstance = tables[tableName];

                if (!dtInstance) {
                    return;
                }

                const length = parseInt($(this).val(), 10);

                dtInstance
                    .page
                    .len(length)
                    .draw();
            });

            /*
            |--------------------------------------------------------------------------
            | CUSTOM SEARCH
            |--------------------------------------------------------------------------
            */
            $('.custom-search-input').on('input', function() {
                const tableName = $(this).data('table');
                const dtInstance = tables[tableName];

                if (!dtInstance) {
                    return;
                }

                const keyword = $(this).val();

                dtInstance
                    .search(keyword)
                    .draw();
            });

            /*
            |--------------------------------------------------------------------------
            | UPDATE FILTERED STATISTICS
            |--------------------------------------------------------------------------
            |
            | Statistik dihitung langsung berdasarkan hasil filter DataTables.
            |
            */
            function updateFilteredStats() {
                let countBelum = 0;
                let countTelah = 0;

                if (dtBelum) {
                    countBelum = dtBelum
                        .rows({
                            search: 'applied'
                        })
                        .count();
                }

                if (dtDitemukan) {
                    countTelah = dtDitemukan
                        .rows({
                            search: 'applied'
                        })
                        .count();
                }

                const total = countBelum + countTelah;

                const progressPercent = total > 0
                    ? Math.round((countTelah / total) * 100)
                    : 0;

                /*
                |--------------------------------------------------------------------------
                | STAT CARDS
                |--------------------------------------------------------------------------
                */
                $('.so-stat-num-total').text(total);
                $('.so-stat-num-belum').text(countBelum);
                $('.so-stat-num-telah').text(countTelah);

                /*
                |--------------------------------------------------------------------------
                | TAB BADGES
                |--------------------------------------------------------------------------
                */
                $('.count-badge-belum').text(countBelum);
                $('.count-badge-telah').text(countTelah);

                /*
                |--------------------------------------------------------------------------
                | HERO PROGRESS
                |--------------------------------------------------------------------------
                */
                $('.hero-progress-percent')
                    .text(progressPercent + '%');

                $('.hero-progress-telah')
                    .text(countTelah);

                $('.hero-progress-total')
                    .text(total);

                /*
                |--------------------------------------------------------------------------
                | SVG PROGRESS
                |--------------------------------------------------------------------------
                */
                const circumference = 2 * Math.PI * 50;

                const offset =
                    circumference -
                    (progressPercent / 100 * circumference);

                $('.hero-progress-circle')
                    .css(
                        'stroke-dashoffset',
                        offset
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | DATATABLE DRAW EVENT
            |--------------------------------------------------------------------------
            |
            | Setiap search, filter, pagination, entries, dll melakukan redraw,
            | statistik otomatis dihitung ulang.
            |
            */
            if (dtBelum) {
                $('#tableBelum').on('draw.dt', function() {
                    updateFilteredStats();
                });
            }

            if (dtDitemukan) {
                $('#tableDitemukan').on('draw.dt', function() {
                    updateFilteredStats();
                });
            }

            /*
            |--------------------------------------------------------------------------
            | APPLY ORGANIZATION FILTER
            |--------------------------------------------------------------------------
            |
            | Filter Divisi dan Departemen menggunakan native DataTables.
            |
            | Index kolom dicari berdasarkan:
            |
            | th.col-divisi
            | th.col-dept
            |
            | sehingga tidak bermasalah walaupun posisi kolom berbeda.
            |
            */
            function applyOrganizationFilters() {
                const selectedDivisi =
                    $('.custom-divisi-filter')
                        .first()
                        .val() || '';

                const selectedDept =
                    $('.custom-dept-filter')
                        .first()
                        .val() || '';

                Object.values(stockOpnameTables)
                    .forEach(function(config) {

                        const dtInstance = config.instance;

                        if (!dtInstance) {
                            return;
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | FIND COLUMN INDEX
                        |--------------------------------------------------------------------------
                        */
                        const divisiColumnIndex =
                            getColumnIndex(
                                config.selector,
                                'col-divisi'
                            );

                        const deptColumnIndex =
                            getColumnIndex(
                                config.selector,
                                'col-dept'
                            );

                        /*
                        |--------------------------------------------------------------------------
                        | DIVISI
                        |--------------------------------------------------------------------------
                        */
                        if (divisiColumnIndex >= 0) {
                            if (selectedDivisi === '') {
                                dtInstance
                                    .column(divisiColumnIndex)
                                    .search('');
                            } else {
                                /*
                                 * Regex dimatikan.
                                 *
                                 * Kita gunakan contains search supaya lebih tahan
                                 * terhadap whitespace / HTML dari <span>.
                                 */
                                dtInstance
                                    .column(divisiColumnIndex)
                                    .search(
                                        selectedDivisi,
                                        false,
                                        false
                                    );
                            }
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | DEPARTEMEN
                        |--------------------------------------------------------------------------
                        */
                        if (deptColumnIndex >= 0) {
                            if (selectedDept === '') {
                                dtInstance
                                    .column(deptColumnIndex)
                                    .search('');
                            } else {
                                /*
                                 * Gunakan native column search.
                                 *
                                 * Tidak memakai data-dept lagi supaya nilai yang
                                 * difilter benar-benar berasal dari kolom yang
                                 * tampil di DataTables.
                                 */
                                dtInstance
                                    .column(deptColumnIndex)
                                    .search(
                                        selectedDept,
                                        false,
                                        false
                                    );
                            }
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | DRAW
                        |--------------------------------------------------------------------------
                        */
                        dtInstance.draw();
                    });

                updateFilteredStats();
            }

            /*
            |--------------------------------------------------------------------------
            | FILTER DIVISI
            |--------------------------------------------------------------------------
            */
            $('.custom-divisi-filter').on('change', function() {
                const value = $(this).val() || '';

                /*
                |--------------------------------------------------------------------------
                | SYNC DIVISI ANTAR TAB
                |--------------------------------------------------------------------------
                */
                $('.custom-divisi-filter')
                    .val(value);

                /*
                |--------------------------------------------------------------------------
                | RESET HALAMAN KE PAGE 1
                |--------------------------------------------------------------------------
                */
                if (dtBelum) {
                    dtBelum.page('first');
                }

                if (dtDitemukan) {
                    dtDitemukan.page('first');
                }

                applyOrganizationFilters();
            });

            /*
            |--------------------------------------------------------------------------
            | FILTER DEPARTEMEN
            |--------------------------------------------------------------------------
            */
            $('.custom-dept-filter').on('change', function() {
                const value = $(this).val() || '';

                /*
                |--------------------------------------------------------------------------
                | SYNC DEPARTEMEN ANTAR TAB
                |--------------------------------------------------------------------------
                */
                $('.custom-dept-filter')
                    .val(value);

                /*
                |--------------------------------------------------------------------------
                | RESET HALAMAN KE PAGE 1
                |--------------------------------------------------------------------------
                */
                if (dtBelum) {
                    dtBelum.page('first');
                }

                if (dtDitemukan) {
                    dtDitemukan.page('first');
                }

                applyOrganizationFilters();
            });

            /*
            |--------------------------------------------------------------------------
            | INITIAL STATISTICS
            |--------------------------------------------------------------------------
            */
            updateFilteredStats();

            /*
            |--------------------------------------------------------------------------
            | ADJUST DATATABLE WHEN TAB CHANGED
            |--------------------------------------------------------------------------
            */
            $('button[data-bs-toggle="pill"]').on(
                'shown.bs.tab',
                function() {
                    $.fn.dataTable
                        .tables({
                            visible: true,
                            api: true
                        })
                        .columns
                        .adjust();
                }
            );

            /*
            |--------------------------------------------------------------------------
            | HANDLE KONDISI
            |--------------------------------------------------------------------------
            */
            function handleKondisiChange() {
                const kondisi = $('#so_kondisi').val();
                const isLost = kondisi === 'Hilang';

                const soLokasi = $('#so_lokasi');
                const soFoto = $('#so_foto');

                /*
                |--------------------------------------------------------------------------
                | HILANG
                |--------------------------------------------------------------------------
                */
                if (isLost) {
                    soLokasi
                        .prop('disabled', true)
                        .prop('required', false)
                        .val('');

                    soFoto
                        .prop('disabled', true)
                        .prop('required', false)
                        .val('');

                    /*
                    |--------------------------------------------------------------------------
                    | HIDE REQUIRED MARK
                    |--------------------------------------------------------------------------
                    */
                    soLokasi
                        .closest('.mb-3')
                        .find('.text-danger')
                        .addClass('d-none');

                    soFoto
                        .closest('.mb-3')
                        .find('.text-danger')
                        .addClass('d-none');

                    return;
                }

                /*
                |--------------------------------------------------------------------------
                | SELAIN HILANG
                |--------------------------------------------------------------------------
                */
                soLokasi
                    .prop('disabled', false)
                    .prop('required', true);

                soFoto
                    .prop('disabled', false)
                    .prop('required', true);

                /*
                |--------------------------------------------------------------------------
                | SHOW REQUIRED MARK
                |--------------------------------------------------------------------------
                */
                soLokasi
                    .closest('.mb-3')
                    .find('.text-danger')
                    .removeClass('d-none');

                soFoto
                    .closest('.mb-3')
                    .find('.text-danger')
                    .removeClass('d-none');
            }

            /*
            |--------------------------------------------------------------------------
            | KONDISI CHANGE
            |--------------------------------------------------------------------------
            */
            $('#so_kondisi').on(
                'change',
                handleKondisiChange
            );

            /*
            |--------------------------------------------------------------------------
            | CEK MANUAL
            |--------------------------------------------------------------------------
            |
            | Menggunakan delegated event.
            |
            | Ini wajib karena DataTables redraw row ketika:
            |
            | - pagination
            | - filter
            | - search
            | - entries
            |
            */
            $(document)
                .off(
                    'click.stockOpname',
                    '.btn-cek-manual'
                )
                .on(
                    'click.stockOpname',
                    '.btn-cek-manual',
                    function(e) {
                        e.preventDefault();
                        e.stopPropagation();

                        const button = $(this);

                        /*
                        |--------------------------------------------------------------------------
                        | DATA ASET
                        |--------------------------------------------------------------------------
                        */
                        const asetId =
                            button.attr('data-aset-id');

                        const asetNomor =
                            button.attr('data-aset-nomor');

                        const asetNama =
                            button.attr('data-aset-nama');

                        /*
                        |--------------------------------------------------------------------------
                        | GET FORM
                        |--------------------------------------------------------------------------
                        */
                        const form =
                            document.getElementById(
                                'stockOpnameForm'
                            );

                        /*
                        |--------------------------------------------------------------------------
                        | RESET DULU
                        |--------------------------------------------------------------------------
                        |
                        | Penting:
                        | reset harus sebelum mengisi so_aset_id.
                        |
                        */
                        if (form) {
                            form.reset();
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | RESET KONDISI INPUT
                        |--------------------------------------------------------------------------
                        */
                        handleKondisiChange();

                        /*
                        |--------------------------------------------------------------------------
                        | SET ASET ID SETELAH RESET
                        |--------------------------------------------------------------------------
                        */
                        $('#so_aset_id')
                            .val(asetId);

                        $('#scanned_aset_display')
                            .text(
                                `${asetNomor} - ${asetNama}`
                            );

                        /*
                        |--------------------------------------------------------------------------
                        | OPEN MODAL
                        |--------------------------------------------------------------------------
                        */
                        const modalElement =
                            document.getElementById(
                                'stockOpnameModal'
                            );

                        if (!modalElement) {
                            return;
                        }

                        const modal =
                            bootstrap.Modal
                                .getOrCreateInstance(
                                    modalElement
                                );

                        modal.show();
                    }
                );

            /*
            |--------------------------------------------------------------------------
            | SUBMIT STOCK OPNAME
            |--------------------------------------------------------------------------
            */
            $('#stockOpnameForm').on(
                'submit',
                function(e) {
                    e.preventDefault();

                    const form = this;

                    /*
                    |--------------------------------------------------------------------------
                    | VALIDASI HTML
                    |--------------------------------------------------------------------------
                    */
                    if (!form.checkValidity()) {
                        form.reportValidity();

                        return;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | VALIDASI ASET ID
                    |--------------------------------------------------------------------------
                    */
                    const asetId =
                        $('#so_aset_id').val();

                    if (!asetId) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Aset Tidak Valid',
                            text: 'ID aset tidak ditemukan. Silakan pilih Cek Manual kembali.'
                        });

                        return;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | FORM DATA
                    |--------------------------------------------------------------------------
                    */
                    const formData =
                        new FormData(form);

                    const btnSubmit =
                        document.getElementById(
                            'btnSubmitOpname'
                        );

                    if (!btnSubmit) {
                        return;
                    }

                    const originalText =
                        btnSubmit.innerHTML;

                    /*
                    |--------------------------------------------------------------------------
                    | LOADING
                    |--------------------------------------------------------------------------
                    */
                    btnSubmit.disabled = true;

                    btnSubmit.innerHTML =
                        '<i class="fas fa-spinner fa-spin me-2"></i> Menyimpan...';

                    /*
                    |--------------------------------------------------------------------------
                    | AJAX
                    |--------------------------------------------------------------------------
                    */
                    fetch(
                        "{{ route('stock-opname.scanStore') }}",
                        {
                            method: 'POST',

                            body: formData,

                            headers: {
                                'X-Requested-With':
                                    'XMLHttpRequest',

                                'Accept':
                                    'application/json'
                            }
                        }
                    )
                    .then(async response => {
                        let data = {};

                        try {
                            data =
                                await response.json();
                        } catch (error) {
                            data = {
                                success: false,

                                message:
                                    'Response server tidak valid.'
                            };
                        }

                        return {
                            ok: response.ok,

                            status:
                                response.status,

                            body:
                                data
                        };
                    })
                    .then(result => {

                        /*
                        |--------------------------------------------------------------------------
                        | RESTORE BUTTON
                        |--------------------------------------------------------------------------
                        */
                        btnSubmit.disabled = false;

                        btnSubmit.innerHTML =
                            originalText;

                        /*
                        |--------------------------------------------------------------------------
                        | SUCCESS
                        |--------------------------------------------------------------------------
                        */
                        if (
                            result.ok &&
                            result.body.success
                        ) {
                            const modalElement =
                                document.getElementById(
                                    'stockOpnameModal'
                                );

                            if (modalElement) {
                                const modal =
                                    bootstrap.Modal
                                        .getInstance(
                                            modalElement
                                        );

                                if (modal) {
                                    modal.hide();
                                }
                            }

                            Swal.fire({
                                icon: 'success',

                                title:
                                    'Berhasil',

                                text:
                                    result.body.message ||
                                    'Temuan berhasil disimpan.',

                                timer:
                                    1500,

                                showConfirmButton:
                                    false
                            })
                            .then(() => {
                                window.location.reload();
                            });

                            return;
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | VALIDATION ERROR
                        |--------------------------------------------------------------------------
                        */
                        if (
                            result.status === 422 &&
                            result.body.errors
                        ) {
                            const errors =
                                Object.values(
                                    result.body.errors
                                )
                                .flat();

                            Swal.fire({
                                icon: 'error',

                                title:
                                    'Data Belum Lengkap',

                                html:
                                    errors
                                        .map(
                                            error =>
                                                `<div>${error}</div>`
                                        )
                                        .join('')
                            });

                            return;
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | GENERAL ERROR
                        |--------------------------------------------------------------------------
                        */
                        Swal.fire({
                            icon: 'error',

                            title: 'Gagal',

                            text:
                                result.body.message ||
                                'Terjadi kesalahan saat menyimpan data.'
                        });
                    })
                    .catch(error => {

                        console.error(
                            'Stock Opname Error:',
                            error
                        );

                        /*
                        |--------------------------------------------------------------------------
                        | RESTORE BUTTON
                        |--------------------------------------------------------------------------
                        */
                        btnSubmit.disabled = false;

                        btnSubmit.innerHTML =
                            originalText;

                        /*
                        |--------------------------------------------------------------------------
                        | ERROR
                        |--------------------------------------------------------------------------
                        */
                        Swal.fire({
                            icon: 'error',

                            title: 'Error',

                            text:
                                'Terjadi kesalahan sistem saat menyimpan data.'
                        });
                    });
                }
            );

            /*
            |--------------------------------------------------------------------------
            | RESET MODAL AFTER CLOSED
            |--------------------------------------------------------------------------
            */
            $('#stockOpnameModal').on(
                'hidden.bs.modal',
                function() {
                    const form =
                        document.getElementById(
                            'stockOpnameForm'
                        );

                    if (form) {
                        form.reset();
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | RESET DATA ASET
                    |--------------------------------------------------------------------------
                    */
                    $('#so_aset_id')
                        .val('');

                    $('#scanned_aset_display')
                        .text('');

                    /*
                    |--------------------------------------------------------------------------
                    | RESET CONDITION BEHAVIOUR
                    |--------------------------------------------------------------------------
                    */
                    handleKondisiChange();
                }
            );

        });
    </script>
@endpush
