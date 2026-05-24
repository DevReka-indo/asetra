@extends('layouts.app')

@section('title', 'Pemulihan Lokasi Aset')

@section('content')
<div class="container-fluid px-1 py-0 mt-0">
    <div class="mb-4">
        <h3 class="fw-bold mb-0">Pemulihan Lokasi Aset</h3>
        <p class="text-muted small">Data di bawah ini adalah Lokasi Aset yang telah dihapus. Anda dapat memulihkannya atau menghapusnya secara permanen.</p>
    </div>

    {{-- FILTER --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('pemulihan.lokasi-aset') }}" class="row g-2 align-items-end">
                {{-- Entries --}}
                <div class="col-md-1">
                    <label class="form-label fw-bold small text-muted text-uppercase" style="font-size: 0.7rem;">Entries</label>
                    <select name="per_page" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>
                {{-- Pencarian --}}
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-muted text-uppercase" style="font-size: 0.7rem;">Pencarian</label>
                    <div class="input-group input-group-sm input-group-focus rounded-3">
                        <span class="input-group-text bg-white border-0 text-muted"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control border-0 shadow-none bg-transparent" placeholder="Cari nama lokasi atau kode..." value="{{ request('search') }}">
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- TABEL PEMULIHAN --}}
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="60" class="text-center">No</th>
                            <th>Nama Lokasi</th>
                            <th width="150">Kode Lokasi</th>
                            <th>Tgl Dihapus</th>
                            <th width="150" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data as $i => $item)
                            <tr>
                                <td class="text-center">{{ $data->firstItem() + $i }}</td>
                                <td class="fw-semibold">{{ $item->nama_lokasi }}</td>
                                <td class="fw-bold text-primary fs-5">{{ $item->kode_lokasi }}</td>
                                <td>{{ $item->deleted_at ? $item->deleted_at->format('d/m/Y H:i') : '-' }}</td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center align-items-center gap-2">
                                        {{-- RESTORE BUTTON --}}
                                        <button type="button" class="btn btn-success btn-sm rounded-circle text-white border-0" 
                                            style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"
                                            title="Pulihkan" data-bs-toggle="modal" data-bs-target="#restoreModal{{ $item->lokasi_id }}">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                        {{-- FORCE DELETE BUTTON --}}
                                        <button type="button" class="btn btn-danger btn-sm rounded-circle text-white border-0" 
                                            style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"
                                            title="Hapus Permanen" data-bs-toggle="modal" data-bs-target="#forceDeleteModal{{ $item->lokasi_id }}">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <i class="fas fa-info-circle mb-2 d-block fa-2x"></i>
                                    Tidak ada data Lokasi Aset di pemulihan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3 d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Menampilkan {{ $data->firstItem() ?? 0 }} sampai {{ $data->lastItem() ?? 0 }} dari {{ $data->total() }} data
                </div>
                <div>
                    {{ $data->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>

@foreach($data as $item)
{{-- MODAL RESTORE --}}
<div class="modal fade" id="restoreModal{{ $item->lokasi_id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-body p-5 text-center bg-light">
                <div class="d-inline-flex align-items-center justify-content-center rounded-4 mb-4" style="width: 80px; height: 80px; background-color: #f1f3f5;">
                    <i class="fas fa-undo fa-3x text-success"></i>
                </div>
                <h4 class="fw-bold text-dark mb-2">Konfirmasi Pulihkan</h4>
                <p class="text-muted mb-3" style="font-size: 1rem;">
                    Anda yakin ingin memulihkan Lokasi Aset <strong class="text-success fs-5">{{ $item->nama_lokasi }}</strong>?
                </p>
                <div class="d-flex justify-content-center gap-3">
                    <form action="{{ route('pemulihan.lokasi-aset.restore', $item->lokasi_id) }}" method="POST" class="w-100 d-flex justify-content-center gap-3">
                        @csrf 
                        @method('PUT')
                        <button type="button" class="btn btn-light rounded-pill fw-bold py-2 shadow-sm border" style="width: 120px;" data-bs-dismiss="modal">Batalkan</button>
                        <button type="submit" class="btn btn-success rounded-pill fw-bold py-2 shadow-sm" style="width: 140px;">Ya, Pulihkan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL FORCE DELETE --}}
<div class="modal fade" id="forceDeleteModal{{ $item->lokasi_id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-body p-5 text-center bg-light">
                <div class="d-inline-flex align-items-center justify-content-center rounded-4 mb-4" style="width: 80px; height: 80px; background-color: #f1f3f5;">
                    <i class="fas fa-exclamation-triangle fa-3x text-danger"></i>
                </div>
                <h4 class="fw-bold text-dark mb-2">Hapus Permanen</h4>
                <p class="text-muted mb-3" style="font-size: 1rem;">
                    Hapus Lokasi Aset <strong class="text-danger fs-5">{{ $item->nama_lokasi }}</strong> secara permanen?
                </p>
                
                <div class="alert alert-danger mb-4 text-start small border-0 shadow-sm rounded-3">
                    <i class="fas fa-info-circle me-1"></i><strong>Peringatan:</strong> Tindakan ini tidak dapat dibatalkan.
                </div>

                <div class="d-flex justify-content-center gap-3">
                    <form action="{{ route('pemulihan.lokasi-aset.force-delete', $item->lokasi_id) }}" method="POST" class="w-100 d-flex justify-content-center gap-3">
                        @csrf 
                        @method('DELETE')
                        <button type="button" class="btn btn-light rounded-pill fw-bold py-2 shadow-sm border" style="width: 120px;" data-bs-dismiss="modal">Batalkan</button>
                        <button type="submit" class="btn btn-danger rounded-pill fw-bold py-2 shadow-sm" style="width: 160px;">Hapus Permanen</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endforeach
@endsection

@push('scripts')
<script>
    window.addEventListener('load', function() {
        const swalConfig = {
            showConfirmButton: true, confirmButtonText: 'OK', confirmButtonColor: '#253070', customClass: { popup: 'rounded-4 shadow' }
        };

        @if (session('success')) Swal.fire({ ...swalConfig, icon: 'success', title: 'Berhasil!', text: '{{ session('success') }}' }); @endif
        @if (session('error')) Swal.fire({ ...swalConfig, icon: 'error', title: 'Gagal!', text: '{{ session('error') }}' }); @endif
    });
</script>
@endpush
