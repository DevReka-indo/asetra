@extends('layouts.app')

@section('title', 'Tambah Pengguna Baru')

@section('content')
<div class="container-fluid px-1 py-0 mt-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Tambah Pengguna Baru</h3>
            <small class="text-muted">Lengkapi formulir di bawah untuk menambahkan pengguna</small>
        </div>
        <a href="{{ route('user.manage') }}" class="btn btn-white border shadow-sm">
            <i class="fas fa-arrow-left me-1"></i> Batal / Kembali
        </a>
    </div>

    <form action="{{ route('user-manage/add') }}" method="POST">
        @csrf
        
        {{-- Informasi Akun --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold text-primary"><i class="fas fa-id-card me-2"></i> Informasi Akun</h6>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-secondary small text-uppercase">ID Pengguna</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-hashtag text-muted"></i></span>
                            <input type="text" name="id" class="form-control bg-light border-start-0" placeholder="Otomatis terisi oleh sistem" disabled>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-secondary small text-uppercase">Email <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-envelope text-primary"></i></span>
                            <input type="email" name="email" class="form-control border-start-0" placeholder="contoh@email.com" required>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Data Pribadi --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold text-primary"><i class="fas fa-user me-2"></i> Data Pribadi</h6>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-secondary small text-uppercase">Nama Depan <span class="text-danger">*</span></label>
                        <input type="text" name="firstname" class="form-control" placeholder="Masukkan nama depan" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-secondary small text-uppercase">Nama Akhir</label>
                        <input type="text" name="lastname" class="form-control" placeholder="Masukkan nama akhir (opsional)">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-secondary small text-uppercase">NIP <span class="text-danger">*</span></label>
                        <input type="text" name="nip" class="form-control" placeholder="Masukkan NIP" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-secondary small text-uppercase">No. Telepon <span class="text-danger">*</span></label>
                        <input type="text" name="phone_number" class="form-control" placeholder="08xxxxxxxxxx" required minlength="10" maxlength="15" pattern="\d{10,15}" title="Nomor telepon harus 10-15 digit angka">
                    </div>
                </div>
            </div>
        </div>

        {{-- Keamanan --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold text-primary"><i class="fas fa-lock me-2"></i> Keamanan</h6>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-secondary small text-uppercase">Kata Sandi <span class="text-danger">*</span></label>
                        <input type="password" name="password" id="password" class="form-control" placeholder="Minimal 8 karakter" minlength="8" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-secondary small text-uppercase">Konfirmasi Kata Sandi <span class="text-danger">*</span></label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Ulangi kata sandi" minlength="8" required oninput="this.setCustomValidity(this.value !== document.getElementById('password').value ? 'Konfirmasi kata sandi tidak cocok' : '')">
                    </div>
                </div>
            </div>
        </div>

        {{-- Organisasi & Posisi --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold text-primary"><i class="fas fa-building me-2"></i> Organisasi & Posisi</h6>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="parent_id" class="form-label fw-semibold text-secondary small text-uppercase">Pilih Organisasi <span class="text-danger">*</span></label>
                        <select class="form-select" id="parent_id" name="parent_id" required>
                            <option value="">-- Pilih Organisasi --</option>
                            @php
                                function renderOrgOptions($node, $level = 0) {
                                    $indent = str_repeat('&nbsp;', $level * 4);
                                    if (isset($node->name_director)) {
                                        echo "<option value='{$node->id_director}' data-type='director'>{$indent}Direktur: {$node->name_director}</option>";
                                    } elseif (isset($node->nm_divisi)) {
                                        echo "<option value='{$node->id_divisi}' data-type='divisi'>{$indent}→ Divisi: {$node->nm_divisi}</option>";
                                    } elseif (isset($node->name_department)) {
                                        echo "<option value='{$node->id_department}' data-type='department'>{$indent}→ Departemen: {$node->name_department}</option>";
                                    } elseif (isset($node->name_section)) {
                                        echo "<option value='{$node->id_section}' data-type='section'>{$indent}→ Bagian: {$node->name_section}</option>";
                                    } elseif (isset($node->name_unit)) {
                                        echo "<option value='{$node->id_unit}' data-type='unit'>{$indent}→ Unit: {$node->name_unit}</option>";
                                    }
                                    if (isset($node->subDirectors)) { foreach ($node->subDirectors as $subDir) renderOrgOptions($subDir, $level + 1); }
                                    if (isset($node->divisi)) { foreach ($node->divisi as $div) renderOrgOptions($div, $level + 1); }
                                    if (isset($node->department)) { foreach ($node->department as $dept) renderOrgOptions($dept, $level + 1); }
                                    if (isset($node->section)) { foreach ($node->section as $sec) renderOrgOptions($sec, $level + 1); }
                                    if (isset($node->unit)) { foreach ($node->unit as $unit) renderOrgOptions($unit, $level + 1); }
                                }
                                if ($mainDirector) renderOrgOptions($mainDirector);
                            @endphp
                        </select>
                        <input type="hidden" name="parent_type" id="parent_type">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-secondary small text-uppercase">Posisi <span class="text-danger">*</span></label>
                        <select name="position_id_position" id="position_id_position" class="form-select" required>
                            <option value="">-- Pilih Posisi --</option>
                            @foreach ($positions as $p)
                                <option value="{{ $p->id_position }}">{{ $p->nm_position }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Hak Akses & Kode Bagian --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold text-primary"><i class="fas fa-shield-alt me-2"></i> Hak Akses & Area Kerja</h6>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-secondary small text-uppercase mb-3">Hak Akses <span class="text-danger">*</span></label>
                        <div class="row g-2">
                            <div class="col-12">
                                <div class="border rounded p-3 h-100">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="role_id_role" value="1" id="role1" required>
                                        <label class="form-check-label w-100 cursor-pointer" for="role1">
                                            <div class="fw-bold text-primary"><i class="fas fa-star me-1"></i> Superadmin</div>
                                            <small class="text-muted">Akses penuh sistem</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="border rounded p-3 h-100">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="role_id_role" value="3" id="role3">
                                        <label class="form-check-label w-100 cursor-pointer" for="role3">
                                            <div class="fw-bold text-warning"><i class="fas fa-cog me-1"></i> Admin</div>
                                            <small class="text-muted">Kelola data level divisi</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="border rounded p-3 h-100">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="role_id_role" value="2" id="role2">
                                        <label class="form-check-label w-100 cursor-pointer" for="role2">
                                            <div class="fw-bold text-info"><i class="fas fa-user me-1"></i> User</div>
                                            <small class="text-muted">Akses terbatas</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-secondary small text-uppercase mb-2">Kode Bagian Kerja</label>
                        <p class="text-muted small mb-3"><i class="fas fa-info-circle me-1"></i> Pilih satu atau lebih bagian kerja. Centang kotak untuk memilih.</p>
                        
                        <div class="border rounded bg-white shadow-sm" style="max-height: 280px; overflow-y: auto;">
                            @foreach ($bagianKerja as $index => $b)
                                <div class="px-3 py-2 {{ $index > 0 ? 'border-top' : '' }}">
                                    <div class="form-check m-0 d-flex align-items-center">
                                        <input class="form-check-input mt-0" type="checkbox" name="kode_bagian[]" value="{{ $b->kode_bagian }}" id="add_bagian_{{ $b->kode_bagian }}">
                                        <label for="add_bagian_{{ $b->kode_bagian }}" class="form-check-label d-flex align-items-center ms-3 mb-0 cursor-pointer" style="gap: 12px;">
                                            <span class="badge bg-primary-subtle text-primary border border-primary text-uppercase" style="width: 70px; text-align: center;">
                                                {{ $b->kode_bagian }}
                                            </span>
                                            <span class="text-dark fw-medium">{{ $b->nama_bagian ?? '-' }}</span>
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-end mb-5">
            <button type="submit" class="btn btn-primary px-5 py-2 fw-bold shadow-sm rounded-pill">
                <i class="fas fa-save me-1"></i> Simpan Pengguna Baru
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", () => {
        // Handle dropdown hierarchy (Parent ID -> Parent Type)
        document.getElementById('parent_id').addEventListener('change', function() {
            var selectedOption = this.options[this.selectedIndex];
            var type = selectedOption ? selectedOption.getAttribute('data-type') : '';
            document.getElementById('parent_type').value = type;
        });
    });
</script>
@endpush