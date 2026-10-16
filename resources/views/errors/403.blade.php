@extends('layouts.app')

@section('title', 'Akses Ditolak')

@section('content')
<div class="row justify-content-center my-5">
    <div class="col-md-7 text-center">
        <div class="card p-5">
            <div class="mb-3">
                <i class="bi bi-shield-lock-fill text-danger" style="font-size: 4rem;"></i>
            </div>
            <h1 class="h3 fw-bold mb-2">Akses Ditolak (403)</h1>
            <p class="text-muted mb-4">Anda tidak memiliki hak akses atau izin untuk membuka halaman ini.</p>
            <div>
                <a href="{{ route('home') }}" class="btn btn-primary px-4">
                    <i class="bi bi-house me-1"></i> Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
