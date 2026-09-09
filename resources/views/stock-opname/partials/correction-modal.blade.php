@php
    $isFailedCorrection = (string) old('correction_detail_id') === (string) $finding->id;
@endphp

<div class="modal fade" id="correctionModal{{ $finding->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form action="{{ route('stock-opname.detail.update', [$session->id, $finding->id]) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                <input type="hidden" name="correction_context" value="{{ $correctionContext ?? 'management' }}">
                <input type="hidden" name="correction_detail_id" value="{{ $finding->id }}">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title fw-bold">Koreksi Hasil Pemeriksaan</h5>
                        <small class="text-muted">{{ $finding->aset->nomor_aset ?? '-' }} - {{ $finding->aset->nama_aset ?? 'Aset tidak tersedia' }}</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-light border py-2 mb-3">
                        <small class="text-muted">Pemeriksa</small>
                        <div class="fw-semibold">
                            {{ $finding->dicekOleh->firstname ?? '-' }} {{ $finding->dicekOleh->lastname ?? '' }} · {{ optional($finding->tanggal_cek)->format('d M Y') }}
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="kondisiTemuan{{ $finding->id }}">Kondisi</label>
                            <select class="form-select" id="kondisiTemuan{{ $finding->id }}" name="kondisi_temuan" required>
                                @foreach(['Baik', 'Rusak', 'Bongkar', 'Tidak Terpakai', 'Hilang', 'Tidak Teridentifikasi'] as $condition)
                                    <option value="{{ $condition }}" @selected(($isFailedCorrection ? old('kondisi_temuan') : $finding->kondisi_temuan) === $condition)>{{ $condition }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="lokasiTemuan{{ $finding->id }}">Lokasi Temuan</label>
                            <select class="form-select" id="lokasiTemuan{{ $finding->id }}" name="lokasi_temuan">
                                <option value="">Tidak diketahui</option>
                                @foreach($lokasis as $lokasi)
                                    <option value="{{ $lokasi->lokasi_id }}" @selected((string) ($isFailedCorrection ? old('lokasi_temuan') : $finding->lokasi_temuan) === (string) $lokasi->lokasi_id)>
                                        {{ $lokasi->nama_lokasi }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold" for="keteranganTemuan{{ $finding->id }}">Keterangan</label>
                            <textarea class="form-control" id="keteranganTemuan{{ $finding->id }}" name="keterangan" rows="3">{{ $isFailedCorrection ? old('keterangan') : $finding->keterangan }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold" for="fotoTemuan{{ $finding->id }}">Ganti Foto Temuan</label>
                            <input class="form-control" id="fotoTemuan{{ $finding->id }}" type="file" name="foto_temuan" accept="image/jpeg,image/png">
                            @if($finding->foto_temuan)
                                <small class="text-muted">Kosongkan untuk mempertahankan foto saat ini.</small>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Simpan Koreksi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
