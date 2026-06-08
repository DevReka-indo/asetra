<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title')</title>

    {{-- Favicon --}}
    <link rel="icon" href="{{ asset('assets/img/icon.png') }}" type="image/png">

    {{-- WebFont harus di head --}}
    <script src="{{ asset('assets/js/plugin/webfont/webfont.min.js') }}"></script>
    <script>
        WebFont.load({
            google: { families: ["Public Sans:300,400,500,600,700"] },
            custom: {
                families: ["Font Awesome 5 Solid","Font Awesome 5 Regular","Font Awesome 5 Brands","simple-line-icons"],
                urls: ["{{ asset('assets/css/fonts.min.css') }}"]
            },
            active: function() { sessionStorage.fonts = true; }
        });
    </script>

    {{-- CSS Eksternal --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/plugins.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/kaiadmin.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/info.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/aset.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/modal.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/scanner.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/profil.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/permission.css') }}">
    @stack('styles')

    <link rel="stylesheet" href="{{ asset('assets/css/sidebar-theme.css') }}">

    
</head>

<body>
    <div class="wrapper">

        {{-- Sidebar --}}
        <div class="sidebar sidebar-style-2" data-background-color="white">
            @include('partials.sidebar')
        </div>
        <div class="sidebar-backdrop"></div>

        {{-- Main Panel --}}
        <div class="main-panel">
            @include('partials.header')

            <div class="container" style="margin-top: 0;">
                <div class="page-inner">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>

    {{-- JS: jQuery --}}
    <script src="{{ asset('assets/js/core/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugin/select2/select2.full.min.js') }}"></script>
    <script src="{{ asset('assets/js/core/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugin/chart.js/chart.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugin/jquery.sparkline/jquery.sparkline.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugin/chart-circle/circles.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugin/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugin/bootstrap-notify/bootstrap-notify.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugin/jsvectormap/jsvectormap.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugin/jsvectormap/world.js') }}"></script>
    <script src="{{ asset('assets/js/plugin/gmaps/gmaps.js') }}"></script>
    <script src="{{ asset('assets/js/plugin/sweetalert/sweetalert.min.js') }}"></script>
    <script src="{{ asset('assets/js/kaiadmin.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

    @stack('scripts')

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            // Script Toggle Sidebar (Mobile/Minimize)
            const sidebar = document.querySelector('.sidebar.sidebar-style-2');
            const backdrop = document.querySelector('.sidebar-backdrop');
            const toggleBtn = document.querySelector('.toggle-sidebar');
            const body = document.body;

            if (toggleBtn && sidebar && backdrop) {
                function openSidebar() {
                    sidebar.classList.add('active');
                    backdrop.classList.add('show');
                    body.classList.add('sidebar-open');
                }

                function closeSidebar() {
                    sidebar.classList.remove('active');
                    backdrop.classList.remove('show');
                    body.classList.remove('sidebar-open');
                }

                function toggleSidebar() {
                    if (sidebar.classList.contains('active')) {
                        closeSidebar();
                    } else {
                        openSidebar();
                    }
                }

                toggleBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    toggleSidebar();
                });

                backdrop.addEventListener('click', closeSidebar);

                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && sidebar.classList.contains('active')) {
                        closeSidebar();
                    }
                });

                window.addEventListener('resize', function() {
                    if (window.innerWidth > 991.98) closeSidebar();
                });
            }
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Script Handle Logika Klik Dropdown Sidebar
            document.querySelectorAll('.nav-link[data-toggle="collapse"]').forEach(function (link) {
                link.addEventListener('click', function (e) {
                    e.preventDefault(); 

                    var targetId = this.getAttribute('href');
                    var target = document.querySelector(targetId);

                    if (!target) return;

                    var isOpen = target.classList.contains('show');
                    var parentLi = this.closest('.nav-item'); 

                    document.querySelectorAll('.nav-collapse.collapse.show').forEach(function (el) {
                        el.classList.remove('show');
                        
                        var otherParentLi = el.closest('.nav-item');
                        if(otherParentLi) {
                            otherParentLi.classList.remove('active_'); 
                            var otherLink = otherParentLi.querySelector('.nav-link[data-toggle="collapse"]');
                            if(otherLink){
                                otherLink.setAttribute('aria-expanded', 'false');
                                otherLink.classList.add('collapsed');
                            }
                        }
                    });

                    document.querySelectorAll('.sidebar-wrapper .nav-item.active_').forEach(function(activeLi) {
                        if (activeLi !== parentLi) {
                            activeLi.classList.remove('active_');
                        }
                    });

                    if (!isOpen) {
                        target.classList.add('show');
                        link.setAttribute('aria-expanded', 'true');
                        link.classList.remove('collapsed');
                        parentLi.classList.add('active_'); 
                    } else {
                        target.classList.remove('show'); 
                        link.setAttribute('aria-expanded', 'false');
                        link.classList.add('collapsed');
                        parentLi.classList.remove('active_'); 
                    }
                });
            });
        });
    </script>
    <script>
        window.addEventListener('pageshow', function(event) {
            let isBackForward = false;
            if (event.persisted) {
                isBackForward = true;
            } else {
                const perfEntries = performance.getEntriesByType("navigation");
                if (perfEntries.length > 0 && perfEntries[perfEntries.length - 1].type === 'back_forward') {
                    isBackForward = true;
                }
            }
            if (isBackForward) {
                window.location.reload();
            }
        });
    </script>
</body>
</html>