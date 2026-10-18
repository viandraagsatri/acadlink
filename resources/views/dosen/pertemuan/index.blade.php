@extends('layouts.app')

@section('title', 'Daftar Pertemuan Bimbingan')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h1 class="h3 fw-bold mb-1">Pertemuan & Bimbingan Mahasiswa</h1>
        <p class="text-muted mb-0">Kelola pengajuan bimbingan, verifikasi jadwal, serta pencatatan revisi skripsi, KP, dan akademik.</p>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card d-flex align-items-center">
            <div class="rounded-circle bg-warning bg-opacity-25 p-3 me-3 text-warning fs-3">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <div>
                <div class="text-muted small">Menunggu Konfirmasi</div>
                <div class="fs-4 fw-bold text-warning">{{ $menungguCount }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card d-flex align-items-center">
            <div class="rounded-circle bg-info bg-opacity-25 p-3 me-3 text-info fs-3">
                <i class="bi bi-calendar2-check"></i>
            </div>
            <div>
                <div class="text-muted small">Disetujui / Terjadwal</div>
                <div class="fs-4 fw-bold text-info">{{ $disetujuiCount }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card d-flex align-items-center">
            <div class="rounded-circle bg-success bg-opacity-25 p-3 me-3 text-success fs-3">
                <i class="bi bi-check-all"></i>
            </div>
            <div>
                <div class="text-muted small">Bimbingan Selesai</div>
                <div class="fs-4 fw-bold text-success">{{ $selesaiCount }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4 shadow-sm">
    <div class="card-body">
        <form method="GET" action="{{ route('dosen.pertemuan.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Status Bimbingan</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="semua" {{ request('status') === 'semua' ? 'selected' : '' }}>Semua Status</option>
                    <option value="menunggu" {{ request('status') === 'menunggu' ? 'selected' : '' }}>Menunggu Konfirmasi</option>
                    <option value="disetujui" {{ request('status') === 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                    <option value="selesai" {{ request('status') === 'selesai' ? 'selected' : '' }}>Selesai</option>
                    <option value="ditolak" {{ request('status') === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Jenis Bimbingan</label>
                <select name="jenis_bimbingan" class="form-select form-select-sm">
                    <option value="semua" {{ request('jenis_bimbingan') === 'semua' ? 'selected' : '' }}>Semua Jenis</option>
                    <option value="skripsi" {{ request('jenis_bimbingan') === 'skripsi' ? 'selected' : '' }}>Bimbingan Skripsi</option>
                    <option value="kerja_praktik" {{ request('jenis_bimbingan') === 'kerja_praktik' ? 'selected' : '' }}>Bimbingan Kerja Praktik</option>
                    <option value="akademik" {{ request('jenis_bimbingan') === 'akademik' ? 'selected' : '' }}>Bimbingan Akademik (PA)</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Pencarian Mahasiswa / Topik</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik nama, NIM, atau judul topik..." class="form-control form-control-sm">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary btn-sm flex-grow-1">
                    <i class="bi bi-search me-1"></i> Cari
                </button>
                <a href="{{ route('dosen.pertemuan.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Mahasiswa</th>
                        <th>Jenis Bimbingan</th>
                        <th>Topik & Metode</th>
                        <th>Tanggal & Waktu</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pertemuans as $pertemuan)
                        <tr>
                            <td class="ps-3">
                                <div class="fw-bold">{{ $pertemuan->mahasiswa->name }}</div>
                                <div class="text-muted small">NIM: {{ $pertemuan->mahasiswa->nim_nip }}</div>
                            </td>
                            <td>
                                <span class="badge badge-lilac px-2 py-1">
                                    {{ $pertemuan->jenis_bimbingan_label }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-semibold text-truncate" style="max-width: 250px;" title="{{ $pertemuan->judul_topik }}">
                                    {{ $pertemuan->judul_topik }}
                                </div>
                                <div class="small text-muted">
                                    <i class="bi bi-broadcast me-1"></i> {{ $pertemuan->metode_label }}
                                </div>
                            </td>
                            <td>
                                <div><i class="bi bi-calendar3 me-1"></i> {{ $pertemuan->tanggal->format('d M Y') }}</div>
                                <div class="text-muted small">
                                    @if ($pertemuan->jam_mulai_format)
                                        <i class="bi bi-clock me-1"></i> {{ $pertemuan->jam_mulai_format }} @if($pertemuan->jam_selesai_format) - {{ $pertemuan->jam_selesai_format }} @endif WIB
                                    @else
                                        <span class="fst-italic">Sesuai konfirmasi</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge {{ $pertemuan->status_badge_class }}">
                                    {{ $pertemuan->status_label }}
                                </span>
                                @if ($pertemuan->arahan_revisis_count ?? $pertemuan->arahanRevisis->count())
                                    <div class="small text-muted mt-1">
                                        <i class="bi bi-list-check me-1"></i> {{ $pertemuan->arahanRevisis->count() }} Revisi
                                    </div>
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                @if ($pertemuan->status === 'disetujui')
                                    <a href="{{ route('dosen.pertemuan.show', $pertemuan->id) }}" class="btn btn-sm btn-primary">
                                        <i class="bi bi-pencil-square me-1"></i> Detail & Catatan
                                    </a>
                                @elseif ($pertemuan->status === 'selesai')
                                    <a href="{{ route('dosen.pertemuan.show', $pertemuan->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-file-earmark-text me-1"></i> Lihat Catatan
                                    </a>
                                @else
                                    <span class="text-muted small fst-italic">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-chat-left-dots fs-1 d-block mb-2"></i>
                                Belum ada data pertemuan bimbingan yang sesuai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if ($pertemuans->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $pertemuans->links() }}
        </div>
    @endif
</div>
@endsection
