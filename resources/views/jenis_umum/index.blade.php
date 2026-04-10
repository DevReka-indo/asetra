@extends('layouts.app')

@section('title', 'Kelola Jenis Aset Umum')

@section('content')
<div class="container-fluid px-1 py-0 mt-0">
    <div class="mb-4">
        <h3 class="fw-bold mb-0">Kelola Jenis Aset Tetap Umum</h3>
    </div>

    {{-- TABEL JENIS ASET TETAP UMUM   --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0 text-primary">Data Jenis Aset Tetap Umum</h5>
                <button type="button" class="btn btn-primary px-4 rounded-3 d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#modalTambahUmum">
                    + Tambah Aset Tetap Umum
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered custom-table-bagian align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="60" class="text-center">No</th>
                            <th>Kode Umum</th>
                            <th>Jenis Aset Tetap</th>
                            <th width="120" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dataUmum as $i => $umum)
                            <tr>
                                <td class="text-center">{{ $dataUmum->firstItem() + $i }}</td>
                                <td class="fw-bold text-primary">{{ $umum->kode_umum }}</td>
                                <td>{{ $umum->jenis_aset }}</td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center align-items-center gap-2">
                                        <button type="button" class="btn btn-info btn-sm rounded-circle text-white border-0" 
                                            style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"
                                            title="Lihat" data-bs-toggle="modal" data-bs-target="#viewUmumModal{{ $umum->id }}">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-warning btn-sm rounded-circle text-white border-0"
                                            style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"
                                            title="Edit" data-bs-toggle="modal" data-bs-target="#editUmumModal{{ $umum->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm rounded-circle text-white border-0" 
                                            style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"
                                            title="Hapus" data-bs-toggle="modal" data-bs-target="#deleteUmumModal{{ $umum->id }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">
                                    <i class="fas fa-info-circle mb-2 d-block fa-2x"></i>
                                    Belum ada data jenis aset tetap umum
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3 d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Menampilkan {{ $dataUmum->firstItem() ?? 0 }} sampai {{ $dataUmum->lastItem() ?? 0 }} dari {{ $dataUmum->total() }} data
                </div>
                <div>
                    {{ $dataUmum->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL TAMBAH --}}
<div class="modal fade" id="modalTambahUmum" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('jenis-aset.storeUmum') }}" method="POST" class="modal-content border-0 shadow">
            @csrf
            <input type="hidden" name="form_type" value="umum">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-primary"><i class="fas fa-folder-plus me-2"></i>Tambah Aset Umum</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted text-uppercase">Kode Umum</label>
                    <input type="text" name="kode_umum" class="form-control @error('kode_umum') is-invalid @enderror" placeholder="Contoh: BNG" value="{{ old('form_type') == 'umum' ? old('kode_umum') : '' }}">
                    @if(old('form_type') == 'umum') @error('kode_umum') <div class="invalid-feedback fw-bold">{{ $message }}</div> @enderror @endif
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted text-uppercase">Jenis Aset</label>
                    <input type="text" name="jenis_aset" class="form-control @error('jenis_aset') is-invalid @enderror" placeholder="Contoh: Bangunan" value="{{ old('form_type') == 'umum' ? old('jenis_aset') : '' }}">
                    @if(old('form_type') == 'umum') @error('jenis_aset') <div class="invalid-feedback fw-bold">{{ $message }}</div> @enderror @endif
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-danger rounded-3 px-4" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-success rounded-3 px-4"><i class="fas fa-save me-1"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

@foreach($dataUmum as $umum)
{{-- MODAL DETAIL --}}
<div class="modal fade" id="viewUmumModal{{ $umum->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-info-circle me-2"></i>Detail Jenis Aset Umum</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <table class="table table-borderless mb-0">
                    <tr>
                        <th width="35%" class="text-muted text-uppercase small align-middle">Kode Umum</th>
                        <td width="5%" class="align-middle">:</td>
                        <td><span class="badge bg-primary px-3 py-2 fs-6">{{ $umum->kode_umum }}</span></td>
                    </tr>
                    <tr>
                        <th class="text-muted text-uppercase small pt-3 align-middle">Jenis Aset</th>
                        <td class="pt-3 align-middle">:</td>
                        <td class="pt-3 fw-bold">{{ $umum->jenis_aset }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- MODAL EDIT --}}
<div class="modal fade" id="editUmumModal{{ $umum->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('jenis-aset.updateUmum', $umum->id) }}" method="POST" class="modal-content border-0 shadow" autocomplete="off">
            @csrf @method('PUT')
            <input type="hidden" name="form_type" value="edit_umum_{{ $umum->id }}"> 
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-warning"><i class="fas fa-edit me-2"></i>Edit Aset Umum</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted text-uppercase">Kode Umum</label>
                    <input type="text" name="kode_umum" class="form-control @error('kode_umum') is-invalid @enderror" value="{{ old('form_type') == 'edit_umum_'.$umum->id ? old('kode_umum') : $umum->kode_umum }}">
                    @if(old('form_type') == 'edit_umum_'.$umum->id) @error('kode_umum') <div class="invalid-feedback fw-bold">{{ $message }}</div> @enderror @endif
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted text-uppercase">Jenis Aset</label>
                    <input type="text" name="jenis_aset" class="form-control @error('jenis_aset') is-invalid @enderror" value="{{ old('form_type') == 'edit_umum_'.$umum->id ? old('jenis_aset') : $umum->jenis_aset }}">
                    @if(old('form_type') == 'edit_umum_'.$umum->id) @error('jenis_aset') <div class="invalid-feedback fw-bold">{{ $message }}</div> @enderror @endif
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-danger rounded-3 px-4" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-warning rounded-3 px-4 text-white fw-bold"><i class="fas fa-save me-1"></i> Update</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL HAPUS --}}
<div class="modal fade" id="deleteUmumModal{{ $umum->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-exclamation-triangle me-2"></i>Konfirmasi Hapus</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-4">
                <i class="fas fa-trash-alt fa-4x text-danger mb-3"></i>
                <h5 class="fw-bold text-dark">Hapus Data Ini?</h5>
                <p class="text-muted mb-0">Hapus <strong>{{ $umum->kode_umum }} - {{ $umum->jenis_aset }}</strong>?</p>
                <div class="alert alert-warning mt-3 mb-0 text-start small">
                    <i class="fas fa-info-circle me-1"></i><strong>Perhatian:</strong> Menghapus Induk Aset ini akan ikut menghapus <strong>semua data khusus</strong> di bawahnya!
                </div>
            </div>
            <div class="modal-footer bg-light border-0 justify-content-center">
                <form action="{{ route('jenis-aset.destroyUmum', $umum->id) }}" method="POST" class="d-flex gap-2">
                    @csrf @method('DELETE')
                    <button type="button" class="btn btn-warning rounded-3 px-3 text-white" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger rounded-3 px-3">Ya, Hapus</button>
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
            showConfirmButton: true, confirmButtonText: 'OK', confirmButtonColor: '#0d6efd', customClass: { popup: 'rounded-4 shadow' }
        };

        @if (session('success')) Swal.fire({ ...swalConfig, icon: 'success', title: 'Berhasil!', text: '{{ session('success') }}' }); @endif
        @if (session('error')) Swal.fire({ ...swalConfig, icon: 'error', title: 'Gagal!', text: '{{ session('error') }}' }); @endif
        @if (session('warning')) Swal.fire({ ...swalConfig, icon: 'warning', title: 'Perhatian!', text: '{{ session('warning') }}' }); @endif

        @if($errors->any())
            setTimeout(function() {
                const formType = "{{ old('form_type') }}";
                if (formType === 'umum') {
                    new bootstrap.Modal(document.getElementById('modalTambahUmum')).show();
                } else if (formType.startsWith('edit_umum_')) {
                    const id = formType.split('_')[2];
                    new bootstrap.Modal(document.getElementById('editUmumModal' + id)).show();
                }
            }, 200); 
        @endif

        const modalTambahEl = document.getElementById('modalTambahUmum');
        if (modalTambahEl) {
            modalTambahEl.addEventListener('hidden.bs.modal', function () {
                const form = this.querySelector('form');
                if (form) {
                    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                    form.reset(); 
                }
            });
        }

        document.querySelectorAll('[id^="editUmumModal"]').forEach(function(modalEditEl) {
            modalEditEl.addEventListener('hidden.bs.modal', function () {
                const form = this.querySelector('form');
                if (form) {
                    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                }
            });
        });
    });
</script>
@endpush