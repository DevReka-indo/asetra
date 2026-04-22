@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@section('content')
    <div class="container-fluid px-1 py-0 mt-0">
        <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold mb-0">Manajemen Pengguna</h3>
        <ul class="breadcrumbs d-flex align-items-center p-0 m-0" style="list-style: none;"> 
            <li class="nav-home d-flex align-items-center">
                <a href="{{ route('superadmin.dashboard') }}" class="text-muted text-decoration-none d-flex align-items-center">
                    <i class="fas fa-home me-2" style="font-size: 15px;"></i>
                <span style="font-size: 14px; font-weight: 500; position: relative; top: 2px;">Dashboard</span>                    
                </a>                
            </li>
            <li class="separator text-muted d-flex align-items-center px-2">
                <span style="font-size: 14px; position: relative; top: 2px;">-</span>
            </li>
            <li class="nav-item d-flex align-items-center">
                <span class="text-muted" style="font-size: 14px; font-weight: 500; position: relative; top: 2px;">Manajemen Pengguna</span>
            </li>
        </ul>
    </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('user.manage') }}">

                    <div class="d-flex gap-2 flex-wrap justify-content-end align-items-center mb-3 pb-3 border-bottom">
                        <button type="button" class="btn btn-success px-3 rounded-3 d-flex align-items-center" onclick="showUploadModal()">
                            <i class="far fa-file-excel me-1"></i> Import File
                        </button>
                        <a href="{{ route('user.create') }}" class="btn btn-primary px-3 rounded-3 d-flex align-items-center">
                            <i class="fas fa-plus me-1"></i> Tambah Pengguna
                        </a>
                    </div>

                    <div class="row g-2 align-items-end">

                        {{-- Entries --}}
                        <div class="col-md-1">
                            <label class="form-label fw-bold small text-muted text-uppercase" style="font-size: 0.7rem;">Entries</label>
                            <select name="per_page" class="form-select form-select-sm rounded-3 w-100">
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
                                <input type="text" name="search" class="form-control border-0 shadow-none bg-transparent"
                                    placeholder="Cari Nama atau NIP..." value="{{ request('search') }}">
                            </div>
                        </div>

                        {{-- Filter Role --}}
                        <div class="col-md-2">
                            <label class="form-label fw-bold small text-muted text-uppercase" style="font-size: 0.7rem;">Hak Akses</label>
                            <select name="role" class="form-select form-select-sm rounded-3 w-100">
                                <option value="">Semua Role</option>
                                <option value="1" {{ request('role') === '1' ? 'selected' : '' }}>Superadmin</option>
                                <option value="3" {{ request('role') === '3' ? 'selected' : '' }}>Admin</option>
                                <option value="2" {{ request('role') === '2' ? 'selected' : '' }}>User</option>
                            </select>
                        </div>

                        {{-- Filter Status --}}
                        <div class="col-md-2">
                            <label class="form-label fw-bold small text-muted text-uppercase" style="font-size: 0.7rem;">Status User</label>
                            <select name="view" class="form-select form-select-sm rounded-3 w-100">
                                <option value="all" {{ $view == 'all' ? 'selected' : '' }}>Semua User</option>
                                <option value="active" {{ $view == 'active' ? 'selected' : '' }}>User Aktif</option>
                                <option value="deleted" {{ $view == 'deleted' ? 'selected' : '' }}>User Non-Aktif</option>
                            </select>
                        </div>

                        <div class="col-auto ms-auto d-flex gap-2">
                            <a href="{{ route('user.manage') }}" class="btn btn-sm px-4 rounded-3 d-flex align-items-center text-white" style="background-color: #1b53a7; border-color: #48abf7;" title="Reset Filter">
                                <i class="fas fa-undo me-1"></i> Reset
                            </a>
                        </div>

                    </div>

                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered custom-table-bagian align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center">Nama</th>
                                <th class="text-center">NIP</th>
                                <th class="text-center">Bagian Kerja</th>
                                <th class="text-center">Posisi</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Hak Akses</th>
                                @if ($view !== 'deleted')
                                    <th>Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if ($user->profile_image)
                                                <img src="data:image/png;base64,{{ $user->profile_image }}"
                                                    class="rounded-circle me-2" width="35" height="35">
                                            @else
                                                <i class="fas fa-user-circle fa-2x text-secondary me-2"></i>
                                            @endif
                                            {{ $user->firstname }} {{ $user->lastname }}
                                        </div>
                                    </td>
                                    <td>{{ $user->nip }}</td>
                                    <td>
                                        @if ($user->unit)
                                            {{ $user->unit->name_unit }}
                                        @elseif($user->section)
                                            {{ $user->section->name_section }}
                                        @elseif($user->department)
                                            {{ $user->department->name_department }}
                                        @elseif($user->divisi)
                                            {{ $user->divisi->nm_divisi }}
                                        @elseif($user->director)
                                            {{ $user->director->name_director }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $user->position->nm_position ?? '-' }}</td>
                                    <td class="text-center">
                                        @if ($user->deleted_at)
                                            <form action="{{ route('user-manage.restore', $user->id) }}" method="PUT"
                                                class="d-inline restore-form">
                                                @csrf
                                                @method('PUT')
                                                <button type="button" class="btn btn-danger btn-sm btn-restore"
                                                    style="width: 80px;" data-id="{{ $user->id }}"
                                                    data-firstname="{{ $user->firstname }}"
                                                    data-lastname="{{ $user->lastname }}">Non-Aktif</button>
                                            </form>
                                        @else
                                            <form action="{{ route('user-manage.destroy', $user->id) }}" method="POST"
                                                class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-success btn-sm btn-delete"
                                                    style="width: 80px;" data-id="{{ $user->id }}"
                                                    data-firstname="{{ $user->firstname }}"
                                                    data-lastname="{{ $user->lastname }}">Aktif</button>
                                            </form>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($user->role->id_role == 1)
                                            <span class="badge bg-primary">
                                                Superadmin
                                            </span>
                                        @elseif($user->role->id_role == 2)
                                            <span class="badge bg-info">
                                                User
                                            </span>
                                        @elseif($user->role->id_role == 3)
                                            <span class="badge bg-warning">
                                                Admin
                                            </span>
                                        @endif
                                    </td>
                                    @if ($view !== 'deleted')
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-2">

                                                 <!-- Tombol View User -->
                                                <a href="{{ route('user.show', $user->id) }}"
                                                    class="btn btn-sm rounded-circle text-white border-0"
                                                    style="background-color:#51a1f1; width:30px; height:30px; display:flex; align-items:center; justify-content:center;"
                                                    title="Lihat Detail">
                                                    <i class="fa-solid fa-eye"></i>
                                                </a>

                                                <!-- Tombol Edit -->
                                                <a href="{{ route('user-manage.edit', $user->id) }}" class="btn btn-sm rounded-circle text-white border-0"
                                                    style="background-color:#FBC02D; width:30px; height:30px; display:flex; align-items:center; justify-content:center;"
                                                    title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>

                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Pengguna tidak ditemukan</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="d-flex justify-content-end mt-3">
                    {{ $users->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>

        </div>

    {{-- Modal Notifikasi --}}
    <div class="modal fade" id="successAddUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content bg-success text-white text-center rounded-3">
                <div class="modal-body">
                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                    <p>User berhasil ditambahkan!</p>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="successEditUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content bg-info text-white text-center rounded-3">
                <div class="modal-body">
                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                    <p>User berhasil diperbarui!</p>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="errorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content bg-danger text-white text-center rounded-3">
                <div class="modal-body">
                    <i class="fas fa-times-circle fa-2x mb-2"></i>
                    <p id="errorPasswordMessage">Terjadi kesalahan.</p>
                </div>
            </div>
        </div>
    </div>
    <style>
        .swal2-icon.no-border {
            border: none !important;
        }
    </style>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM Content Loaded - Script dimulai');

            $('#org_select_{{ $user->id ?? '-' }}').select2({
                theme: "bootstrap-5",
                placeholder: "Pilih Organisasi",
                allowClear: true,
                width: "100%"
            });
            $(document).ready(function() {
                console.log("Select2 loaded?", typeof $.fn.select2);
            });
        });

        // Function untuk menampilkan notifikasi
        function showNotification(message, type) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: type === 'success' ? 'Berhasil!' : 'Gagal',
                    text: message,
                    icon: type,
                    showConfirmButton: true, // tombol OK muncul
                    confirmButtonText: 'OK',
                    confirmButtonColor: type === 'success' ? '#253070' : '#d33'
                }).then((result) => {
                    if (result.isConfirmed && type === 'success') {
                        // kalau sukses dan user klik OK → balik ke index
                        window.location.href = "{{ route('user.manage') }}";
                    }
                });
            } else {
                alert(message);
                // fallback redirect
                if (type === 'success') {
                    window.location.href = "{{ route('user.manage') }}";
                }
            }
        }

        document.addEventListener("DOMContentLoaded", function() {
            @if (session('success'))
                showNotification('{{ session('success') }}', 'success');
            @endif

            @if (session('error'))
                showNotification('{{ session('error') }}', 'error');
            @endif
        });

        document.addEventListener("DOMContentLoaded", () => {
            document.querySelectorAll('.btn-delete').forEach(btn => {
                btn.addEventListener('click', function() {
                    let userId = this.dataset.id;
                    let firstname = this.dataset.firstname; // ambil dari atribut data-firstname
                    let lastname = this.dataset.lastname; // ambil dari atribut data-lastname
                    let fullName = `${firstname} ${lastname}`; // gabungkan nama lengkap

                    Swal.fire({
                        title: 'Yakin ingin menonaktifkan <b style="color:red;">' +
                            fullName + '</b>?',
                        text: "Pengguna yang tidak aktif tidak dapat menggunakan sistem. Pengguna nonaktif dapat diaktifkan kembali.",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#6c757d",
                        cancelButtonColor: "#d33",
                        confirmButtonText: "Ya, nonaktifkan",
                        cancelButtonText: "Batal"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch(`/user-manage/delete/${userId}`, {
                                    method: "DELETE",
                                    headers: {
                                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                                        "Accept": "application/json"
                                    }
                                })
                                .then(res => res.json())
                                .then(data => {
                                    Swal.fire({
                                        title: "Berhasil!",
                                        text: data.success,
                                        icon: "success"
                                    }).then(() => {
                                        location.reload(); // refresh tabel
                                    });
                                })
                                .catch(err => {
                                    Swal.fire("Error!", "Gagal menonaktifkan pengguna",
                                        "error");
                                });
                        }
                    });
                });
            });
        });

        document.addEventListener("DOMContentLoaded", () => {
            document.querySelectorAll('.btn-restore').forEach(btn => {
                btn.addEventListener('click', function() {
                    let userId = this.dataset.id;
                    let firstname = this.dataset.firstname; // ambil dari atribut data-firstname
                    let lastname = this.dataset.lastname; // ambil dari atribut data-lastname
                    let fullName = `${firstname} ${lastname}`; // gabungkan nama lengkap

                    Swal.fire({
                        title: 'Yakin ingin mengaktifkan <b style="color:green;">' +
                            fullName + '</b>?',
                        text: "Pengguna yang diaktifkan dapat kembali menggunakan sistem.",
                        icon: "question",
                        showCancelButton: true,
                        confirmButtonColor: "#253070",
                        cancelButtonColor: "#6c757d",
                        confirmButtonText: "Ya, aktifkan",
                        cancelButtonText: "Batal"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch(`/user-manage/restore/${userId}`, {
                                    method: "PUT",
                                    headers: {
                                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                                        "Accept": "application/json"
                                    }
                                })
                                .then(res => res.json())
                                .then(data => {
                                    Swal.fire({
                                        title: "Berhasil!",
                                        text: data.success,
                                        icon: "success"
                                    }).then(() => {
                                        location.reload(); // refresh tabel
                                    });
                                })
                                .catch(err => {
                                    Swal.fire("Error!", "Gagal mengaktifkan pengguna",
                                        "error");
                                });
                        }
                    });
                });
            });
        });

        // Function untuk mengupdate parent_type saat parent_id berubah
        document.querySelectorAll('.parent_id_select').forEach(function(select) {

            // set parent_type saat pilihan berubah
            select.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const type = selectedOption ? selectedOption.getAttribute('data-type') : '';

                const hiddenInput = this.closest('.col-md-6').querySelector('.parent_type_input');
                if (hiddenInput) hiddenInput.value = type || '';
            });

            select.dispatchEvent(new Event('change'));
        });
        // document.querySelectorAll('.parent_id_select').forEach(function(select) {
        //     select.addEventListener('change', function() {
        //         var selectedOption = this.options[this.selectedIndex];
        //         var type = selectedOption.getAttribute('data-type');

        //         // cari hidden input di parent div yang sama
        //         var hiddenInput = this.closest('.col-md-6').querySelector('.parent_type_input');
        //         hiddenInput.value = type;
        //         console.log('Selected type:', type, 'for parent ID:', this.value);
        //     });
        // });

        //Create parent type
        document.getElementById('parent_id').addEventListener('change', function() {
            var selectedOption = this.options[this.selectedIndex];
            var type = selectedOption.getAttribute('data-type');
            document.getElementById('parent_type').value = type;
            console.log('Selected type:', type, 'for parent ID:', this.value);

        });


        document.addEventListener('DOMContentLoaded', function() {
            const allPositions = @json($positions);

            const positionMap = {
                'director': [1],
                'divisi': [2, 3, 4],
                'department': [3, 4, 5, 6, 7, 8],
                'section': [5, 6, 7, 8, 9],
                'unit': [9]
            };

            document.querySelectorAll('.edit_parent_id').forEach(function(parentSelect) {
                const positionSelect = parentSelect.closest('.modal').querySelector('.edit_position');
                const hiddenType = parentSelect.closest('.modal').querySelector('.edit_parent_type');

                function updatePositions() {
                    const selectedOption = parentSelect.options[parentSelect.selectedIndex];
                    const type = selectedOption ? selectedOption.getAttribute('data-type') : null;

                    // Set hidden input
                    hiddenType.value = type;

                    // Kosongkan posisi
                    positionSelect.innerHTML = '';

                    if (type && positionMap[type]) {
                        positionSelect.disabled = false;
                        let filtered = allPositions.filter(pos => positionMap[type].includes(pos
                            .id_position));
                        filtered.forEach(pos => {
                            let opt = document.createElement('option');
                            opt.value = pos.id_position;
                            opt.textContent = pos.nm_position;
                            positionSelect.appendChild(opt);
                        });
                    } else {
                        positionSelect.disabled = true;
                        let opt = document.createElement('option');
                        opt.textContent = '-- Pilih posisi setelah pilih induk --';
                        positionSelect.appendChild(opt);
                    }
                }

                // Run pertama kali
                updatePositions();

                // Event listener
                parentSelect.addEventListener('change', updatePositions);
            });
        });



        document.getElementById('edit_parent_id').addEventListener('change', function() {
            var selectedOption = this.options[this.selectedIndex];
            var type = selectedOption.getAttribute('data-type');
            document.getElementById('edit_parent_type').value = type;
        });

        function showUploadModal() {
            Swal.fire({
                title: 'Import File?',
                html: `
            Anda dapat mengunggah file Excel untuk menambahkan pengguna baru.<br>
            Unduh format file Excel <a href="/Format Data User SIPO.xlsx" target="_blank">disini</a>.<br>
            <span class="text-danger" style="font-size: medium">
                Hanya mendukung format <strong>.xlsx</strong>
            </span>
            <br><br>
            <input type="file" id="fileInput"
                class="form-control rounded-3"
                style="padding:20px;"
                accept=".xlsx">
        `,
                iconHtml: `<i class="fas fa-cloud-arrow-up"></i>`,
                customClass: {
                    icon: 'no-border'
                },
                showCancelButton: true,
                cancelButtonText: 'Batal',
                confirmButtonText: 'Unggah',
                preConfirm: () => {
                    const file = document.getElementById('fileInput').files[0];
                    if (!file) {
                        Swal.showValidationMessage('Harap pilih file Excel yang valid');
                        return false;
                    }

                    // Check extension
                    const validExtensions = ['xlsx'];
                    const fileExtension = file.name.split('.').pop().toLowerCase();
                    if (!validExtensions.includes(fileExtension)) {
                        Swal.showValidationMessage(
                            'Format file tidak valid. Harap pilih file Excel (.xlsx)');
                        return false;
                    }

                    // Prepare FormData
                    const formData = new FormData();
                    formData.append('file_user', file);

                    // Send to backend
                    return fetch("{{ route('user-manage.import') }}", {
                            method: "POST",
                            body: formData,
                            headers: {
                                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                                "X-Requested-With": "XMLHttpRequest",
                                "Accept": "application/json"
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (!data.status) {
                                throw new Error(data.message || "Gagal mengimpor file");
                            }
                            return data; 
                        })
                        .catch(error => {
                            Swal.showValidationMessage(error.message);
                        });
                }
            }).then((result) => {
                console.log(result);

                Swal.fire({
                    icon: result.value.status ? 'success' : 'error',
                    title: result.value.status ? 'Berhasil' : 'Gagal',
                    text: result.value.message,
                    //confirmButtonColor: '#253070',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        location.reload();
                    }
                });
            });
        }
    </script>
@endpush
