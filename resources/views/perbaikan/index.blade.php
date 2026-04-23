@extends('layouts.app')

@section('title', 'Pengajuan Perbaikan Aset')

@section('content')
<div class="container-fluid px-1 py-0 mt-0">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold mb-0">Pengajuan Perbaikan Aset</h3>
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
                <span class="text-muted" style="font-size: 14px; font-weight: 500; position: relative; top: 2px;">Pengajuan Perbaikan</span>
            </li>
        </ul>
    </div>

    {{-- ALERT --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body">

            {{-- Entries --}}
            <form method="GET" action="{{ route('perbaikan.index') }}" class="row g-2 align-items-end mb-3">
                <div class="col-md-1">
                    <label class="form-label fw-bold small text-muted text-uppercase" style="font-size: 0.7rem;">Entries</label>
                    <select name="per_page" class="form-select form-select-sm rounded-3 w-100" onchange="this.form.submit()">
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>
                <div class="col-md-11">
                    {{-- STATUS BUTTONS --}}
                    <div class="d-flex flex-wrap gap-2">
                @php
                    $statuses = [
                        ''          => ['label' => 'Semua',          'icon' => 'fa-list'],
                        'menunggu'  => ['label' => 'Menunggu Review', 'icon' => 'fa-clock'],
                        'disetujui' => ['label' => 'Disetujui',       'icon' => 'fa-check-circle'],
                        'ditolak'   => ['label' => 'Ditolak',         'icon' => 'fa-times-circle'],
                        'selesai'   => ['label' => 'Selesai',         'icon' => 'fa-flag-checkered'],
                    ];
                    $activeStatus = request('status', '');
                @endphp

                @foreach($statuses as $val => $info)
                    <a href="{{ route('perbaikan.index', array_merge(request()->except('page'), ['status' => $val])) }}"
                       class="btn btn-sm rounded-pill {{ $activeStatus === $val ? 'btn-navy text-white' : 'btn-outline-secondary' }}"
                       style="{{ $activeStatus === $val ? 'background-color:#253070;' : '' }}">
                        <i class="fas {{ $info['icon'] }} me-1"></i>{{ $info['label'] }}
                    </a>
                @endforeach
                    </div>
                </div>
            </form>

            {{-- TABEL --}}
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Aset</th>
                            <th>Pengaju</th>
                            <th>Urgensi</th>
                            <th>Tanggal Pengajuan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pengajuans as $p)
                        <tr>
                            <td class="text-muted small">{{ $loop->iteration + ($pengajuans->currentPage() - 1) * $pengajuans->perPage() }}</td>
                            <td>
                                <div class="fw-bold" style="font-size: 13px;">
                                    {{ $p->aset->nama_aset ?? '-' }}
                                </div>
                                <small class="text-muted">{{ $p->aset->nomor_aset ?? '-' }}</small>
                            </td>
                            <td>
                                <small>{{ $p->pengaju ? $p->pengaju->firstname . ' ' . $p->pengaju->lastname : '-' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-{{ $p->urgensi_badge }} rounded-pill px-3">
                                    {{ ucfirst($p->tingkat_urgensi) }}
                                </span>
                            </td>
                            <td>
                                <small>{{ $p->tanggal_pengajuan ? $p->tanggal_pengajuan->format('d M Y') : '-' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-{{ $p->status_badge }} rounded-pill px-3">
                                    {{ $p->status_label }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('perbaikan.show', $p->id) }}"
                                   class="btn btn-info btn-sm rounded-circle text-white border-0" 
                                    style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"
                                    title="Lihat">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                Belum ada data pengajuan perbaikan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            <div class="d-flex justify-content-end mt-3">
                {{ $pengajuans->links() }}
            </div>

        </div>
    </div>

</div>
@endsection
