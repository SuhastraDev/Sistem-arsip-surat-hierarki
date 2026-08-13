@extends('layouts.main')

@section('title', 'Kelola Pegawai')

@section('content')

<!-- Header Section -->
<div class="content-header bg-white p-3 rounded shadow-sm mb-3">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-1">
                <i class="fas fa-users-cog me-2 text-primary"></i>Data Akun Pegawai
            </h5>
            <small class="text-muted">Kelola akun dan hak akses pengguna sistem</small>
        </div>
        <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus-circle me-1"></i>Tambah Pegawai Baru
        </a>
    </div>
</div>

<!-- Success Alert -->
@if(session('success'))
<div class="alert alert-success border-0 shadow-sm d-flex align-items-center mb-3" role="alert">
    <i class="fas fa-check-circle me-3"></i>
    <div>
        <strong>Berhasil!</strong> {{ session('success') }}
    </div>
    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- Table Section -->
<div class="table-container bg-white rounded shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-primary">
                <tr>
                    <th width="5%" class="text-center text-white">No</th>
                    <th width="20%" class="text-white">Nama Lengkap</th>
                    <th width="20%" class="text-white">Email & Login</th>
                    <th width="18%" class="text-white">Jabatan / Posisi</th>
                    <th width="10%" class="text-center text-white">Role</th>
                    <th width="17%" class="text-white">Atasan Langsung</th>
                    <th width="10%" class="text-center text-white">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="user-avatar bg-primary text-white rounded-circle me-2">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div class="fw-semibold">{{ $user->name }}</div>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex align-items-center text-muted small">
                            <i class="fas fa-envelope me-2"></i>
                            {{ $user->email }}
                        </div>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-briefcase text-primary me-2"></i>
                            {{ $user->jabatan }}
                        </div>
                    </td>
                    <td class="text-center">
                        @php
                        $badge = 'secondary';
                        if($user->role == 'kabid') $badge = 'danger';
                        if($user->role == 'kasi') $badge = 'warning text-dark';
                        if($user->role == 'staff') $badge = 'success';
                        if($user->role == 'admin') $badge = 'primary';
                        @endphp
                        <span class="badge bg-{{ $badge }} text-uppercase">{{ $user->role }}</span>
                    </td>
                    <td>
                        @if($user->atasan)
                        <div class="d-flex align-items-center small">
                            <i class="fas fa-user-tie text-primary me-2"></i>
                            <div>
                                <div class="fw-semibold">{{ $user->atasan->name }}</div>
                                <div class="text-muted" style="font-size: 0.7rem;">{{ $user->atasan->jabatan }}</div>
                            </div>
                        </div>
                        @else
                        <span class="text-muted small">-</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <div class="d-inline-flex flex-wrap justify-content-center gap-1">
                            <a href="{{ route('users.show', $user) }}" class="btn btn-sm btn-outline-primary" title="Lihat Detail Akun">
                                <i class="fas fa-eye"></i> Detail
                            </a>
                            <form action="{{ route('users.destroy', $user->id) }}" method="POST"
                                onsubmit="return confirm('Yakin ingin menghapus akun pegawai ini? Data surat terkait mungkin akan kehilangan referensi.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Hapus User">
                                    <i class="fas fa-trash-alt"></i> Hapus
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <div class="text-muted">
                            <i class="fas fa-users fa-3x mb-3 opacity-50"></i>
                            <h5>Tidak Ada Data</h5>
                            <p class="mb-0">Belum ada data pegawai. Silakan tambah pegawai baru.</p>
                            <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm mt-3">
                                <i class="fas fa-plus-circle me-2"></i>Tambah Pegawai
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<style>
    /* Content Sections */
    .content-header,
    .table-container {
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border: 1px solid #e9ecef;
    }

    .content-header h5 {
        font-size: 1rem;
        font-weight: 600;
        margin: 0;
    }

    /* User Avatar */
    .user-avatar {
        width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.875rem;
        flex-shrink: 0;
    }

    /* Table */
    .table {
        font-size: 0.875rem;
    }

    .table-primary {
        background: #003366 !important;
    }

    .table-primary th {
        background: #003366 !important;
        border-color: #002952;
        padding: 0.85rem 0.75rem;
        font-size: 0.813rem;
        font-weight: 600;
        color: white !important;
    }

    .table tbody td {
        padding: 0.85rem 0.75rem;
        border-color: #e9ecef;
    }

    .table-hover tbody tr {
        transition: all 0.2s ease;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(0, 64, 133, 0.05);
        transform: scale(1.002);
    }

    /* Badge */
    .badge {
        font-size: 0.7rem;
        font-weight: 600;
        padding: 0.35rem 0.65rem;
        border-radius: 6px;
        letter-spacing: 0.3px;
    }

    /* Buttons */
    .btn {
        font-size: 0.875rem;
        font-weight: 500;
        border-radius: 6px;
        transition: all 0.2s ease;
    }

    .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
    }

    .btn-sm {
        font-size: 0.813rem;
        padding: 0.375rem 0.75rem;
    }

    .btn-primary {
        background: linear-gradient(135deg, #004085 0%, #0056b3 100%);
        border: none;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #003266 0%, #004085 100%);
    }

    /* Alert */
    .alert {
        border-radius: 6px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .table {
            font-size: 0.813rem;
        }

        .user-avatar {
            width: 30px;
            height: 30px;
            font-size: 0.75rem;
        }
    }
</style>

@endsection
