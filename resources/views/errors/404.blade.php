@extends('layouts.app')

@section('title', 'Halaman Tidak Ditemukan')

@section('content')
<div class="row justify-content-center my-5">
    <div class="col-md-7 text-center">
        <div class="card p-5">
            <div class="mb-3">
                <i class="bi bi-compass text-muted" style="font-size: 4rem;"></i>
            </div>
            <h1 class="h3 fw-bold mb-2">Halaman Tidak Ditemukan (404)</h1>
            <p class="text-muted mb-4">Mohon maaf, halaman yang Anda cari tidak tersedia atau tautan sudah kedaluwarsa.</p>
            <div>
                <a href="{{ route('home') }}" class="btn btn-primary px-4">
                    <i class="bi bi-house me-1"></i> Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
