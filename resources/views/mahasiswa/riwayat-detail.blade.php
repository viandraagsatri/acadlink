@extends('layouts.app')
@section('title', 'Detail Bimbingan')
@section('content')
<div class="mb-3">
    <a href="{{ route('mahasiswa.riwayat') }}" class="small"><i class="bi bi-arrow-left me-1"></i> Kembali ke Riwayat</a>
</div>

<div class="d-flex flex-wrap justify-content-between align-items-start mb-4 gap-3">
    <div>
        <span class="badge badge-lilac px-3 py-1 mb-2">{{ $pertemuan->jenis_bimbingan_label }}</span>
        <h1 class="h3 fw-bold mb-1">{{ $pertemuan->judul_topik }}</h1>
        <p class="text-muted mb-0">Detail Pertemuan Bimbingan</p>
    </div>
    <span class="badge {{ $pertemuan->status_badge_class }} fs-6 px-3 py-2">{{ $pertemuan->status_label }}</span>
</div>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card shadow-sm mb-4">
            <div class="card-header py-3">
                <h5 class="card-title fs-6 fw-bold mb-0"><i class="bi bi-info-circle me-2"></i>Informasi Pertemuan</h5>
            </div>
            <div class="card-body small">
                <div class="mb-3">
                    <div class="text-muted">Tanggal</div>
                    <div class="fw-semibold">{{ $pertemuan->tanggal->locale('id')->translatedFormat('l, d F Y') }}</div>
                </div>
                <div class="mb-3">
                    <div class="text-muted">Waktu</div>
                    <div class="fw-semibold">
                        @if ($pertemuan->jam_mulai_format)
                            {{ $pertemuan->jam_mulai_format }}@if ($pertemuan->jam_selesai_format) - {{ $pertemuan->jam_selesai_format }}@endif WIB
                        @else
                            <span class="fst-italic text-muted">Belum ditentukan</span>
                        @endif
                    </div>
                </div>
                <div class="mb-3">
                    <div class="text-muted">Metode</div>
                    <div class="fw-semibold">{{ $pertemuan->metode_label }}</div>
                </div>
                <div class="mb-3">
                    <div class="text-muted">Tempat / Tautan</div>
                    <div class="fw-semibold">
                        @if ($pertemuan->tempat && \Illuminate\Support\Str::startsWith($pertemuan->tempat, ['http://', 'https://']))
                            <a href="{{ $pertemuan->tempat }}" target="_blank" rel="noopener noreferrer">Buka tautan pertemuan</a>
                        @else
                            {{ $pertemuan->tempat ?: 'Belum ditentukan' }}
                        @endif
                    </div>
                </div>
                @if ($pertemuan->deskripsi_singkat)
                    <div class="mb-0">
                        <div class="text-muted">Deskripsi Pengajuan</div>
                        <div>{{ $pertemuan->deskripsi_singkat }}</div>
                    </div>
                @endif
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header py-3">
                <h5 class="card-title fs-6 fw-bold mb-0"><i class="bi bi-person-badge me-2"></i>Dosen Pembimbing</h5>
            </div>
            <div class="card-body small">
                <div class="fw-semibold fs-6">{{ $pertemuan->dosen->name }}</div>
                <div class="text-muted">NIP: {{ $pertemuan->dosen->nim_nip }}</div>
                <div class="text-muted">{{ $pertemuan->dosen->email }}</div>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        @if ($pertemuan->status === 'ditolak')
            <div class="alert alert-danger shadow-sm">
                <div class="fw-bold mb-1"><i class="bi bi-x-circle me-1"></i> Pengajuan ditolak dosen</div>
                <div class="small">{{ $pertemuan->alasan_penolakan ?: 'Dosen tidak mencantumkan alasan.' }}</div>
                <a href="{{ route('mahasiswa.bimbingan.create') }}" class="btn btn-sm btn-outline-danger mt-2">
                    <i class="bi bi-arrow-repeat me-1"></i> Ajukan Ulang
                </a>
            </div>
        @endif

        <div class="card shadow-sm mb-4">
            <div class="card-header py-3">
                <h5 class="card-title fs-6 fw-bold mb-0"><i class="bi bi-journal-text me-2"></i>Catatan Hasil Pembahasan</h5>
            </div>
            <div class="card-body">
                @if ($pertemuan->catatan_hasil)
                    <div style="white-space: pre-line;">{{ $pertemuan->catatan_hasil }}</div>
                    @if ($pertemuan->tanggal_bimbingan_selesai)
                        <div class="small text-muted mt-3">
                            Diselesaikan pada {{ $pertemuan->tanggal_bimbingan_selesai->format('d M Y, H:i') }} WIB
                        </div>
                    @endif
                @else
                    <div class="text-muted fst-italic">Dosen belum mengisi catatan hasil pembahasan.</div>
                @endif
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title fs-6 fw-bold mb-0"><i class="bi bi-list-task me-2"></i>Arahan Revisi</h5>
                <a href="{{ route('mahasiswa.revisi') }}" class="small">Perbarui progres</a>
            </div>
            <div class="card-body p-0">
                @forelse ($pertemuan->arahanRevisis as $revisi)
                    <div class="p-3 {{ ! $loop->last ? 'border-bottom' : '' }}">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div class="fw-semibold">{{ $revisi->isi_revisi }}</div>
                            <span class="badge {{ $revisi->status_badge_class }} flex-shrink-0">{{ $revisi->status_label }}</span>
                        </div>
                        <div class="small text-muted mt-1">
                            <span class="badge {{ $revisi->prioritas_badge_class }} me-2">{{ $revisi->prioritas_label }}</span>
                            @if ($revisi->tenggat_waktu)
                                <i class="bi bi-calendar-event me-1"></i> Tenggat {{ $revisi->tenggat_waktu->format('d M Y') }}
                            @endif
                        </div>
                        @if ($revisi->catatan_mahasiswa)
                            <div class="small mt-2"><span class="text-muted">Catatanmu:</span> {{ $revisi->catatan_mahasiswa }}</div>
                        @endif
                    </div>
                @empty
                    <div class="p-4 text-center text-muted small">Belum ada arahan revisi untuk pertemuan ini.</div>
                @endforelse
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header py-3">
                <h5 class="card-title fs-6 fw-bold mb-0"><i class="bi bi-clock-history me-2"></i>Pembahasan Sebelumnya dengan {{ $pertemuan->dosen->name }}</h5>
            </div>
            <div class="card-body p-0">
                @forelse ($pertemuanSebelumnya as $prev)
                    <div class="p-3 {{ ! $loop->last ? 'border-bottom' : '' }}">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div>
                                <div class="fw-semibold">{{ $prev->judul_topik }}</div>
                                <div class="small text-muted">{{ $prev->tanggal->format('d M Y') }} &bull; {{ $prev->jenis_bimbingan_label }}</div>
                            </div>
                            <a href="{{ route('mahasiswa.riwayat.show', $prev->id) }}" class="btn btn-outline-primary btn-sm flex-shrink-0">Detail</a>
                        </div>
                        @if ($prev->catatan_hasil)
                            <div class="small mt-2" style="white-space: pre-line;">{{ \Illuminate\Support\Str::limit($prev->catatan_hasil, 220) }}</div>
                        @endif
                        @if ($prev->arahanRevisis->isNotEmpty())
                            <div class="small text-muted mt-2">
                                <i class="bi bi-list-check me-1"></i>
                                {{ $prev->arahanRevisis->where('status', 'selesai')->count() }}/{{ $prev->arahanRevisis->count() }} revisi selesai
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="p-4 text-center text-muted small">Belum ada pembahasan sebelumnya dengan dosen ini.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
