@extends('layouts.app')

@section('title', 'Kelola Kategori Aset')

@section('content')
<div class="container-fluid px-1 py-0 mt-0">
    <div class="mb-4">
        <h3 class="fw-bold mb-0">
            Kelola Kategori Aset
            @if($jenisAktif)
                <span class="badge bg-{{ $jenisAktif->warna_badge_safe }} ms-2 fs-6">{{ $jenisAktif->nama_jenis }}</span>
            @endif
        </h3>
    </div>

    {{-- FILTER & TOOLBAR --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('kategori-aset.index') }}" class="row g-2 align-items-end">
                {{-- Entries --}}
                <div class="col-md-1">
                    <label class="form-label fw-bold small text-muted text-uppercase" style="font-size: 0.7rem;">Entries</label>
                    <select name="per_page" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                        <option value="10"  {{ request('per_page') == 10  ? 'selected' : '' }}>10</option>
                        <option value="25"  {{ request('per_page') == 25  ? 'selected' : '' }}>25</option>
                        <option value="50"  {{ request('per_page') == 50  ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>

                {{-- Filter Jenis --}}
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-muted text-uppercase" style="font-size: 0.7rem;">Filter Jenis Kategori</label>
                    <select name="jenis_kategori_id" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                        <option value="">-- Semua Jenis --</option>
                        @foreach($jenisList as $jenis)
                            <option value="{{ $jenis->id }}" {{ request('jenis_kategori_id') == $jenis->id ? 'selected' : '' }}>
                                {{ $jenis->kode_awalan }} - {{ $jenis->nama_jenis }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Pencarian --}}
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-muted text-uppercase" style="font-size: 0.7rem;">Pencarian</label>
                    <div class="input-group input-group-sm input-group-focus rounded-3">
                        <span class="input-group-text bg-white border-0 text-muted"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control border-0 shadow-none bg-transparent"
                            placeholder="Cari kode atau nama..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-auto ms-auto">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-warning px-4 rounded-3 d-flex align-items-center text-dark"
                            data-bs-toggle="modal" data-bs-target="#modalImportKategori">
                            <i class="fas fa-file-import me-1"></i> Import
                        </button>
                        <a href="{{ route('kategori-aset.export', request()->query()) }}" class="btn btn-success px-4 rounded-3 d-flex align-items-center text-white" title="Export Data">
                            <i class="fas fa-file-excel me-1"></i> Export
                        </a>
                        <button type="button" class="btn btn-primary px-4 rounded-3 d-flex align-items-center"
                            data-bs-toggle="modal" data-bs-target="#modalTambahKategori">
                            <i class="fas fa-plus me-1"></i> Tambah Kategori
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- TABEL DATA --}}
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="60" class="text-center">No</th>
                            <th width="200">Nama Kategori</th>
                            <th width="130">Kode</th>
                            <th width="160" class="text-center">Jenis Kategori</th>
                            <th width="150" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data as $i => $item)
                            <tr>
                                <td class="text-center">{{ $data->firstItem() + $i }}</td>
                                <td>{{ $item->nama }}</td>
                                <td class="fw-bold text-primary">{{ $item->kode }}</td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $item->tipe_badge_color }}">
                                        {{ $item->tipe_label }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center align-items-center gap-2">
                                        <button type="button"
                                            class="btn btn-info btn-sm rounded-circle text-white border-0"
                                            style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"
                                            title="Detail"
                                            data-bs-toggle="modal" data-bs-target="#detailModal{{ $item->id }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button"
                                            class="btn btn-warning btn-sm rounded-circle text-white border-0"
                                            style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"
                                            title="Edit"
                                            data-bs-toggle="modal" data-bs-target="#editModal{{ $item->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button"
                                            class="btn btn-danger btn-sm rounded-circle text-white border-0"
                                            style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"
                                            title="Hapus"
                                            data-bs-toggle="modal" data-bs-target="#deleteModal{{ $item->id }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fas fa-boxes fa-3x mb-3 d-block opacity-25"></i>
                                    Belum ada data Kategori Aset
                                    @if(request('jenis_kategori_id'))
                                        untuk jenis ini
                                    @endif
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

{{-- MODAL IMPORT --}}
    <div class="modal fade" id="modalImportKategori" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('kategori-aset.import') }}" method="POST" enctype="multipart/form-data"
                class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                @csrf
                <div class="modal-header border-0 pt-4 px-4 pb-3" style="background-color: #f39c12;">
                    <h5 class="modal-title fw-bold text-white">
                        <i class="fas fa-file-import me-2"></i> Import Kategori Aset
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 bg-light">
                    <div class="alert alert-info border-0 shadow-sm rounded-3 mb-4">
                        <h6 class="fw-bold mb-2"><i class="fas fa-info-circle me-1"></i> Format Excel:</h6>
                        <ul class="mb-2 small">
                            <li>Baris pertama sebagai Judul (Heading Row).</li>
                            <li>Kolom A: <strong>kode</strong> (awalan sesuai jenis kategori)</li>
                            <li>Kolom B: <strong>nama</strong></li>
                        </ul>
                        <a href="{{ route('kategori-aset.template') }}" class="btn btn-sm btn-outline-info w-100 rounded-pill fw-bold">
                            <i class="fas fa-download me-1"></i> Download Template Excel
                        </a>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small" style="color: #253070;">JENIS KATEGORI <span class="text-danger">*</span></label>
                        <select name="jenis_kategori_id" class="form-select shadow-sm rounded-3" required>
                            <option value="" disabled selected>-- Pilih Jenis --</option>
                            @foreach($jenisList as $jenis)
                                <option value="{{ $jenis->id }}">{{ $jenis->kode_awalan }} - {{ $jenis->nama_jenis }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-bold small" style="color: #253070;">PILIH FILE EXCEL (.xlsx, .xls, .csv)</label>
                        <input type="file" name="file" class="form-control shadow-sm rounded-3" required accept=".xlsx,.xls,.csv">
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

{{-- MODAL TAMBAH --}}
<div class="modal fade" id="modalTambahKategori" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('kategori-aset.store') }}" method="POST"
            class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" autocomplete="off">
            @csrf
            <input type="hidden" name="form_type" value="tambah">

            <div class="modal-header border-0 pt-4 px-4 pb-3" style="background-color: #253070;">
                <h5 class="modal-title fw-bold text-white">
                    <i class="fas fa-plus-circle me-2"></i> Tambah Kategori Aset
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4 bg-light">
                {{-- Dropdown Jenis Kategori --}}
                <div class="mb-4">
                    <label class="form-label fw-bold small" style="color: #253070;">JENIS KATEGORI <span class="text-danger">*</span></label>
                    <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-layer-group"></i></span>
                        <select name="jenis_kategori_id" id="addJenisSelect"
                            class="form-select border-start-0 fs-6 @error('jenis_kategori_id') is-invalid @enderror" required>
                            <option value="" disabled selected>-- Pilih Jenis Kategori --</option>
                            @foreach($jenisList as $jenis)
                                <option value="{{ $jenis->id }}"
                                    data-awalan="{{ $jenis->kode_awalan }}"
                                    {{ (old('form_type') == 'tambah' && old('jenis_kategori_id') == $jenis->id) || (request('jenis_kategori_id') == $jenis->id) ? 'selected' : '' }}>
                                    {{ $jenis->kode_awalan }} – {{ $jenis->nama_jenis }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @if(old('form_type') == 'tambah') @error('jenis_kategori_id') <div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div> @enderror @endif
                </div>

                {{-- Nama --}}
                <div class="mb-2">
                    <label class="form-label fw-bold small" style="color: #253070;">NAMA KLASIFIKASI <span class="text-danger">*</span></label>
                    <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-tag"></i></span>
                        <input type="text" name="nama"
                            class="form-control border-start-0 fs-6 @error('nama') is-invalid @enderror"
                            placeholder="Contoh: Tanah" value="{{ old('form_type') == 'tambah' ? old('nama') : '' }}">
                    </div>
                    @if(old('form_type') == 'tambah') @error('nama') <div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div> @enderror @endif
                </div>

                {{-- Kode --}}
                <div class="mb-4">
                    <label class="form-label fw-bold small" style="color: #253070;">KODE <span class="text-danger">*</span></label>
                    <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-barcode"></i></span>
                        <input type="text" name="kode" id="addKodeInput"
                            class="form-control border-start-0 fs-6 @error('kode') is-invalid @enderror"
                            placeholder="Contoh: 101" maxlength="10"
                            value="{{ old('form_type') == 'tambah' ? old('kode') : '' }}">
                    </div>
                    <small class="text-muted mt-1 d-block" id="addKodeHint">Pilih Jenis Kategori dulu untuk melihat awalan kode yang benar</small>
                    @if(old('form_type') == 'tambah') @error('kode') <div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div> @enderror @endif
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

{{-- MODAL EDIT & HAPUS per item --}}
@foreach($data as $item)
{{-- MODAL DETAIL --}}
<div class="modal fade" id="detailModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 pt-4 px-4 pb-3" style="background-color: #253070;">
                <h5 class="modal-title fw-bold text-white">
                    <i class="fas fa-info-circle me-2"></i> Detail Kategori Aset
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                    <div class="card-body p-0">
                        <table class="table table-borderless mb-0">
                            <tbody>
                                <tr class="border-bottom">
                                    <th class="bg-white px-4 py-3 text-muted small text-uppercase">Jenis Kategori</th>
                                    <td class="px-4 py-3">
                                        <span class="badge bg-{{ $item->tipe_badge_color }}">
                                            {{ $item->tipe_label }}
                                        </span>
                                    </td>
                                </tr>
                                <tr class="border-bottom">
                                    <th class="bg-white px-4 py-3 text-muted small text-uppercase">Nama Kategori</th>
                                    <td class="px-4 py-3 fw-bold text-dark">{{ $item->nama }}</td>
                                </tr>
                                <tr class="border-bottom">
                                    <th class="bg-white px-4 py-3 text-muted small text-uppercase" width="160">Kode Kategori</th>
                                    <td class="px-4 py-3 fw-bold text-primary fs-5">{{ $item->kode }}</td>
                                </tr>
                                
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-top-0 pt-0 pb-4 px-4">
                <button type="button" class="btn btn-secondary rounded-pill px-4 fw-bold shadow-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL EDIT --}}
<div class="modal fade" id="editModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('kategori-aset.update', $item->id) }}" method="POST"
            class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" autocomplete="off">
            @csrf @method('PUT')
            <input type="hidden" name="form_type_edit" value="{{ $item->id }}">

            <div class="modal-header border-0 pt-4 px-4 pb-3" style="background-color: #253070;">
                <h5 class="modal-title fw-bold text-white">
                    <i class="fas fa-edit me-2"></i> Edit Kategori Aset
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4 bg-light">
                {{-- Dropdown Jenis --}}
                <div class="mb-4">
                    <label class="form-label fw-bold small" style="color: #253070;">JENIS KATEGORI <span class="text-danger">*</span></label>
                    <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-layer-group"></i></span>
                        <select name="jenis_kategori_id"
                            class="form-select border-start-0 fs-6 edit-jenis-select"
                            data-item-id="{{ $item->id }}" required>
                            <option value="" disabled>-- Pilih Jenis --</option>
                            @foreach($jenisList as $jenis)
                                <option value="{{ $jenis->id }}"
                                    data-awalan="{{ $jenis->kode_awalan }}"
                                    {{ (session('form_type_edit') == $item->id && old('jenis_kategori_id') == $jenis->id) || $item->jenis_kategori_id == $jenis->id ? 'selected' : '' }}>
                                    {{ $jenis->kode_awalan }} – {{ $jenis->nama_jenis }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Nama --}}
                <div class="mb-2">
                    <label class="form-label fw-bold small" style="color: #253070;">NAMA KLASIFIKASI <span class="text-danger">*</span></label>
                    <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-tag"></i></span>
                        <input type="text" name="nama"
                            class="form-control border-start-0 fs-6 @error('nama') is-invalid @enderror"
                            value="{{ session('form_type_edit') == $item->id ? old('nama') : $item->nama }}">
                    </div>
                    @if(session('form_type_edit') == $item->id) @error('nama') <div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div> @enderror @endif
                </div>

                {{-- Kode --}}
                <div class="mb-4">
                    <label class="form-label fw-bold small" style="color: #253070;">KODE <span class="text-danger">*</span></label>
                    <div class="input-group input-group-lg shadow-sm rounded-3 input-group-focus">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-barcode"></i></span>
                        <input type="text" name="kode" maxlength="10"
                            class="form-control border-start-0 fs-6 @error('kode') is-invalid @enderror"
                            value="{{ session('form_type_edit') == $item->id ? old('kode') : $item->kode }}">
                    </div>
                    <small class="text-muted mt-1 d-block edit-kode-hint-{{ $item->id }}">
                        Awalan kode harus sesuai jenis: <strong>{{ $item->jenisKategori?->kode_awalan }}</strong>
                    </small>
                    @if(session('form_type_edit') == $item->id) @error('kode') <div class="text-danger small mt-1 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div> @enderror @endif
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
                <div class="d-inline-flex align-items-center justify-content-center rounded-4 mb-4"
                    style="width: 80px; height: 80px; background-color: #f1f3f5;">
                    <i class="fas fa-exclamation-triangle fa-3x text-danger"></i>
                </div>
                <h4 class="fw-bold text-dark mb-2">Konfirmasi Hapus</h4>
                <p class="text-muted mb-3">
                    Hapus kategori <br>
                    <strong class="text-danger fs-5">{{ $item->kode }} - {{ $item->nama }}</strong>?
                </p>
                <div class="d-flex justify-content-center gap-3">
                    <form action="{{ route('kategori-aset.destroy', $item->id) }}" method="POST"
                        class="w-100 d-flex justify-content-center gap-3">
                        @csrf @method('DELETE')
                        <button type="button" class="btn btn-light rounded-pill fw-bold py-2 shadow-sm border"
                            style="width: 120px;" data-bs-dismiss="modal">Batalkan</button>
                        <button type="submit" class="btn btn-danger rounded-pill fw-bold py-2 shadow-sm"
                            style="width: 140px;">Ya, Hapus</button>
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
            showConfirmButton: true, confirmButtonText: 'OK',
            confirmButtonColor: '#253070', customClass: { popup: 'rounded-4 shadow' }
        };

        @if (session('success')) Swal.fire({ ...swalConfig, icon: 'success', title: 'Berhasil!', text: '{{ session('success') }}' }); @endif
        @if (session('error'))   Swal.fire({ ...swalConfig, icon: 'error',   title: 'Gagal!',    text: '{{ session('error') }}'   }); @endif

        // Hint awalan kode pada modal Tambah
        const addJenisSelect = document.getElementById('addJenisSelect');
        const addKodeHint    = document.getElementById('addKodeHint');
        if (addJenisSelect) {
            function updateAddHint() {
                const opt = addJenisSelect.options[addJenisSelect.selectedIndex];
                const awalan = opt ? opt.getAttribute('data-awalan') : null;
                addKodeHint.innerHTML = awalan
                    ? `Kode harus diawali dengan angka <strong>${awalan}</strong>`
                    : 'Pilih Jenis Kategori dulu untuk melihat awalan kode yang benar';
            }
            addJenisSelect.addEventListener('change', updateAddHint);
            updateAddHint();
        }

        // Hint awalan kode pada setiap modal Edit
        document.querySelectorAll('.edit-jenis-select').forEach(function(sel) {
            sel.addEventListener('change', function() {
                const itemId = this.getAttribute('data-item-id');
                const opt    = this.options[this.selectedIndex];
                const awalan = opt ? opt.getAttribute('data-awalan') : null;
                const hint   = document.querySelector('.edit-kode-hint-' + itemId);
                if (hint) {
                    hint.innerHTML = awalan
                        ? `Awalan kode harus sesuai jenis: <strong>${awalan}</strong>`
                        : 'Pilih jenis terlebih dahulu';
                }
            });
        });

        // Buka kembali modal jika ada error validasi
        @if($errors->any())
            setTimeout(function() {
                const formType = "{{ old('form_type') }}";
                const formTypeEdit = "{{ session('form_type_edit') }}";
                if (formType === 'tambah') {
                    new bootstrap.Modal(document.getElementById('modalTambahKategori')).show();
                } else if (formTypeEdit) {
                    const modalEl = document.getElementById('editModal' + formTypeEdit);
                    if (modalEl) new bootstrap.Modal(modalEl).show();
                }
            }, 200);
        @endif
    });
</script>
@endpush
