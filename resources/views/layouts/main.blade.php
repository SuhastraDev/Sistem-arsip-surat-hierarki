<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Arsip Surat Digital</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='20' fill='%23004085'/><path d='M35 25h20l10 10v40h-30z' fill='white'/><path d='M55 25v10h10' fill='none' stroke='white' stroke-width='2'/></svg>">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: #f7fafc;
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
        }

        /* SIDEBAR */
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #004085 0%, #002752 100%);
            color: white;
            box-shadow: 4px 0 15px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 0;
            left: 0;
            width: 280px;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .sidebar .brand {
            padding: 30px 25px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar .brand-icon {
            width: 45px;
            height: 45px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }

        .sidebar .brand-text {
            flex: 1;
        }

        .sidebar .brand-title {
            font-size: 1.3rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin: 0;
        }

        .sidebar .brand-subtitle {
            font-size: 0.75rem;
            opacity: 0.7;
            margin: 0;
        }

        .sidebar-nav {
            padding: 20px 0;
            overflow-y: auto;
            max-height: calc(100vh - 180px);
        }

        .sidebar-nav::-webkit-scrollbar {
            width: 5px;
        }

        .sidebar-nav::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
        }

        .menu-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: rgba(255, 255, 255, 0.5);
            margin: 25px 25px 12px;
            font-weight: 700;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.75);
            padding: 13px 25px;
            font-size: 0.95rem;
            border-left: 3px solid transparent;
            transition: all 0.2s ease;
            position: relative;
            display: flex;
            align-items: center;
            text-decoration: none;
        }

        .nav-link:hover {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.08);
            border-left-color: #00d2ff;
        }

        .nav-link.active {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.15);
            border-left-color: #00d2ff;
            font-weight: 600;
        }

        .nav-link i {
            margin-right: 12px;
            width: 22px;
            text-align: center;
            font-size: 1.1rem;
        }

        /* Badge pada Sidebar */
        .nav-badge {
            margin-left: auto;
            padding: 3px 9px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 700;
        }

        /* Logout Button di Sidebar */
        .sidebar-footer {
            position: absolute;
            bottom: 20px;
            left: 0;
            right: 0;
            padding: 0 20px;
        }

        .btn-logout {
            width: 100%;
            padding: 12px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-logout:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        /* MAIN CONTENT */
        .main-content {
            margin-left: 280px;
            transition: all 0.3s ease;
            min-height: 100vh;
        }

        /* TOP NAVBAR */
        .top-navbar {
            background: white;
            padding: 20px 40px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .navbar-title h5 {
            margin: 0;
            font-weight: 700;
            color: #1a202c;
            font-size: 1.4rem;
        }

        .navbar-date {
            color: #718096;
            font-size: 0.9rem;
            margin-top: 2px;
        }

        /* User Dropdown */
        .user-dropdown {
            display: flex;
            align-items: center;
            gap: 15px;
            cursor: pointer;
            padding: 8px 15px;
            border-radius: 12px;
            transition: all 0.2s;
        }

        .user-dropdown:hover {
            background: #f7fafc;
        }

        .user-info {
            text-align: right;
        }

        .user-name {
            font-weight: 700;
            color: #1a202c;
            font-size: 0.95rem;
            margin: 0;
        }

        .user-role {
            color: #718096;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
        }

        .user-avatar {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #004085 0%, #002752 100%);
            color: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            font-weight: 600;
        }

        /* Dropdown Menu */
        .dropdown-menu {
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            padding: 8px;
            margin-top: 10px;
        }

        .dropdown-item {
            padding: 10px 15px;
            border-radius: 8px;
            transition: all 0.2s;
            font-size: 0.9rem;
        }

        .dropdown-item:hover {
            background: #f7fafc;
        }

        .dropdown-item i {
            width: 20px;
        }

        /* PAGE CONTENT */
        .page-content {
            padding: 30px 40px;
        }

        /* RESPONSIVE */
        @media (max-width: 992px) {
            .sidebar {
                margin-left: -280px;
            }

            .sidebar.active {
                margin-left: 0;
                box-shadow: 0 0 50px rgba(0, 0, 0, 0.3);
            }

            .main-content {
                margin-left: 0;
            }

            .top-navbar {
                padding: 15px 20px;
            }

            .page-content {
                padding: 20px;
            }

            .navbar-title h5 {
                font-size: 1.1rem;
            }

            .user-info {
                display: none;
            }
        }

        @media (max-width: 576px) {
            .top-navbar {
                padding: 12px 15px;
            }

            .page-content {
                padding: 15px;
            }

            .navbar-date {
                font-size: 0.8rem;
            }

            .user-avatar {
                width: 38px;
                height: 38px;
                font-size: 0.95rem;
            }
        }

        /* Mobile Toggle Button */
        .sidebar-toggle {
            display: none;
            width: 40px;
            height: 40px;
            background: #f7fafc;
            border: none;
            border-radius: 10px;
            color: #4a5568;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .sidebar-toggle:hover {
            background: #e2e8f0;
        }

        @media (max-width: 992px) {
            .sidebar-toggle {
                display: flex;
                align-items: center;
                justify-content: center;
            }
        }
    </style>
</head>

<body>

    <!-- SIDEBAR -->
    <nav class="sidebar" id="sidebar">
        <div class="brand">
            <div class="brand-icon">
                <i class="fas fa-file-alt"></i>
            </div>
            <div class="brand-text">
                <div class="brand-title">E-ARSIP</div>
                <div class="brand-subtitle">Sistem Arsip Digital</div>
            </div>
        </div>

        <div class="sidebar-nav">

            <div class="menu-label">Menu Utama</div>
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>

            @if(Auth::user()->role == 'admin')
            <div class="menu-label">Master Data</div>
            <a href="{{ route('jenis-surat.index') }}" class="nav-link {{ request()->is('jenis-surat*') ? 'active' : '' }}">
                <i class="fas fa-layer-group"></i>
                <span>Jenis Surat</span>
            </a>
            <a href="{{ route('users.index') }}" class="nav-link {{ request()->is('users*') ? 'active' : '' }}">
                <i class="fas fa-users-cog"></i>
                <span>Manajemen Pengguna</span>
            </a>
            @endif

            @if(in_array(Auth::user()->role, ['staff', 'kabid', 'kasi']))
            <div class="menu-label">Tugas & Disposisi</div>

            @php
            $countInbox = \App\Models\DisposisiSurat::where('penerima_id', Auth::id())->where('is_read', 0)->count();
            @endphp
            <a href="{{ route('disposisi.index') }}" class="nav-link {{ request()->is('disposisi*') ? 'active' : '' }}">
                <i class="fas fa-inbox"></i>
                <span>Surat Masuk</span>
                @if($countInbox > 0)
                <span class="badge bg-danger nav-badge">{{ $countInbox }}</span>
                @endif
            </a>
            @endif

            @if(in_array(Auth::user()->role, ['admin', 'staff', 'kabid', 'kasi']))

            @php
            $notifCount = 0;
            $notifColor = 'danger';

            if(Auth::user()->role == 'staff') {
            $notifCount = \App\Models\PengajuanSurat::where('pemohon_id', Auth::id())
            ->whereIn('status', ['draft', 'ditolak'])
            ->count();
            $notifColor = 'warning';
            }
            elseif(in_array(Auth::user()->role, ['kabid', 'kasi'])) {
            $notifCount = \App\Models\PengajuanSurat::where('posisi_saat_ini', Auth::id())->count();
            }
            @endphp

            <a href="{{ route('pengajuan-surat.index') }}" class="nav-link {{ request()->is('pengajuan-surat*') ? 'active' : '' }}">
                <i class="fas fa-file-signature"></i>
                <span>{{ Auth::user()->role == 'staff' ? 'Pengajuan Surat' : 'Pengajuan Surat' }}</span>

                @if($notifCount > 0)
                <span class="badge bg-{{ $notifColor }} nav-badge">{{ $notifCount }}</span>
                @endif
            </a>
            @endif

        </div>

        <div class="sidebar-footer">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn-logout">
                    <i class="fas fa-sign-out-alt me-2"></i> Keluar
                </button>
            </form>
        </div>
    </nav>

    <!-- MAIN CONTENT -->
    <div class="main-content">

        <!-- TOP NAVBAR -->
        <div class="top-navbar">
            <div class="d-flex align-items-center gap-3">
                <button class="sidebar-toggle" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>

                <div class="navbar-title">
                    <h5>@yield('title', 'Dashboard')</h5>
                    <div class="navbar-date">{{ date('l, d F Y') }}</div>
                </div>
            </div>

            <div class="dropdown">
                <div class="user-dropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="user-info">
                        <div class="user-name">{{ Auth::user()->name }}</div>
                        <div class="user-role">{{ Auth::user()->role }}</div>
                    </div>
                    <div class="user-avatar">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                </div>

                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="{{ route('profile.edit') }}">
                            <i class="fas fa-user-cog text-muted"></i> Profil & Password
                        </a>
                    </li>
                    <li>
                        <hr class="dropdown-divider my-1">
                    </li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="fas fa-sign-out-alt"></i> Keluar
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>

        <!-- PAGE CONTENT -->
        <div class="page-content">
            @yield('content')
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.querySelector('.sidebar-toggle');

            if (window.innerWidth <= 992) {
                if (!sidebar.contains(event.target) && !toggle.contains(event.target)) {
                    sidebar.classList.remove('active');
                }
            }
        });
    </script>

</body>

</html>
