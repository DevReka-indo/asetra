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
            <form method="GET" action="{{ route('aset.index') }}" class="row g-3 align-items-end">

                {{-- Entries --}}
                <div class="col-md-2">
                    <label class="form-label fw-bold small text-muted text-uppercase" style="font-size: 0.7rem;">
                        Entries
                    </label>
                    <select name="per_page"
                            class="form-select form-select-sm rounded-3"
                            onchange="this.form.submit()">
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    </select>
                </div>

                {{-- Button Tambah --}}
                <div class="col-auto ms-auto d-flex gap-2">
                    <a href="{{ route('aset.scanner') }}" class="btn btn-navy px-4 rounded-3 d-flex align-items-center mb-0" style="background-color: #253070; color: white;">
                        <i class="fas fa-qrcode me-2"></i> Scan Barcode
                    </a>
                    <a href="{{ route('aset.create') }}" class="btn btn-primary px-4 rounded-3 d-flex align-items-center mb-0">
                        + Tambah Data Aset
                    </a>
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
                                {{-- QR Code --}}
                                <td class="text-center">
                                    @php
                                        // Membuat URL dinamis ke halaman detail aset
                                        $urlDetail = route('aset.show', $aset->id);
                                    @endphp

                                    {{-- Generate QR Code berisi Link Detail --}}
                                    {!! QrCode::size(150)->generate($urlDetail) !!}
                                </td>

                                {{-- Data Aset --}}
                                <td>{{ $aset->nomor_aset }}</td>
                                <td>{{ $aset->nama_aset }}</td>
                                <td>{{ $aset->lokasi->nama_lokasi ?? $aset->lokasi->nm_lokasi_aset ?? '-' }}</td>
                                <td>{{ $aset->status_kondisi ?? '-' }}</td>
                                <td>{{ $aset->status_aset ?? '-' }}</td>

                                {{-- Action Buttons --}}
                                <td class="text-center">
                                    <div class="d-flex justify-content-center align-items-center gap-2">
                                        {{-- TOMBOL SHOW (LIHAT) --}}
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

                                        {{-- TOMBOL HAPUS (Tetap pakai Modal Konfirmasi agar Aman) --}}
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
@endsection
