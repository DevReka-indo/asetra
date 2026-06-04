@extends('layouts.app')

@section('title', 'Manajemen Hak Akses')

@section('content')
    @php
        $totalDept = $departments->count();
        $totalSection = $departments->sum(fn($d) => $d->section->count());
        $totalPerm = $permissions->count();
        $totalAssignments = $departments->sum(fn($d) => $d->permissions->count())
            + $departments->sum(fn($d) => $d->section->sum(fn($s) => $s->permissions->count()));
        $saOnlyNames = ['manage_organization', 'manage_users'];
    @endphp

    <div class="container-fluid px-1 py-0">

        {{-- Page Header + Breadcrumb --}}
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h3 class="fw-bold mb-0">Manajemen Hak Akses</h3>
            <ul class="perm-breadcrumb d-flex align-items-center p-0 m-0">
                <li class="perm-breadcrumb-home d-flex align-items-center">
                    <a href="{{ route('dashboard') }}"
                        class="text-muted text-decoration-none d-flex align-items-center">
                        <i class="fas fa-home me-2 perm-breadcrumb-icon"></i>
                        <span class="perm-breadcrumb-text">Dashboard</span>
                    </a>
                </li>
                <li class="perm-breadcrumb-separator text-muted d-flex align-items-center px-2">
                    <span class="perm-breadcrumb-text">-</span>
                </li>
                <li class="perm-breadcrumb-item d-flex align-items-center">
                    <span class="text-muted perm-breadcrumb-text">Manajemen Hak Akses</span>
                </li>
            </ul>
        </div>

        @if (session('success'))
            @push('scripts')
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: "{{ session('success') }}",
                            icon: 'success',
                            confirmButtonColor: '#003366',
                            confirmButtonText: 'OK',
                            customClass: {
                                popup: 'border-radius-1rem',
                            }
                        });
                    });
                </script>
            @endpush
        @endif

        <form id="permissionForm" method="POST" action="{{ route('permissions.update') }}">
            @csrf

            <div class="perm-shell">

                {{-- HERO --}}
                <div class="perm-hero">
                    <div class="position-relative perm-hero-inner">
                        <h2><i class="fas fa-user-shield me-2"></i>Matriks Hak Akses</h2>
                        <p class="hero-subtitle mb-0">
                            Kelola izin menu untuk setiap Departemen dan Bagian. Centang pada level Departemen otomatis
                            mewarisi ke seluruh Bagian di bawahnya.
                        </p>
                    </div>
                </div>

                {{-- STATS --}}
                <div class="stat-grid">
                    <div class="row g-3">
                        <div class="col-sm-6 col-lg-3">
                            <div class="stat-tile">
                                <div class="icon"><i class="fas fa-building"></i></div>
                                <div>
                                    <span class="label">Departemen</span>
                                    <div class="value">{{ number_format($totalDept) }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="stat-tile">
                                <div class="icon" style="background: rgba(23, 162, 184, 0.12); color:#17a2b8;">
                                    <i class="fas fa-sitemap"></i>
                                </div>
                                <div>
                                    <span class="label">Bagian</span>
                                    <div class="value">{{ number_format($totalSection) }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="stat-tile">
                                <div class="icon" style="background: rgba(255, 152, 0, 0.12); color:#fb8c00;">
                                    <i class="fas fa-key"></i>
                                </div>
                                <div>
                                    <span class="label">Jenis Izin</span>
                                    <div class="value">{{ number_format($totalPerm) }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="stat-tile">
                                <div class="icon" style="background: rgba(40, 167, 69, 0.12); color:#28a745;">
                                    <i class="fas fa-check-double"></i>
                                </div>
                                <div>
                                    <span class="label">Total Penugasan</span>
                                    <div class="value">{{ number_format($totalAssignments) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- TOOLBAR --}}
                <div class="perm-toolbar">
                    <div class="perm-search">
                        <i class="fas fa-search"></i>
                        <input type="text" id="permSearch" placeholder="Cari nama Departemen atau Bagian..."
                            autocomplete="off">
                    </div>

                    <span class="dirty-pill">
                        <i class="fas fa-circle dirty-pill-dot"></i>
                        Ada perubahan belum disimpan
                    </span>

                    <div class="d-flex gap-2 ms-auto flex-wrap">
                        <button type="button" class="toolbar-btn" onclick="toggleAllDepartments(true)">
                            <i class="fas fa-angle-double-down"></i> Buka Semua
                        </button>
                        <button type="button" class="toolbar-btn" onclick="toggleAllDepartments(false)">
                            <i class="fas fa-angle-double-up"></i> Tutup Semua
                        </button>
                        <button type="button" class="toolbar-btn btn-warning-action" onclick="checkAllGA()">
                            <i class="fas fa-check-double"></i> Centang Semua GA
                        </button>
                        <button type="button" class="toolbar-btn btn-danger-action" onclick="clearAll()">
                            <i class="fas fa-broom"></i> Bersihkan Semua
                        </button>
                    </div>
                </div>

                {{-- MATRIX --}}
                <div class="perm-matrix-wrap">
                    @if ($departments->isEmpty())
                        <div class="perm-empty">
                            <div class="empty-icon"><i class="fas fa-building"></i></div>
                            <h6 class="fw-bold mb-1">Belum ada struktur organisasi</h6>
                            <p class="small mb-0">Buat data Departemen terlebih dahulu untuk dapat mengatur hak akses.</p>
                        </div>
                    @else
                        <table class="perm-matrix">
                            <thead>
                                <tr>
                                    <th class="col-name">Struktur Organisasi</th>
                                    @foreach ($permissions as $permission)
                                        @php $isSaOnly = in_array($permission->name, $saOnlyNames); @endphp
                                        <th class="perm-permission-header {{ $isSaOnly ? 'is-sa-only' : '' }}"
                                            title="{{ $permission->name }}">
                                            <span class="perm-desc">
                                                @if ($isSaOnly)
                                                    <i class="fas fa-lock" style="font-size: 0.7rem;"></i>
                                                @endif
                                                {{ $permission->description }}
                                            </span>
                                            <span class="perm-code">{{ $permission->name }}</span>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody id="permTbody">
                                @foreach ($departments as $department)
                                    @php
                                        $hasSections = $department->section->isNotEmpty();
                                        $deptKey = 'dept-' . $department->id_department;
                                    @endphp

                                    {{-- Department Row --}}
                                    <tr class="row-dept" data-row-name="{{ strtolower($department->name_department) }}"
                                        data-dept-row="{{ $department->id_department }}">
                                        <td class="cell-name">
                                            <div class="d-flex align-items-center gap-2">
                                                @if ($hasSections)
                                                    <button type="button" class="toggle-chevron"
                                                        onclick="toggleDept('{{ $deptKey }}', this)"
                                                        aria-expanded="true">
                                                        <i class="fas fa-chevron-down toggle-icon"></i>
                                                    </button>
                                                @else
                                                    <span style="width: 22px;"></span>
                                                @endif
                                                <div class="row-avatar">
                                                    <i class="fas fa-building"></i>
                                                </div>
                                                <div>
                                                    <div class="row-name-text">{{ $department->name_department }}</div>
                                                    <div class="row-meta">
                                                        ID #{{ $department->id_department }}
                                                            @if ($hasSections)
                                                            &bull; {{ $department->section->count() }} Bagian
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        @foreach ($permissions as $permission)
                                            @php
                                                $hasDeptPerm = $department->permissions->contains($permission->id);
                                                $isGAOnly = !in_array($permission->name, $saOnlyNames);
                                            @endphp
                                            <td class="text-center">
                                                <div class="form-check form-switch">
                                                    <input
                                                        class="form-check-input permission-checkbox dept-permission {{ $isGAOnly ? 'ga-permission' : 'sa-permission' }}"
                                                        type="checkbox"
                                                        name="department_permissions[{{ $department->id_department }}][]"
                                                        value="{{ $permission->id }}" role="switch"
                                                        data-dept-id="{{ $department->id_department }}"
                                                        data-perm-id="{{ $permission->id }}"
                                                        {{ $hasDeptPerm ? 'checked' : '' }}
                                                        onchange="cascadePermission(this)">
                                                </div>
                                            </td>
                                        @endforeach
                                    </tr>

                                    {{-- Section Rows --}}
                                    @if ($hasSections)
                                        @foreach ($department->section as $section)
                                            <tr class="row-section section-of-{{ $deptKey }}"
                                                data-row-name="{{ strtolower($section->name_section) }}"
                                                data-parent-key="{{ $deptKey }}">
                                                <td class="cell-name">
                                                    <div class="d-flex align-items-center gap-2 ps-3">
                                                        <span style="width: 22px;"></span>
                                                        <div class="row-avatar">
                                                            <i class="fas fa-sitemap"></i>
                                                        </div>
                                                        <div>
                                                            <div class="row-name-text">{{ $section->name_section }}</div>
                                                            <div class="row-meta">ID #{{ $section->id_section }}</div>
                                                        </div>
                                                    </div>
                                                </td>

                                                @foreach ($permissions as $permission)
                                                    @php
                                                        $hasSecPerm = $section->permissions->contains($permission->id);
                                                        $isGAOnly = !in_array($permission->name, $saOnlyNames);
                                                    @endphp
                                                    <td class="text-center">
                                                        <div class="form-check form-switch">
                                                            <input
                                                                class="form-check-input permission-checkbox section-permission {{ $isGAOnly ? 'ga-permission' : 'sa-permission' }}"
                                                                type="checkbox"
                                                                name="section_permissions[{{ $section->id_section }}][]"
                                                                value="{{ $permission->id }}" role="switch"
                                                                data-parent-dept-id="{{ $department->id_department }}"
                                                                data-perm-id="{{ $permission->id }}"
                                                                {{ $hasSecPerm ? 'checked' : '' }}>
                                                        </div>
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>

                {{-- SAVE BAR --}}
                <div class="perm-savebar">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Klik <strong>Simpan Perubahan</strong> untuk menyimpan perubahan hak akses.
                    </small>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-reset-perm" onclick="resetForm()">
                            <i class="fas fa-undo me-1"></i> Reset
                        </button>
                        <button type="submit" class="btn btn-save-perm">
                            <i class="fas fa-save me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </div>
            </div>
        </form>

        {{-- HELP / LEGEND --}}
        <div class="help-card" id="helpCard">
            <div class="help-header" onclick="toggleHelp()">
                <i class="fas fa-circle-info" style="color:#003366;"></i>
                <h6>Panduan & Proteksi Keamanan</h6>
                <i class="fas fa-chevron-down chev"></i>
            </div>
            <div class="help-body">
                <ul class="help-list">
                    <li>
                        <strong>Pewarisan Departemen.</strong>
                        Mencentang hak akses pada level Departemen otomatis mewariskan ke seluruh Bagian di bawahnya.
                        Switch Bagian akan terkunci selama Departemen aktif.
                    </li>
                    <li>
                        <strong>Pengecualian Bagian.</strong>
                        Bila switch Departemen tidak aktif, Anda dapat mengaktifkan izin pada Bagian tertentu secara
                        spesifik.
                    </li>
                    <li class="warning">
                        <strong>Superadmin Bypass.</strong>
                        Pengguna dengan role <code>Superadmin</code> otomatis memiliki seluruh hak akses tanpa pengecekan
                        di tabel ini.
                    </li>
                    <li class="danger">
                        <strong>Izin Eksklusif Superadmin.</strong>
                        Izin <code>manage_organization</code> dan <code>manage_users</code> sebaiknya tidak ditautkan ke
                        struktur non-admin demi keamanan data.
                    </li>
                </ul>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // STATE
        let initialFormSnapshot = null;
        let isDirty = false;

        // DEPT/SECTION COLLAPSE
        function toggleDept(deptKey, btn) {
            const sections = document.querySelectorAll('.section-of-' + deptKey);
            const isCollapsed = btn.classList.toggle('collapsed');
            const expanded = !isCollapsed;
            btn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            sections.forEach(row => {
                row.style.display = expanded ? '' : 'none';
            });
        }

        function toggleAllDepartments(open) {
            document.querySelectorAll('.toggle-chevron').forEach(btn => {
                const isCollapsed = btn.classList.contains('collapsed');
                if (open && isCollapsed) {
                    btn.classList.remove('collapsed');
                    btn.setAttribute('aria-expanded', 'true');
                } else if (!open && !isCollapsed) {
                    btn.classList.add('collapsed');
                    btn.setAttribute('aria-expanded', 'false');
                }
            });
            document.querySelectorAll('.row-section').forEach(row => {
                row.style.display = open ? '' : 'none';
            });
        }

        // CASCADE: dept ON => sections inherit & locked, dept OFF => sections unlocked
        function cascadePermission(deptCheckbox) {
            const deptId = deptCheckbox.getAttribute('data-dept-id');
            const permId = deptCheckbox.getAttribute('data-perm-id');
            const isChecked = deptCheckbox.checked;

            const sectionCheckboxes = document.querySelectorAll(
                `.section-permission[data-parent-dept-id="${deptId}"][data-perm-id="${permId}"]`
            );

            sectionCheckboxes.forEach(cb => {
                if (isChecked) {
                    cb.checked = true;
                    cb.disabled = true;
                } else {
                    cb.disabled = false;
                    cb.checked = false;
                }
            });
        }

        // BULK ACTIONS
        function checkAllGA() {
            document.querySelectorAll('.dept-permission.ga-permission').forEach(cb => {
                cb.checked = true;
                cascadePermission(cb);
            });
            document.querySelectorAll('.section-permission.ga-permission').forEach(cb => {
                if (!cb.disabled) cb.checked = true;
            });
            markDirty();
        }

        async function clearAll() {
            const confirmed = await confirmReset('Bersihkan semua centang?', 'Semua hak akses Departemen & Bagian akan dihapus.');
            if (!confirmed) return;
            doClearAll();
            markDirty();
        }

        function doClearAll() {
            document.querySelectorAll('.permission-checkbox').forEach(cb => {
                cb.checked = false;
                cb.disabled = false;
            });
        }

        async function resetForm() {
            const confirmed = await confirmReset('Reset perubahan?', 'Semua perubahan akan dikembalikan ke kondisi terakhir tersimpan.');
            if (!confirmed) return;
            clearDirty();
            window.location.reload();
        }

        async function confirmReset(title, text) {
            if (typeof Swal === 'undefined') {
                return window.confirm((title || 'Lanjutkan?') + '\n' + (text || ''));
            }
            
            const result = await Swal.fire({
                title: title || 'Lanjutkan?',
                text: text || '',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#003366',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Lanjutkan',
                cancelButtonText: 'Batal',
                customClass: {
                    popup: 'border-radius-1rem',
                }
            });
            
            return result.isConfirmed;
        }

        // SEARCH
        function setupSearch() {
            const input = document.getElementById('permSearch');
            if (!input) return;

            let timer;
            input.addEventListener('input', () => {
                clearTimeout(timer);
                timer = setTimeout(() => applyFilter(input.value.trim().toLowerCase()), 100);
            });
        }

        function applyFilter(q) {
            const allRows = document.querySelectorAll('#permTbody tr');
            if (!q) {
                allRows.forEach(r => r.classList.remove('row-hidden'));
                return;
            }

            // 1. cocokkan tiap row dengan query
            const matchedDepts = new Set();
            allRows.forEach(row => {
                const name = row.getAttribute('data-row-name') || '';
                const isMatch = name.includes(q);
                if (isMatch) {
                    row.classList.remove('row-hidden');
                    if (row.classList.contains('row-section')) {
                        const parentKey = row.getAttribute('data-parent-key');
                        matchedDepts.add(parentKey);
                    } else {
                        matchedDepts.add('dept-' + row.getAttribute('data-dept-row'));
                    }
                } else {
                    row.classList.add('row-hidden');
                }
            });

            // 2. tampilkan parent dept untuk section yang match 
            allRows.forEach(row => {
                if (row.classList.contains('row-dept')) {
                    const key = 'dept-' + row.getAttribute('data-dept-row');
                    if (matchedDepts.has(key)) row.classList.remove('row-hidden');
                }
            });
        }

        // DIRTY TRACKING
        function markDirty() {
            document.body.classList.add('perm-dirty');
            isDirty = true;
        }

        function clearDirty() {
            document.body.classList.remove('perm-dirty');
            isDirty = false;
        }

        function setupDirtyTracking() {
            const form = document.getElementById('permissionForm');
            if (!form) return;

            // Take initial snapshot only on first user interaction with the form
            const initSnapshot = () => {
                if (initialFormSnapshot === null) {
                    initialFormSnapshot = serializeForm(form);
                }
            };

            form.addEventListener('focusin', initSnapshot, { once: true });
            form.addEventListener('click', initSnapshot, { once: true });
            form.addEventListener('change', () => {
                initSnapshot();
                const now = serializeForm(form);
                if (now !== initialFormSnapshot) {
                    markDirty();
                } else {
                    clearDirty();
                }
            });

            window.addEventListener('beforeunload', e => {
                if (isDirty) {
                    e.preventDefault();
                    e.returnValue = '';
                }
            });

            form.addEventListener('submit', () => {
                clearDirty();
            });
        }

        function serializeForm(form) {
            const data = new FormData(form);
            const arr = [];
            for (const [k, v] of data.entries()) arr.push(k + '=' + v);
            arr.sort();
            return arr.join('&');
        }

        // HELP COLLAPSE
        function toggleHelp() {
            document.getElementById('helpCard')?.classList.toggle('is-open');
        }

        // INIT
        document.addEventListener('DOMContentLoaded', function () {
            // sync cascade untuk dept yang sudah dicentang dari awal
            document.querySelectorAll('.dept-permission').forEach(cb => {
                if (cb.checked) cascadePermission(cb);
            });

            setupSearch();
            setupDirtyTracking();

            // buka help card secara default
            document.getElementById('helpCard')?.classList.add('is-open');
        });
    </script>
@endpush

