@extends('layouts.app')
@section('content')
<div class="container py-4">
    <h3>Daftar Revisi</h3>
    
    <h5 class="mt-4 text-danger">Belum Selesai (Pending)</h5>
    @forelse($revisiPending as $rev)
        <div class="card mb-2 shadow-sm border-danger">
            <div class="card-body">
                <p>{{ $rev->deskripsi_revisi }}</p>
                <form action="{{ route('mahasiswa.revisi.update', $rev->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-2">
                        <textarea name="catatan_mahasiswa" class="form-control" placeholder="Tulis progres/tanggapan revisi..." required></textarea>
                    </div>
                    <div class="mb-2">
                        <input type="file" name="file_progres" class="form-control form-control-sm">
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary">Tandai Selesai</button>
                </form>
            </div>
        </div>
    @empty
        <p class="text-muted">Tidak ada revisi yang menggantung. Bagus!</p>
    @endforelse

    <h5 class="mt-5 text-success">Sudah Selesai (Done)</h5>
    @forelse($revisiDone as $rev)
        <div class="card mb-2 shadow-sm border-success bg-light">
            <div class="card-body">
                <p class="mb-1"><s>{{ $rev->deskripsi_revisi }}</s></p>
                <small class="text-muted">Progres Kamu: {{ $rev->catatan_mahasiswa }}</small>
            </div>
        </div>
    @empty
        <p class="text-muted">Belum ada revisi yang diselesaikan.</p>
    @endforelse
</div>
@endsection