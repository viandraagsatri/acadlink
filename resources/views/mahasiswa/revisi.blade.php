@extends('layouts.app')
@section('title', 'Revisi Saya')
@section('content')
<div class="mb-4">
    <h1 class="h3 fw-bold mb-1">Daftar Revisi</h1>
    <p class="text-muted mb-0">Arahan revisi dari dosen pembimbing beserta progres pengerjaannya.</p>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="fw-semibold">Progres Revisi</span>
            <span class="small text-muted">{{ $jumlahSelesai }} dari {{ $total }} revisi selesai ({{ $progress }}%)</span>
        </div>
        <div class="progress" role="progressbar" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100" style="height: 10px;">
            <div class="progress-bar" style="width: {{ $progress }}%; background-color: var(--lilac-dark);"></div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <h2 class="h6 fw-bold text-uppercase text-muted mb-3">
            Belum Selesai <span class="badge bg-danger ms-1">{{ $revisiPending->count() }}</span>
        </h2>
        @forelse ($revisiPending as $revisi)
            @include('mahasiswa.revisi-item', ['revisi' => $revisi])
        @empty
            <div class="card p-4 text-center text-muted">
                <div class="fs-1"><i class="bi bi-emoji-smile"></i></div>
                <div class="fw-semibold">Tidak ada revisi yang menggantung. Bagus!</div>
            </div>
        @endforelse
    </div>

    <div class="col-lg-5">
        <h2 class="h6 fw-bold text-uppercase text-muted mb-3">
            Sudah Selesai <span class="badge bg-success ms-1">{{ $revisiDone->count() }}</span>
        </h2>
        @forelse ($revisiDone as $revisi)
            @include('mahasiswa.revisi-item', ['revisi' => $revisi])
        @empty
            <div class="card p-4 text-center text-muted small">Belum ada revisi yang diselesaikan.</div>
        @endforelse
    </div>
</div>
@endsection
