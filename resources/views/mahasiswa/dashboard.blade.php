<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <span class="badge badge-lilac px-3 py-1 mb-2">Beranda Mahasiswa</span>
        <h1 class="h3 fw-bold mb-1">Selamat Datang, {{ auth()->user()->name }}</h1>
        <p class="text-muted mb-0">NIM: {{ auth()->user()->nim_nip ?? '-' }} &bull; Pantau jadwal bimbingan yang disetujui dan progres revisimu.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('mahasiswa.bimbingan.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle me-1"></i> Ajukan Bimbingan
        </a>
        <a href="{{ route('mahasiswa.jadwal') }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-list-check me-1"></i> Pengajuan Aktif
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card d-flex align-items-center p-3 bg-white shadow-sm rounded">
            <div class="rounded-circle bg-warning bg-opacity-25 p-3 me-3 text-warning fs-4">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <div>
                <div class="text-muted small">Menunggu Konfirmasi</div>
                <div class="fs-4 fw-bold text-warning">{{ $menungguCount }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card d-flex align-items-center p-3 bg-white shadow-sm rounded">
            <div class="rounded-circle bg-info bg-opacity-25 p-3 me-3 text-info fs-4">
                <i class="bi bi-calendar2-check"></i>
            </div>
            <div>
                <div class="text-muted small">Jadwal Disetujui</div>
                <div class="fs-4 fw-bold text-info">{{ $disetujuiCount }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card d-flex align-items-center p-3 bg-white shadow-sm rounded">
            <div class="rounded-circle bg-success bg-opacity-25 p-3 me-3 text-success fs-4">
                <i class="bi bi-check-all"></i>
            </div>
            <div>
                <div class="text-muted small">Bimbingan Selesai</div>
                <div class="fs-4 fw-bold text-success">{{ $selesaiCount }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card d-flex align-items-center p-3 bg-white shadow-sm rounded">
            <div class="rounded-circle bg-danger bg-opacity-25 p-3 me-3 text-danger fs-4">
                <i class="bi bi-list-task"></i>
            </div>
            <div>
                <div class="text-muted small">Revisi Belum Selesai</div>
                <div class="fs-4 fw-bold text-danger">{{ $revisiBelumCount }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-7">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title fs-6 fw-bold mb-0"><i class="bi bi-calendar-check me-2"></i>Jadwal Bimbingan Disetujui</h5>
                <a href="{{ route('mahasiswa.jadwal') }}" class="small">Lihat Pengajuan</a>
            </div>
            <div class="card-body">
                @forelse ($jadwalMendatang as $jadwal)
                    <div class="p-3 bg-light rounded border mb-3">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <div>
                                <span class="badge badge-lilac small mb-1">{{ $jadwal->jenis_bimbingan_label }}</span>
                                <div class="fw-bold">{{ $jadwal->judul_topik }}</div>
                            </div>
                            @if ($jadwal->tanggal->isToday())
                                <span class="badge bg-danger flex-shrink-0">Hari ini</span>
                            @elseif ($jadwal->tanggal->isTomorrow())
                                <span class="badge bg-warning text-dark flex-shrink-0">Besok</span>
                            @else
                                <span class="badge bg-secondary flex-shrink-0">{{ (int) today()->diffInDays($jadwal->tanggal, false) }} hari lagi</span>
                            @endif
                        </div>

                        <div class="small mb-1">
                            <i class="bi bi-calendar-event me-2"></i>{{ $jadwal->tanggal->locale('id')->translatedFormat('l, d F Y') }}
                        </div>
                        <div class="small mb-1">
                            <i class="bi bi-clock me-2"></i>
                            @if ($jadwal->jam_mulai_format)
                                {{ $jadwal->jam_mulai_format }}@if ($jadwal->jam_selesai_format) - {{ $jadwal->jam_selesai_format }}@endif WIB
                            @else
                                <span class="fst-italic text-muted">Jam menyusul dari dosen</span>
                            @endif
                        </div>
                        <div class="small mb-1">
                            <i class="bi bi-broadcast me-2"></i>{{ $jadwal->metode_label }}
                        </div>
                        <div class="small mb-2">
                            <i class="bi bi-geo-alt me-2"></i>
                            @if ($jadwal->tempat && \Illuminate\Support\Str::startsWith($jadwal->tempat, ['http://', 'https://']))
                                <a href="{{ $jadwal->tempat }}" target="_blank" rel="noopener noreferrer">Buka tautan pertemuan</a>
                            @else
                                {{ $jadwal->tempat ?: 'Lokasi menyusul dari dosen' }}
                            @endif
                        </div>

                        <div class="d-flex justify-content-between align-items-center border-top pt-2">
                            <div class="small">
                                <i class="bi bi-person-badge me-1"></i>
                                <span class="fw-semibold">{{ optional($jadwal->dosen)->name ?? 'Dosen Pembimbing' }}</span>
                                <span class="text-muted">&bull; NIP {{ optional($jadwal->dosen)->nim_nip ?? '-' }}</span>
                            </div>
                            <a href="{{ route('mahasiswa.riwayat.show', $jadwal->id) }}" class="btn btn-outline-primary btn-sm py-0 px-2">Detail</a>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4 text-muted small">
                        <div class="fs-1"><i class="bi bi-calendar-x"></i></div>
                        Belum ada jadwal bimbingan yang disetujui dalam waktu dekat.
                    </div>
                @endforelse

                @if ($jadwalTerlewat->isNotEmpty())
                    <div class="small text-muted fw-semibold mt-3 mb-2">Sudah lewat, menunggu catatan hasil dari dosen</div>
                    @foreach ($jadwalTerlewat as $jadwal)
                        <div class="d-flex justify-content-between align-items-center small border rounded p-2 mb-2">
                            <div>
                                <div class="fw-semibold text-truncate" style="max-width: 280px;">{{ $jadwal->judul_topik }}</div>
                                <div class="text-muted">{{ $jadwal->tanggal->format('d M Y') }} &bull; {{ optional($jadwal->dosen)->name }}</div>
                            </div>
                            <a href="{{ route('mahasiswa.riwayat.show', $jadwal->id) }}" class="btn btn-outline-secondary btn-sm py-0 px-2">Detail</a>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title fs-6 fw-bold mb-0"><i class="bi bi-list-task me-2"></i>Progres Revisi</h5>
                <a href="{{ route('mahasiswa.revisi') }}" class="small">Lihat Semua</a>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small fw-semibold">{{ $revisiSelesaiCount }} dari {{ $revisiTotal }} revisi selesai</span>
                    <span class="small text-muted">{{ $revisiProgress }}%</span>
                </div>
                <div class="progress mb-3" role="progressbar" aria-valuenow="{{ $revisiProgress }}" aria-valuemin="0" aria-valuemax="100" style="height: 10px;">
                    <div class="progress-bar" style="width: {{ $revisiProgress }}%; background-color: var(--lilac-dark);"></div>
                </div>

                @forelse ($revisiAktif as $revisi)
                    <div class="p-3 bg-light rounded border mb-2">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div class="small fw-semibold">{{ \Illuminate\Support\Str::limit($revisi->isi_revisi, 90) }}</div>
                            <span class="badge {{ $revisi->status_badge_class }} flex-shrink-0">{{ $revisi->status_label }}</span>
                        </div>
                        <div class="small text-muted mt-1">
                            @if ($revisi->tenggat_waktu)
                                <i class="bi bi-calendar-event me-1"></i> Tenggat {{ $revisi->tenggat_waktu->format('d M Y') }}
                                @if ($revisi->tenggat_waktu->lt(today()))
                                    <span class="badge bg-danger ms-1">Terlambat</span>
                                @endif
                            @else
                                Tanpa tenggat
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-3 text-muted small">Tidak ada revisi yang tertunda.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h5 class="card-title fs-6 fw-bold mb-0">Pengajuan dan Pertemuan Terbaru</h5>
        <a href="{{ route('mahasiswa.riwayat') }}" class="small">Lihat Riwayat</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small">
                    <tr>
                        <th class="ps-3">Dosen Pembimbing</th>
                        <th>Topik / Tanggal</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pertemuanTerbaru as $ptm)
                        <tr>
                            <td class="ps-3">
                                <div class="fw-bold small">{{ optional($ptm->dosen)->name ?? 'Dosen' }}</div>
                                <div class="text-muted small">{{ $ptm->jenis_bimbingan_label }}</div>
                            </td>
                            <td>
                                <div class="small fw-semibold text-truncate" style="max-width: 240px;">{{ $ptm->judul_topik }}</div>
                                <div class="text-muted small">{{ optional($ptm->tanggal)->format('d M Y') }}</div>
                            </td>
                            <td>
                                <span class="badge {{ $ptm->status_badge_class }} small">{{ $ptm->status_label }}</span>
                            </td>
                            <td class="text-end pe-3">
                                <a href="{{ route('mahasiswa.riwayat.show', $ptm->id) }}" class="btn btn-outline-primary btn-sm py-0 px-2">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted small">Belum ada riwayat pengajuan bimbingan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
