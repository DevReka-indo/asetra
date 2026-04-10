@extends('layouts.app')

@section('title', 'Kelola Kode Bagian Kerja')

@section('content')
<div class="container-fluid px-1 py-0 mt-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0">Kelola Kode Bagian Kerja</h3>
    </div>

    {{-- FILTER --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                {{-- 1. Entries --}}
                <div class="col-md-1">
                    <label class="form-label fw-bold small text-muted text-uppercase" style="font-size: 0.7rem;">Entries</label>
                    <select name="per_page" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    </select>
                </div>

                {{-- 2. Kategori --}}
                <div class="col-md-3">
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

                {{-- 3. Status --}}
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
                        <button type="submit" class="btn px-4 rounded-3 d-flex align-items-center text-white" style="background-color: navy; border-color: navy;">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                        
                        <a href="{{ route('kode-bagian.index') }}" class="btn text-white px-4 rounded-3 d-flex align-items-center" style="background-color: #6f42c1; border-color: #6f42c1;">
                            <i class="fas fa-undo me-1"></i> Reset
                        </a>

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
                                            {{-- ♻️ RECOVERY --}}
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
        <div class="modal-content border-0 shadow">
                
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title fw-bold">
                        <i class="fas fa-info-circle me-2"></i> Detail Kode Bagian
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
                
            <div class="modal-body p-4">
                <table class="table table-borderless mb-0">
                    <tr>
                        <th width="35%" class="text-muted text-uppercase small align-middle">Kode Bagian</th>
                        <td width="5%" class="align-middle">:</td>
                        <td>
                            <span class="badge bg-primary px-3 py-2 fs-6 shadow-sm">{{ $row->kode_bagian }}</span>
                        </td>
                    </tr>
                    <tr>
                        <th class="text-muted text-uppercase small pt-3 align-middle">Nama Bagian</th>
                        <td class="pt-3 align-middle">:</td>
                        <td class="pt-3 fw-bold fs-6 text-dark">{{ $row->nama_bagian }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted text-uppercase small pt-3 align-middle">Kategori</th>
                        <td class="pt-3 align-middle">:</td>
                        <td class="pt-3 text-dark">{{ $row->kategori ?? 'Tidak ada kategori' }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted text-uppercase small pt-3 align-middle">Status</th>
                        <td class="pt-3 align-middle">:</td>
                        <td class="pt-3">
                            @if ($row->is_active)
                                <span class="badge rounded-pill bg-success px-3">Aktif</span>
                            @else
                                <span class="badge rounded-pill bg-secondary px-3">Nonaktif</span>
                            @endif
                        </td>
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
        <form action="{{ route('kode-bagian.store') }}" method="POST" class="modal-content border-0 shadow" autocomplete="off">
            @csrf
            <input type="hidden" name="form_type" value="tambah">

            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-plus-circle text-primary me-2"></i> Tambah Kode Bagian
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted text-uppercase">Kode Bagian</label>
                    <input type="text" name="kode_bagian" class="form-control @error('kode_bagian') is-invalid @enderror" value="{{ old('form_type') == 'tambah' ? old('kode_bagian') : '' }}" placeholder="Contoh: IT-01"> 
                    @if(old('form_type') == 'tambah')
                        @error('kode_bagian') <div class="invalid-feedback fw-bold">{{ $message }}</div> @enderror
                    @endif
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted text-uppercase">Nama Bagian</label>
                    <input type="text" name="nama_bagian" class="form-control @error('nama_bagian') is-invalid @enderror" value="{{ old('form_type') == 'tambah' ? old('nama_bagian') : '' }}" placeholder="Contoh: Information Technology">
                    @if(old('form_type') == 'tambah')
                        @error('nama_bagian') <div class="invalid-feedback fw-bold">{{ $message }}</div> @enderror
                    @endif
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted text-uppercase">Kategori</label>
                    <input type="text" name="kategori" class="form-control @error('kategori') is-invalid @enderror" value="{{ old('form_type') == 'tambah' ? old('kategori') : '' }}" placeholder="Contoh: Operasional">
                    @if(old('form_type') == 'tambah')
                        @error('kategori') <div class="invalid-feedback fw-bold">{{ $message }}</div> @enderror
                    @endif
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted text-uppercase">Status</label>
                    <select name="is_active" class="form-select @error('is_active') is-invalid @enderror">
                        <option value="1" {{ old('form_type') == 'tambah' && old('is_active') == '1' ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ old('form_type') == 'tambah' && old('is_active') == '0' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
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
            <form action="{{ route('kode-bagian.update', $row->id) }}" method="POST" class="modal-content border-0 shadow" autocomplete="off">
                @csrf
                @method('PUT')
                <input type="hidden" name="form_type" value="edit_{{ $row->id }}">

                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold text-warning">
                        <i class="fas fa-edit me-2"></i> Edit Kode Bagian
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted text-uppercase">Kode Bagian</label>
                        <input type="text" name="kode_bagian" class="form-control @error('kode_bagian') is-invalid @enderror" value="{{ old('form_type') == 'edit_'.$row->id ? old('kode_bagian') : $row->kode_bagian }}"> 
                        @if(old('form_type') == 'edit_'.$row->id)
                            @error('kode_bagian') <div class="invalid-feedback fw-bold">{{ $message }}</div> @enderror
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted text-uppercase">Nama Bagian</label>
                        <input type="text" name="nama_bagian" class="form-control @error('nama_bagian') is-invalid @enderror" value="{{ old('form_type') == 'edit_'.$row->id ? old('nama_bagian') : $row->nama_bagian }}">
                        @if(old('form_type') == 'edit_'.$row->id)
                            @error('nama_bagian') <div class="invalid-feedback fw-bold">{{ $message }}</div> @enderror
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted text-uppercase">Kategori</label>
                        <input type="text" name="kategori" class="form-control @error('kategori') is-invalid @enderror" value="{{ old('form_type') == 'edit_'.$row->id ? old('kategori') : $row->kategori }}">
                        @if(old('form_type') == 'edit_'.$row->id)
                            @error('kategori') <div class="invalid-feedback fw-bold">{{ $message }}</div> @enderror
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted text-uppercase">Status</label>
                        <select name="is_active" class="form-select @error('is_active') is-invalid @enderror">
                            @php $statusValue = old('form_type') == 'edit_'.$row->id ? old('is_active') : $row->is_active; @endphp
                            <option value="1" {{ $statusValue == 1 ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ $statusValue == 0 ? 'selected' : '' }}>Nonaktif</option>
                        </select>
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
                    <p class="text-muted mb-0">Anda yakin ingin menghapus <strong>{{ $row->kode_bagian }} - {{ $row->nama_bagian }}</strong>?</p>
                </div>
                <div class="modal-footer bg-light border-0 justify-content-center">
                    <form action="{{ route('kode-bagian.destroy', $row->id) }}" method="POST" class="d-flex gap-2">
                        @csrf 
                        @method('DELETE')
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
                        form.reset();
                    }
                });
            }

            document.querySelectorAll('[id^="modalEdit"]').forEach(function(modalEditEl) {
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