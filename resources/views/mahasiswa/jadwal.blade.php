@extends('layouts.app')
@section('content')
<div class="container py-4">
    <h3>Jadwal Bimbingan Disetujui</h3>
    <div class="list-group mt-3">
        @forelse($jadwalList as $jadwal)
            <div class="list-group-item list-group-item-action">
                <h5 class="mb-1">{{ $jadwal->topik_pembahasan }} ({{ $jadwal->jenis_bimbingan }})</h5>
                <p class="mb-1">Dosen: <strong>{{ $jadwal->dosen->name ?? '-' }}</strong></p>
                <small>Waktu: {{ $jadwal->jadwal_bimbingan }} | Tempat/Link: {{ $jadwal->lokasi_atau_link ?? 'Belum ditentukan' }}</small>
            </div>
        @empty
            <div class="alert alert-info">Belum ada jadwal bimbingan yang disetujui.</div>
        @endforelse
    </div>
</div>
@endsection