@extends('layouts.app')

@section('title', 'Kelola Kode Bagian Kerja')

@section('content')
<div class="container-fluid px-1 py-0 mt-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0">Kelola Kode Bagian Kerja</h3>
        <ul class="breadcrumbs d-flex align-items-center p-0 m-0" style="list-style: none;"> 
            <li class="nav-home d-flex align-items-center">
                <a href="{{ route('dashboard') }}" class="text-muted text-decoration-none d-flex align-items-center">
                    <i class="fas fa-home me-2" style="font-size: 15px;"></i>
                    <span style="font-size: 14px; font-weight: 500; position: relative; top: 2px;">Dashboard</span>                    
                </a>                
            </li>
            <li class="separator text-muted d-flex align-items-center px-2">
                <span style="font-size: 14px; position: relative; top: 2px;">-</span>
            </li>
            <li class="nav-item d-flex align-items-center">
                <span class="text-muted" style="font-size: 14px; font-weight: 500; position: relative; top: 2px;">Kelola Kode Bagian Kerja</span>
            </li>
        </ul>
    </div>

    {{-- FILTER --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
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
                        <input type="text" name="search" class="form-control border-0 shadow-none bg-transparent" placeholder="Cari kode atau nama..." value="{{ request('search') }}">
                    </div>
                </div>

                

                {{-- Kategori --}}
                <div class="col-md-2">
                    <label class="form-label fw-bold small text-muted text-uppercase" style="font-size: 0.7rem;">Kategori</label>
                    <select name="kategori" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                        <option value="">-- Semua Kategori --</option>
                        @foreach ($kategoriList as $kategori)
                            <option value="{{ $kategori }}" {{ (isset($filterKategori) && $filterKategori == $kategori) ? 'selected' : '' }}>
                                {{ $kategori }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Status --}}
                <div class="col-md-2">
                    <label class="form-label fw-bold small text-muted text-uppercase" style="font-size: 0.7rem;">Status</label>
                    <select name="status" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                        <option value="">-- Semua Status --</option>
                        <option value="1" {{ (isset($filterStatus) && $filterStatus === '1') ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ (isset($filterStatus) && $filterStatus === '0') ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>

                <div class="col-auto ms-auto">
                    <div class="d-flex gap-2">
                        <form method="POST" action="{{ route('user-manage.sync-sipo') }}" id="syncForm" class="d-inline m-0">
                            @csrf
                            <button type="submit" class="btn text-white px-3 rounded-3 d-flex align-items-center" onclick="showSyncLoading(event)" style="background-color: #e67e22; border-color: #e67e22;">
                                <i class="fas fa-sync-alt me-1"></i> Sinkronkan dari SIPO
                            </button>
                        </form>
                        <button type="button" class="btn btn-primary px-4 rounded-3 d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#modalTambah">
                            <i class="fas fa-plus me-1"></i> Tambah Kode Bagian
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
                            <th>Kode Bagian</th>
                            <th>Nama Bagian</th>
                            <th>Kategori</th>
                            <th>Status</th>
                            <th width="150" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data as $i => $row)
                            <tr>
                                <td class="text-center">{{ $data->firstItem() + $i }}</td>
                                <td class="fw-bold text-primary">{{ $row->kode_bagian }}</td>
                                <td>{{ $row->nama_bagian }}</td>
                                <td>{{ $row->kategori ?? '-' }}</td>
                                <td>
                                    @if ($row->is_active)
                                        <span class="badge rounded-pill bg-success">Aktif</span>
                                    @else
                                        <span class="badge rounded-pill bg-secondary">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center align-items-center gap-2">
                                        @if ($row->trashed())
                                            {{-- Pemulihan --}}
                                            <form action="{{ route('kode-bagian.restore', $row->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button class="btn btn-sm btn-outline-success rounded-circle" style="width:32px; height:32px;" title="Pulihkan">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                            </form>
                                        @else
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
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-info-circle mb-2 d-block fa-2x"></i>
                                    Belum ada data kode bagian kerja
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
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
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                
            <div class="modal-header border-0 pt-4 px-4 pb-3" style="background-color: #253070;">
                <h5 class="modal-title fw-bold text-white">
                    <i class="fas fa-info-circle me-2"></i> Detail Kode Bagian
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
                
            <div class="modal-body p-4 bg-light">
                <div class="bg-white rounded-4 p-4 shadow-sm border-0">
                    <div class="row align-items-center mb-3">
                        <div class="col-5 text-muted small text-uppercase fw-bold"><i class="fas fa-building me-2 text-primary"></i>Nama Bagian</div>
                        <div class="col-7 fw-bold text-dark fs-6">{{ $row->nama_bagian }}</div>
                    </div>
                    <hr class="text-muted opacity-25">
                    <div class="row align-items-center mb-3">
                        <div class="col-5 text-muted small text-uppercase fw-bold"><i class="fas fa-barcode me-2 text-primary"></i>Kode Bagian</div>
                        <div class="col-7">
                            <span class="badge bg-primary px-3 py-2 fs-6 shadow-sm rounded-pill">{{ $row->kode_bagian }}</span>
                        </div>
                    </div>
                    <hr class="text-muted opacity-25">
                    <div class="row align-items-center mb-3">
                        <div class="col-5 text-muted small text-uppercase fw-bold"><i class="fas fa-tags me-2 text-primary"></i>Kategori</div>
                        <div class="col-7 text-dark fw-medium">{{ $row->kategori ?? 'Tidak ada kategori' }}</div>
                    </div>
                    <hr class="text-muted opacity-25">
                    <div class="row align-items-center">
                        <div class="col-5 text-muted small text-uppercase fw-bold"><i class="fas fa-toggle-on me-2 text-primary"></i>Status</div>
                        <div class="col-7">
                            @if ($row->is_active)
                                <span class="badge rounded-pill bg-success px-3 py-2 shadow-sm"><i class="fas fa-check-circle me-1"></i> Aktif</span>
                            @else
                                <span class="badge rounded-pill bg-secondary px-3 py-2 shadow-sm"><i class="fas fa-times-circle me-1"></i> Nonaktif</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endforeach

{{-- MODAL TAMBAH --}}
<div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('kode-bagian.store') }}" method="POST" id="formTambahKodeBagian" class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" autocomplete="off" novalidate>
            @csrf
            <input type="hidden" name="form_type" value="tambah">

            <div class="modal-header border-0 pt-4 px-4 pb-3" style="background-color: #253070;">
                <h5 class="modal-title fw-bold text-white">
                    <i class="fas fa-plus-circle me-2"></i> Tambah Kode Bagian
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4 bg-light">
                <div class="mb-4">
                    <label class="form-label fw-bold small flex-grow-1" style="color: #253070;">NAMA BAGIAN <span class="text-danger">*</span></label>
                    <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-building"></i></span>
                        <input type="text" name="nama_bagian" class="form-control border-start-0 fs-6 @error('nama_bagian') is-invalid @enderror" value="{{ old('form_type') == 'tambah' ? old('nama_bagian') : '' }}" placeholder="Contoh: Teknologi Informasi" required maxlength="255">
                    </div>
                    @if(old('form_type') == 'tambah')
                        @error('nama_bagian') <div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div> @enderror
                    @endif
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold small flex-grow-1" style="color: #253070;">KODE BAGIAN <span class="text-danger">*</span></label>
                    <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-barcode"></i></span>
                        <input type="text" name="kode_bagian" class="form-control border-start-0 fs-6 @error('kode_bagian') is-invalid @enderror" value="{{ old('form_type') == 'tambah' ? old('kode_bagian') : '' }}" placeholder="Contoh: TI" required maxlength="20"> 
                    </div>
                    @if(old('form_type') == 'tambah')
                        @error('kode_bagian') <div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div> @enderror
                    @endif
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold small flex-grow-1" style="color: #253070;">KATEGORI</label>
                    <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-tags"></i></span>
                        <input type="text" name="kategori" class="form-control border-start-0 fs-6 @error('kategori') is-invalid @enderror" value="{{ old('form_type') == 'tambah' ? old('kategori') : '' }}" placeholder="Contoh: Departemen" maxlength="100">
                    </div>
                    @if(old('form_type') == 'tambah')
                        @error('kategori') <div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div> @enderror
                    @endif
                </div>

                <div class="mb-2">
                    <label class="form-label fw-bold small flex-grow-1" style="color: #253070;">STATUS</label>
                    <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-toggle-on"></i></span>
                        <select name="is_active" class="form-select border-start-0 fs-6 @error('is_active') is-invalid @enderror">
                            <option value="1" {{ old('form_type') == 'tambah' && old('is_active') == '1' ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ old('form_type') == 'tambah' && old('is_active') == '0' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>
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

{{-- MODAL EDIT & HAPUS --}}
@foreach($data as $row)
    <!-- Modal Edit -->
    <div class="modal fade" id="modalEdit{{ $row->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('kode-bagian.update', $row->id) }}" method="POST" id="formEditKodeBagian{{ $row->id }}" class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" autocomplete="off" novalidate>
                @csrf
                @method('PUT')
                <input type="hidden" name="form_type" value="edit_{{ $row->id }}">

                <div class="modal-header border-0 pt-4 px-4 pb-3" style="background-color: #253070;">
                    <h5 class="modal-title fw-bold text-white">
                        <i class="fas fa-edit me-2"></i> Edit Kode Bagian
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4 bg-light">
                    

                    <div class="mb-4">
                        <label class="form-label fw-bold small flex-grow-1" style="color: #253070;">NAMA BAGIAN <span class="text-danger">*</span></label>
                        <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-building"></i></span>
                            <input type="text" name="nama_bagian" class="form-control border-start-0 fs-6 @error('nama_bagian') is-invalid @enderror" value="{{ old('form_type') == 'edit_'.$row->id ? old('nama_bagian') : $row->nama_bagian }}" required maxlength="255">
                        </div>
                        @if(old('form_type') == 'edit_'.$row->id)
                            @error('nama_bagian') <div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div> @enderror
                        @endif
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small flex-grow-1" style="color: #253070;">KODE BAGIAN <span class="text-danger">*</span></label>
                        <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-barcode"></i></span>
                            <input type="text" name="kode_bagian" class="form-control border-start-0 fs-6 @error('kode_bagian') is-invalid @enderror" value="{{ old('form_type') == 'edit_'.$row->id ? old('kode_bagian') : $row->kode_bagian }}" required maxlength="20"> 
                        </div>
                        @if(old('form_type') == 'edit_'.$row->id)
                            @error('kode_bagian') <div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div> @enderror
                        @endif
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small flex-grow-1" style="color: #253070;">KATEGORI</label>
                        <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-tags"></i></span>
                            <input type="text" name="kategori" class="form-control border-start-0 fs-6 @error('kategori') is-invalid @enderror" value="{{ old('form_type') == 'edit_'.$row->id ? old('kategori') : $row->kategori }}" maxlength="100">
                        </div>
                        @if(old('form_type') == 'edit_'.$row->id)
                            @error('kategori') <div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div> @enderror
                        @endif
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-bold small flex-grow-1" style="color: #253070;">STATUS</label>
                        <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-toggle-on"></i></span>
                            <select name="is_active" class="form-select border-start-0 fs-6 @error('is_active') is-invalid @enderror">
                                @php $statusValue = old('form_type') == 'edit_'.$row->id ? old('is_active') : $row->is_active; @endphp
                                <option value="1" {{ $statusValue == 1 ? 'selected' : '' }}>Aktif</option>
                                <option value="0" {{ $statusValue == 0 ? 'selected' : '' }}>Nonaktif</option>
                            </select>
                        </div>
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

    <!-- Modal Hapus -->
    <div class="modal fade" id="modalDelete{{ $row->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-body p-5 text-center bg-light">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-4 mb-4" style="width: 80px; height: 80px; background-color: #f1f3f5;">
                        <i class="fas fa-exclamation-triangle fa-3x text-danger"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-2">Konfirmasi Hapus</h4>
                    <p class="text-muted mb-4" style="font-size: 1rem;">
                        Anda yakin ingin menghapus data <br>
                        <strong class="text-danger fs-5">{{ $row->kode_bagian }} - {{ $row->nama_bagian }}</strong>?
                    </p>
                    <div class="d-flex justify-content-center gap-3">
                        <form action="{{ route('kode-bagian.destroy', $row->id) }}" method="POST" class="w-100 d-flex justify-content-center gap-3">
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
                showConfirmButton: true,
                confirmButtonText: 'OK',
                confirmButtonColor: '#253070',
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
                        var modalTambah = new bootstrap.Modal(document.getElementById('modalTambah'));
                        modalTambah.show();
                    } else if (formType.startsWith('edit_')) {
                        const id = formType.split('_')[1];
                        var modalEdit = new bootstrap.Modal(document.getElementById('modalEdit' + id));
                        modalEdit.show();
                    }
                }, 200); 
            @endif

            const modalTambahEl = document.getElementById('modalTambah');
            if (modalTambahEl) {
                modalTambahEl.addEventListener('hidden.bs.modal', function () {
                    const form = this.querySelector('form');
                    if (form) {
                        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                        form.querySelectorAll('.invalid-feedback-custom').forEach(el => el.remove());
                        form.querySelectorAll('.input-group').forEach(el => {
                            el.classList.remove('border', 'border-danger');
                        });
                        form.reset();
                    }
                });
            }

            document.querySelectorAll('[id^="modalEdit"]').forEach(function(modalEditEl) {
                modalEditEl.addEventListener('hidden.bs.modal', function () {
                    const form = this.querySelector('form');
                    if (form) {
                        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                        form.querySelectorAll('.invalid-feedback-custom').forEach(el => el.remove());
                        form.querySelectorAll('.input-group').forEach(el => {
                            el.classList.remove('border', 'border-danger');
                        });
                        form.reset();
                    }
                });
            });

            // Client-side and AJAX Validation on Submit
            const formsToValidate = document.querySelectorAll('#formTambahKodeBagian, form[id^="formEditKodeBagian"]');
            formsToValidate.forEach(form => {
                form.addEventListener('submit', async function(e) {
                    e.preventDefault(); // Prevent standard submission

                    // Clear any old custom error messages and styles
                    form.querySelectorAll('.invalid-feedback-custom').forEach(el => el.remove());
                    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                    form.querySelectorAll('.input-group').forEach(el => {
                        el.classList.remove('border', 'border-danger');
                    });

                    let isValid = true;
                    let firstInvalidEl = null;

                    const fields = form.querySelectorAll('input, select');
                    fields.forEach(field => {
                        if (field.type === 'hidden') return;
                        
                        // Try to find the associated label text
                        let labelText = '';
                        const formGroup = field.closest('.mb-4, .mb-2');
                        if (formGroup) {
                            const labelEl = formGroup.querySelector('label');
                            if (labelEl) {
                                labelText = labelEl.textContent.replace('*', '').trim();
                            }
                        }
                        
                        // Fallback to name or placeholder
                        if (!labelText) {
                            labelText = field.getAttribute('placeholder') || field.getAttribute('name') || 'Kolom';
                        }

                        if (field.hasAttribute('required') && (!field.value || (field.tagName === 'SELECT' && field.value === ''))) {
                            isValid = false;
                            
                            // Style input field and group
                            field.classList.add('is-invalid');
                            const inputGroup = field.closest('.input-group');
                            if (inputGroup) {
                                inputGroup.classList.add('border', 'border-danger');
                            }

                            // Create inline error message element
                            const errorDiv = document.createElement('div');
                            errorDiv.className = 'text-danger small mt-1 fw-bold invalid-feedback-custom';
                            errorDiv.innerHTML = `<i class="fas fa-exclamation-circle me-1"></i>${labelText} wajib diisi.`;
                            
                            // Insert error message element
                            const targetAnchor = inputGroup || field;
                            targetAnchor.parentNode.insertBefore(errorDiv, targetAnchor.nextSibling);

                            if (!firstInvalidEl) {
                                firstInvalidEl = targetAnchor;
                            }
                        } else if (field.value) {
                            const maxLen = field.getAttribute('maxlength');
                            if (maxLen && field.value.length > parseInt(maxLen)) {
                                isValid = false;
                                
                                // Style input field and group
                                field.classList.add('is-invalid');
                                const inputGroup = field.closest('.input-group');
                                if (inputGroup) {
                                    inputGroup.classList.add('border', 'border-danger');
                                }

                                // Create inline error message element
                                const errorDiv = document.createElement('div');
                                errorDiv.className = 'text-danger small mt-1 fw-bold invalid-feedback-custom';
                                errorDiv.innerHTML = `<i class="fas fa-exclamation-circle me-1"></i>${labelText} tidak boleh lebih dari ${maxLen} karakter.`;
                                
                                // Insert error message element
                                const targetAnchor = inputGroup || field;
                                targetAnchor.parentNode.insertBefore(errorDiv, targetAnchor.nextSibling);

                                if (!firstInvalidEl) {
                                    firstInvalidEl = targetAnchor;
                                }
                            }
                        }
                    });

                    if (!isValid) {
                        if (firstInvalidEl) {
                            firstInvalidEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                        return;
                    }

                    // If client-side validation passes, submit via AJAX (fetch)
                    Swal.fire({
                        title: 'Menyimpan...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });

                    try {
                        const formData = new FormData(form);
                        const response = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            },
                            body: formData
                        });

                        const responseData = await response.json();

                        Swal.close();

                        if (response.ok) {
                            // On success, show SweetAlert and reload the page
                            await Swal.fire({
                                ...swalConfig,
                                icon: 'success',
                                title: 'Berhasil!',
                                text: responseData.message || 'Data berhasil disimpan.'
                            });
                            location.reload();
                        } else if (response.status === 422) {
                            // Validation error (like duplicate code)
                            const errors = responseData.errors;
                            
                            Object.keys(errors).forEach(fieldName => {
                                const field = form.querySelector(`[name="${fieldName}"]`);
                                if (field) {
                                    field.classList.add('is-invalid');
                                    const inputGroup = field.closest('.input-group');
                                    if (inputGroup) {
                                        inputGroup.classList.add('border', 'border-danger');
                                    }

                                    // Display error message
                                    const errorDiv = document.createElement('div');
                                    errorDiv.className = 'text-danger small mt-1 fw-bold invalid-feedback-custom';
                                    errorDiv.innerHTML = `<i class="fas fa-exclamation-circle me-1"></i>${errors[fieldName].join(', ')}`;

                                    const targetAnchor = inputGroup || field;
                                    targetAnchor.parentNode.insertBefore(errorDiv, targetAnchor.nextSibling);
                                }
                            });

                            // Scroll to first invalid element
                            const firstErrorField = form.querySelector('.is-invalid');
                            if (firstErrorField) {
                                firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            }
                        } else {
                            // Other server errors
                            Swal.fire({
                                ...swalConfig,
                                icon: 'error',
                                title: 'Gagal!',
                                text: responseData.message || 'Terjadi kesalahan pada server.'
                            });
                        }
                    } catch (err) {
                        Swal.close();
                        Swal.fire({
                            ...swalConfig,
                            icon: 'error',
                            title: 'Gagal!',
                            text: 'Tidak dapat menghubungi server. Silakan coba lagi.'
                        });
                    }
                });
            });
        });

        function showSyncLoading(event) {
            event.preventDefault();
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Menyinkronkan Data...',
                    text: 'Harap tunggu, sedang mengambil data dari API SIPO.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            }
            document.getElementById('syncForm').submit();
        }
    </script>
@endpush