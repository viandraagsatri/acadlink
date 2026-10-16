@extends('layouts.app')

@section('title', 'Kelola Jadwal Konsultasi')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h1 class="h3 fw-bold mb-1">Kelola Jadwal Konsultasi</h1>
        <p class="text-muted mb-0">Atur ketersediaan waktu bimbingan akademik, kerja praktik, dan skripsi.</p>
    </div>
    <a href="{{ route('dosen.jadwal.create') }}" class="btn btn-primary d-inline-flex align-items-center">
        <i class="bi bi-calendar-plus me-2"></i> Tambah Jadwal Baru
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card d-flex align-items-center">
            <div class="rounded-circle bg-light p-3 me-3 text-primary fs-3">
                <i class="bi bi-calendar-check"></i>
            </div>
            <div>
                <div class="text-muted small">Total Jadwal Dibuat</div>
                <div class="fs-4 fw-bold">{{ $totalJadwal }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card d-flex align-items-center">
            <div class="rounded-circle bg-light p-3 me-3 text-success fs-3">
                <i class="bi bi-check-circle"></i>
            </div>
            <div>
                <div class="text-muted small">Jadwal Aktif</div>
                <div class="fs-4 fw-bold text-success">{{ $totalAktif }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card d-flex align-items-center">
            <div class="rounded-circle bg-light p-3 me-3 text-purple fs-3">
                <i class="bi bi-clock-history"></i>
            </div>
            <div>
                <div class="text-muted small">Sistem Anti-Bentrok</div>
                <div class="small text-muted">Validasi otomatis jam & tanggal</div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('dosen.jadwal.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="semua" {{ request('status') === 'semua' ? 'selected' : '' }}>Semua Status</option>
                    <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                    <option value="selesai" {{ request('status') === 'selesai' ? 'selected' : '' }}>Selesai</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Jenis Bimbingan</label>
                <select name="jenis_bimbingan" class="form-select form-select-sm">
                    <option value="semua" {{ request('jenis_bimbingan') === 'semua' ? 'selected' : '' }}>Semua Jenis</option>
                    <option value="Semua" {{ request('jenis_bimbingan') === 'Semua' ? 'selected' : '' }}>Terbuka Semua Jenis</option>
                    <option value="Skripsi" {{ request('jenis_bimbingan') === 'Skripsi' ? 'selected' : '' }}>Skripsi</option>
                    <option value="Kerja Praktik" {{ request('jenis_bimbingan') === 'Kerja Praktik' ? 'selected' : '' }}>Kerja Praktik</option>
                    <option value="Akademik" {{ request('jenis_bimbingan') === 'Akademik' ? 'selected' : '' }}>Akademik</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Tanggal</label>
                <input type="date" name="tanggal" value="{{ request('tanggal') }}" class="form-control form-control-sm">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary btn-sm flex-grow-1">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                <a href="{{ route('dosen.jadwal.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
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
                        <th class="ps-3">Hari & Tanggal</th>
                        <th>Waktu (WIB)</th>
                        <th>Jenis Bimbingan</th>
                        <th>Metode & Lokasi</th>
                        <th>Kuota Terisi</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($jadwals as $jadwal)
                        <tr>
                            <td class="ps-3">
                                <div class="fw-bold">{{ $jadwal->hari }}</div>
                                <div class="text-muted small">{{ $jadwal->tanggal->format('d M Y') }}</div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <i class="bi bi-clock me-1"></i> {{ $jadwal->jam_mulai_format }} - {{ $jadwal->jam_selesai_format }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-lilac px-2 py-1">
                                    {{ $jadwal->jenis_bimbingan }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-semibold small">{{ $jadwal->metode_badge }}</div>
                                <div class="text-muted small text-truncate" style="max-width: 220px;" title="{{ $jadwal->ruangan_atau_link }}">
                                    @if(str_starts_with($jadwal->ruangan_atau_link, 'http'))
                                        <a href="{{ $jadwal->ruangan_atau_link }}" target="_blank" class="text-primary text-decoration-none">
                                            <i class="bi bi-link-45deg"></i> Buka Tautan Online
                                        </a>
                                    @else
                                        <i class="bi bi-geo-alt me-1"></i> {{ $jadwal->ruangan_atau_link }}
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fw-bold {{ $jadwal->is_penuh ? 'text-danger' : 'text-success' }}">
                                        {{ $jadwal->kuota_terisi }} / {{ $jadwal->kuota }}
                                    </span>
                                    @if ($jadwal->is_penuh)
                                        <span class="badge bg-danger small">Penuh</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if ($jadwal->status === 'aktif')
                                    <span class="badge bg-success">Aktif</span>
                                @elseif ($jadwal->status === 'nonaktif')
                                    <span class="badge bg-secondary">Nonaktif</span>
                                @else
                                    <span class="badge bg-info text-dark">Selesai</span>
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('dosen.jadwal.edit', $jadwal->id) }}" class="btn btn-outline-secondary" title="Ubah Jadwal">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $jadwal->id }}" title="Hapus Jadwal">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>

                                <div class="modal fade" id="deleteModal{{ $jadwal->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content text-start">
                                            <div class="modal-header">
                                                <h5 class="modal-title fs-6 fw-bold">Konfirmasi Hapus Jadwal</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p class="mb-1">Apakah Anda yakin ingin menghapus jadwal bimbingan pada:</p>
                                                <p class="fw-bold text-danger mb-2">
                                                    {{ $jadwal->hari }}, {{ $jadwal->tanggal->format('d M Y') }} ({{ $jadwal->jam_mulai_format }} - {{ $jadwal->jam_selesai_format }})
                                                </p>
                                                <small class="text-muted">Jika sudah ada mahasiswa yang terdaftar pada jadwal ini, jadwal tidak akan dapat dihapus dan disarankan untuk dinonaktifkan.</small>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                                <form action="{{ route('dosen.jadwal.destroy', $jadwal->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm">Ya, Hapus Jadwal</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
                                Belum ada data jadwal konsultasi yang sesuai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if ($jadwals->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $jadwals->links() }}
        </div>
    @endif
</div>
@endsection
