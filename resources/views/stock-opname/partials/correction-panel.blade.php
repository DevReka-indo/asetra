<div class="panel-card mt-3 mb-4">
    <div class="panel-head">
        <div class="section-title mb-0">
            <span class="dot"></span> Hasil Pemeriksaan Saat Ini
        </div>
        <span class="summary-pill">{{ $allFindings->count() }} Temuan</span>
    </div>
    <div class="panel-body p-0">
        @if($allFindings->isEmpty())
            <div class="empty-state py-5">
                <i class="fas fa-clipboard-list d-block mb-2"></i>
                <p class="mb-0">Belum ada hasil pemeriksaan.</p>
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
                                    <div>{{ $finding->dicekOleh->name ?? '-' }}</div>
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
