@php
    $hasFindingFilters = request()->filled('search')
        || request()->filled('kondisi')
        || request()->filled('lokasi_id')
        || request()->filled('checker_id');
@endphp

<div class="panel-card mt-3 mb-4">
    <div class="panel-head flex-wrap gap-2">
        <div class="section-title mb-0">
            <span class="dot"></span> Hasil Pemeriksaan Saat Ini
        </div>
        <span class="summary-pill">
            {{ $allFindings->total() }} hasil
            @if($allFindings->total() !== $totalChecked)
                dari {{ $totalChecked }} temuan
            @endif
        </span>
    </div>

    <div class="panel-body border-bottom so-filter-panel">
        <form method="GET" action="{{ route('stock-opname.show', $session) }}">
            <div class="row g-3">
                <div class="col-lg-6">
                    <label class="form-label so-filter-label">Cari Aset / Pemeriksa</label>
                    <div class="input-group so-filter-control">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input
                            type="search"
                            name="search"
                            class="form-control"
                            value="{{ request('search') }}"
                            placeholder="Nomor aset, nama aset, deskripsi, atau pemeriksa..."
                        >
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <label class="form-label so-filter-label">Kondisi</label>
                    <select name="kondisi" class="form-select so-filter-control">
                        <option value="">Semua Kondisi</option>
                        @foreach(['Baik', 'Rusak', 'Bongkar', 'Tidak Terpakai', 'Hilang', 'Tidak Teridentifikasi'] as $kondisi)
                            <option value="{{ $kondisi }}" @selected(request('kondisi') === $kondisi)>{{ $kondisi }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-3 col-md-6">
                    <label class="form-label so-filter-label">Lokasi Temuan</label>
                    <select name="lokasi_id" class="form-select so-filter-control">
                        <option value="">Semua Lokasi</option>
                        @foreach($lokasis as $lokasi)
                            <option value="{{ $lokasi->lokasi_id }}" @selected((string) request('lokasi_id') === (string) $lokasi->lokasi_id)>
                                {{ $lokasi->nama_lokasi }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row g-3 mt-1 align-items-end">
                <div class="col-lg-4 col-md-6">
                    <label class="form-label so-filter-label">Pemeriksa</label>
                    <select name="checker_id" class="form-select so-filter-control">
                        <option value="">Semua Pemeriksa</option>
                        @foreach($availableCheckers as $checker)
                            <option value="{{ $checker->id }}" @selected((string) request('checker_id') === (string) $checker->id)>
                                {{ trim($checker->firstname.' '.$checker->lastname) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-2 col-md-3 col-6">
                    <label class="form-label so-filter-label">Per Halaman</label>
                    <select name="per_page" class="form-select so-filter-control">
                        @foreach([10, 20, 50, 100] as $size)
                            <option value="{{ $size }}" @selected((int) request('per_page', 20) === $size)>{{ $size }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-6 col-md-3">
                    <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                        <button type="submit" class="btn btn-primary so-filter-apply">
                            <i class="fas fa-filter me-2"></i>Terapkan Filter
                        </button>
                        @if($hasFindingFilters)
                            <a href="{{ route('stock-opname.show', $session) }}" class="btn btn-outline-danger so-filter-reset">
                                <i class="fas fa-rotate-left me-2"></i>Reset
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="panel-body p-0">
        @if($allFindings->isEmpty())
            <div class="empty-state py-5">
                <i class="fas fa-clipboard-list d-block mb-2"></i>
                <p class="mb-1">
                    {{ $hasFindingFilters ? 'Tidak ada hasil pemeriksaan yang sesuai filter.' : 'Belum ada hasil pemeriksaan.' }}
                </p>
                @if($hasFindingFilters)
                    <a href="{{ route('stock-opname.show', $session) }}" class="btn btn-sm btn-outline-secondary mt-2">
                        <i class="fas fa-rotate-left me-1"></i> Reset Filter
                    </a>
                @endif
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Aset</th>
                            <th>Kondisi</th>
                            <th>Lokasi Temuan</th>
                            <th>Deskripsi Aset</th>
                            <th>Pemeriksa</th>
                            <th>Foto</th>
                            @can('manage_stock_opname')
                                @if($session->isActive())
                                    <th class="text-end">Aksi</th>
                                @endif
                            @endcan
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($allFindings as $finding)
                            <tr>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $finding->aset->nomor_aset ?? '-' }}</div>
                                    <small class="text-muted">{{ $finding->aset->nama_aset ?? 'Aset tidak tersedia' }}</small>
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $finding->kondisi_temuan }}</span></td>
                                <td>{{ $finding->lokasiTemuan->nama_lokasi ?? $finding->lokasi_temuan ?? '-' }}</td>
                                <td>
                                    <div>{{ $finding->deskripsi_temuan ?: '-' }}</div>
                                    @if($finding->keterangan)
                                        <small class="text-muted">Catatan pemeriksaan lama: {{ $finding->keterangan }}</small>
                                    @endif
                                </td>
                                <td>
                                    <div>{{ trim(($finding->dicekOleh->firstname ?? '').' '.($finding->dicekOleh->lastname ?? '')) ?: '-' }}</div>
                                    <small class="text-muted">{{ optional($finding->tanggal_cek)->format('d M Y') }}</small>
                                </td>
                                <td>
                                    @if($finding->foto_temuan)
                                        <a href="{{ Storage::url($finding->foto_temuan) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary" title="Lihat foto temuan">
                                            <i class="fas fa-image"></i>
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                @can('correct_stock_opname_detail', $finding)
                                    @if($session->isActive())
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm so-btn-outline" data-bs-toggle="modal" data-bs-target="#correctionModal{{ $finding->id }}">
                                                <i class="fas fa-pen me-1"></i> Koreksi
                                            </button>
                                        </td>
                                    @endif
                                @endcan
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-3 py-3 border-top d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                <div class="text-muted small">
                    Menampilkan {{ $allFindings->firstItem() ?? 0 }} sampai {{ $allFindings->lastItem() ?? 0 }}
                    dari {{ $allFindings->total() }} hasil
                </div>
                <div>
                    {{ $allFindings->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @endif
    </div>
</div>

@if($session->isActive())
    @foreach($allFindings as $finding)
        @can('correct_stock_opname_detail', $finding)
            @include('stock-opname.partials.correction-modal', [
                'finding' => $finding,
                'correctionContext' => 'management',
            ])
        @endcan
    @endforeach
@endif
