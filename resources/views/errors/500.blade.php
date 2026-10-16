@extends('layouts.app')

@section('title', 'Terjadi Kendala Sistem')

@section('content')
<div class="row justify-content-center my-5">
    <div class="col-md-7 text-center">
        <div class="card p-5">
            <div class="mb-3">
                <i class="bi bi-gear-wide-connected text-warning" style="font-size: 4rem;"></i>
            </div>
            <h1 class="h3 fw-bold mb-2">Terjadi Kendala Sistem (500)</h1>
            <p class="text-muted mb-4">Sistem sedang memproses kendala sementara. Silakan coba beberapa saat lagi.</p>
            <div>
                <a href="{{ route('home') }}" class="btn btn-primary px-4">
                    <i class="bi bi-arrow-clockwise me-1"></i> Muat Ulang Halaman
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
