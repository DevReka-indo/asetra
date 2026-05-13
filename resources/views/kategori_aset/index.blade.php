@extends('layouts.app')

@section('title', 'Kelola ' . $title)

@section('content')
<div class="container-fluid px-1 py-0 mt-0">
    <div class="mb-4">
        <h3 class="fw-bold mb-0">Kelola {{ $title }}</h3>
    </div>

    {{-- MODAL IMPORT --}}
    <div class="modal fade" id="modalImportKategori" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('kategori-aset.import') }}" method="POST" enctype="multipart/form-data" class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                @csrf
                <input type="hidden" name="type" value="{{ $type }}">
                <div class="modal-header border-0 pt-4 px-4 pb-3" style="background-color: #f39c12;">
                    <h5 class="modal-title fw-bold text-white">
                        <i class="fas fa-file-import me-2"></i> Import {{ $title }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 bg-light">
                    <div class="alert alert-info border-0 shadow-sm rounded-3 mb-4">
                        <h6 class="fw-bold mb-2"><i class="fas fa-info-circle me-1"></i> Format Excel:</h6>
                        <ul class="mb-2 small">
                            <li>Gunakan baris pertama sebagai Judul (Heading Row).</li>
                            <li>Kolom A: <strong>kode</strong> (e.g. {{ $type == 'aset_tetap' ? '101' : '201' }})</li>
                            <li>Kolom B: <strong>nama</strong> (e.g. {{ $type == 'aset_tetap' ? 'Tanah' : 'Lemari' }})</li>
                        </ul>
                        <a href="{{ route('kategori-aset.template') }}" class="btn btn-sm btn-outline-info w-100 rounded-pill fw-bold">
                            <i class="fas fa-download me-1"></i> Download Template Excel
                        </a>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-bold small" style="color: #253070;">PILIH FILE EXCEL (.xlsx, .xls, .csv)</label>
                        <div class="input-group input-group-lg shadow-sm rounded-3">
                            <input type="file" name="file" class="form-control fs-6" required accept=".xlsx, .xls, .csv">
                        </div>
                        <small class="text-muted mt-2 d-block">Ukuran maksimal file: 2MB</small>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0 pt-3 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold shadow-sm border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning text-dark rounded-pill px-4 fw-bold shadow-sm">
                        <i class="fas fa-upload me-1"></i> Upload & Import
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- FILTER --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ URL::current() }}" class="row g-2 align-items-end">
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
                        <input type="text" name="search" class="form-control border-0 shadow-none bg-transparent" placeholder="Cari kode atau nama klasifikasi..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-auto ms-auto">
                    <div class="d-flex gap-2">
                        {{-- Import --}}
                        <button type="button" class="btn btn-warning px-4 rounded-3 d-flex align-items-center text-dark" title="Import Data" data-bs-toggle="modal" data-bs-target="#modalImportKategori">
                            <i class="fas fa-file-import me-1"></i> Import
                        </button>

                        {{-- Tombol Tambah --}}
                        <button type="button" class="btn btn-primary px-4 rounded-3 d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#modalTambahKategori">
                            <i class="fas fa-plus me-1"></i> Tambah {{ $title }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- TABEL KLASIFIKASI ASET --}}
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="60" class="text-center">No</th>
                            <th width="150">Kode</th>
                            <th>Nama Kategori</th>
                            <th width="120" class="text-center">Tipe</th>
                            <th width="150" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data as $i => $item)
                            <tr>
                                <td class="text-center">{{ $data->firstItem() + $i }}</td>
                                <td class="fw-bold text-primary">{{ $item->kode }}</td>
                                <td>{{ $item->nama }}</td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $item->tipe_badge_color }}">
                                        {{ $item->tipe_label }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center align-items-center gap-2">
                                        <button type="button" class="btn btn-warning btn-sm rounded-circle text-white border-0"
                                            style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"
                                            title="Edit" data-bs-toggle="modal" data-bs-target="#editModal{{ $item->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm rounded-circle text-white border-0" 
                                            style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"
                                            title="Hapus" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $item->id }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <i class="fas fa-info-circle mb-2 d-block fa-2x"></i>
                                    Belum ada data {{ $title }}
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

{{-- MODAL TAMBAH --}}
<div class="modal fade" id="modalTambahKategori" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('kategori-aset.store') }}" method="POST" class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" autocomplete="off">
            @csrf
            <input type="hidden" name="form_type" value="tambah">

            <div class="modal-header border-0 pt-4 px-4 pb-3" style="background-color: #253070;">
                <h5 class="modal-title fw-bold text-white">
                    <i class="fas fa-plus-circle me-2"></i> Tambah {{ $title }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4 bg-light">
                <div class="mb-4">
                    <label class="form-label fw-bold small" style="color: #253070;">KODE <span class="text-danger">*</span></label>
                    <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-barcode"></i></span>
                        <input type="text" name="kode" class="form-control border-start-0 fs-6 @error('kode') is-invalid @enderror" placeholder="Contoh: {{ $type == 'aset_tetap' ? '101' : '201' }}" value="{{ old('form_type') == 'tambah' ? old('kode') : '' }}">
                    </div>
                    <small class="text-muted mt-1 d-block">Gunakan awalan <strong>{{ $type == 'aset_tetap' ? '1' : '2' }}</strong> untuk {{ $title }}</small>
                    @if(old('form_type') == 'tambah') @error('kode') <div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div> @enderror @endif
                </div>

                <div class="mb-2">
                    <label class="form-label fw-bold small" style="color: #253070;">NAMA KLASIFIKASI <span class="text-danger">*</span></label>
                    <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-tag"></i></span>
                        <input type="text" name="nama" class="form-control border-start-0 fs-6 @error('nama') is-invalid @enderror" placeholder="Contoh: {{ $type == 'aset_tetap' ? 'Tanah' : 'Lemari' }}" value="{{ old('form_type') == 'tambah' ? old('nama') : '' }}">
                    </div>
                    @if(old('form_type') == 'tambah') @error('nama') <div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div> @enderror @endif
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

@foreach($data as $item)
{{-- MODAL EDIT --}}
<div class="modal fade" id="editModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('kategori-aset.update', $item->id) }}" method="POST" class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" autocomplete="off">
            @csrf @method('PUT')
            <input type="hidden" name="form_type" value="edit_{{ $item->id }}"> 
            
            <div class="modal-header border-0 pt-4 px-4 pb-3" style="background-color: #253070;">
                <h5 class="modal-title fw-bold text-white">
                    <i class="fas fa-edit me-2"></i> Edit {{ $title }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4 bg-light">
                <div class="mb-4">
                    <label class="form-label fw-bold small" style="color: #253070;">KODE <span class="text-danger">*</span></label>
                    <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-barcode"></i></span>
                        <input type="text" name="kode" class="form-control border-start-0 fs-6 @error('kode') is-invalid @enderror" value="{{ old('form_type') == 'edit_'.$item->id ? old('kode') : $item->kode }}">
                    </div>
                    @if(old('form_type') == 'edit_'.$item->id) @error('kode') <div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div> @enderror @endif
                </div>

                <div class="mb-2">
                    <label class="form-label fw-bold small" style="color: #253070;">NAMA KLASIFIKASI <span class="text-danger">*</span></label>
                    <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-tag"></i></span>
                        <input type="text" name="nama" class="form-control border-start-0 fs-6 @error('nama') is-invalid @enderror" value="{{ old('form_type') == 'edit_'.$item->id ? old('nama') : $item->nama }}">
                    </div>
                    @if(old('form_type') == 'edit_'.$item->id) @error('nama') <div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div> @enderror @endif
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
<div class="modal fade" id="deleteModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-body p-5 text-center bg-light">
                <div class="d-inline-flex align-items-center justify-content-center rounded-4 mb-4" style="width: 80px; height: 80px; background-color: #f1f3f5;">
                    <i class="fas fa-exclamation-triangle fa-3x text-danger"></i>
                </div>
                <h4 class="fw-bold text-dark mb-2">Konfirmasi Hapus</h4>
                <p class="text-muted mb-3" style="font-size: 1rem;">
                    Hapus klasifikasi <br><strong class="text-danger fs-5">{{ $item->kode }} - {{ $item->nama }}</strong>?
                </p>

                <div class="d-flex justify-content-center gap-3">
                    <form action="{{ route('kategori-aset.destroy', $item->id) }}" method="POST" class="w-100 d-flex justify-content-center gap-3">
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

        @if($errors->any())
            setTimeout(function() {
                const formType = "{{ old('form_type') }}";
                if (formType === 'tambah') {
                    new bootstrap.Modal(document.getElementById('modalTambahKategori')).show();
                } else if (formType.startsWith('edit_')) {
                    const id = formType.split('_')[1];
                    new bootstrap.Modal(document.getElementById('editModal' + id)).show();
                }
            }, 200); 
        @endif
    });
</script>
@endpush
