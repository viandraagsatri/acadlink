
@extends('layouts.app')

@section('title', 'Pengajuan Bimbingan')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Pengajuan Bimbingan</h2>
            <p class="text-secondary mb-0">
                Kelola permintaan bimbingan dari mahasiswa.
            </p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        @foreach ([
            ['Menunggu', $totalPending, 'warning'],
            ['Disetujui', $totalApproved, 'success'],
            ['Ditolak', $totalRejected, 'danger'],
            ['Selesai', $totalCompleted, 'primary']
        ] as [$label, $jumlah, $warna])
            <div class="col-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-secondary small">{{ $label }}</div>
                        <div class="fs-3 fw-bold text-{{ $warna }}">
                            {{ $jumlah }}
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="GET"
                  action="{{ route('dosen.pengajuan.index') }}"
                  class="row g-2 mb-4">

                <div class="col-md-6">
                    <input type="text"
                           name="search"
                           class="form-control"
                           placeholder="Cari nama, NIM, atau topik..."
                           value="{{ request('search') }}">
                </div>

                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">Semua status</option>
                        <option value="pending" @selected(request('status') === 'pending')>
                            Menunggu
                        </option>
                        <option value="approved" @selected(request('status') === 'approved')>
                            Disetujui
                        </option>
                        <option value="rejected" @selected(request('status') === 'rejected')>
                            Ditolak
                        </option>
                        <option value="completed" @selected(request('status') === 'completed')>
                            Selesai
                        </option>
                    </select>
                </div>

                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-primary flex-grow-1">
                        <i class="bi bi-search me-1"></i> Cari
                    </button>
                    <a href="{{ route('dosen.pengajuan.index') }}"
                       class="btn btn-outline-secondary">
                        Reset
                    </a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Mahasiswa</th>
                            <th>Jenis</th>
                            <th>Topik</th>
                            <th>Jadwal</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pengajuans as $pengajuan)
                            <tr>
                                <td>
                                    <div class="fw-semibold">
                                        {{ $pengajuan->mahasiswa->name ?? 'Mahasiswa' }}
                                    </div>
                                    <small class="text-secondary">
                                        {{ $pengajuan->mahasiswa->nim_nip ?? '-' }}
                                    </small>
                                </td>
                                <td>{{ $pengajuan->jenis_bimbingan }}</td>
                                <td>{{ $pengajuan->topik_pembahasan }}</td>
                                <td>
                                    @if ($pengajuan->jadwal_bimbingan)
                                        {{ \Carbon\Carbon::parse($pengajuan->jadwal_bimbingan)->format('d/m/Y H:i') }}
                                    @else
                                        <span class="text-secondary">Belum ditentukan</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $badge = [
                                            'pending' => 'warning',
                                            'approved' => 'success',
                                            'rejected' => 'danger',
                                            'completed' => 'primary',
                                        ][$pengajuan->status] ?? 'secondary';

                                        $labelStatus = [
                                            'pending' => 'Menunggu',
                                            'approved' => 'Disetujui',
                                            'rejected' => 'Ditolak',
                                            'completed' => 'Selesai',
                                        ][$pengajuan->status] ?? $pengajuan->status;
                                    @endphp

                                    <span class="badge text-bg-{{ $badge }}">
                                        {{ $labelStatus }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('dosen.pengajuan.show', $pengajuan->id) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="bi bi-inbox fs-1 text-secondary"></i>
                                    <p class="mt-2 mb-0">
                                        Belum ada pengajuan bimbingan.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $pengajuans->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
