<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Arsip Surat Digital</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Newsreader:opsz,wght@6..72,600;6..72,700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='20' fill='%23004085'/><path d='M35 25h20l10 10v40h-30z' fill='white'/><path d='M55 25v10h10' fill='none' stroke='white' stroke-width='2'/></svg>">

    <style>
        :root {
            --ink: #102033;
            --muted: #647083;
            --paper: #fbfaf6;
            --paper-soft: #f3f0e8;
            --line: #d9ded6;
            --forest: #0f766e;
            --forest-deep: #0b4f49;
            --blueprint: #1d4d7a;
            --clay: #b85c38;
            --gold: #d8a030;
            --shadow-soft: 0 14px 32px rgba(16, 32, 51, .08);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background:
                linear-gradient(90deg, rgba(16, 32, 51, .035) 1px, transparent 1px) 0 0 / 34px 34px,
                linear-gradient(180deg, #fbfaf6 0%, #eef4ef 100%);
            color: var(--ink);
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            overflow-x: hidden;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        .navbar-title h5 {
            color: var(--ink);
            font-family: 'Newsreader', Georgia, serif;
            font-weight: 700;
            letter-spacing: 0;
        }

        a {
            color: var(--blueprint);
        }

        .card,
        .alert,
        .modal-content,
        .dropdown-menu,
        .form-control,
        .form-select,
        .btn,
        .badge {
            border-radius: 8px;
        }

        .card,
        .request-overview,
        .filter-panel,
        .queue-panel,
        .detail-panel,
        .form-panel,
        .process-strip,
        .workbench-hero,
        .input-panel,
        .list-panel {
            background: rgba(255, 255, 255, .92) !important;
            border: 1px solid var(--line) !important;
            box-shadow: var(--shadow-soft) !important;
        }

        .card-header,
        .queue-panel-header,
        .detail-panel-header,
        .list-panel-header,
        .requirement-panel-header {
            background: linear-gradient(180deg, #fffdf8 0%, #f5f2ea 100%) !important;
            border-color: var(--line) !important;
        }

        .btn {
            font-weight: 700;
            letter-spacing: 0;
        }

        .btn-primary,
        .btn-success {
            background: linear-gradient(135deg, var(--forest) 0%, var(--forest-deep) 100%);
            border-color: var(--forest);
            box-shadow: 0 8px 18px rgba(15, 118, 110, .18);
        }

        .btn-primary:hover,
        .btn-success:hover {
            background: linear-gradient(135deg, #14877e 0%, #0d5c55 100%);
            border-color: #14877e;
            transform: translateY(-1px);
        }

        .btn-outline-primary {
            border-color: rgba(29, 77, 122, .35);
            color: var(--blueprint);
        }

        .btn-outline-primary:hover {
            background: var(--blueprint);
            border-color: var(--blueprint);
        }

        .form-control,
        .form-select {
            background-color: #fffdf8;
            border-color: #cfd8d1;
            color: var(--ink);
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--forest);
            box-shadow: 0 0 0 .22rem rgba(15, 118, 110, .14);
        }

        .form-label,
        .field-label {
            color: #334155;
            font-size: .82rem;
            font-weight: 800;
        }

        .table {
            color: var(--ink);
        }

        .table thead th {
            background: #eef2ec !important;
            border-bottom: 1px solid var(--line);
            color: #334155;
            font-size: .74rem;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .table tbody tr {
            transition: background .18s ease;
        }

        .table tbody tr:hover {
            background: #fffdf8;
        }

        .badge.bg-primary,
        .badge.bg-success {
            background: var(--forest) !important;
        }

        .badge.bg-secondary {
            background: #647083 !important;
        }

        [style*="#004085"],
        [style*="#002752"],
        [style*="#0056b3"],
        [style*="#003266"] {
            background: linear-gradient(135deg, var(--forest) 0%, var(--forest-deep) 100%) !important;
        }

        [style*="background: #004085"],
        [style*="border-color: #004085"],
        [style*="border: 2px solid #004085"],
        [style*="border-left: 3px solid #004085"] {
            background-color: var(--forest) !important;
            border-color: var(--forest) !important;
        }

        [style*="#dc3545"],
        [style*="#b02a37"] {
            background: linear-gradient(135deg, var(--clay) 0%, #823f2a 100%) !important;
        }

        [style*="#fff9e6"],
        [style*="#fff5f5"],
        [style*="#e8f5e9"],
        [style*="#e8f4fd"],
        [style*="#e7f3ff"],
        [style*="#f8f9fa"] {
            background-color: #fffdf8 !important;
        }

        [style*="border-left: 3px"] {
            border-left-width: 5px !important;
            border-left-color: var(--gold) !important;
        }

        .section-kicker {
            color: var(--clay) !important;
            font-size: .72rem;
            font-weight: 900;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        /* SIDEBAR */
        .sidebar {
            min-height: 100vh;
            background:
                linear-gradient(180deg, rgba(255, 255, 255, .06) 0 1px, transparent 1px) 0 0 / 100% 38px,
                linear-gradient(180deg, #102033 0%, #0b4f49 100%);
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
            position: relative;
        }

        .sidebar .brand::after {
            background: var(--gold);
            bottom: -1px;
            content: "";
            height: 3px;
            left: 25px;
            position: absolute;
            width: 72px;
        }

        .sidebar .brand-icon {
            width: 45px;
            height: 45px;
            background: rgba(216, 160, 48, 0.22);
            border: 1px solid rgba(216, 160, 48, .4);
            border-radius: 8px;
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
            letter-spacing: 0;
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
            margin: 2px 14px;
            padding: 12px 11px;
            font-size: 0.95rem;
            border: 1px solid transparent;
            border-radius: 8px;
            transition: all 0.2s ease;
            position: relative;
            display: flex;
            align-items: center;
            text-decoration: none;
        }

        .nav-link:hover {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.08);
            border-color: rgba(216, 160, 48, .28);
        }

        .nav-link.active {
            color: #fff;
            background: rgba(255, 255, 255, 0.14);
            border-color: rgba(216, 160, 48, .45);
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
            border-radius: 8px;
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
            border-radius: 8px;
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
            background: rgba(255, 253, 248, .92);
            border-bottom: 1px solid var(--line);
            backdrop-filter: blur(16px);
            padding: 20px 40px;
            box-shadow: 0 10px 24px rgba(16, 32, 51, .05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .navbar-title h5 {
            margin: 0;
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
            border-radius: 8px;
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
            background: linear-gradient(135deg, var(--forest) 0%, var(--blueprint) 100%);
            color: white;
            border-radius: 8px;
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
            border-radius: 8px;
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

        .page-content::before {
            color: rgba(184, 92, 56, .18);
            content: "E-SURAT";
            font-family: 'Newsreader', Georgia, serif;
            font-size: clamp(3rem, 12vw, 8rem);
            font-weight: 700;
            line-height: 1;
            pointer-events: none;
            position: fixed;
            right: 28px;
            top: 96px;
            z-index: -1;
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
            <a href="{{ route('verification.index') }}" class="nav-link {{ request()->is('verifikasi*') ? 'active' : '' }}">
                <i class="fas fa-shield-halved"></i>
                <span>Verifikasi Dokumen</span>
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

            @if(Auth::user()->role === 'staff')
            <div class="menu-label">Tugas Staff</div>

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
            ->whereIn('status', ['draft', 'ditolak', 'selesai'])
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
        <style>
            .page-content [style*="#004085"],
            .page-content [style*="#002752"],
            .page-content [style*="#0056b3"],
            .page-content [style*="#003266"],
            .page-content .btn-primary,
            .page-content .btn-success {
                background: linear-gradient(135deg, var(--forest) 0%, var(--forest-deep) 100%) !important;
                border-color: var(--forest) !important;
            }

            .page-content [style*="border-color: #004085"],
            .page-content [style*="border: 2px solid #004085"],
            .page-content [style*="border-left: 3px solid #004085"] {
                border-color: var(--forest) !important;
            }

            .page-content .card,
            .page-content .modal-content,
            .page-content .form-control,
            .page-content .form-select,
            .page-content .btn,
            .page-content .alert {
                border-radius: 8px !important;
            }
        </style>

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
