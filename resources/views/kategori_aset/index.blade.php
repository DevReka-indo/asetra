@extends('layouts.app')

@section('title', 'Kelola Kategori Aset')

@section('content')
<div class="container-fluid px-1 py-0 mt-0">
    <div class="mb-4">
        <h3 class="fw-bold mb-0">Kelola Kategori Aset</h3>
    </div>

    {{-- FILTER --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('kategori-aset.index') }}" class="row g-2 align-items-end">
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
                        <input type="text" name="search" class="form-control border-0 shadow-none bg-transparent" placeholder="Cari kode atau nama kategori..." value="{{ request('search') }}">
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
                        <button type="button" class="btn btn-primary px-4 rounded-3 d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#modalTambahKategori">
                            <i class="fas fa-plus me-1"></i> Tambah Kategori
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- TABEL KATEGORI ASET --}}
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="60" class="text-center">No</th>
                            <th>Kode Kategori</th>
                            <th>Nama Kategori</th>
                            <th width="150" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dataKategori as $i => $kategori)
                            <tr>
                                <td class="text-center">{{ $dataKategori->firstItem() + $i }}</td>
                                <td class="fw-bold text-primary">{{ $kategori->kode }}</td>
                                <td>{{ $kategori->nama_kategori }}</td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center align-items-center gap-2">
                                        <button type="button" class="btn btn-info btn-sm rounded-circle text-white border-0" 
                                            style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"
                                            title="Lihat" data-bs-toggle="modal" data-bs-target="#viewKategoriModal{{ $kategori->kategori_id }}">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-warning btn-sm rounded-circle text-white border-0"
                                            style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"
                                            title="Edit" data-bs-toggle="modal" data-bs-target="#editKategoriModal{{ $kategori->kategori_id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm rounded-circle text-white border-0" 
                                            style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"
                                            title="Hapus" data-bs-toggle="modal" data-bs-target="#deleteKategoriModal{{ $kategori->kategori_id }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">
                                    <i class="fas fa-info-circle mb-2 d-block fa-2x"></i>
                                    Belum ada data kategori aset
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3 d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Menampilkan {{ $dataKategori->firstItem() ?? 0 }} sampai {{ $dataKategori->lastItem() ?? 0 }} dari {{ $dataKategori->total() }} data
                </div>
                <div>
                    {{ $dataKategori->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL TAMBAH --}}
<div class="modal fade" id="modalTambahKategori" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('kategori-aset.store') }}" method="POST" class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" autocomplete="off">
            @csrf
            <input type="hidden" name="form_type" value="kategori">

            <div class="modal-header border-0 pt-4 px-4 pb-3" style="background-color: #253070;">
                <h5 class="modal-title fw-bold text-white">
                    <i class="fas fa-plus-circle me-2"></i> Tambah Kategori Aset
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4 bg-light">
                <div class="mb-4">
                    <label class="form-label fw-bold small flex-grow-1" style="color: #253070;">KODE KATEGORI <span class="text-danger">*</span></label>
                    <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-barcode"></i></span>
                        <input type="text" name="kode" class="form-control border-start-0 fs-6 @error('kode') is-invalid @enderror" placeholder="Contoh: ELK" value="{{ old('form_type') == 'kategori' ? old('kode') : '' }}">
                    </div>
                    @if(old('form_type') == 'kategori') @error('kode') <div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div> @enderror @endif
                </div>

                <div class="mb-2">
                    <label class="form-label fw-bold small flex-grow-1" style="color: #253070;">NAMA KATEGORI <span class="text-danger">*</span></label>
                    <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-tag"></i></span>
                        <input type="text" name="nama_kategori" class="form-control border-start-0 fs-6 @error('nama_kategori') is-invalid @enderror" placeholder="Contoh: Elektronik" value="{{ old('form_type') == 'kategori' ? old('nama_kategori') : '' }}">
                    </div>
                    @if(old('form_type') == 'kategori') @error('nama_kategori') <div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div> @enderror @endif
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

@foreach($dataKategori as $kategori)
{{-- MODAL DETAIL --}}
<div class="modal fade" id="viewKategoriModal{{ $kategori->kategori_id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 pt-4 px-4 pb-3" style="background-color: #253070;">
                <h5 class="modal-title fw-bold text-white">
                    <i class="fas fa-info-circle me-2"></i> Detail Kategori Aset
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <div class="bg-white rounded-4 p-4 shadow-sm border-0">
                    <div class="row align-items-center mb-3">
                        <div class="col-5 text-muted small text-uppercase fw-bold"><i class="fas fa-barcode me-2 text-primary"></i>Kode Kategori</div>
                        <div class="col-7">
                            <span class="badge bg-primary px-3 py-2 fs-6 shadow-sm rounded-pill">{{ $kategori->kode }}</span>
                        </div>
                    </div>
                    <hr class="text-muted opacity-25">
                    <div class="row align-items-center">
                        <div class="col-5 text-muted small text-uppercase fw-bold"><i class="fas fa-tag me-2 text-primary"></i>Nama Kategori</div>
                        <div class="col-7 fw-bold text-dark fs-6">{{ $kategori->nama_kategori }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL EDIT --}}
<div class="modal fade" id="editKategoriModal{{ $kategori->kategori_id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('kategori-aset.update', $kategori->kategori_id) }}" method="POST" class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" autocomplete="off">
            @csrf @method('PUT')
            <input type="hidden" name="form_type" value="edit_kategori_{{ $kategori->kategori_id }}"> 
            
            <div class="modal-header border-0 pt-4 px-4 pb-3" style="background-color: #253070;">
                <h5 class="modal-title fw-bold text-white">
                    <i class="fas fa-edit me-2"></i> Edit Kategori Aset
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4 bg-light">
                <div class="mb-4">
                    <label class="form-label fw-bold small flex-grow-1" style="color: #253070;">KODE KATEGORI <span class="text-danger">*</span></label>
                    <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-barcode"></i></span>
                        <input type="text" name="kode" class="form-control border-start-0 fs-6 @error('kode') is-invalid @enderror" value="{{ old('form_type') == 'edit_kategori_'.$kategori->kategori_id ? old('kode') : $kategori->kode }}">
                    </div>
                    @if(old('form_type') == 'edit_kategori_'.$kategori->kategori_id) @error('kode') <div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div> @enderror @endif
                </div>

                <div class="mb-2">
                    <label class="form-label fw-bold small flex-grow-1" style="color: #253070;">NAMA KATEGORI <span class="text-danger">*</span></label>
                    <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-tag"></i></span>
                        <input type="text" name="nama_kategori" class="form-control border-start-0 fs-6 @error('nama_kategori') is-invalid @enderror" value="{{ old('form_type') == 'edit_kategori_'.$kategori->kategori_id ? old('nama_kategori') : $kategori->nama_kategori }}">
                    </div>
                    @if(old('form_type') == 'edit_kategori_'.$kategori->kategori_id) @error('nama_kategori') <div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div> @enderror @endif
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
<div class="modal fade" id="deleteKategoriModal{{ $kategori->kategori_id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-body p-5 text-center bg-light">
                <div class="d-inline-flex align-items-center justify-content-center rounded-4 mb-4" style="width: 80px; height: 80px; background-color: #f1f3f5;">
                    <i class="fas fa-exclamation-triangle fa-3x text-danger"></i>
                </div>
                <h4 class="fw-bold text-dark mb-2">Konfirmasi Hapus</h4>
                <p class="text-muted mb-4" style="font-size: 1rem;">
                    Hapus data <br><strong class="text-danger fs-5">{{ $kategori->kode }} - {{ $kategori->nama_kategori }}</strong>?
                </p>
                <div class="d-flex justify-content-center gap-3">
                    <form action="{{ route('kategori-aset.destroy', $kategori->kategori_id) }}" method="POST" class="w-100 d-flex justify-content-center gap-3">
                        @csrf 
                        @method('DELETE')
                        <button type="button" class="btn btn-light rounded-pill fw-bold py-2 shadow-sm border" style="width: 120px;" data-bs-dismiss="modal">Batalkan</button>
                        <button type="submit" class="btn btn-danger rounded-pill fw-bold py-2 shadow-sm" style="width: 140px;">Ya, Hapus</button>
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
                if (formType === 'kategori') {
                    new bootstrap.Modal(document.getElementById('modalTambahKategori')).show();
                } else if (formType.startsWith('edit_kategori_')) {
                    const id = formType.split('_')[2];
                    new bootstrap.Modal(document.getElementById('editKategoriModal' + id)).show();
                }
            }, 200); 
        @endif

        const modalTambahEl = document.getElementById('modalTambahKategori');
        if (modalTambahEl) {
            modalTambahEl.addEventListener('hidden.bs.modal', function () {
                const form = this.querySelector('form');
                if (form) {
                    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                    form.reset(); 
                }
            });
        }

        document.querySelectorAll('[id^="editKategoriModal"]').forEach(function(modalEditEl) {
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
