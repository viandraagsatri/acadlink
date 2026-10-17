@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center align-items-center" style="min-height: 75vh;">
        <div class="col-md-5 col-lg-4">
            <div class="card border-0 shadow-lg rounded-4 p-4 bg-white">
                <div class="card-body">
                    
                    <div class="text-center mb-4">
                        <h4 class="fw-bold text-dark mb-2">Welcome to AcadLink</h4>
                        <div class="d-inline-flex align-items-center justify-content-center bg-light text-purple rounded-circle p-3 mb-1 shadow-sm" style="width: 70px; height: 70px;">
                            <i class="bi bi-mortarboard-fill fs-1 text-purple"></i>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label text-muted small fw-semibold">Email</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                                <input id="email" type="email" class="form-control bg-light border-start-0 @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="Email Anda">
                            </div>
                            @error('email')
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label text-muted small fw-semibold">Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                                <input id="password" type="password" class="form-control bg-light border-start-0 border-end-0 @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="Password Anda">
                                <button class="btn btn-light border border-start-0 text-muted toggle-password" type="button" data-target="password" aria-label="Tampilkan password">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            @error('password')
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3 form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label small text-muted" for="remember">
                                Ingat Saya
                            </label>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary rounded-pill py-2 fw-semibold shadow-sm">
                                Masuk
                            </button>
                        </div>

                        <div class="text-center mt-4">
                            <p class="small text-muted">Belum punya akun? <a href="{{ route('register') }}" class="text-decoration-none fw-semibold text-purple">Daftar sekarang</a></p>
                        </div>

                    </form>
                </div>
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