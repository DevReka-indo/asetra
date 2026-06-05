@extends('layouts.app')

@section('title', 'Detail Pengajuan Perbaikan' . $pengajuan->id)

@section('content')
<div class="container-fluid px-1 py-0 mt-0">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold mb-0">Detail Pengajuan Perbaikan - <span class="text-muted">{{ $pengajuan->id }}</span></h3>
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
                <a href="{{ route('perbaikan.index') }}" class="text-muted text-decoration-none">
                    <span style="font-size: 14px; font-weight: 500; position: relative; top: 2px;">Pengajuan Perbaikan</span>
                </a>
            </li>
            <li class="separator text-muted d-flex align-items-center px-2">
                <span style="font-size: 14px; position: relative; top: 2px;">-</span>
            </li>
            <li class="nav-item d-flex align-items-center">
                <span class="text-muted" style="font-size: 14px; font-weight: 500; position: relative; top: 2px;">Detail Pengajuan Perbaikan</span>
            </li>
        </ul>
    </div>

<div class="card shadow-sm border-0">
    <div class="card-body">
    {{-- ALERT --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">

        {{-- Info Pengajuan --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white pt-3 pb-2 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-primary mb-0">
                        <i class="fas fa-tools me-2"></i>Informasi Pengajuan
                    </h6>
                    <span class="badge bg-{{ $pengajuan->status_badge }} rounded-pill px-3 py-2">
                        {{ $pengajuan->status_label }}
                    </span>
                </div>
                <div class="card-body px-4 py-4">

                    {{-- Aset --}}
                    <div class="mb-4 p-3 bg-light rounded-3">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <span class="info-label d-block small text-muted">Nama Aset</span>
                                <span class="fw-bold">{{ $pengajuan->aset->nama_aset ?? '-' }}</span>
                            </div>
                            <div class="col-md-6">
                                <span class="info-label d-block small text-muted">Nomor Aset</span>
                                @if($pengajuan->aset)
                                    <a href="{{ route('aset.show', $pengajuan->aset_id) }}" class="fw-bold text-primary text-decoration-none">
                                        {{ $pengajuan->aset->nomor_aset ?? '-' }}
                                        <i class="fas fa-external-link-alt ms-1 small"></i>
                                    </a>
                                @else
                                    <span class="fw-bold text-muted">-</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Pengaju & Waktu --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <span class="info-label d-block small text-muted">Diajukan Oleh</span>
                            <span class="fw-semibold">
                                {{ $pengajuan->pengaju ? $pengajuan->pengaju->firstname . ' ' . $pengajuan->pengaju->lastname : '-' }}
                            </span>
                        </div>
                        <div class="col-md-6">
                            <span class="info-label d-block small text-muted">Tanggal Pengajuan</span>
                            <span class="fw-semibold">
                                {{ $pengajuan->tanggal_pengajuan ? $pengajuan->tanggal_pengajuan->format('d M Y') : '-' }}
                            </span>
                        </div>
                        <div class="col-md-6">
                            <span class="info-label d-block small text-muted">Tingkat Urgensi</span>
                            <span class="badge bg-{{ $pengajuan->urgensi_badge }} rounded-pill px-3">
                                {{ ucfirst($pengajuan->tingkat_urgensi) }}
                            </span>
                        </div>
                    </div>

                    {{-- Deskripsi Kerusakan --}}
                    <div class="mb-4">
                        <span class="info-label d-block small text-muted mb-1">Deskripsi Kerusakan</span>
                        <div class="p-3 bg-light rounded-3" style="line-height: 1.7;">
                            {{ $pengajuan->deskripsi_kerusakan }}
                        </div>
                    </div>

                    {{-- Foto Kerusakan --}}
                    @if($pengajuan->foto_kerusakan)
                    <div class="mb-4">
                        <span class="info-label d-block small text-muted mb-2">Foto Kerusakan</span>
                        <a href="{{ asset('storage/' . $pengajuan->foto_kerusakan) }}" target="_blank">
                            <img src="{{ asset('storage/' . $pengajuan->foto_kerusakan) }}"
                                 alt="Foto Kerusakan"
                                 class="img-fluid rounded-3 shadow-sm"
                                 style="max-height: 300px; object-fit: cover;">
                        </a>
                    </div>
                    @endif

                    {{-- Catatan Admin (jika sudah diproses) --}}
                    @if($pengajuan->catatan)
                    <div class="mb-4">
                        <span class="info-label d-block small text-muted mb-1">
                            <i class="fas fa-comment-alt me-1"></i>Catatan Admin
                        </span>
                        <div class="p-3 border-start border-3 border-primary bg-light rounded-end" style="line-height: 1.7;">
                            {{ $pengajuan->catatan }}
                        </div>
                    </div>
                    @endif

                    {{-- Info selesai --}}
                    @if($pengajuan->isDone())
                    <div class="alert alert-success d-flex align-items-center gap-3 rounded-3">
                        <i class="fas fa-check-circle fa-2x"></i>
                        <div>
                            <div class="fw-bold">Perbaikan telah selesai</div>
                            <small>
                                Kondisi setelah: <strong>{{ $pengajuan->kondisi_setelah ?? '-' }}</strong> —
                                Selesai pada: <strong>{{ $pengajuan->tanggal_selesai?->format('d M Y') ?? '-' }}</strong>
                            </small>
                        </div>
                    </div>
                    @endif

                </div>
            </div>

            {{-- RIWAYAT PENGAJUAN PERBAIKAN --}}
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white pt-3 pb-2">
                    <h6 class="fw-bold text-primary mb-0">
                        <i class="fas fa-history me-2"></i>Riwayat Pengajuan Perbaikan - {{ $pengajuan->aset->nomor_aset ?? '-' }}
                    </h6>
                </div>
                <div class="card-body px-4 py-4">
                    @if($riwayatPengajuan->count() > 0)
                        <div class="table-responsive bg-white rounded-3 border" style="max-height: 500px; overflow-y: auto; margin-bottom: 0;">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light sticky-top" style="top: 0; z-index: 10;">
                                    <tr>
                                        <th class="px-3" width="8%">No</th>
                                        <th class="px-3" width="14%">Tgl Pengajuan</th>
                                        <th class="px-3" width="10%">Urgensi</th>
                                        <th class="px-3" width="18%">Status</th>
                                        <th class="px-3" width="28%">Deskripsi</th>
                                        <th class="px-3" width="12%">Pengaju</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($riwayatPengajuan as $p)
                                    <tr>
                                        <td class="px-3">
                                            <small class="text-muted">{{ $loop->iteration }}</small>
                                        </td>
                                        <td class="px-3">
                                            <small>{{ $p->tanggal_pengajuan ? $p->tanggal_pengajuan->format('d M Y') : '-' }}</small>
                                        </td>
                                        <td class="px-3">
                                            <span class="badge bg-{{ $p->urgensi_badge }} rounded-pill px-2">
                                                {{ ucfirst($p->tingkat_urgensi) }}
                                            </span>
                                        </td>
                                        <td class="px-3">
                                            <span class="badge bg-{{ $p->status_badge }} rounded-pill px-2">
                                                {{ $p->status_label }}
                                            </span>
                                        </td>
                                        <td class="px-3">
                                            <small class="text-truncate d-block" title="{{ $p->deskripsi_kerusakan }}">
                                                {{ $p->deskripsi_kerusakan }}
                                            </small>
                                        </td>
                                        <td class="px-3">
                                            <small>{{ $p->pengaju ? $p->pengaju->firstname . ' ' . $p->pengaju->lastname : '-' }}</small>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-4 text-center text-muted">
                            <i class="fas fa-inbox fa-2x mb-2 d-block opacity-50"></i>
                            <small>Tidak ada riwayat pengajuan lain untuk aset ini.</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Timeline & Aksi --}}
        <div class="col-lg-4">

            {{-- TIMELINE STATUS --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white pt-3 pb-2">
                    <h6 class="fw-bold text-primary mb-0"><i class="fas fa-history me-2"></i>Riwayat Status</h6>
                </div>
                <div class="card-body px-4 py-3">
                    <ul class="list-unstyled mb-0">
                        {{-- Diajukan --}}
                        <li class="d-flex gap-3 mb-3">
                            <div class="text-center" style="width: 28px;">
                                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center mx-auto"
                                     style="width:28px;height:28px;">
                                    <i class="fas fa-paper-plane text-white" style="font-size:11px;"></i>
                                </div>
                                <div class="border-start border-2 mx-auto" style="height:30px;width:1px;"></div>
                            </div>
                            <div class="pt-1">
                                <div class="fw-semibold" style="font-size:13px;">Pengajuan Dibuat</div>
                                <small class="text-muted">{{ $pengajuan->tanggal_pengajuan?->format('d M Y') }}</small>
                            </div>
                        </li>

                        {{-- Diproses --}}
                        @if($pengajuan->diproses_oleh)
                        <li class="d-flex gap-3 mb-3">
                            <div class="text-center" style="width: 28px;">
                                <div class="rounded-circle bg-{{ $pengajuan->status === 'ditolak' ? 'danger' : 'info' }} d-flex align-items-center justify-content-center mx-auto"
                                     style="width:28px;height:28px;">
                                    <i class="fas fa-{{ $pengajuan->status === 'ditolak' ? 'times' : 'check' }} text-white" style="font-size:11px;"></i>
                                </div>
                                @if($pengajuan->isDone())
                                <div class="border-start border-2 mx-auto" style="height:30px;width:1px;"></div>
                                @endif
                            </div>
                            <div class="pt-1">
                                <div class="fw-semibold" style="font-size:13px;">
                                    {{ $pengajuan->isRejected() ? 'Ditolak' : 'Disetujui' }}
                                </div>
                                <small class="text-muted">
                                    Oleh: {{ $pengajuan->pemroses ? $pengajuan->pemroses->firstname : '-' }},
                                    {{ $pengajuan->tanggal_diproses?->format('d M Y') }}
                                </small>
                            </div>
                        </li>
                        @else
                        <li class="d-flex gap-3 mb-3 opacity-50">
                            <div style="width:28px;">
                                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center mx-auto"
                                     style="width:28px;height:28px;">
                                    <i class="fas fa-hourglass-half text-white" style="font-size:11px;"></i>
                                </div>
                            </div>
                            <div class="pt-1">
                                <div class="fw-semibold" style="font-size:13px;">Menunggu Review</div>
                                <small class="text-muted">Belum diproses</small>
                            </div>
                        </li>
                        @endif

                        {{-- Selesai --}}
                        @if($pengajuan->isDone())
                        <li class="d-flex gap-3">
                            <div style="width:28px;">
                                <div class="rounded-circle bg-success d-flex align-items-center justify-content-center mx-auto"
                                     style="width:28px;height:28px;">
                                    <i class="fas fa-flag-checkered text-white" style="font-size:11px;"></i>
                                </div>
                            </div>
                            <div class="pt-1">
                                <div class="fw-semibold" style="font-size:13px;">Perbaikan Selesai</div>
                                <small class="text-muted">{{ $pengajuan->tanggal_selesai?->format('d M Y') }}</small>
                            </div>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>

            {{-- AKSI ADMIN BAGIAN UMUM --}}
            @php
                $user = Auth::user();
                $isSuperAdmin = $user->role_id_role === 1 || strtolower($user->role->nm_role ?? '') === 'superadmin';
                $isAdmin = in_array(strtolower($user->role->nm_role ?? ''), ['admin', 'superadmin']);
                $canProcess = $isSuperAdmin || ($isAdmin && $user->isBagianUmum());
            @endphp

            @if($canProcess)

                {{-- Setujui / Tolak jika masih menunggu --}}
                @if($pengajuan->isPending())
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white pt-3 pb-2">
                        <h6 class="fw-bold text-primary mb-0"><i class="fas fa-tasks me-2"></i>Proses Pengajuan</h6>
                    </div>
                    <div class="card-body px-4 py-3">
                        <form id="formProses" action="{{ route('perbaikan.proses', $pengajuan->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="aksi" id="inputAksi" value="">
                            <input type="hidden" name="catatan" id="inputCatatan" value="">

                            <div class="d-flex gap-3 mt-2">
                                <button type="button" class="btn btn-white border flex-fill rounded-pill fw-bold shadow-sm py-2"
                                        onclick="showModalProses('ditolak')">
                                    <i class="fas fa-times me-2 text-danger"></i><span class="text-dark">Tolak</span>
                                </button>
                                <button type="button" class="btn text-white rounded-pill fw-bold shadow-sm flex-fill py-2" style="background-color: #253070;"
                                        onclick="showModalProses('disetujui')">
                                    <i class="fas fa-check me-2"></i>Setujui
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                @endif

                {{-- Tandai Selesai jika sudah disetujui --}}
                @if($pengajuan->isApproved())
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white pt-3 pb-2">
                        <h6 class="fw-bold text-success mb-0"><i class="fas fa-flag-checkered me-2"></i>Tandai Selesai</h6>
                    </div>
                    <div class="card-body px-4 py-3">
                        <form action="{{ route('perbaikan.selesai', $pengajuan->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label class="form-label fw-bold small">Kondisi Aset Setelah Perbaikan <span class="text-danger">*</span></label>
                                <select name="kondisi_setelah" class="form-select form-select-sm bg-light border-0 shadow-sm" required>
                                    <option value="">-- Pilih kondisi --</option>
                                    <option value="Baik">🟢 Baik</option>
                                    <option value="Cukup">🟡 Cukup</option>
                                    <option value="Rusak Ringan">🟠 Rusak Ringan</option>
                                    <option value="Tidak Terpakai">⚪ Tidak Terpakai</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold small">Catatan Penutup <span class="text-muted fw-normal">(opsional)</span></label>
                                <textarea name="catatan" rows="2" class="form-control form-control-sm bg-light border-0 shadow-sm"
                                          placeholder="Ringkasan hasil perbaikan...">{{ $pengajuan->catatan }}</textarea>
                            </div>

                            <button type="submit" class="btn btn-success w-100 rounded-pill">
                                <i class="fas fa-flag-checkered me-2"></i>Tandai Perbaikan Selesai
                            </button>
                        </form>
                    </div>
                </div>
                @endif
            @endif
        </div>
    </div>

</div>

{{-- Modal Konfirmasi Proses --}}
<div class="modal fade" id="modalProses" tabindex="-1" aria-labelledby="modalProsesLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-body p-5 text-center bg-light">
                <div class="d-inline-flex align-items-center justify-content-center rounded-4 mb-4" style="width: 80px; height: 80px; background-color: #f1f3f5;" id="modalIconContainer">
                    <i class="fas fa-question fa-3x" id="modalIcon"></i>
                </div>
                <h4 class="fw-bold text-dark mb-2" id="modalTitle">Konfirmasi</h4>
                <p class="text-muted mb-4" style="font-size: 1rem;" id="modalDesc">Deskripsi</p>
                
                {{-- Textarea Catatan / Alasan --}}
                <div class="mb-4 text-start" id="modalCatatanContainer">
                    <label class="form-label fw-bold small text-dark" id="modalCatatanLabel">Catatan</label>
                    <textarea id="modalCatatan" rows="3" class="form-control bg-white border shadow-sm" style="font-size: 14px;"></textarea>
                    <div class="text-danger small mt-1 d-none" id="modalCatatanError">Alasan penolakan wajib diisi!</div>
                </div>

                <div class="d-flex justify-content-center gap-3">
                    <button type="button" class="btn btn-light rounded-pill fw-bold py-2 shadow-sm border" style="width: 120px;" data-bs-dismiss="modal">Batalkan</button>
                    <button type="button" class="btn rounded-pill fw-bold py-2 shadow-sm" style="width: 140px;" id="btnConfirmProses" onclick="executeProses()">Ya, Lanjutkan</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentAksi = '';
let myModal = null;

function showModalProses(aksi) {
    currentAksi = aksi;
    const modalTitle = document.getElementById('modalTitle');
    const modalDesc = document.getElementById('modalDesc');
    const modalIcon = document.getElementById('modalIcon');
    const btnConfirm = document.getElementById('btnConfirmProses');
    const iconContainer = document.getElementById('modalIconContainer');
    const modalCatatanLabel = document.getElementById('modalCatatanLabel');
    const modalCatatan = document.getElementById('modalCatatan');
    const modalCatatanError = document.getElementById('modalCatatanError');
    
    // Reset catatan and error
    modalCatatan.value = '';
    modalCatatan.classList.remove('is-invalid');
    modalCatatanError.classList.add('d-none');
    
    if(aksi === 'disetujui') {
        modalTitle.innerText = 'Setujui Pengajuan';
        modalDesc.innerHTML = 'Apakah Anda yakin ingin menyetujui pengajuan perbaikan ini?';
        modalIcon.className = 'fas fa-check-circle fa-3x text-success';
        iconContainer.className = 'd-inline-flex align-items-center justify-content-center rounded-4 mb-4';
        iconContainer.style.backgroundColor = '#f1f3f5';
        btnConfirm.className = 'btn rounded-pill fw-bold py-2 shadow-sm text-white';
        btnConfirm.style.backgroundColor = '#253070';
        btnConfirm.innerText = 'Ya, Setujui';
        
        modalCatatanLabel.innerHTML = 'Catatan Persetujuan <span class="text-muted fw-normal">(opsional)</span>';
        modalCatatan.placeholder = 'Catatan tambahan untuk pengaju...';
    } else {
        modalTitle.innerText = 'Tolak Pengajuan';
        modalDesc.innerHTML = 'Apakah Anda yakin ingin menolak pengajuan perbaikan ini?';
        modalIcon.className = 'fas fa-exclamation-triangle fa-3x text-danger';
        iconContainer.className = 'd-inline-flex align-items-center justify-content-center rounded-4 mb-4';
        iconContainer.style.backgroundColor = '#f1f3f5';
        btnConfirm.className = 'btn btn-danger rounded-pill fw-bold py-2 shadow-sm text-white';
        btnConfirm.style.backgroundColor = ''; // Hapus style inline agar pakai class btn-danger
        btnConfirm.innerText = 'Ya, Tolak';
        
        modalCatatanLabel.innerHTML = 'Alasan Penolakan <span class="text-danger">*</span>';
        modalCatatan.placeholder = 'Wajib diisi! Masukkan alasan penolakan...';
    }
    
    if(!myModal) {
        myModal = new bootstrap.Modal(document.getElementById('modalProses'));
    }
    myModal.show();
}

function executeProses() {
    const modalCatatan = document.getElementById('modalCatatan');
    const modalCatatanError = document.getElementById('modalCatatanError');
    
    if (currentAksi === 'ditolak' && modalCatatan.value.trim() === '') {
        modalCatatan.classList.add('is-invalid');
        modalCatatanError.classList.remove('d-none');
        return;
    }
    
    document.getElementById('inputAksi').value = currentAksi;
    document.getElementById('inputCatatan').value = modalCatatan.value;
    document.getElementById('formProses').submit();
}

window.addEventListener('load', function() {
    const swalConfig = {
        showConfirmButton: true,
        confirmButtonText: 'OK',
        confirmButtonColor: '#253070',
        customClass: {
            popup: 'rounded-4 shadow'
        }
    };

    @if (session('success'))
        Swal.fire({
            ...swalConfig,
            icon: 'success',
            title: 'Berhasil!',
            text: '{{ session('success') }}'
        });
    @endif

    @if (session('error'))
        Swal.fire({
            ...swalConfig,
            icon: 'error',
            title: 'Gagal!',
            text: '{{ session('error') }}'
        });
    @endif
});
</script>
@endpush
@endsection
