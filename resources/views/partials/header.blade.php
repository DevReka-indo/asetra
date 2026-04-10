<nav class="navbar navbar-expand-lg bg-white border-bottom shadow-sm">
    <div class="container-fluid">

        <button type="button" class="custom-toggle-sidebar" aria-label="Toggle sidebar" 
            style="background: transparent; border: none; padding: 5px 10px; cursor: pointer; outline: none;">
            <i class="fa fa-bars" style="font-size: 20px; color: #253070;"></i>
        </button>

        <div class="flex-grow-1"></div>

        <ul class="navbar-nav ms-auto align-items-center" style="gap:24px;">
            <li class="nav-item dropdown" style="list-style: none;">
                <a class="nav-link" href="#" id="notifDropdown" role="button"
                    data-bs-toggle="dropdown" aria-expanded="false"
                    style="background: #E9E6EB; width: 42px; height: 42px; border-radius: 50%; display: flex; align-items: center; justify-content: center; position: relative; padding: 0;">
                    
                    <i class="fa fa-bell" style="color:#253070; font-size: 22px;"></i>
                    
                    <span id="notif-count" 
                        style="display: none; position: absolute; top: 0px; right: 0px; background: #dc3545; color: white; font-size: 10px; font-weight: bold; min-width: 17px; height: 17px; border-radius: 50%; border: 2px solid white; align-items: center; justify-content: center;">
                        0
                    </span>
                </a>
                
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" aria-labelledby="notifDropdown" style="width:300px;">
                    <li class="dropdown-header">Notifikasi</li>
                    <li><div id="notif-body" class="px-3 py-2 text-center text-muted small">Memuat...</div></li>
                </ul>
            </li>

            <li class="nav-item dropdown" style="list-style: none; margin-left: -20px;"> 
                <a class="nav-link" href="#" id="profileDropdown" role="button"
                    data-bs-toggle="dropdown" aria-expanded="false"
                    style="background: transparent; width: 42px; height: 42px; display: flex; align-items: center; justify-content: center; padding: 0;">
                    
                    @if (Auth::user()->profile_image)
                        <img src="data:image/png;base64,{{ Auth::user()->profile_image }}" alt="profile"
                            class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover; border: 1px solid #ddd;">
                    @else
                        <i class="fa fa-user-circle" style="color:#253070; font-size: 42px;"></i>
                    @endif
                </a>

                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" aria-labelledby="profileDropdown" style="min-width:240px;">
                    <li class="px-3 py-2">
                        <div class="fw-bold" style="font-size: 14px;">{{ Auth::user()->firstname }}</div>
                        <div class="text-muted small">{{ Auth::user()->role_id_role == 1 ? 'Admin' : 'Staff' }}</div>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="{{ route('edit-profile') }}">Profil</a></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button class="dropdown-item text-danger" type="submit">Keluar</button>
                        </form>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</nav>

{{-- Toggle Sidebar + Backdrop --}}
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.querySelector('.custom-toggle-sidebar');
            const sidebar = document.querySelector('.sidebar');
            const backdrop = document.querySelector('.sidebar-backdrop');
            const body = document.body;

            const isMobile = () => window.matchMedia('(max-width: 991.98px)').matches;

            function toggleSidebar(e) {
                if (e) {
                    e.preventDefault();
                    e.stopPropagation();
                }

                if (isMobile()) {
                    if (sidebar?.classList.contains('active')) {
                        closeMobileSidebar();
                    } else {
                        openMobileSidebar();
                    }
                } else {
                    body.classList.toggle('sidebar_minimize');
                }
            }

            function openMobileSidebar() {
                sidebar?.classList.add('active');
                backdrop?.classList.add('show');
                document.documentElement.style.overflow = 'hidden';
                document.body.style.overflow = 'hidden';
            }

            function closeMobileSidebar() {
                sidebar?.classList.remove('active');
                backdrop?.classList.remove('show');
                document.documentElement.style.overflow = '';
                document.body.style.overflow = '';
            }

            if (toggleBtn) {
                toggleBtn.addEventListener('click', toggleSidebar);
            }

            if (backdrop) {
                backdrop.addEventListener('click', closeMobileSidebar);
            }

            document.addEventListener('keydown', e => {
                if (e.key === 'Escape') {
                    if (isMobile() && sidebar?.classList.contains('active')) {
                        closeMobileSidebar();
                    }
                }
            });

            document.querySelectorAll('.sidebar a').forEach(a => {
                a.addEventListener('click', (e) => {
                    if (!isMobile()) return; // Abaikan jika di Desktop

                    if (a.hasAttribute('data-bs-toggle') && a.getAttribute('data-bs-toggle') === 'collapse') {
                        return;
                    }

                    if (a.getAttribute('href') && a.getAttribute('href') !== '#') {
                        closeMobileSidebar();
                    }
                });
            });

            window.addEventListener('resize', () => {
                if (!isMobile() && sidebar?.classList.contains('active')) {
                    closeMobileSidebar(); 
                }
            });
        });
    </script>
@endpush