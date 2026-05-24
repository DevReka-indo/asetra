@extends('layouts.app')
@section('title', 'Profil Saya')

@section('content')
    @php
        $roleId = $user->role_id_role;
        $roleName = $roleId == 1 ? 'Superadmin' : ($roleId == 3 ? 'Admin' : 'User');
        $roleClass = $roleId == 1 ? 'role-superadmin' : ($roleId == 3 ? 'role-admin' : 'role-user');
        $roleIcon = $roleId == 1 ? 'star' : ($roleId == 3 ? 'cog' : 'user');
        $statusLabel = $user->deleted_at ? 'Non-Aktif' : 'Aktif';
        $statusClass = $user->deleted_at ? 'danger' : 'success';
        $dashboardRoute = $roleId == 1
            ? 'superadmin.dashboard'
            : ($user->section_id_section == 12
                ? 'general-affairs.dashboard'
                : ($roleId == 3 ? 'manager.dashboard' : 'staff.dashboard'));
    @endphp

    <div class="container-fluid px-1 py-0">

        {{-- Page Header + Breadcrumb --}}
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h3 class="fw-bold mb-0">Profil Saya</h3>
            <ul class="breadcrumbs d-flex align-items-center p-0 m-0" style="list-style: none;">
                <li class="nav-home d-flex align-items-center">
                    <a href="{{ route($dashboardRoute) }}"
                        class="text-muted text-decoration-none d-flex align-items-center">
                        <i class="fas fa-home me-2" style="font-size: 15px;"></i>
                        <span style="font-size: 14px; font-weight: 500; position: relative; top: 2px;">Dashboard</span>
                    </a>
                </li>
                <li class="separator text-muted d-flex align-items-center px-2">
                    <span style="font-size: 14px; position: relative; top: 2px;">-</span>
                </li>
                <li class="nav-item d-flex align-items-center">
                    <span class="text-muted"
                        style="font-size: 14px; font-weight: 500; position: relative; top: 2px;">Profil</span>
                </li>
            </ul>
        </div>

        <form id="formProfile" action="{{ route('superadmin.updateProfile') }}" method="POST" enctype="multipart/form-data"
            novalidate>
            @csrf
            <input type="file" id="profileImageInput" name="profile_image" accept="image/*" hidden>

            {{-- ===== SATU WRAPPER CARD ===== --}}
            <div class="profile-shell">

                {{-- HERO --}}
                <div class="profile-hero">
                    <div class="position-relative" style="z-index: 2;">
                        <h2><i class="fas fa-user-circle me-2"></i>Halo, {{ $user->firstname }}!</h2>
                        <p class="hero-subtitle mb-0">Kelola informasi akun, kontak, dan keamanan profilmu di sini.</p>
                    </div>
                </div>

                {{-- SUMMARY (avatar + ringkasan + tombol edit) --}}
                <div class="summary-area">
                    <div class="row g-4 align-items-center">
                        <div class="col-md-4 text-center">
                            <div id="avatarWrapper"
                                class="avatar-wrapper {{ $user->profile_image ? '' : 'is-placeholder' }}">
                                @if ($user->profile_image)
                                    <img id="profileImagePreview" src="data:image/png;base64,{{ $user->profile_image }}"
                                        alt="profile-photo">
                                @else
                                    <i id="profileImagePreview" class="fas fa-user avatar-placeholder"></i>
                                @endif

                                <div id="avatarOverlay" class="avatar-overlay">
                                    <i class="fas fa-camera"></i>
                                    <span>Ubah Foto</span>
                                </div>
                            </div>

                            <h5 class="fw-bold mb-1" id="displayName">
                                {{ trim($user->firstname . ' ' . $user->lastname) }}
                            </h5>
                            <p class="text-muted small mb-2">{{ $user->email }}</p>

                            <span class="role-badge {{ $roleClass }}">
                                <i class="fas fa-{{ $roleIcon }}"></i> {{ $roleName }}
                            </span>

                            <div id="photoActions" class="mt-3" style="display: none;">
                                <button type="button" class="btn btn-outline-primary btn-photo-action me-1"
                                    onclick="triggerPhotoUpload()">
                                    <i class="fas fa-upload me-1"></i>Ganti Foto
                                </button>
                                @if ($user->profile_image)
                                    <button type="button" id="btnDeletePhoto" class="btn btn-outline-danger btn-photo-action"
                                        onclick="deleteProfilePhoto()">
                                        <i class="fas fa-trash me-1"></i>Hapus
                                    </button>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-8">
                            <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
                                <div>
                                    <h6 class="fw-bold mb-1" style="color:#003366;">Ringkasan Akun</h6>
                                    <small class="text-muted">Informasi singkat profil pengguna</small>
                                </div>
                                <button type="button" id="editModeBtn" class="btn btn-edit-profile"
                                    onclick="toggleEditMode()">
                                    <i class="fas fa-edit me-2"></i>Edit Profil
                                </button>
                            </div>

                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <div class="info-pill">
                                        <div class="icon"><i class="fas fa-id-badge"></i></div>
                                        <div>
                                            <span class="label">NIP</span>
                                            <span class="value">{{ $user->nip ?? '-' }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="info-pill">
                                        <div class="icon" style="background: rgba(40, 167, 69, 0.12); color:#28a745;">
                                            <i class="fas fa-shield-alt"></i>
                                        </div>
                                        <div>
                                            <span class="label">Status</span>
                                            <span class="value text-{{ $statusClass }}">{{ $statusLabel }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="info-pill">
                                        <div class="icon" style="background: rgba(23, 162, 184, 0.12); color:#17a2b8;">
                                            <i class="fas fa-briefcase"></i>
                                        </div>
                                        <div>
                                            <span class="label">Posisi</span>
                                            <span class="value">
                                                {{ $roleId == 1 ? 'Super Admin' : $position ?? '-' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="info-pill">
                                        <div class="icon" style="background: rgba(255, 152, 0, 0.12); color:#fb8c00;">
                                            <i class="fas fa-phone-alt"></i>
                                        </div>
                                        <div>
                                            <span class="label">Telepon</span>
                                            <span class="value">{{ $user->phone_number ?: '-' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- DATA PRIBADI (EDITABLE) --}}
                <div class="profile-section" data-editable="true">
                    <div class="section-title">
                        <i class="fas fa-user"></i> Data Pribadi
                        <span class="badge-edit"><i class="fas fa-pen"></i> Bisa diubah</span>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="field-label" for="firstname">
                                <i class="fas fa-user"></i> Nama Depan <span class="req">*</span>
                            </label>
                            <div class="field-input-wrap">
                                <i class="fas fa-signature field-icon"></i>
                                <input type="text" name="firstname" id="firstname"
                                    class="form-control profile-input is-edit-allowed" value="{{ $user->firstname }}"
                                    readonly required maxlength="50">
                            </div>
                            <div class="invalid-feedback profile-feedback" id="firstnameError"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="field-label" for="lastname">
                                <i class="fas fa-user"></i> Nama Belakang
                            </label>
                            <div class="field-input-wrap">
                                <i class="fas fa-signature field-icon"></i>
                                <input type="text" name="lastname" id="lastname"
                                    class="form-control profile-input is-edit-allowed" value="{{ $user->lastname }}"
                                    readonly maxlength="50">
                            </div>
                            <div class="invalid-feedback profile-feedback" id="lastnameError"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="field-label" for="phone_number">
                                <i class="fas fa-phone"></i> Nomor Telepon <span class="req">*</span>
                            </label>
                            <div class="field-input-wrap">
                                <i class="fas fa-mobile-alt field-icon"></i>
                                <input type="text" name="phone_number" id="phone_number"
                                    class="form-control profile-input is-edit-allowed"
                                    value="{{ $user->phone_number ?? '' }}" readonly required maxlength="20"
                                    placeholder="Belum diisi" inputmode="tel">
                            </div>
                            <div class="invalid-feedback profile-feedback" id="phone_numberError"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="field-label" for="nipDisplay">
                                <i class="fas fa-id-badge"></i> NIP
                            </label>
                            <div class="field-input-wrap">
                                <i class="fas fa-fingerprint field-icon"></i>
                                <input type="text" id="nipDisplay" class="form-control profile-readonly"
                                    value="{{ $user->nip ?? '-' }}" readonly disabled>
                            </div>
                            <small class="field-help locked"><i class="fas fa-lock"></i> NIP tidak dapat diubah</small>
                        </div>
                    </div>
                </div>

                {{-- INFORMASI AKUN --}}
                <div class="profile-section">
                    <div class="section-title">
                        <i class="fas fa-id-card"></i> Informasi Akun
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="field-label">
                                <i class="fas fa-envelope"></i> Email
                            </label>
                            <div class="field-input-wrap">
                                <i class="fas fa-at field-icon"></i>
                                <input type="email" class="form-control profile-readonly" value="{{ $user->email }}"
                                    readonly disabled>
                            </div>
                            <small class="field-help locked"><i class="fas fa-lock"></i> Email tidak dapat diubah</small>
                        </div>

                        <div class="col-md-6">
                            <label class="field-label">
                                <i class="fas fa-user-shield"></i> Hak Akses
                            </label>
                            <div class="field-input-wrap">
                                <i class="fas fa-{{ $roleIcon }} field-icon"></i>
                                <input type="text" class="form-control profile-readonly" value="{{ $roleName }}" readonly
                                    disabled>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ORGANISASI & POSISI --}}
                <div class="profile-section">
                    <div class="section-title">
                        <i class="fas fa-building"></i> Organisasi &amp; Posisi
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="field-label">
                                <i class="fas fa-building"></i> Direktorat
                            </label>
                            <div class="field-input-wrap">
                                <i class="fas fa-landmark field-icon"></i>
                                <input type="text" class="form-control profile-readonly" value="{{ $director ?? '-' }}"
                                    readonly disabled>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="field-label">
                                <i class="fas fa-sitemap"></i> Organisasi
                            </label>
                            <div class="field-input-wrap">
                                <i class="fas fa-network-wired field-icon"></i>
                                <input type="text" class="form-control profile-readonly" value="{{ $orgName ?? '-' }}"
                                    readonly disabled>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="field-label">
                                <i class="fas fa-briefcase"></i> Posisi
                            </label>
                            <div class="field-input-wrap">
                                <i class="fas fa-user-tie field-icon"></i>
                                <input type="text" class="form-control profile-readonly"
                                    value="{{ $roleId == 1 ? 'Super Admin' : $position ?? '-' }}" readonly disabled>
                            </div>
                            <small class="field-help locked"><i class="fas fa-lock"></i> Posisi diatur oleh Admin</small>
                        </div>
                    </div>
                </div>

                {{-- KEAMANAN: PASSWORD --}}
                <div class="profile-section section-password" data-editable="true">
                    <div class="section-title" style="color:#b88500;">
                        <i class="fas fa-lock"></i> Ubah Password
                        <span class="badge-edit" style="background: rgba(255, 193, 7, 0.2); color:#b88500;">
                            <i class="fas fa-pen"></i> Opsional
                        </span>
                    </div>
                    <p class="text-muted small mb-3">
                        <i class="fas fa-info-circle me-1"></i>
                        Kosongkan jika tidak ingin mengubah password. Minimal 6 karakter.
                    </p>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="field-label" for="password">
                                <i class="fas fa-key"></i> Password Baru
                            </label>
                            <div class="input-group">
                                <div class="field-input-wrap flex-grow-1">
                                    <i class="fas fa-lock field-icon"></i>
                                    <input type="password" name="password" id="password"
                                        class="form-control profile-input is-edit-allowed" placeholder="Minimal 6 karakter"
                                        autocomplete="off" data-form-type="other" data-lpignore="true"
                                        data-1p-ignore="true" data-bwignore="true">
                                </div>
                                <button type="button" class="btn password-toggle" onclick="togglePassword('password')">
                                    <i class="fas fa-eye" id="passwordEye"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback profile-feedback d-block" id="passwordError"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="field-label" for="password_confirmation">
                                <i class="fas fa-check-double"></i> Konfirmasi Password
                            </label>
                            <div class="input-group">
                                <div class="field-input-wrap flex-grow-1">
                                    <i class="fas fa-lock field-icon"></i>
                                    <input type="password" name="password_confirmation" id="password_confirmation"
                                        class="form-control profile-input is-edit-allowed" placeholder="Ulangi password baru"
                                        autocomplete="off" data-form-type="other" data-lpignore="true"
                                        data-1p-ignore="true" data-bwignore="true">
                                </div>
                                <button type="button" class="btn password-toggle"
                                    onclick="togglePassword('password_confirmation')">
                                    <i class="fas fa-eye" id="password_confirmationEye"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback profile-feedback d-block" id="password_confirmationError"></div>
                        </div>
                    </div>
                </div>

                {{-- FOOTER ACTION BAR (di dalam wrapper card, hanya muncul saat edit mode) --}}
                <div class="profile-footer" id="profileFooter">
                    <div class="d-flex gap-2 ms-auto">
                        <button type="button" class="btn btn-cancel-profile" onclick="cancelEdit()">
                                Batal
                        </button>
                        <button type="button" class="btn btn-save-profile" onclick="submitProfile()">
                            <i class="fas fa-save me-1"></i>Simpan Perubahan
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        // STATE
        let isEditMode = false;
        let isDirty = false;
        let originalValues = {};

        // EDIT MODE TOGGLE
        function toggleEditMode() {
            if (isEditMode) {
                cancelEdit();
            } else {
                enableEditMode();
            }
        }

        function enableEditMode() {
            isEditMode = true;
            isDirty = false;
            document.body.classList.add('edit-mode');

            // simpan nilai awal field editable
            document.querySelectorAll('.profile-input.is-edit-allowed').forEach(input => {
                if (input.name) originalValues[input.name] = input.value;
                input.removeAttribute('readonly');
            });

            // tombol & UI
            showById('editModeBtn', false);
            showById('photoActions', true, 'block');

            // avatar editable
            document.getElementById('avatarWrapper')?.classList.add('editable');

            // scroll ke section data pribadi agar fokus ke form
            const target = document.querySelector('.profile-section[data-editable="true"]');
            if (target) {
                setTimeout(() => target.scrollIntoView({ behavior: 'smooth', block: 'start' }), 80);
            }
        }

        async function cancelEdit() {
            if (isDirty) {
                const ok = await swalConfirm({
                    title: 'Batalkan perubahan?',
                    text: 'Perubahan yang belum disimpan akan hilang.',
                    icon: 'warning',
                    confirmButtonText: 'Ya, batalkan',
                    cancelButtonText: 'Lanjut edit'
                });
                if (!ok) return;
            }

            // restore values
            document.querySelectorAll('.profile-input.is-edit-allowed').forEach(input => {
                if (input.name && Object.prototype.hasOwnProperty.call(originalValues, input.name)) {
                    input.value = originalValues[input.name];
                }
                input.setAttribute('readonly', true);
                input.classList.remove('is-invalid');
            });

            // clear password
            setValueIfExist('password', '');
            setValueIfExist('password_confirmation', '');

            // refresh display name
            updateDisplayName();

            // UI reset
            document.body.classList.remove('edit-mode');
            showById('editModeBtn', true, 'inline-flex');
            showById('photoActions', false);
            document.getElementById('avatarWrapper')?.classList.remove('editable');

            isEditMode = false;
            isDirty = false;
            clearErrors();
        }

        // PHOTO UPLOAD
        function triggerPhotoUpload() {
            if (!isEditMode) return;
            document.getElementById('profileImageInput')?.click();
        }

        document.addEventListener('DOMContentLoaded', function () {
            const wrapper = document.getElementById('avatarWrapper');
            const fileInput = document.getElementById('profileImageInput');

            wrapper?.addEventListener('click', () => {
                if (isEditMode) fileInput?.click();
            });

            fileInput?.addEventListener('change', async function (e) {
                const file = e.target.files?.[0];
                if (!file) return;

                if (file.size > 2 * 1024 * 1024) {
                    await showAlert('Ukuran file terlalu besar. Maksimal 2MB.', 'error');
                    fileInput.value = '';
                    return;
                }

                const allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!allowed.includes(file.type)) {
                    await showAlert('Format file tidak didukung. Gunakan JPG, PNG, atau GIF.', 'error');
                    fileInput.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = (ev) => previewImage(ev.target.result);
                reader.readAsDataURL(file);
                isDirty = true;
            });
        });

        function previewImage(src) {
            const wrapper = document.getElementById('avatarWrapper');
            if (!wrapper) return;

            const current = document.getElementById('profileImagePreview');
            current?.remove();

            const img = document.createElement('img');
            img.id = 'profileImagePreview';
            img.src = src;
            img.alt = 'profile-photo';
            wrapper.insertBefore(img, wrapper.firstChild);
            wrapper.classList.remove('is-placeholder');

            // pastikan tombol hapus tampil
            ensureDeleteBtnVisible();
        }

        function ensureDeleteBtnVisible() {
            const photoActions = document.getElementById('photoActions');
            if (!photoActions) return;

            let btn = document.getElementById('btnDeletePhoto');
            if (!btn) {
                btn = document.createElement('button');
                btn.id = 'btnDeletePhoto';
                btn.type = 'button';
                btn.className = 'btn btn-outline-danger btn-photo-action';
                btn.innerHTML = '<i class="fas fa-trash me-1"></i>Hapus';
                btn.addEventListener('click', deleteProfilePhoto);
                photoActions.appendChild(btn);
            }
        }

        async function deleteProfilePhoto() {
            if (!isEditMode) return;

            const ok = await swalConfirm({
                title: 'Hapus foto profil?',
                text: 'Foto akan dihapus dan diganti avatar standar.',
                icon: 'warning',
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#dc3545'
            });
            if (!ok) return;

            Swal.fire({
                title: 'Menghapus...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            try {
                await fetch(@json(route('superadmin.deletePhoto')), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                // ganti foto dengan placeholder icon
                const wrapper = document.getElementById('avatarWrapper');
                const current = document.getElementById('profileImagePreview');
                current?.remove();

                if (wrapper) {
                    const ph = document.createElement('i');
                    ph.id = 'profileImagePreview';
                    ph.className = 'fas fa-user avatar-placeholder';
                    wrapper.insertBefore(ph, wrapper.firstChild);
                    wrapper.classList.add('is-placeholder');
                }

                document.getElementById('btnDeletePhoto')?.remove();

                const fileInput = document.getElementById('profileImageInput');
                if (fileInput) fileInput.value = '';

                isDirty = true;
                Swal.close();
                await showAlert('Foto profil berhasil dihapus.', 'success');
            } catch (e) {
                Swal.close();
                await showAlert(e.message || 'Gagal menghapus foto.', 'error');
            }
        }

        // PASSWORD VISIBILITY
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const eye = document.getElementById(fieldId + 'Eye');
            if (!field || !eye) return;

            if (field.type === 'password') {
                field.type = 'text';
                eye.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                field.type = 'password';
                eye.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        // VALIDASI
        const validationRules = {
            firstname: {
                required: true,
                maxLength: 50,
                pattern: /^[A-Za-z.\s]+$/,
                patternMessage: 'Hanya huruf, titik, dan spasi.'
            },
            lastname: {
                required: false,
                maxLength: 50,
                pattern: /^[A-Za-z.\s]*$/,
                patternMessage: 'Hanya huruf, titik, dan spasi.'
            },
            phone_number: {
                required: true,
                maxLength: 20,
                pattern: /^[0-9+\-\s]+$/,
                patternMessage: 'Format nomor telepon tidak valid.'
            }
        };

        function validateField(fieldId, rules) {
            const field = document.getElementById(fieldId);
            const errorDiv = document.getElementById(fieldId + 'Error');
            if (!field || !errorDiv) return true;

            field.classList.remove('is-invalid');
            errorDiv.textContent = '';

            const value = (field.value || '').trim();

            if (rules.required && !value) {
                errorDiv.textContent = 'Field ini wajib diisi.';
                field.classList.add('is-invalid');
                return false;
            }

            if (value) {
                if (rules.minLength && value.length < rules.minLength) {
                    errorDiv.textContent = `Minimal ${rules.minLength} karakter.`;
                    field.classList.add('is-invalid');
                    return false;
                }
                if (rules.maxLength && value.length > rules.maxLength) {
                    errorDiv.textContent = `Maksimal ${rules.maxLength} karakter.`;
                    field.classList.add('is-invalid');
                    return false;
                }
                if (rules.pattern && !rules.pattern.test(value)) {
                    errorDiv.textContent = rules.patternMessage || 'Format tidak valid.';
                    field.classList.add('is-invalid');
                    return false;
                }
            }

            return true;
        }

        function validatePasswords() {
            const password = document.getElementById('password');
            const confirm = document.getElementById('password_confirmation');
            const errorPwd = document.getElementById('passwordError');
            const errorConfirm = document.getElementById('password_confirmationError');
            if (!password || !confirm) return true;

            password.classList.remove('is-invalid');
            confirm.classList.remove('is-invalid');
            errorPwd.textContent = '';
            errorConfirm.textContent = '';

            let valid = true;

            if (password.value.length > 0) {
                if (password.value.length < 6) {
                    errorPwd.textContent = 'Password minimal 6 karakter.';
                    password.classList.add('is-invalid');
                    valid = false;
                }
                if (confirm.value.length === 0) {
                    errorConfirm.textContent = 'Konfirmasi password wajib diisi.';
                    confirm.classList.add('is-invalid');
                    valid = false;
                } else if (password.value !== confirm.value) {
                    errorConfirm.textContent = 'Konfirmasi password tidak sama.';
                    confirm.classList.add('is-invalid');
                    valid = false;
                }
            }

            return valid;
        }

        function clearErrors() {
            document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            document.querySelectorAll('.profile-feedback').forEach(el => el.textContent = '');
        }

        function updateDisplayName() {
            const fn = (document.getElementById('firstname')?.value || '').trim();
            const ln = (document.getElementById('lastname')?.value || '').trim();
            const target = document.getElementById('displayName');
            if (target) target.textContent = `${fn} ${ln}`.trim();
        }

        // BIND EVENTS
        document.addEventListener('DOMContentLoaded', function () {
            Object.keys(validationRules).forEach(id => {
                const field = document.getElementById(id);
                if (!field) return;

                field.addEventListener('blur', () => validateField(id, validationRules[id]));
                field.addEventListener('input', () => {
                    if (id === 'firstname' || id === 'lastname') updateDisplayName();
                    if (isEditMode) isDirty = true;
                });
            });

            document.getElementById('password')?.addEventListener('input', () => {
                if (isEditMode) isDirty = true;
                validatePasswords();
            });
            document.getElementById('password_confirmation')?.addEventListener('input', () => {
                if (isEditMode) isDirty = true;
                validatePasswords();
            });

            document.getElementById('formProfile')?.addEventListener('submit', function (e) {
                e.preventDefault();
                submitProfile();
            });

            // peringatan keluar halaman saat dirty
            window.addEventListener('beforeunload', function (e) {
                if (isEditMode && isDirty) {
                    e.preventDefault();
                    e.returnValue = '';
                }
            });
        async function submitProfile() {
            let ok = true;
            Object.keys(validationRules).forEach(id => {
                if (!validateField(id, validationRules[id])) ok = false;
            });
            if (!validatePasswords()) ok = false;

            if (!ok) {
                await showAlert('Mohon periksa kembali data yang diisi.', 'error');
                return;
            }

            const form = document.getElementById('formProfile');
            if (!form) return;

            Swal.fire({
                title: 'Menyimpan...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            try {
                const formData = new FormData(form);
                const res = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                let success = false;
                let message = '';

                const ct = res.headers.get('content-type') || '';
                if (ct.includes('application/json')) {
                    let data = null;
                    try { data = await res.json(); } catch (_) { }
                    success = !!(res.ok && (data?.success ?? true));
                    message = data?.message || (success ? 'Profil berhasil diperbarui.' : 'Gagal memperbarui profil.');
                } else {
                    success = res.ok;
                    message = success ? 'Profil berhasil diperbarui.' : `Request gagal (${res.status}).`;
                }

                Swal.close();

                if (!success) {
                    await showAlert(message, 'error');
                    return;
                }

                await showAlert(message, 'success');
                window.location.reload();
            } catch (err) {
                Swal.close();
                await showAlert(err.message || 'Terjadi kesalahan pada server', 'error');
            }
        }

        // UTIL
        function showById(id, show, displayMode) {
            const el = document.getElementById(id);
            if (!el) return;
            el.style.display = show ? (displayMode || '') : 'none';
        }

        function setValueIfExist(id, val) {
            const el = document.getElementById(id);
            if (el) el.value = val;
        }

        function showAlert(message, type) {
            const config = {
                text: message,
                confirmButtonText: 'OK',
                allowOutsideClick: false,
                allowEscapeKey: false,
                customClass: { confirmButton: 'btn btn-primary px-4 py-2' },
                buttonsStyling: false
            };

            if (type === 'success') {
                config.title = 'Berhasil!';
                config.icon = 'success';
                config.customClass.confirmButton = 'btn btn-success px-4 py-2';
            } else if (type === 'error') {
                config.title = 'Gagal!';
                config.icon = 'error';
                config.customClass.confirmButton = 'btn btn-danger px-4 py-2';
            } else if (type === 'warning') {
                config.title = 'Peringatan!';
                config.icon = 'warning';
                config.customClass.confirmButton = 'btn btn-warning px-4 py-2';
            } else if (type === 'info') {
                config.title = 'Informasi';
                config.icon = 'info';
                config.customClass.confirmButton = 'btn btn-info px-4 py-2';
            }

            if (typeof Swal !== 'undefined') return Swal.fire(config);
            alert(message);
            return Promise.resolve();
        }

        async function swalConfirm({ title, text, icon = 'warning', confirmButtonText = 'Ya', cancelButtonText = 'Batal', confirmButtonColor }) {
            if (typeof Swal === 'undefined') {
                return Promise.resolve(window.confirm(text || title || 'Lanjutkan?'));
            }
            const res = await Swal.fire({
                title,
                text,
                icon,
                showCancelButton: true,
                confirmButtonText,
                cancelButtonText,
                reverseButtons: true,
                customClass: {
                    confirmButton: 'btn btn-warning px-4 py-2 mx-1',
                    cancelButton: 'btn btn-secondary px-4 py-2 mx-1'
                },
                buttonsStyling: !!confirmButtonColor,
                confirmButtonColor
            });
            return res.isConfirmed;
        }
    </script>
@endpush
