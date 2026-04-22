@extends('layouts.app')

@section('title', 'Data Aset Perusahaan')

@section('content')
<div class="container-fluid px-1 py-0 mt-0">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold mb-0">Data Aset Perusahaan</h3>
    </div>

    {{-- FILTER --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('aset.index') }}">
                
                {{-- Tombol-Tombol Action Utama --}}
                <div class="d-flex gap-2 flex-wrap justify-content-end align-items-center mb-3 pb-3 border-bottom">
                    {{-- Tombol Cetak Label Terpilih --}}
                    <button type="button" id="btnCetakLabelSelected" class="btn btn-dark px-3 rounded-3 d-flex align-items-center text-white" title="Cetak Label Terpilih">
                        <i class="fas fa-tags me-1"></i> Cetak Label 
                    </button>

                    {{-- Tombol Cetak Label Per Ruangan --}}
                    <button type="button" class="btn btn-secondary px-3 rounded-3 d-flex align-items-center text-white" title="Cetak Label Per Ruangan" data-bs-toggle="modal" data-bs-target="#modalCetakPerRuangan">
                        <i class="fas fa-building me-1"></i> Cetak Per Ruangan
                    </button>

                    {{-- Tombol Import --}}
                    <button type="button" class="btn btn-warning px-3 rounded-3 d-flex align-items-center text-dark" title="Import Data">
                        <i class="fas fa-file-import me-1"></i> Import
                    </button>

                    {{-- Tombol Export --}}
                    <button type="button" class="btn btn-success px-3 rounded-3 d-flex align-items-center text-white" title="Export Data">
                        <i class="fas fa-file-excel me-1"></i> Export
                    </button>

                    {{-- Tombol Scan --}}
                    <a href="{{ route('aset.scanner') }}" class="btn btn-navy px-3 rounded-3 d-flex align-items-center text-white" style="background-color: #253070;">
                        <i class="fas fa-qrcode me-1"></i> Scan Barcode
                    </a>

                    {{-- Button Tambah --}}
                    <a href="{{ route('aset.create') }}" class="btn btn-primary px-3 rounded-3 d-flex align-items-center">
                        <i class="fas fa-plus me-1"></i> Tambah Data Aset
                    </a>
                </div>

                {{-- Form Filter, Pencarian & Reset --}}
                <div class="row g-2 align-items-end">
                    {{-- Pencarian --}}
                    <div class="col-md-3">
                        <label class="form-label fw-bold small text-muted text-uppercase" style="font-size: 0.7rem;">Pencarian</label>
                        <div class="input-group input-group-sm input-group-focus rounded-3">
                            <span class="input-group-text bg-white border-0 text-muted"><i class="fas fa-search"></i></span>
                            <input type="text" name="search" class="form-control border-0 shadow-none bg-transparent" placeholder="Cari nomor, nama atau jenis aset..." value="{{ request('search') }}">
                        </div>
                    </div>

                    {{-- Filter Kondisi --}}
                    <div class="col-md-2">
                        <label class="form-label fw-bold small text-muted text-uppercase" style="font-size: 0.7rem;">Kondisi Aset</label>
                        <select name="kondisi" class="form-select form-select-sm rounded-3 w-100" onchange="this.form.submit()">
                            <option value="">Semua Kondisi</option>
                            <option value="Baik" {{ request('kondisi') == 'Baik' ? 'selected' : '' }}>Baik</option>
                            <option value="Rusak" {{ request('kondisi') == 'Rusak' ? 'selected' : '' }}>Rusak</option>
                            <option value="Bongkar" {{ request('kondisi') == 'Bongkar' ? 'selected' : '' }}>Bongkar</option>
                            <option value="Tidak Terpakai" {{ request('kondisi') == 'Tidak Terpakai' ? 'selected' : '' }}>Tidak Terpakai</option>
                            <option value="Hilang" {{ request('kondisi') == 'Hilang' ? 'selected' : '' }}>Hilang</option>
                            <option value="Lainnya" {{ request('kondisi') == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                    </div>

                    {{-- Filter Status --}}
                    <div class="col-md-2">
                        <label class="form-label fw-bold small text-muted text-uppercase" style="font-size: 0.7rem;">Status Aset</label>
                        <select name="status_aset" class="form-select form-select-sm rounded-3 w-100" onchange="this.form.submit()">
                            <option value="">Semua Status</option>
                            <option value="Aktif" {{ request('status_aset') == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="Tidak Aktif" {{ request('status_aset') == 'Tidak Aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                            <option value="Dalam Perbaikan" {{ request('status_aset') == 'Dalam Perbaikan' ? 'selected' : '' }}>Dalam Perbaikan</option>
                            <option value="Dipinjam" {{ request('status_aset') == 'Dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                            <option value="Hilang" {{ request('status_aset') == 'Hilang' ? 'selected' : '' }}>Hilang</option>
                        </select>
                    </div>

                    {{-- Entries --}}
                    <div class="col-md-1">
                        <label class="form-label fw-bold small text-muted text-uppercase" style="font-size: 0.7rem;">Entries</label>
                        <select name="per_page" class="form-select form-select-sm rounded-3 w-100" onchange="this.form.submit()">
                            <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>

                    {{-- Tombol Reset --}}
                    <div class="col-auto ms-auto d-flex gap-2">
                        {{-- Tombol Reset --}}
                        <a href="{{ route('aset.index') }}" class="btn px-4 rounded-3 d-flex align-items-center text-white" style="background-color: #1b53a7; border-color: #48abf7;" title="Reset Filter">
                            <i class="fas fa-undo me-1"></i> Reset
                        </a>
                    </div>
                </div>

            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="40" class="text-center border-end">
                                <input class="form-check-input" type="checkbox" id="checkAllAset">
                            </th>
                            <th width="80" class="text-center">Kode QR</th>
                            <th>Kode Aset</th>
                            <th>Nama Aset</th>
                            <th>Lokasi Aset</th>
                            <th>Kondisi Aset</th>
                            <th>Status Aset</th>
                            <th width="180" class="text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($asets as $aset)
                            <tr>
                                {{-- Checkbox --}}
                                <td class="text-center border-end">
                                    <input class="form-check-input aset-checkbox" type="checkbox" name="ids[]" value="{{ $aset->id }}" form="formCetakLabelSelected">
                                </td>

                                {{-- QR Code --}}
                                <td class="text-center">
                                    @php
                                        // Membuat URL dinamis ke halaman detail aset
                                        $urlDetail = route('aset.show', $aset->id);
                                    @endphp

                                    {{-- Generate QR Code berisi Link Detail (Ukuran disesuaikan untuk tabel) --}}
                                    {!! QrCode::size(60)->generate($urlDetail) !!}
                                </td>

                                {{-- Data Aset --}}
                                <td>{{ $aset->nomor_aset }}</td>
                                <td>{{ $aset->nama_aset }}</td>
                                <td>{{ $aset->lokasi->nama_lokasi ?? $aset->lokasi->nm_lokasi_aset ?? '-' }}</td>
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
                                    
                                    @if($kondisi == 'Lainnya' && !empty($aset->keterangan_kondisi))
                                        <div class="mt-1 small text-muted fst-italic" style="font-size: 0.75rem;">
                                            <i class="fas fa-angle-right me-1"></i>{{ $aset->keterangan_kondisi }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if($aset->status_aset == 'Aktif')
                                        <span class="badge bg-success rounded-pill px-3">Aktif</span>
                                    @elseif($aset->status_aset == 'Dalam Perbaikan')
                                        <span class="badge bg-warning text-white rounded-pill px-3">Perbaikan</span>
                                    @elseif($aset->status_aset == 'Dipinjam')
                                        <span class="badge bg-info text-white rounded-pill px-3">Dipinjam</span>
                                    @elseif($aset->status_aset == 'Hilang')
                                        <span class="badge bg-dark rounded-pill px-3">Hilang</span>
                                    @elseif($aset->status_aset == 'Tidak Aktif')
                                        <span class="badge bg-danger rounded-pill px-3">Tidak Aktif</span>
                                    @else
                                        <span class="badge bg-secondary rounded-pill px-3">{{ $aset->status_aset ?? 'Tidak Aktif' }}</span>
                                    @endif
                                </td>

                                {{-- Action Buttons --}}
                                <td class="text-center">
                                    <div class="d-flex justify-content-center align-items-center gap-2">
                                        {{-- TOMBOL SHOW --}}
                                        <a href="{{ route('aset.show', $aset->id) }}" 
                                        class="btn btn-info btn-sm rounded-circle text-white border-0" 
                                        style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"
                                        title="Lihat">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>

                                        {{-- TOMBOL EDIT --}}
                                        <a href="{{ route('aset.edit', $aset->id) }}" 
                                        class="btn btn-warning btn-sm rounded-circle text-white border-0"
                                        style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"
                                        title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        {{-- TOMBOL HAPUS --}}
                                        <button type="button" class="btn btn-danger btn-sm rounded-circle text-white border-0" 
                                                style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"
                                                title="Hapus" data-bs-toggle="modal" data-bs-target="#deleteAsetModal{{ $aset->id }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>

                                    {{-- Modal Konfirmasi Hapus --}}
                                    <div class="modal fade" id="deleteAsetModal{{ $aset->id }}" tabindex="-1" aria-labelledby="deleteAsetModalLabel{{ $aset->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="deleteAsetModalLabel{{ $aset->id }}">Konfirmasi Hapus</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    Apakah Anda yakin ingin menghapus aset <strong>{{ $aset->nomor_aset }}</strong>? Tindakan ini tidak dapat dibatalkan.
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <form action="{{ route('aset.destroy', $aset->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">Hapus</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    Tidak ada data aset.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-3 d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Menampilkan {{ $asets->firstItem() ?? 0 }} sampai {{ $asets->lastItem() ?? 0 }} dari {{ $asets->total() }} data
                </div>
                <div>
                    {{ $asets->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Modal Cetak Per Ruangan -->
<div class="modal fade" id="modalCetakPerRuangan" tabindex="-1" aria-labelledby="modalCetakPerRuanganLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">

            {{-- Header --}}
            <div class="modal-header border-0 pt-4 px-4 pb-3" style="background-color: #253070;">
                <h5 class="modal-title fw-bold text-white" id="modalCetakPerRuanganLabel">
                    <i class="fas fa-print me-2"></i> Cetak Label Per Ruangan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- Body --}}
            <div class="modal-body p-4 bg-light">
                <form id="formCetakLabelLokasi" action="{{ route('aset.cetak-label-lokasi') }}" method="POST" target="_blank">
                    @csrf

                    {{-- Pilih Lokasi --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-uppercase" style="color: #253070; font-size: 0.72rem;">
                            <i class="fas fa-map-marker-alt me-1"></i> Pilih Ruangan (Lokasi) <span class="text-danger">*</span>
                        </label>
                        <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus align-items-center bg-white border">
                            <span class="input-group-text bg-white border-0 text-muted"><i class="fas fa-door-open"></i></span>
                            <select class="form-select border-0 shadow-none fs-6" id="lokasiSelect" name="lokasi_id" required>
                                <option value="">-- Pilih Lokasi --</option>
                                @foreach ($lokasiList as $lok)
                                    <option value="{{ $lok->lokasi_id }}">{{ $lok->nama_lokasi ?? $lok->nm_lokasi_aset }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Preview Aset --}}
                    <div id="previewAsetContainer" class="d-none">
                        <label class="form-label fw-bold small text-uppercase mb-2" style="color: #253070; font-size: 0.72rem;">
                            <i class="fas fa-list me-1"></i> Daftar Aset di Ruangan Ini
                        </label>
                        <div class="bg-white rounded-3 shadow-sm border overflow-hidden">
                            <div style="max-height: 220px; overflow-y: auto;">
                                <table class="table table-sm table-hover mb-0 align-middle">
                                    <thead style="background-color: #f1f3f9; position: sticky; top: 0; z-index: 1;">
                                        <tr>
                                            <th class="ps-3 py-2 text-uppercase small fw-bold text-muted" style="font-size: 0.7rem; width: 55%;">No Aset</th>
                                            <th class="py-2 text-uppercase small fw-bold text-muted" style="font-size: 0.7rem;">Nama Aset</th>
                                        </tr>
                                    </thead>
                                    <tbody id="previewAsetBody">
                                        {{-- AJAX Content --}}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <p class="text-muted small mt-2 mb-0"><i class="fas fa-info-circle me-1"></i> Semua aset di ruangan ini akan ikut dicetak.</p>
                    </div>

                </form>
            </div>

            {{-- Footer --}}
            <div class="modal-footer bg-light border-top-0 pt-2 pb-4 px-4">
                <button type="button" class="btn btn-light rounded-pill px-4 fw-bold shadow-sm border" data-bs-dismiss="modal">
                    <i></i> Batal
                </button>
                <button type="submit" form="formCetakLabelLokasi" class="btn text-white rounded-pill px-4 fw-bold shadow-sm" id="btnProsesCetakLokasi" disabled style="background-color: #253070;">
                    <i class="fas fa-print me-1"></i> Cetak Semua Aset Di Ruangan Ini
                </button>
            </div>

        </div>
    </div>
</div>

<form id="formCetakLabelSelected" action="{{ route('aset.cetak-label') }}" method="POST" target="_blank">
    @csrf
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check All logic
    const checkAll = document.getElementById('checkAllAset');
    const checkboxes = document.querySelectorAll('.aset-checkbox');
    
    if (checkAll) {
        checkAll.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
    }

    // Submit form for selected
    const btnCetakSelected = document.getElementById('btnCetakLabelSelected');
    if (btnCetakSelected) {
        btnCetakSelected.addEventListener('click', function() {
            const checkedBoxes = document.querySelectorAll('.aset-checkbox:checked');
            if (checkedBoxes.length === 0) {
                alert('Pilih minimal satu aset untuk dicetak!');
                return;
            }
            document.getElementById('formCetakLabelSelected').submit();
        });
    }

    // AJAX Preview Lokasi
    const lokasiSelect = document.getElementById('lokasiSelect');
    const previewContainer = document.getElementById('previewAsetContainer');
    const previewBody = document.getElementById('previewAsetBody');
    const btnProsesCetakLokasi = document.getElementById('btnProsesCetakLokasi');

    if (lokasiSelect) {
        lokasiSelect.addEventListener('change', function() {
            const lokasiId = this.value;
            if (!lokasiId) {
                previewContainer.classList.add('d-none');
                btnProsesCetakLokasi.disabled = true;
                return;
            }

            // Fetch
            fetch(`/aset/lokasi/${lokasiId}/preview`)
                .then(res => res.json())
                .then(data => {
                    previewBody.innerHTML = '';
                    if (data.length === 0) {
                        previewBody.innerHTML = '<tr><td colspan="2" class="text-center text-muted py-3">Tidak ada aset di ruangan ini!</td></tr>';
                        btnProsesCetakLokasi.disabled = true;
                    } else {
                        data.forEach((aset, i) => {
                            const bg = i % 2 === 0 ? '' : 'style="background:#f8f9fa"';
                            previewBody.innerHTML += `<tr ${bg}>
                                <td>${aset.nomor_aset || '-'}</td>
                                <td>${aset.nama_aset || '-'}</td>
                            </tr>`;
                        });
                        btnProsesCetakLokasi.disabled = false;
                    }
                    previewContainer.classList.remove('d-none');
                })
                .catch(err => {
                    console.error('Error fetching preview:', err);
                    alert('Gagal mengambil data aset.');
                });
        });
    }

    // Reset modal 
    const modalCetakEl = document.getElementById('modalCetakPerRuangan');
    if (modalCetakEl) {
        modalCetakEl.addEventListener('show.bs.modal', function () {
            // Reset dropdown ke pilihan awal
            if (lokasiSelect) lokasiSelect.value = '';
            // Kosongkan isi tabel preview
            if (previewBody) previewBody.innerHTML = '';
            // Sembunyikan container preview
            if (previewContainer) previewContainer.classList.add('d-none');
            // Disable tombol cetak
            if (btnProsesCetakLokasi) btnProsesCetakLokasi.disabled = true;
        });
    }
});
</script>
@endsection
