@extends('layouts.app')
@section('title', 'Riwayat Bimbingan')
@section('content')
<div class="mb-4">
    <h1 class="h3 fw-bold mb-1">Riwayat & Catatan Bimbingan</h1>
    <p class="text-muted mb-0">Bimbingan yang sudah selesai beserta catatan dosen, dan pengajuan yang ditolak beserta alasannya.</p>
</div>

<div class="row g-3 mb-4">
    <div class="col-6">
        <div class="stat-card d-flex align-items-center p-3 bg-white shadow-sm rounded">
            <div class="rounded-circle bg-success bg-opacity-25 p-3 me-3 text-success fs-4">
                <i class="bi bi-check-all"></i>
            </div>
            <div>
                <div class="text-muted small">Selesai</div>
                <div class="fs-4 fw-bold text-success">{{ $selesaiCount }}</div>
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="stat-card d-flex align-items-center p-3 bg-white shadow-sm rounded">
            <div class="rounded-circle bg-danger bg-opacity-25 p-3 me-3 text-danger fs-4">
                <i class="bi bi-x-circle"></i>
            </div>
            <div>
                <div class="text-muted small">Ditolak</div>
                <div class="fs-4 fw-bold text-danger">{{ $ditolakCount }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4 shadow-sm">
    <div class="card-body">
        <form method="GET" action="{{ route('mahasiswa.riwayat') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold" for="status">Status</label>
                <select name="status" id="status" class="form-select form-select-sm">
                    <option value="">Selesai & Ditolak</option>
                    <option value="selesai" @selected(request('status') === 'selesai')>Selesai</option>
                    <option value="ditolak" @selected(request('status') === 'ditolak')>Ditolak</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold" for="jenis_bimbingan">Jenis Bimbingan</label>
                <select name="jenis_bimbingan" id="jenis_bimbingan" class="form-select form-select-sm">
                    <option value="">Semua Jenis</option>
                    <option value="skripsi" @selected(request('jenis_bimbingan') === 'skripsi')>Bimbingan Skripsi</option>
                    <option value="kerja_praktik" @selected(request('jenis_bimbingan') === 'kerja_praktik')>Bimbingan Kerja Praktik</option>
                    <option value="akademik" @selected(request('jenis_bimbingan') === 'akademik')>Bimbingan Akademik (PA)</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold" for="search">Cari Topik</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Ketik judul topik..." class="form-control form-control-sm">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary btn-sm flex-grow-1">
                    <i class="bi bi-search me-1"></i> Cari
                </button>
                <a href="{{ route('mahasiswa.riwayat') }}" class="btn btn-outline-secondary btn-sm" title="Reset filter">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

@forelse ($riwayatList as $riwayat)
    @php
        $totalRevisi = $riwayat->arahanRevisis->count();
        $revisiSelesai = $riwayat->arahanRevisis->where('status', 'selesai')->count();
    @endphp
    <div class="card mb-3 shadow-sm {{ $riwayat->status === 'ditolak' ? 'border-danger' : '' }}">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                <div>
                    <span class="badge badge-lilac px-2 py-1 mb-1">{{ $riwayat->jenis_bimbingan_label }}</span>
                    <h5 class="fw-bold mb-1">{{ $riwayat->judul_topik }}</h5>
                </div>
                <span class="badge {{ $riwayat->status_badge_class }} px-3 py-2 flex-shrink-0">{{ $riwayat->status_label }}</span>
            </div>

            <p class="text-muted small mb-2">
                <i class="bi bi-person-badge me-1"></i>{{ $riwayat->dosen->name ?? '-' }}
                &bull; <i class="bi bi-calendar3 me-1"></i>{{ $riwayat->tanggal->format('d M Y') }}
            </p>
            <hr class="my-2">

            @if ($riwayat->status === 'ditolak')
                <div class="alert alert-danger small mb-0">
                    <div class="fw-bold mb-1"><i class="bi bi-x-circle me-1"></i> Alasan ditolak</div>
                    {{ $riwayat->alasan_penolakan ?: 'Dosen tidak mencantumkan alasan.' }}
                </div>
            @else
                <div class="small fw-semibold mb-1">Catatan Dosen</div>
                <div class="small mb-2" style="white-space: pre-line;">{{ $riwayat->catatan_hasil ? \Illuminate\Support\Str::limit($riwayat->catatan_hasil, 300) : 'Tidak ada catatan.' }}</div>
                @if ($totalRevisi > 0)
                    <div class="small text-muted"><i class="bi bi-list-check me-1"></i>{{ $revisiSelesai }}/{{ $totalRevisi }} revisi selesai</div>
                @endif
            @endif

            <div class="mt-3 d-flex gap-2">
                <a href="{{ route('mahasiswa.riwayat.show', $riwayat->id) }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-file-earmark-text me-1"></i> Detail
                </a>
                @if ($riwayat->status === 'ditolak')
                    <a href="{{ route('mahasiswa.bimbingan.create') }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-arrow-repeat me-1"></i> Ajukan Ulang
                    </a>
                @endif
            </div>
        </div>
    </div>
@empty
    <div class="alert alert-info">Belum ada riwayat bimbingan yang selesai atau ditolak.</div>
@endforelse

@if ($riwayatList->hasPages())
    <div class="d-flex justify-content-center mt-3">
        {{ $riwayatList->links() }}
    </div>
@endif
@endsection
