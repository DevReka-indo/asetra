<div class="sidebar-logo">
    <div class="logo-header d-flex align-items-center justify-content-center p-3 pt-4 pb-4" style="padding:14px 16px;">
        <a href="{{ url('dashboard') }}" class="logo" style="display:block; width:100%; text-decoration:none;">
            <div
                style="
                    background:#fff;
                    padding:10px 14px;
                    display:flex;
                    align-items:center;
                    justify-content:center;
                    overflow:hidden;
                    width:100%;
                ">
                <img src="/assets/img/Logo-reka.png" alt="SIPO"
                    style="display:block; max-width:100%; height:auto; max-height:60px; margin:0;" />
            </div>
        </a>
    </div>
</div>

<div class="sidebar-wrapper">
    <div class="sidebar-content">
        <ul class="nav nav-secondary" style="margin-top: 50px;">

        <!-- SUPERADMIN & GENERAL AFFAIRS -->
            @if (Auth::user()->role->nm_role == 'superadmin')
                <li class="nav-item {{ request()->routeIs('superadmin.dashboard') ? 'active_' : '' }}">
                    <a href="{{ route('superadmin.dashboard') }}" class="nav-link">
                        <i class="fas fa-home"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
            @elseif (Auth::user()->section_id_section == 12)
                <li class="nav-item {{ request()->routeIs('general-affairs.dashboard') ? 'active_' : '' }}">
                    <a href="{{ route('general-affairs.dashboard') }}" class="nav-link">
                        <i class="fas fa-home"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
            @endif

            <!-- SUPERADMIN & GENERAL AFFAIRS MENU -->
            @if (Auth::user()->role->nm_role == 'superadmin' || Auth::user()->section_id_section == 12)

                <li class="nav-item {{ request()->routeIs('lokasi-aset.*') ? 'active_' : '' }}">
                    <a href="{{ route('lokasi-aset.index') }}" class="nav-link">
                        <i class="fas fa-map-marker-alt"></i>
                        <p>Lokasi Aset</p>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('kategori-tetap.*') || request()->routeIs('kategori-inventaris.*') ? 'active_' : '' }}">
                    <a href="#jenisAsetDrop"
                        class="nav-link {{ request()->routeIs('kategori-tetap.*') || request()->routeIs('kategori-inventaris.*') ? '' : 'collapsed' }}" 
                        data-toggle="collapse"
                        aria-expanded="{{ request()->routeIs('kategori-tetap.*') || request()->routeIs('kategori-inventaris.*') ? 'true' : 'false' }}">
                            <i class="fas fa-boxes"></i>
                            <p>Kategori Aset</p>
                            <span class="caret"></span>
                    </a>

                    <div class="collapse {{ request()->routeIs('klasifikasi-tetap.*') || request()->routeIs('klasifikasi-inventaris.*') ? 'show' : '' }}" 
                        id="jenisAsetDrop">
                        <ul class="nav nav-collapse" style="margin-top: 0; padding-bottom: 10px;">

                            {{-- Sub-menu 1: Aset Tetap --}}
                            <li class="{{ request()->routeIs('kategori-tetap.*') ? 'active' : '' }}">
                                <a href="{{ route('kategori-tetap.index') }}">
                                    <span class="sub-item">Kategori Aset Tetap</span>
                                </a>
                            </li>

                            {{-- Sub-menu 2: Inventaris / EC --}}
                            <li class="{{ request()->routeIs('kategori-inventaris.*') ? 'active' : '' }}">
                                <a href="{{ route('kategori-inventaris.index') }}">
                                    <span class="sub-item">Kategori Aset EC</span>
                                </a>
                            </li>

                        </ul>
                    </div>
                </li>

                <li class="nav-item {{ request()->routeIs('aset.index') ? 'active_' : '' }}">
                    <a href="{{ route('aset.index') }}" class="nav-link">
                        <i class="fas fa-box"></i>
                        <p>Data Aset Perusahaan</p>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('aset.pic') ? 'active_' : '' }}">
                    <a href="{{ route('aset.pic') }}" class="nav-link">
                        <i class="fas fa-user-tag"></i>
                        <p>Aset PIC</p>
                    </a>
                </li>
                
                <li class="nav-item {{ request()->routeIs('log-aset.index') ? 'active_' : '' }}">
                    <a href="{{ route('log-aset.index') }}" class="nav-link">
                        <i class="fas fa-clipboard-check"></i>
                        <p>Riwayat Monitoring</p>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('perbaikan.*') ? 'active_' : '' }}">
                    <a href="{{ route('perbaikan.index') }}" class="nav-link">
                        <i class="fas fa-tools"></i>
                        <p>Pengajuan Perbaikan</p>
                    </a>
                </li>

                <li class="nav-item {{ request()->is('pemulihan*') ? 'active_' : '' }}">
                    <a href="#pemulihanDrop"
                        class="nav-link {{ request()->is('pemulihan*') ? '' : 'collapsed' }}" 
                        data-toggle="collapse"
                        aria-expanded="{{ request()->is('pemulihan*') ? 'true' : 'false' }}">
                            <i class="fas fa-trash-restore"></i>
                            <p>Pemulihan</p>
                            <span class="caret"></span>
                    </a>

                    <div class="collapse {{ request()->is('pemulihan*') ? 'show' : '' }}" 
                        id="pemulihanDrop">
                        <ul class="nav nav-collapse" style="margin-top: 0; padding-bottom: 10px;">
                            <li class="{{ request()->routeIs('pemulihan.kategori-aset') ? 'active' : '' }}">
                                <a href="{{ route('pemulihan.kategori-aset') }}">
                                    <span class="sub-item">Kategori Aset</span>
                                </a>
                            </li>
                            <li class="{{ request()->routeIs('pemulihan.data-aset') ? 'active' : '' }}">
                                <a href="{{ route('pemulihan.data-aset') }}">
                                    <span class="sub-item">Data Aset</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            @endif

            <!-- SUPERADMIN ONLY MENU -->
            @if (Auth::user()->role->nm_role == 'superadmin')
                <li class="nav-item {{ request()->routeIs('organization.manageOrganization') || request()->routeIs('kode-bagian.index') ? 'active_' : '' }}">
                    <a href="#strukturOrganisasiDrop"
                        class="nav-link {{ request()->routeIs('organization.manageOrganization') || request()->routeIs('kode-bagian.index') ? '' : 'collapsed' }}" 
                        data-toggle="collapse"
                        aria-expanded="{{ request()->routeIs('organization.manageOrganization') || request()->routeIs('kode-bagian.index') ? 'true' : 'false' }}">
                            <i class="fas fa-sitemap"></i>
                            <p>Struktur Organisasi</p>
                            <span class="caret"></span>
                    </a>

                    <div class="collapse {{ request()->routeIs('organization.manageOrganization') || request()->routeIs('kode-bagian.index') ? 'show' : '' }}" 
                        id="strukturOrganisasiDrop">
                        <ul class="nav nav-collapse" style="margin-top: 0; padding-bottom: 10px;">

                            <li class="{{ request()->routeIs('organization.manageOrganization') ? 'active' : '' }}">
                                <a href="{{ route('organization.manageOrganization') }}">
                                    <span class="sub-item">Kelola Struktur</span>
                                </a>
                            </li>

                            <li class="{{ request()->routeIs('kode-bagian.index') ? 'active' : '' }}">
                                <a href="{{ route('kode-bagian.index') }}">
                                    <span class="sub-item">Manajemen Kode Bagian Kerja</span>
                                </a>
                            </li>

                        </ul>
                    </div>
                </li>

                <li class="nav-item {{ request()->routeIs('user.manage') ? 'active_' : '' }}">
                    <a href="{{ route('user.manage') }}" class="nav-link">
                        <i class="fas fa-users"></i>
                        <p>Manage Users</p>
                    </a>
                </li>
            @endif

            <!-- MENU STAFF & MANAGER (NON-GA) -->
            @if (Auth::user()->role->nm_role != 'superadmin' && !Auth::user()->isBagianUmum())
                
                <li class="nav-item {{ request()->routeIs('manager.dashboard') || request()->routeIs('staff.dashboard') ? 'active_' : '' }}">
                    <a href="{{ Auth::user()->role_id_role == 3 ? route('manager.dashboard') : route('staff.dashboard') }}" class="nav-link">
                        <i class="fas fa-home"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('aset.index') ? 'active_' : '' }}">
                    <a href="{{ route('aset.index') }}" class="nav-link">
                        <i class="fas fa-box"></i>
                        <p>Data Aset Departemen</p>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('aset.pic') ? 'active_' : '' }}">
                    <a href="{{ route('aset.pic') }}" class="nav-link">
                        <i class="fas fa-user-tag"></i>
                        <p>Aset PIC</p>
                    </a>
                </li>
                
                <li class="nav-item {{ request()->routeIs('aset.scanner') ? 'active_' : '' }}">
                    <a href="{{ route('aset.scanner') }}" class="nav-link">
                        <i class="fas fa-qrcode"></i>
                        <p>Scanner Aset</p>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('log-aset.index') ? 'active_' : '' }}">
                    <a href="{{ route('log-aset.index') }}" class="nav-link">
                        <i class="fas fa-clipboard-check"></i>
                        <p>Monitoring Aset</p>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('perbaikan.*') ? 'active_' : '' }}">
                    <a href="{{ route('perbaikan.index') }}" class="nav-link">
                        <i class="fas fa-tools"></i>
                        <p>Pengajuan Perbaikan</p>
                    </a>
                </li>
            @endif
        </ul>
    </div>
</div>
