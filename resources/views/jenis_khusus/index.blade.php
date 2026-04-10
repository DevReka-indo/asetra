@extends('layouts.app')

@section('title', 'Kelola Jenis Aset Khusus')

@section('content')
<div class="container-fluid px-1 py-0 mt-0">
    <div class="mb-4">
        <h3 class="fw-bold mb-0">Kelola Jenis Aset Tetap Khusus</h3>
    </div>

    {{-- TABEL JENIS ASET TETAP KHUSUS  --}}
    <div class="card shadow-sm border-0">
        <div class="card-body">
            
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0" style="color: navy;">Data Jenis Aset Tetap Khusus</h5>
                <button type="button" class="btn px-4 rounded-3 d-flex align-items-center text-white" style="background-color: navy; border-color: navy;" data-bs-toggle="modal" data-bs-target="#modalTambahKhusus">
                    + Tambah Aset Tetap Khusus
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered custom-table-bagian align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="60" class="text-center">No</th>
                            <th>Kode Umum</th>
                            <th>Kode Khusus</th>
                            <th>Jenis Aset Khusus</th>
                            <th>Kode Gabungan</th>
                            <th width="120" class="text-center">Aksi</th>
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
                                <td class="fw-bold text-success">{{ $khusus->full_kode }}</td>
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
        <form action="{{ route('jenis-aset.storeKhusus') }}" method="POST" class="modal-content border-0 shadow" autocomplete="off">
            @csrf
            <input type="hidden" name="form_type" value="khusus"> 
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold" style="color: navy;"><i class="fas fa-file-medical me-2"></i>Tambah Aset Khusus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted text-uppercase">Pilih Jenis Aset Umum</label>
                    <select name="jenis_aset_umum_id" class="form-select @error('jenis_aset_umum_id') is-invalid @enderror">
                        <option value="">-- Pilih Induk Aset --</option>
                        @foreach($listUmum as $item)
                            <option value="{{ $item->id }}" {{ old('form_type') == 'khusus' && old('jenis_aset_umum_id') == $item->id ? 'selected' : '' }}>
                                {{ $item->jenis_aset }}
                            </option>
                        @endforeach
                    </select>
                    @if(old('form_type') == 'khusus') @error('jenis_aset_umum_id') <div class="invalid-feedback fw-bold">{{ $message }}</div> @enderror @endif
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted text-uppercase">Kode Khusus</label>
                    <input type="text" name="kode_khusus" class="form-control @error('kode_khusus') is-invalid @enderror" placeholder="Contoh: A" value="{{ old('form_type') == 'khusus' ? old('kode_khusus') : '' }}">
                    @if(old('form_type') == 'khusus') @error('kode_khusus') <div class="invalid-feedback fw-bold">{{ $message }}</div> @enderror @endif
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted text-uppercase">Jenis Aset Khusus</label>
                    <input type="text" name="jenis_aset" class="form-control @error('jenis_aset') is-invalid @enderror" placeholder="Contoh: Gedung Sewa" value="{{ old('form_type') == 'khusus' ? old('jenis_aset') : '' }}">
                    @if(old('form_type') == 'khusus') @error('jenis_aset') <div class="invalid-feedback fw-bold">{{ $message }}</div> @enderror @endif
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-danger rounded-3 px-4" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-success rounded-3 px-4"><i class="fas fa-save me-1"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

@foreach($dataKhusus as $khusus)
{{-- MODAL DETAIL --}}
<div class="modal fade" id="viewKhususModal{{ $khusus->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-info-circle me-2"></i>Detail Aset Khusus</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <table class="table table-borderless mb-0 align-middle">
                    <tr>
                        <th width="40%" class="text-muted text-uppercase small">Induk Aset (Umum)</th>
                        <td width="5%">:</td>
                        <td class="fw-bold">
                            @if($khusus->jenisAsetUmum)
                                {{ $khusus->jenisAsetUmum->jenis_aset }} <span class="badge bg-secondary ms-1">{{ $khusus->jenisAsetUmum->kode_umum }}</span>
                            @else
                                <span class="text-danger">Induk Dihapus</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th class="text-muted text-uppercase small pt-3">Kode Khusus</th>
                        <td class="pt-3">:</td>
                        <td class="pt-3"><span class="badge bg-warning text-dark px-3 py-2 fs-6">{{ $khusus->kode_khusus }}</span></td>
                    </tr>
                    <tr>
                        <th class="text-muted text-uppercase small">Jenis Aset Khusus</th>
                        <td>:</td>
                        <td class="fw-bold">{{ $khusus->jenis_aset }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted text-uppercase small pt-4">KODE GABUNGAN</th>
                        <td class="pt-4">:</td>
                        <td class="pt-4"><span class="badge bg-success px-3 py-2 fs-5 shadow-sm">{{ $khusus->full_kode }}</span></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- MODAL EDIT --}}
<div class="modal fade" id="editKhususModal{{ $khusus->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('jenis-aset.updateKhusus', $khusus->id) }}" method="POST" class="modal-content border-0 shadow" autocomplete="off">
            @csrf @method('PUT')
            <input type="hidden" name="form_type" value="edit_khusus_{{ $khusus->id }}"> 
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-warning"><i class="fas fa-edit me-2"></i>Edit Aset Khusus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted text-uppercase">Pilih Induk Aset</label>
                    <select name="jenis_aset_umum_id" class="form-select @error('jenis_aset_umum_id') is-invalid @enderror">
                        <option value="">-- Pilih Induk Aset --</option>
                        @foreach($listUmum as $item)
                            @php
                                $isSelected = old('form_type') == 'edit_khusus_'.$khusus->id ? (old('jenis_aset_umum_id') == $item->id) : ($khusus->jenis_aset_umum_id == $item->id);
                            @endphp
                            <option value="{{ $item->id }}" {{ $isSelected ? 'selected' : '' }}>{{ $item->jenis_aset }}</option>
                        @endforeach
                    </select>
                    @if(old('form_type') == 'edit_khusus_'.$khusus->id) @error('jenis_aset_umum_id') <div class="invalid-feedback fw-bold">{{ $message }}</div> @enderror @endif
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted text-uppercase">Kode Khusus</label>
                    <input type="text" name="kode_khusus" class="form-control @error('kode_khusus') is-invalid @enderror" value="{{ old('form_type') == 'edit_khusus_'.$khusus->id ? old('kode_khusus') : $khusus->kode_khusus }}">
                    @if(old('form_type') == 'edit_khusus_'.$khusus->id) @error('kode_khusus') <div class="invalid-feedback fw-bold">{{ $message }}</div> @enderror @endif
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted text-uppercase">Jenis Aset Khusus</label>
                    <input type="text" name="jenis_aset" class="form-control @error('jenis_aset') is-invalid @enderror" value="{{ old('form_type') == 'edit_khusus_'.$khusus->id ? old('jenis_aset') : $khusus->jenis_aset }}">
                    @if(old('form_type') == 'edit_khusus_'.$khusus->id) @error('jenis_aset') <div class="invalid-feedback fw-bold">{{ $message }}</div> @enderror @endif
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
<div class="modal fade" id="deleteKhususModal{{ $khusus->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-exclamation-triangle me-2"></i>Konfirmasi Hapus</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-4">
                <i class="fas fa-trash-alt fa-4x text-danger mb-3"></i>
                <h5 class="fw-bold text-dark">Hapus Data Ini?</h5>
                <p class="text-muted mb-0">Hapus <strong>{{ $khusus->full_kode }} - {{ $khusus->jenis_aset }}</strong>?</p>
                <p class="text-danger small mt-2 mb-0">Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <div class="modal-footer bg-light border-0 justify-content-center">
                <form action="{{ route('jenis-aset.destroyKhusus', $khusus->id) }}" method="POST" class="d-flex gap-2">
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