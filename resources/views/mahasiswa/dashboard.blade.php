@extends('layouts.app')
@section('content')
<div class="container py-4">
    <h3>Dashboard Mahasiswa</h3>
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card p-3 shadow-sm">
                <h5>Jadwal Terdekat</h5>
                @if($jadwalMendatang)
                    <p class="mb-1"><strong>{{ $jadwalMendatang->topik_pembahasan }}</strong></p>
                    <small class="text-muted">{{ $jadwalMendatang->jadwal_bimbingan }}</small>
                @else
                    <p class="text-muted mb-0">Tidak ada jadwal bimbingan terdekat.</p>
                @endif
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 shadow-sm">
                <h5>Revisi Pending</h5>
                <h3>{{ $totalRevisiPending }}</h3>
                <small class="text-muted">Tugas revisi belum selesai</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 shadow-sm">
                <h5>Bimbingan Selesai</h5>
                <h3>{{ $totalSelesai }}</h3>
                <small class="text-muted">Total sesi tuntas</small>
            </div>
        </div>
    </div>
</div>
@endsection