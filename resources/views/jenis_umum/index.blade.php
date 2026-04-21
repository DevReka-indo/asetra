@extends('layouts.app')

@section('title', 'Kelola Jenis Aset Umum')

@section('content')
<div class="container-fluid px-1 py-0 mt-0">
    <div class="mb-4">
        <h3 class="fw-bold mb-0">Kelola Jenis Aset Tetap Umum</h3>
    </div>

    {{-- FILTER --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('jenis-umum.index') }}" class="row g-2 align-items-end">
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
                        <input type="text" name="search" class="form-control border-0 shadow-none bg-transparent" placeholder="Cari kode atau jenis aset..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-auto ms-auto">
                    <div class="d-flex gap-2">
                        {{-- Import --}}
                        <button type="button" class="btn btn-warning px-4 rounded-3 d-flex align-items-center text-dark" title="Import Data">
                            <i class="fas fa-file-import me-1"></i> Import
                        </button>

                        {{-- Export --}}
                        <button type="button" class="btn btn-success px-4 rounded-3 d-flex align-items-center text-white" title="Export Data">
                            <i class="fas fa-file-excel me-1"></i> Export
                        </button>

                        {{-- Tombol Tambah --}}
                        <button type="button" class="btn btn-primary px-4 rounded-3 d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#modalTambahUmum">
                            <i class="fas fa-plus me-1"></i> Tambah Aset Umum
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- TABEL JENIS ASET TETAP UMUM   --}}
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="60" class="text-center">No</th>
                            <th>Kode Umum</th>
                            <th>Jenis Aset Tetap</th>
                            <th width="150" class="text-center">Aksi</th>
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
        <form action="{{ route('jenis-aset.storeUmum') }}" method="POST" class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" autocomplete="off">
            @csrf
            <input type="hidden" name="form_type" value="umum">

            <div class="modal-header border-0 pt-4 px-4 pb-3" style="background-color: #253070;">
                <h5 class="modal-title fw-bold text-white">
                    <i class="fas fa-plus-circle me-2"></i> Tambah Aset Umum
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4 bg-light">
                <div class="mb-4">
                    <label class="form-label fw-bold small flex-grow-1" style="color: #253070;">KODE UMUM <span class="text-danger">*</span></label>
                    <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-barcode"></i></span>
                        <input type="text" name="kode_umum" class="form-control border-start-0 fs-6 @error('kode_umum') is-invalid @enderror" placeholder="Contoh: BNG" value="{{ old('form_type') == 'umum' ? old('kode_umum') : '' }}">
                    </div>
                    @if(old('form_type') == 'umum') @error('kode_umum') <div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div> @enderror @endif
                </div>

                <div class="mb-2">
                    <label class="form-label fw-bold small flex-grow-1" style="color: #253070;">JENIS ASET <span class="text-danger">*</span></label>
                    <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-cubes"></i></span>
                        <input type="text" name="jenis_aset" class="form-control border-start-0 fs-6 @error('jenis_aset') is-invalid @enderror" placeholder="Contoh: Bangunan" value="{{ old('form_type') == 'umum' ? old('jenis_aset') : '' }}">
                    </div>
                    @if(old('form_type') == 'umum') @error('jenis_aset') <div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div> @enderror @endif
                </div>
            </div>

            <div class="modal-footer bg-light border-top-0 pt-3 pb-4 px-4">
                <button type="button" class="btn btn-light rounded-pill px-4 fw-bold shadow-sm border" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn text-white rounded-pill px-4 fw-bold shadow-sm" style="background-color: #253070;">
                    <i class="fas fa-save me-1"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

@foreach($dataUmum as $umum)
{{-- MODAL DETAIL --}}
<div class="modal fade" id="viewUmumModal{{ $umum->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 pt-4 px-4 pb-3" style="background-color: #253070;">
                <h5 class="modal-title fw-bold text-white">
                    <i class="fas fa-info-circle me-2"></i> Detail Jenis Aset Umum
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <div class="bg-white rounded-4 p-4 shadow-sm border-0">
                    <div class="row align-items-center mb-3">
                        <div class="col-5 text-muted small text-uppercase fw-bold"><i class="fas fa-barcode me-2 text-primary"></i>Kode Umum</div>
                        <div class="col-7">
                            <span class="badge bg-primary px-3 py-2 fs-6 shadow-sm rounded-pill">{{ $umum->kode_umum }}</span>
                        </div>
                    </div>
                    <hr class="text-muted opacity-25">
                    <div class="row align-items-center">
                        <div class="col-5 text-muted small text-uppercase fw-bold"><i class="fas fa-cubes me-2 text-primary"></i>Jenis Aset</div>
                        <div class="col-7 fw-bold text-dark fs-6">{{ $umum->jenis_aset }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL EDIT --}}
<div class="modal fade" id="editUmumModal{{ $umum->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('jenis-aset.updateUmum', $umum->id) }}" method="POST" class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" autocomplete="off">
            @csrf @method('PUT')
            <input type="hidden" name="form_type" value="edit_umum_{{ $umum->id }}"> 
            
            <div class="modal-header border-0 pt-4 px-4 pb-3" style="background-color: #253070;">
                <h5 class="modal-title fw-bold text-white">
                    <i class="fas fa-edit me-2"></i> Edit Aset Umum
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4 bg-light">
                <div class="mb-4">
                    <label class="form-label fw-bold small flex-grow-1" style="color: #253070;">KODE UMUM <span class="text-danger">*</span></label>
                    <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-barcode"></i></span>
                        <input type="text" name="kode_umum" class="form-control border-start-0 fs-6 @error('kode_umum') is-invalid @enderror" value="{{ old('form_type') == 'edit_umum_'.$umum->id ? old('kode_umum') : $umum->kode_umum }}">
                    </div>
                    @if(old('form_type') == 'edit_umum_'.$umum->id) @error('kode_umum') <div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div> @enderror @endif
                </div>

                <div class="mb-2">
                    <label class="form-label fw-bold small flex-grow-1" style="color: #253070;">JENIS ASET <span class="text-danger">*</span></label>
                    <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-cubes"></i></span>
                        <input type="text" name="jenis_aset" class="form-control border-start-0 fs-6 @error('jenis_aset') is-invalid @enderror" value="{{ old('form_type') == 'edit_umum_'.$umum->id ? old('jenis_aset') : $umum->jenis_aset }}">
                    </div>
                    @if(old('form_type') == 'edit_umum_'.$umum->id) @error('jenis_aset') <div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div> @enderror @endif
                </div>
            </div>

            <div class="modal-footer bg-light border-top-0 pt-3 pb-4 px-4">
                <button type="button" class="btn btn-light rounded-pill px-4 fw-bold shadow-sm border" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn text-white rounded-pill px-4 fw-bold shadow-sm" style="background-color: #253070;">
                    <i class="fas fa-save me-1"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL HAPUS --}}
<div class="modal fade" id="deleteUmumModal{{ $umum->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-body p-5 text-center bg-light">
                <div class="d-inline-flex align-items-center justify-content-center rounded-4 mb-4" style="width: 80px; height: 80px; background-color: #f1f3f5;">
                    <i class="fas fa-exclamation-triangle fa-3x text-danger"></i>
                </div>
                <h4 class="fw-bold text-dark mb-2">Konfirmasi Hapus</h4>
                <p class="text-muted mb-3" style="font-size: 1rem;">
                    Hapus data <strong class="text-danger fs-5">{{ $umum->kode_umum }} - {{ $umum->jenis_aset }}</strong>?
                </p>
                
                <div class="alert alert-warning mb-4 text-start small border-0 shadow-sm rounded-3">
                    <i class="fas fa-info-circle me-1"></i><strong>Perhatian:</strong> Menghapus Aset Umum ini akan ikut menghapus <strong>semua data aset khusus</strong> di bawahnya!
                </div>

                <div class="d-flex justify-content-center gap-3">
                    <form action="{{ route('jenis-aset.destroyUmum', $umum->id) }}" method="POST" class="w-100 d-flex justify-content-center gap-3">
                        @csrf 
                        @method('DELETE')
                        <button type="button" class="btn btn-light rounded-pill fw-bold py-2 shadow-sm border" style="width: 120px;" data-bs-dismiss="modal">Batalkan</button>
                        <button type="submit" class="btn btn-danger rounded-pill fw-bold py-2 shadow-sm" style="width: 140px;">Ya, Hapus Data</button>
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