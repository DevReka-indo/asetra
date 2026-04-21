@extends('layouts.app')

@section('title', 'Kelola Jenis Aset Khusus')

@section('content')
<div class="container-fluid px-1 py-0 mt-0">
    <div class="mb-4">
        <h3 class="fw-bold mb-0">Kelola Jenis Aset Tetap Khusus</h3>
    </div>

    {{-- FILTER --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('jenis-khusus.index') }}" class="row g-2 align-items-end">
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
                {{-- Filter Jenis Umum --}}
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-muted text-uppercase" style="font-size: 0.7rem;">Filter Aset Umum</label>
                    <select name="jenis_aset_umum_id" class="form-select form-select-sm rounded-3 w-100" onchange="this.form.submit()">
                        <option value="">Semua Jenis Aset Umum</option>
                        @foreach($listUmum as $umum)
                            <option value="{{ $umum->id }}" {{ request('jenis_aset_umum_id') == $umum->id ? 'selected' : '' }}>
                                {{ $umum->kode_umum }} - {{ $umum->jenis_aset }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Pencarian --}}
                <div class="col-md-2">
                    <label class="form-label fw-bold small text-muted text-uppercase" style="font-size: 0.7rem;">Pencarian</label>
                    <div class="input-group input-group-sm input-group-focus rounded-3">
                        <span class="input-group-text bg-white border-0 text-muted"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control border-0 shadow-none bg-transparent" placeholder="Cari kode atau aset..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-auto ms-auto">
                    <div class="d-flex gap-2">
                        {{-- Reset --}}
                        <a href="{{ route('jenis-khusus.index') }}" class="btn px-4 rounded-3 d-flex align-items-center text-white" style="background-color: #1b53a7; border-color: #1b53a7;" title="Reset Filter">
                            <i class="fas fa-undo me-1"></i> Reset
                        </a>

                        {{-- Import --}}
                        <button type="button" class="btn btn-warning px-4 rounded-3 d-flex align-items-center text-dark" title="Import Data">
                            <i class="fas fa-file-import me-1"></i> Import
                        </button>

                        {{-- Export --}}
                        <button type="button" class="btn btn-success px-4 rounded-3 d-flex align-items-center text-white" title="Export Data">
                            <i class="fas fa-file-excel me-1"></i> Export
                        </button>

                        {{-- Tombol Tambah --}}
                        <button type="button" class="btn btn-primary px-4 rounded-3 d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#modalTambahKhusus">
                            <i class="fas fa-plus me-1"></i> Tambah Aset Khusus
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- TABEL JENIS ASET TETAP KHUSUS  --}}
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="5%" class="text-center">No</th>
                            <th width="15%">Kode Umum</th>
                            <th width="15%">Kode Khusus</th>
                            <th width="20%">Jenis Aset Khusus</th>
                            <th width="15%">Kode Gabungan</th>
                            <th width="15%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dataKhusus as $i => $khusus)
                            <tr>
                                <td class="text-center">{{ $dataKhusus->firstItem() + $i }}</td>
                                <td>
                                    @if ($khusus->jenisAsetUmum)
                                        @php
                                            $colors = ['#0d6efd', '#198754', '#dc3545', '#ffc107', '#6c757d', '#6f42c1', '#fd7e14', '#0dcaf0', '#20c997', '#d63384', '#84cc16', '#6610f2', '#8b5e3c', '#808000', '#1b3a57', '#800000', '#334155'];
                                            $colorIndex = ($khusus->jenisAsetUmum->id - 1) % count($colors);
                                            $bgColor = $colors[$colorIndex];
                                        @endphp
                                        <span class="badge text-white" style="background-color: {{ $bgColor }};">
                                            {{ $khusus->jenisAsetUmum->kode_umum }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $khusus->kode_khusus }}</td>
                                <td>{{ $khusus->jenis_aset }}</td>
                                <td><span class="badge text-white p-2 shadow-sm" style="background-color: #253070;">{{ $khusus->full_kode }}</span></td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center align-items-center gap-2">
                                        <button type="button" class="btn btn-info btn-sm rounded-circle text-white border-0" 
                                            style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"
                                            title="Lihat" data-bs-toggle="modal" data-bs-target="#viewKhususModal{{ $khusus->id }}">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-warning btn-sm rounded-circle text-white border-0"
                                            style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"
                                            title="Edit" data-bs-toggle="modal" data-bs-target="#editKhususModal{{ $khusus->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm rounded-circle text-white border-0" 
                                            style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"
                                            title="Hapus" data-bs-toggle="modal" data-bs-target="#deleteKhususModal{{ $khusus->id }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="fas fa-info-circle mb-2 d-block fa-2x"></i>
                                    Belum ada data jenis aset tetap khusus
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3 d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Menampilkan {{ $dataKhusus->firstItem() ?? 0 }} sampai {{ $dataKhusus->lastItem() ?? 0 }} dari {{ $dataKhusus->total() }} data
                </div>
                <div>
                    {{ $dataKhusus->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>


{{-- MODAL TAMBAH --}}
<div class="modal fade" id="modalTambahKhusus" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('jenis-aset.storeKhusus') }}" method="POST" class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" autocomplete="off">
            @csrf
            <input type="hidden" name="form_type" value="khusus"> 
            
            <div class="modal-header border-0 pt-4 px-4 pb-3" style="background-color: #253070;">
                <h5 class="modal-title fw-bold text-white">
                    <i class="fas fa-plus-circle me-2"></i> Tambah Aset Khusus
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4 bg-light">
                <div class="mb-4">
                    <label class="form-label fw-bold small flex-grow-1" style="color: #253070;">JENIS ASET UMUM <span class="text-danger">*</span></label>
                    <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus align-items-center bg-white border">
                        <span class="input-group-text bg-white border-0 text-muted"><i class="fas fa-sitemap"></i></span>
                        <select name="jenis_aset_umum_id" class="form-select border-0 shadow-none fs-6 @error('jenis_aset_umum_id') is-invalid @enderror">
                            <option value="">-- Pilih Aset Umum --</option>
                            @foreach($listUmum as $item)
                                <option value="{{ $item->id }}" {{ old('form_type') == 'khusus' && old('jenis_aset_umum_id') == $item->id ? 'selected' : '' }}>
                                    {{ $item->jenis_aset }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @if(old('form_type') == 'khusus') @error('jenis_aset_umum_id') <div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div> @enderror @endif
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold small flex-grow-1" style="color: #253070;">KODE KHUSUS <span class="text-danger">*</span></label>
                    <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-barcode"></i></span>
                        <input type="text" name="kode_khusus" class="form-control border-start-0 fs-6 @error('kode_khusus') is-invalid @enderror" placeholder="Contoh: A" value="{{ old('form_type') == 'khusus' ? old('kode_khusus') : '' }}">
                    </div>
                    @if(old('form_type') == 'khusus') @error('kode_khusus') <div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div> @enderror @endif
                </div>

                <div class="mb-2">
                    <label class="form-label fw-bold small flex-grow-1" style="color: #253070;">JENIS ASET KHUSUS <span class="text-danger">*</span></label>
                    <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-cube"></i></span>
                        <input type="text" name="jenis_aset" class="form-control border-start-0 fs-6 @error('jenis_aset') is-invalid @enderror" placeholder="Contoh: Gedung Sewa" value="{{ old('form_type') == 'khusus' ? old('jenis_aset') : '' }}">
                    </div>
                    @if(old('form_type') == 'khusus') @error('jenis_aset') <div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div> @enderror @endif
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

@foreach($dataKhusus as $khusus)
{{-- MODAL DETAIL --}}
<div class="modal fade" id="viewKhususModal{{ $khusus->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 pt-4 px-4 pb-3" style="background-color: #253070;">
                <h5 class="modal-title fw-bold text-white">
                    <i class="fas fa-info-circle me-2"></i> Detail Aset Khusus
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <div class="bg-white rounded-4 p-4 shadow-sm border-0">
                    <div class="row align-items-center mb-3">
                        <div class="col-5 text-muted small text-uppercase fw-bold"><i class="fas fa-sitemap me-2 text-primary"></i>Jenis Aset Umum</div>
                        <div class="col-7">
                            @if($khusus->jenisAsetUmum)
                                <span class="fw-bold text-dark">{{ $khusus->jenisAsetUmum->jenis_aset }}</span>
                                <span class="badge bg-primary px-3 py-2 fs-6 shadow-sm rounded-pill">{{ $khusus->jenisAsetUmum->kode_umum }}</span>
                            @else
                                <span class="badge bg-danger rounded-pill">Jenis Aset Umum Dihapus</span>
                            @endif
                        </div>
                    </div>
                    <hr class="text-muted opacity-25">
                    <div class="row align-items-center mb-3">
                        <div class="col-5 text-muted small text-uppercase fw-bold"><i class="fas fa-barcode me-2 text-primary"></i>Kode Khusus</div>
                        <div class="col-7">
                            <span class="badge bg-info text-white px-3 py-2 fs-6 shadow-sm rounded-pill">{{ $khusus->kode_khusus }}</span>
                        </div>
                    </div>
                    <hr class="text-muted opacity-25">
                    <div class="row align-items-center mb-3">
                        <div class="col-5 text-muted small text-uppercase fw-bold"><i class="fas fa-cube me-2 text-primary"></i>Jenis Aset</div>
                        <div class="col-7 fw-bold text-dark fs-6">{{ $khusus->jenis_aset }}</div>
                    </div>
                    <hr class="text-muted opacity-25">
                    <div class="row align-items-center">
                        <div class="col-5 text-muted small text-uppercase fw-bold"><i class="fas fa-link me-2 text-primary"></i>Kode Gabungan</div>
                        <div class="col-7">
                            <span class="badge bg-navy px-3 py-2 fs-5 shadow-sm rounded-pill">{{ $khusus->full_kode }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL EDIT --}}
<div class="modal fade" id="editKhususModal{{ $khusus->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('jenis-aset.updateKhusus', $khusus->id) }}" method="POST" class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" autocomplete="off">
            @csrf @method('PUT')
            <input type="hidden" name="form_type" value="edit_khusus_{{ $khusus->id }}"> 
            
            <div class="modal-header border-0 pt-4 px-4 pb-3" style="background-color: #253070;">
                <h5 class="modal-title fw-bold text-white">
                    <i class="fas fa-edit me-2"></i> Edit Aset Khusus
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4 bg-light">
                <div class="mb-4">
                    <label class="form-label fw-bold small flex-grow-1" style="color: #253070;"> JENIS ASET UMUM <span class="text-danger">*</span></label>
                    <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus align-items-center bg-white border">
                        <span class="input-group-text bg-white border-0 text-muted"><i class="fas fa-sitemap"></i></span>
                        <select name="jenis_aset_umum_id" class="form-select border-0 shadow-none fs-6 @error('jenis_aset_umum_id') is-invalid @enderror">
                            <option value="">-- Pilih Jenis Aset Umum --</option>
                            @foreach($listUmum as $item)
                                @php
                                    $isSelected = old('form_type') == 'edit_khusus_'.$khusus->id ? (old('jenis_aset_umum_id') == $item->id) : ($khusus->jenis_aset_umum_id == $item->id);
                                @endphp
                                <option value="{{ $item->id }}" {{ $isSelected ? 'selected' : '' }}>
                                    {{ $item->jenis_aset }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @if(old('form_type') == 'edit_khusus_'.$khusus->id) @error('jenis_aset_umum_id') <div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div> @enderror @endif
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold small flex-grow-1" style="color: #253070;">KODE KHUSUS <span class="text-danger">*</span></label>
                    <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-barcode"></i></span>
                        <input type="text" name="kode_khusus" class="form-control border-start-0 fs-6 @error('kode_khusus') is-invalid @enderror" value="{{ old('form_type') == 'edit_khusus_'.$khusus->id ? old('kode_khusus') : $khusus->kode_khusus }}">
                    </div>
                    @if(old('form_type') == 'edit_khusus_'.$khusus->id) @error('kode_khusus') <div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div> @enderror @endif
                </div>

                <div class="mb-2">
                    <label class="form-label fw-bold small flex-grow-1" style="color: #253070;">JENIS ASET KHUSUS <span class="text-danger">*</span></label>
                    <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-cube"></i></span>
                        <input type="text" name="jenis_aset" class="form-control border-start-0 fs-6 @error('jenis_aset') is-invalid @enderror" value="{{ old('form_type') == 'edit_khusus_'.$khusus->id ? old('jenis_aset') : $khusus->jenis_aset }}">
                    </div>
                    @if(old('form_type') == 'edit_khusus_'.$khusus->id) @error('jenis_aset') <div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div> @enderror @endif
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
<div class="modal fade" id="deleteKhususModal{{ $khusus->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-body p-5 text-center bg-light">
                <div class="d-inline-flex align-items-center justify-content-center rounded-4 mb-4" style="width: 80px; height: 80px; background-color: #f1f3f5;">
                    <i class="fas fa-exclamation-triangle fa-3x text-danger"></i>
                </div>
                <h4 class="fw-bold text-dark mb-2">Konfirmasi Hapus</h4>
                <p class="text-muted mb-4" style="font-size: 1rem;">
                    Hapus data <br><strong class="text-danger fs-5">{{ $khusus->full_kode }} - {{ $khusus->jenis_aset }}</strong>?
                </p>
                <div class="d-flex justify-content-center gap-3">
                    <form action="{{ route('jenis-aset.destroyKhusus', $khusus->id) }}" method="POST" class="w-100 d-flex justify-content-center gap-3">
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
                if (formType === 'khusus') {
                    new bootstrap.Modal(document.getElementById('modalTambahKhusus')).show();
                } else if (formType.startsWith('edit_khusus_')) {
                    const id = formType.split('_')[2];
                    new bootstrap.Modal(document.getElementById('editKhususModal' + id)).show();
                }
            }, 200); 
        @endif

        const modalTambahEl = document.getElementById('modalTambahKhusus');
        if (modalTambahEl) {
            modalTambahEl.addEventListener('hidden.bs.modal', function () {
                const form = this.querySelector('form');
                if (form) {
                    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                    form.reset(); 
                }
            });
        }

        document.querySelectorAll('[id^="editKhususModal"]').forEach(function(modalEditEl) {
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