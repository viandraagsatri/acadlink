@extends('layouts.app')
@section('content')
<div class="container py-4">
    <h3>Riwayat & Catatan Bimbingan</h3>
    <div class="mt-3">
        @forelse($riwayatList as $riwayat)
            <div class="card mb-3 shadow-sm">
                <div class="card-body">
                    <h5>{{ $riwayat->topik_pembahasan }}</h5>
                    <p class="text-muted mb-1">Dosen: {{ $riwayat->dosen->name ?? '-' }} | Status: <span class="badge bg-secondary">{{ $riwayat->status }}</span></p>
                    <hr>
                    <p><strong>Catatan Dosen:</strong> {{ $riwayat->catatan_dosen ?? 'Tidak ada catatan.' }}</p>
                </div>
            </div>
        @empty
            <div class="alert alert-info">Belum ada riwayat bimbingan yang selesai.</div>
        @endforelse
    </div>
</div>
@endsection