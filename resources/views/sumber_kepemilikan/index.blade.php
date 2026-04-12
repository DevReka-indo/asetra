@extends('layouts.app')

@section('title', 'Kelola Sumber Kepemilikan')

@section('content')
<div class="container-fluid px-1 py-0 mt-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0">Kelola Sumber Kepemilikan</h3>
    </div>

    {{-- FILTER --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('sumber-kepemilikan.index') }}" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label fw-bold small text-muted text-uppercase" style="font-size: 0.7rem;">Entries</label>
                    <select name="per_page" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    </select>
                </div>
                <div class="col-auto ms-auto">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-primary px-4 rounded-3 d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#modalTambah">
                            + Tambah Sumber
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="60" class="text-center">No</th>
                            <th>Kode</th>
                            <th>Nama Sumber</th>
                            <th width="150" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data as $i => $row)
                            <tr>
                                <td class="text-center">{{ $data->firstItem() + $i }}</td>
                                <td class="fw-bold text-primary">{{ $row->kode }}</td>
                                <td>{{ $row->nama }}</td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center align-items-center gap-2">
                                        <button type="button" class="btn btn-info btn-sm rounded-circle text-white border-0"
                                            style="width:32px; height:32px; display:flex; align-items:center; justify-content:center;"
                                            title="Lihat Detail" data-bs-toggle="modal" data-bs-target="#modalDetail{{ $row->id }}">
                                            <i class="fas fa-eye"></i>
                                        </button>

                                        <button type="button" class="btn btn-warning btn-sm rounded-circle text-white border-0"
                                            style="width:32px; height:32px; display:flex; align-items:center; justify-content:center;"
                                            title="Edit" data-bs-toggle="modal" data-bs-target="#modalEdit{{ $row->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <button type="button" class="btn btn-danger btn-sm rounded-circle text-white border-0"
                                            style="width:32px; height:32px; display:flex; align-items:center; justify-content:center;"
                                            title="Hapus" data-bs-toggle="modal" data-bs-target="#modalDelete{{ $row->id }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="fas fa-info-circle mb-2 d-block fa-2x"></i>
                                    Belum ada data sumber kepemilikan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4 d-flex justify-content-between align-items-center">
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

{{-- MODAL DETAIL --}}
@foreach($data as $row)
<div class="modal fade" id="modalDetail{{ $row->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-info-circle me-2"></i> Detail Sumber Kepemilikan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <table class="table table-borderless mb-0">
                    <tr>
                        <th width="35%" class="text-muted text-uppercase small align-middle">Kode</th>
                        <td width="5%" class="align-middle">:</td>
                        <td>
                            <span class="badge bg-primary px-3 py-2 fs-6 shadow-sm">{{ $row->kode }}</span>
                        </td>
                    </tr>
                    <tr>
                        <th class="text-muted text-uppercase small pt-3 align-middle">Nama Sumber</th>
                        <td class="pt-3 align-middle">:</td>
                        <td class="pt-3 fw-bold fs-6 text-dark">{{ $row->nama }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endforeach

{{-- MODAL TAMBAH --}}
<div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('sumber-kepemilikan.store') }}" method="POST" class="modal-content border-0 shadow" autocomplete="off">
            @csrf
            <input type="hidden" name="form_type" value="tambah">

            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-plus-circle text-primary me-2"></i> Tambah Sumber Kepemilikan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted text-uppercase">Kode</label>
                    <input type="text" name="kode" class="form-control @error('kode') is-invalid @enderror"
                        value="{{ old('form_type') == 'tambah' ? old('kode') : '' }}"
                        placeholder="Contoh: REKA, HDB, ANAK01">
                    @if(old('form_type') == 'tambah')
                        @error('kode') <div class="invalid-feedback fw-bold">{{ $message }}</div> @enderror
                    @endif
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted text-uppercase">Nama Sumber</label>
                    <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror"
                        value="{{ old('form_type') == 'tambah' ? old('nama') : '' }}"
                        placeholder="Contoh: Perusahaan Induk, Hibah, PT Anak 1">
                    @if(old('form_type') == 'tambah')
                        @error('nama') <div class="invalid-feedback fw-bold">{{ $message }}</div> @enderror
                    @endif
                </div>
            </div>

            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-danger rounded-3 px-4" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-success rounded-3 px-4 fw-bold">
                    <i class="fas fa-save me-1"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL EDIT --}}
@foreach($data as $row)
<div class="modal fade" id="modalEdit{{ $row->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('sumber-kepemilikan.update', $row->id) }}" method="POST" class="modal-content border-0 shadow" autocomplete="off">
            @csrf
            @method('PUT')
            <input type="hidden" name="form_type" value="edit_{{ $row->id }}">

            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-warning">
                    <i class="fas fa-edit me-2"></i> Edit Sumber Kepemilikan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted text-uppercase">Kode</label>
                    <input type="text" name="kode" class="form-control @error('kode') is-invalid @enderror"
                        value="{{ old('form_type') == 'edit_'.$row->id ? old('kode') : $row->kode }}">
                    @if(old('form_type') == 'edit_'.$row->id)
                        @error('kode') <div class="invalid-feedback fw-bold">{{ $message }}</div> @enderror
                    @endif
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted text-uppercase">Nama Sumber</label>
                    <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror"
                        value="{{ old('form_type') == 'edit_'.$row->id ? old('nama') : $row->nama }}">
                    @if(old('form_type') == 'edit_'.$row->id)
                        @error('nama') <div class="invalid-feedback fw-bold">{{ $message }}</div> @enderror
                    @endif
                </div>
            </div>

            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-danger rounded-3 px-4" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-warning rounded-3 px-4 text-white fw-bold">
                    <i class="fas fa-save me-1"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL HAPUS --}}
<div class="modal fade" id="modalDelete{{ $row->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-exclamation-triangle me-2"></i>Konfirmasi Hapus</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-4">
                <i class="fas fa-trash-alt fa-4x text-danger mb-3"></i>
                <h5 class="fw-bold text-dark">Hapus Data Ini?</h5>
                <p class="text-muted mb-0">Anda yakin ingin menghapus <strong>{{ $row->kode }} - {{ $row->nama }}</strong>?</p>
            </div>
            <div class="modal-footer bg-light border-0 justify-content-center">
                <form action="{{ route('sumber-kepemilikan.destroy', $row->id) }}" method="POST" class="d-flex gap-2">
                    @csrf @method('DELETE')
                    <button type="button" class="btn btn-warning rounded-3 px-4 text-white" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger rounded-3 px-4 fw-bold">Ya, Hapus</button>
                </form>
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
                showConfirmButton: true,
                confirmButtonText: 'OK',
                confirmButtonColor: '#0d6efd',
                customClass: { popup: 'rounded-4 shadow' }
            };

            @if (session('success'))
                Swal.fire({ ...swalConfig, icon: 'success', title: 'Berhasil!', text: '{{ session('success') }}' });
            @endif

            @if (session('error'))
                Swal.fire({ ...swalConfig, icon: 'error', title: 'Gagal!', text: '{{ session('error') }}' });
            @endif

            @if($errors->any())
                setTimeout(function() {
                    const formType = "{{ old('form_type') }}";
                    if (formType === 'tambah') {
                        new bootstrap.Modal(document.getElementById('modalTambah')).show();
                    } else if (formType.startsWith('edit_')) {
                        const id = formType.split('_')[1];
                        new bootstrap.Modal(document.getElementById('modalEdit' + id)).show();
                    }
                }, 200);
            @endif

            const modalTambahEl = document.getElementById('modalTambah');
            if (modalTambahEl) {
                modalTambahEl.addEventListener('hidden.bs.modal', function () {
                    const form = this.querySelector('form');
                    if (form) {
                        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                        form.reset();
                    }
                });
            }

            document.querySelectorAll('[id^="modalEdit"]').forEach(function(modalEditEl) {
                modalEditEl.addEventListener('hidden.bs.modal', function () {
                    const form = this.querySelector('form');
                    if (form) form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                });
            });
        });
    </script>
@endpush
