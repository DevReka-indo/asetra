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
                
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 py-2" aria-labelledby="notifDropdown" style="width:320px; max-height: 420px; overflow-y: auto;">
                    <li class="dropdown-header d-flex justify-content-between align-items-center px-3 pb-2 border-bottom" style="margin-bottom: 5px;">
                        <span class="fw-bold text-dark" style="font-size: 13px;">Notifikasi</span>
                        <a href="javascript:void(0)" id="mark-all-read-btn" class="text-decoration-none text-primary fw-semibold" style="font-size: 11px;">Tandai Semua Dibaca</a>
                    </li>
                    <div id="notif-list-container">
                        <li><div id="notif-body" class="px-3 py-3 text-center text-muted small">Memuat...</div></li>
                    </div>
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

            // --- NOTIFICATION AJAX SYSTEM ---
            function fetchNotifications() {
                $.ajax({
                    url: "{{ route('notifications.get') }}",
                    method: 'GET',
                    success: function(data) {
                        const count = data.unreadCount;
                        const notifCountBadge = $('#notif-count');
                        if (count > 0) {
                            notifCountBadge.text(count).css('display', 'inline-flex');
                        } else {
                            notifCountBadge.hide();
                        }

                        const listContainer = $('#notif-list-container');
                        listContainer.empty();

                        if (data.notifications.length === 0) {
                            listContainer.append('<li><div class="px-3 py-4 text-center text-muted small"><i class="fas fa-bell-slash d-block mb-2 text-muted" style="font-size: 18px; opacity:0.5;"></i>Belum ada notifikasi</div></li>');
                            return;
                        }

                        data.notifications.forEach(function(notif) {
                            let iconClass = 'fa-info-circle';
                            let bgStyle = 'background: rgba(108,117,125,.1); color: #6c757d;';
                            
                            if (notif.type === 'perbaikan') {
                                iconClass = 'fa-wrench';
                                bgStyle = 'background: rgba(255,159,28,.12); color: #ff9f1c;';
                            } else if (notif.type === 'monitoring') {
                                iconClass = 'fa-chart-line';
                                bgStyle = 'background: rgba(37,48,112,.1); color: #253070;';
                            } else if (notif.type === 'stock_opname') {
                                iconClass = 'fa-clipboard-check';
                                bgStyle = 'background: rgba(40,167,69,.1); color: #28a745;';
                            }

                            const itemBg = notif.read ? 'transparent' : 'rgba(37,48,112,.03)';
                            const itemHtml = `
                                <li>
                                    <a class="dropdown-item notif-item px-3 py-2.5 d-flex align-items-start gap-2 border-bottom" 
                                       href="${notif.url}" 
                                       data-id="${notif.id}" 
                                       style="white-space: normal; background-color: ${itemBg}; transition: background 0.2s ease; cursor: pointer;">
                                        <div class="notif-icon-circle rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" 
                                             style="width: 34px; height: 34px; ${bgStyle} font-size: 13px;">
                                            <i class="fas ${iconClass}"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="fw-bold text-dark" style="font-size: 12px; line-height: 1.3;">${notif.title}</div>
                                            <div class="text-muted mt-0.5" style="font-size: 11px; line-height: 1.3;">${notif.message}</div>
                                            <div class="text-muted mt-1 d-flex align-items-center gap-1" style="font-size: 10px;">
                                                <i class="far fa-clock" style="font-size: 10px;"></i> ${notif.time}
                                            </div>
                                        </div>
                                        ${!notif.read ? '<span class="unread-dot bg-primary rounded-circle align-self-center" style="width: 6px; height: 6px; flex-shrink:0;"></span>' : ''}
                                    </a>
                                </li>
                            `;
                            listContainer.append(itemHtml);
                        });
                    },
                    error: function(err) {
                        console.error('Failed to fetch notifications:', err);
                    }
                });
            }

            // Initial fetch and poll every 30 seconds
            fetchNotifications();
            setInterval(fetchNotifications, 30000);

            // Handle notification item click (mark as read then redirect)
            $(document).on('click', '.notif-item', function(e) {
                e.preventDefault();
                const notifId = $(this).data('id');
                const redirectUrl = $(this).attr('href');

                $.ajax({
                    url: `/notifications/${notifId}/read`,
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    },
                    success: function() {
                        window.location.href = redirectUrl;
                    },
                    error: function() {
                        window.location.href = redirectUrl;
                    }
                });
            });

            // Handle mark all as read
            $(document).on('click', '#mark-all-read-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();

                $.ajax({
                    url: "{{ route('notifications.readAll') }}",
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    },
                    success: function() {
                        fetchNotifications();
                    },
                    error: function(err) {
                        console.error('Failed to mark all notifications as read:', err);
                    }
                });
            });
        });
    </script>
@endpush