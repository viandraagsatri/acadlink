@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h4 mb-3">Login</h1>

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}"
                               class="form-control @error('email') is-invalid @enderror" required autofocus>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <input type="password" name="password" id="password" autocomplete="current-password"
                                class="form-control @error('password') is-invalid @enderror" required>
                            <button type="button" class="btn btn-outline-secondary toggle-password" data-target="password"
                                    aria-label="Tampilkan password" title="Tampilkan password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label" for="remember">Ingat saya</label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Masuk</button>
                </form>

                <p class="text-center mt-3 mb-0 small">
                    Belum punya akun? <a href="{{ route('register') }}">Daftar</a>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.toggle-password').forEach(function (tombol) {
        tombol.addEventListener('click', function () {
            const input = document.getElementById(tombol.dataset.target);
            const ikon = tombol.querySelector('i');
            const akanTampil = input.type === 'password';

            input.type = akanTampil ? 'text' : 'password';
            ikon.classList.toggle('bi-eye', !akanTampil);
            ikon.classList.toggle('bi-eye-slash', akanTampil);

            const teks = akanTampil ? 'Sembunyikan password' : 'Tampilkan password';
            tombol.setAttribute('aria-label', teks);
            tombol.title = teks;
        });
    });
</script>
@endsection