
@extends('layouts.app')

@section('title', 'Beranda')

@section('content')
@guest
    <div class="row align-items-center py-4 mb-4">
        <div class="col-lg-7">
            <span class="badge badge-lilac px-3 py-2 rounded-pill mb-3">
                Sistem Bimbingan Akademik Terpadu
            </span>
            <h1 class="display-5 fw-bold mb-3">
                Solusi Manajemen Konsultasi & Revisi Bimbingan
            </h1>
            <p class="lead text-muted mb-4">
                AcadLink memudahkan Dosen dalam menyusun jadwal konsultasi bebas bentrok,
                mengonfirmasi pertemuan bimbingan Skripsi, Kerja Praktik, dan Akademik,
                serta mencatat hasil pembahasan dan daftar arahan revisi secara terstruktur.
            </p>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('login') }}" class="btn btn-primary px-4 py-2">
                    Masuk Sekarang
                </a>
                <a href="{{ route('register') }}" class="btn btn-outline-primary px-4 py-2">
                    Daftar Akun
                </a>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-2">
        <div class="col-md-4">
            <div class="card h-100 p-3 shadow-sm">
                <div class="card-body">
                    <div class="fs-1 text-primary mb-3">
                        <i class="bi bi-calendar3-range"></i>
                    </div>
                    <h5 class="fw-bold">Pengelolaan Jadwal & Bebas Bentrok</h5>
                    <p class="text-muted small mb-0">
                        Dosen dapat membuat, mengubah, dan menghapus jadwal konsultasi
                        dengan validasi pencegahan jadwal tumpang-tindih secara otomatis.
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 p-3 shadow-sm">
                <div class="card-body">
                    <div class="fs-1 text-primary mb-3">
                        <i class="bi bi-journal-check"></i>
                    </div>
                    <h5 class="fw-bold">Pencatatan Hasil Bimbingan</h5>
                    <p class="text-muted small mb-0">
                        Simpan ringkasan diskusi setiap sesi bimbingan Skripsi, KP,
                        atau PA secara terstruktur.
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 p-3 shadow-sm">
                <div class="card-body">
                    <div class="fs-1 text-primary mb-3">
                        <i class="bi bi-list-task"></i>
                    </div>
                    <h5 class="fw-bold">Arahan & Butir Revisi</h5>
                    <p class="text-muted small mb-0">
                        Dosen memberikan daftar poin arahan perbaikan beserta batas
                        waktu dan prioritas pengerjaan untuk setiap mahasiswa.
                    </p>
                </div>
            </div>
        </div>
    </div>
@endguest

@auth
    @if (auth()->user()->isDosen())
        {{-- HEADER DASHBOARD DOSEN --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div>
                <span class="badge badge-lilac px-3 py-1 mb-2">
                    Panel Dosen Pembimbing
                </span>
                <h1 class="h3 fw-bold mb-1">
                    Selamat Datang, Dosen {{ auth()->user()->name }}
                </h1>
                <p class="text-muted mb-0">
                    NIP: {{ auth()->user()->nim_nip ?? '-' }} &bull;
                    Atur jadwal bimbingan dan pantau pengajuan mahasiswa Anda.
                </p>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('dosen.jadwal.create') }}"
                   class="btn btn-primary btn-sm">
                    <i class="bi bi-calendar-plus me-1"></i>
                    Buat Jadwal Baru
                </a>

                <a href="{{ route('dosen.pertemuan.index') }}"
                   class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-journal-check me-1"></i>
                    Pertemuan & Bimbingan
                </a>

                <a href="{{ route('dosen.pengajuan.index') }}"
                   class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-inbox me-1"></i>
                    Pengajuan Bimbingan
                </a>
            </div>
        </div>

        {{-- STATISTIK DASHBOARD LAMA --}}
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="stat-card d-flex align-items-center p-3 bg-white shadow-sm rounded">
                    <div class="rounded-circle bg-light p-3 me-3 text-primary fs-3">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Jadwal Konsultasi Aktif</div>
                        <div class="fs-4 fw-bold text-primary">
                            {{ $jadwalAktifCount ?? 0 }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="stat-card d-flex align-items-center p-3 bg-white shadow-sm rounded">
                    <div class="rounded-circle bg-warning bg-opacity-25 p-3 me-3 text-warning fs-3">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Pertemuan Menunggu</div>
                        <div class="fs-4 fw-bold text-warning">
                            {{ $pertemuanMenungguCount ?? 0 }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="stat-card d-flex align-items-center p-3 bg-white shadow-sm rounded">
                    <div class="rounded-circle bg-success bg-opacity-25 p-3 me-3 text-success fs-3">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Pertemuan Telah Selesai</div>
                        <div class="fs-4 fw-bold text-success">
                            {{ $pertemuanSelesaiCount ?? 0 }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- STATISTIK PENGAJUAN ORANG 4 --}}
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="stat-card d-flex align-items-center p-3 bg-white shadow-sm rounded h-100">
                    <div class="rounded-circle bg-warning bg-opacity-25 p-3 me-3 text-warning fs-3">
                        <i class="bi bi-inbox"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Pengajuan Menunggu</div>
                        <div class="fs-4 fw-bold text-warning">
                            {{ $pengajuanMenungguCount ?? 0 }}
                        </div>
                        <a href="{{ route('dosen.pengajuan.index', ['status' => 'pending']) }}"
                           class="small">
                            Periksa pengajuan
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="stat-card d-flex align-items-center p-3 bg-white shadow-sm rounded h-100">
                    <div class="rounded-circle bg-success bg-opacity-25 p-3 me-3 text-success fs-3">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Pengajuan Disetujui</div>
                        <div class="fs-4 fw-bold text-success">
                            {{ $pengajuanDisetujuiCount ?? 0 }}
                        </div>
                        <a href="{{ route('dosen.pengajuan.index', ['status' => 'approved']) }}"
                           class="small">
                            Lihat pengajuan
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- DAFTAR PENGAJUAN TERBARU --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title fs-6 fw-bold mb-0">
                    Pengajuan Bimbingan Terbaru
                </h5>
                <a href="{{ route('dosen.pengajuan.index') }}" class="small">
                    Lihat Semua
                </a>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th class="ps-3">Mahasiswa</th>
                                <th>Topik</th>
                                <th>Status</th>
                                <th class="text-end pe-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pengajuanTerbaru ?? [] as $pengajuan)
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-bold small">
                                            {{ $pengajuan->mahasiswa->name ?? '-' }}
                                        </div>
                                        <div class="text-muted small">
                                            {{ $pengajuan->mahasiswa->nim_nip ?? '-' }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small fw-semibold">
                                            {{ $pengajuan->topik_pembahasan ?? '-' }}
                                        </div>
                                        <div class="text-muted small">
                                            {{ $pengajuan->jenis_bimbingan ?? '-' }}
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $warnaStatus = [
                                                'pending' => 'bg-warning text-dark',
                                                'approved' => 'bg-success',
                                                'rejected' => 'bg-danger',
                                                'completed' => 'bg-primary',
                                            ];
                                        @endphp

                                        <span class="badge {{ $warnaStatus[$pengajuan->status] ?? 'bg-secondary' }}">
                                            {{ ucfirst($pengajuan->status) }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-3">
                                        <a href="{{ route('dosen.pengajuan.show', $pengajuan->id) }}"
                                           class="btn btn-outline-primary btn-sm">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4"
                                        class="text-center py-4 text-muted small">
                                        Belum ada pengajuan bimbingan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- PERTEMUAN TERBARU DAN JADWAL KONSULTASI --}}
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card shadow-sm h-100">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h5 class="card-title fs-6 fw-bold mb-0">
                            Pertemuan Bimbingan Terbaru
                        </h5>
                        <a href="{{ route('dosen.pertemuan.index') }}" class="small">
                            Lihat Semua
                        </a>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light small">
                                    <tr>
                                        <th class="ps-3">Mahasiswa</th>
                                        <th>Topik</th>
                                        <th>Status</th>
                                        <th class="text-end pe-3">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($pertemuanTerbaru ?? [] as $ptm)
                                        <tr>
                                            <td class="ps-3">
                                                <div class="fw-bold small">
                                                    {{ $ptm->mahasiswa->name ?? '-' }}
                                                </div>
                                                <div class="text-muted small">
                                                    {{ $ptm->jenis_bimbingan_label ?? '-' }}
                                                </div>
                                            </td>
                                            <td>
                                                <div class="small fw-semibold text-truncate"
                                                     style="max-width: 200px;">
                                                    {{ $ptm->judul_topik ?? '-' }}
                                                </div>
                                                <div class="text-muted small">
                                                    {{ $ptm->tanggal
                                                        ? \Carbon\Carbon::parse($ptm->tanggal)->format('d M Y')
                                                        : '-' }}
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge {{ $ptm->status_badge_class ?? 'bg-secondary' }} small">
                                                    {{ $ptm->status_label ?? $ptm->status }}
                                                </span>
                                            </td>
                                            <td class="text-end pe-3">
                                                <a href="{{ route('dosen.pertemuan.show', $ptm->id) }}"
                                                   class="btn btn-outline-primary btn-sm py-0 px-2">
                                                    Detail
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4"
                                                class="text-center py-4 text-muted small">
                                                Belum ada pertemuan bimbingan.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card shadow-sm h-100">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h5 class="card-title fs-6 fw-bold mb-0">
                            Jadwal Konsultasi Mendatang
                        </h5>
                        <a href="{{ route('dosen.jadwal.index') }}" class="small">
                            Kelola Jadwal
                        </a>
                    </div>

                    <div class="card-body">
                        @forelse ($jadwalMendatang ?? [] as $jm)
                            <div class="p-3 bg-light rounded border mb-2">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <span class="badge badge-lilac small mb-1">
                                            {{ $jm->jenis_bimbingan }}
                                        </span>
                                        <div class="fw-bold">
                                            {{ $jm->hari }},
                                            {{ $jm->tanggal
                                                ? \Carbon\Carbon::parse($jm->tanggal)->format('d M Y')
                                                : '-' }}
                                        </div>
                                        <div class="small text-muted">
                                            <i class="bi bi-clock me-1"></i>
                                            {{ $jm->jam_mulai_format ?? $jm->jam_mulai }}
                                            -
                                            {{ $jm->jam_selesai_format ?? $jm->jam_selesai }} WIB
                                        </div>
                                        <div class="small text-muted">
                                            <i class="bi bi-geo-alt me-1"></i>
                                            {{ $jm->ruangan_atau_link }}
                                        </div>
                                    </div>
                                    <span class="badge {{ isset($jm->is_penuh) && $jm->is_penuh ? 'bg-danger' : 'bg-success' }}">
                                        {{ $jm->kuota_terisi ?? 0 }}/{{ $jm->kuota ?? 0 }} Kuota
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted small">
                                Belum ada jadwal mendatang.
                                Silakan buat slot jadwal baru.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

    @elseif(auth()->user()->role === 'mahasiswa' || (method_exists(auth()->user(), 'isMahasiswa') && auth()->user()->isMahasiswa()))
        {{-- HEADER DASHBOARD MAHASISWA --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div>
                <span class="badge badge-lilac px-3 py-1 mb-2">
                    Beranda Mahasiswa
                </span>
                <h1 class="h3 fw-bold mb-1">
                    Selamat Datang, {{ auth()->user()->name }}
                </h1>
                <p class="text-muted mb-0">
                    NPM: {{ auth()->user()->nim_nip ?? '-' }} &bull;
                    Pantau status pengajuan bimbingan dan jadwal konsultasi dosen.
                </p>
            </div>

            <div class="d-flex flex-wrap gap-2">
                {{-- Hubungkan ke route formulir pengajuan jika sudah tersedia --}}
                <a href="{{ route('mahasiswa.bimbingan.create') }}"
                   class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle me-1"></i>
                    Ajukan Bimbingan
                </a>

                <a href="{{ route('mahasiswa.jadwal') }}"
                   class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-calendar-event me-1"></i>
                    Lihat Jadwal
                </a>
            </div>
        </div>

        {{-- STATISTIK MAHASISWA --}}
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="stat-card d-flex align-items-center p-3 bg-white shadow-sm rounded">
                    <div class="rounded-circle bg-warning bg-opacity-25 p-3 me-3 text-warning fs-3">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Menunggu Konfirmasi</div>
                        <div class="fs-4 fw-bold text-warning">
                            {{ $pertemuanMenungguCount ?? 0 }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="stat-card d-flex align-items-center p-3 bg-white shadow-sm rounded">
                    <div class="rounded-circle bg-primary bg-opacity-25 p-3 me-3 text-primary fs-3">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Bimbingan Disetujui</div>
                        <div class="fs-4 fw-bold text-primary">
                            {{ $pertemuanDisetujuiCount ?? 0 }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="stat-card d-flex align-items-center p-3 bg-white shadow-sm rounded">
                    <div class="rounded-circle bg-success bg-opacity-25 p-3 me-3 text-success fs-3">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Selesai</div>
                        <div class="fs-4 fw-bold text-success">
                            {{ $pertemuanSelesaiCount ?? 0 }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- RIWAYAT PERTEMUAN DAN JADWAL MAHASISWA --}}
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card shadow-sm h-100">
                    <div class="card-header py-3">
                        <h5 class="card-title fs-6 fw-bold mb-0">
                            Pertemuan Bimbingan Terbaru
                        </h5>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light small">
                                    <tr>
                                        <th class="ps-3">Dosen Pembimbing</th>
                                        <th>Topik / Tanggal</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($pertemuanTerbaru ?? [] as $ptm)
                                        <tr>
                                            <td class="ps-3">
                                                <div class="fw-bold small">
                                                    {{ optional($ptm->dosen)->name ?? 'Dosen' }}
                                                </div>
                                                <div class="text-muted small">
                                                    {{ $ptm->jenis_bimbingan_label ?? '-' }}
                                                </div>
                                            </td>
                                            <td>
                                                <div class="small fw-semibold text-truncate"
                                                     style="max-width: 180px;">
                                                    {{ $ptm->judul_topik ?? '-' }}
                                                </div>
                                                <div class="text-muted small">
                                                    {{ $ptm->tanggal
                                                        ? \Carbon\Carbon::parse($ptm->tanggal)->format('d M Y')
                                                        : '-' }}
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge {{ $ptm->status_badge_class ?? 'bg-secondary' }} small">
                                                    {{ $ptm->status_label ?? $ptm->status }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3"
                                                class="text-center py-4 text-muted small">
                                                Belum ada riwayat pengajuan bimbingan.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card shadow-sm h-100">
                    <div class="card-header py-3">
                        <h5 class="card-title fs-6 fw-bold mb-0">
                            Jadwal Bimbingan Disetujui
                        </h5>
                    </div>

                    <div class="card-body">
                        @forelse ($jadwalMendatang ?? [] as $jm)
                            <div class="p-3 bg-light rounded border mb-2">
                                <div class="fw-bold small text-primary mb-1">
                                    {{ optional($jm->dosen)->name ?? 'Dosen Pembimbing' }}
                                </div>
                                <div class="small">
                                    <i class="bi bi-calendar-event me-1"></i>
                                    {{ $jm->tanggal
                                        ? \Carbon\Carbon::parse($jm->tanggal)->format('d M Y')
                                        : '-' }}
                                </div>
                                <div class="small text-muted">
                                    <i class="bi bi-clock me-1"></i>
                                    {{ $jm->jam_mulai ?? '' }} -
                                    {{ $jm->jam_selesai ?? '' }} WIB
                                </div>
                                <div class="small text-muted">
                                    <i class="bi bi-geo-alt me-1"></i>
                                    {{ $jm->ruangan_atau_link ?? 'Lokasi menyusul' }}
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted small">
                                Belum ada jadwal bimbingan yang disetujui dalam waktu dekat.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

    @else
        <div class="card p-4 shadow-sm text-center my-4">
            <h1 class="h4 fw-bold">Selamat Datang di AcadLink</h1>
            <p class="text-muted mb-0">
                Sistem Bimbingan Akademik Mahasiswa dan Dosen.
            </p>
        </div>
    @endif
@endauth
@endsection
