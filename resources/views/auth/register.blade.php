@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center align-items-center" style="min-height: 75vh;">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow-lg rounded-4 p-4 bg-white">
                <div class="card-body">
                    
                    <div class="text-center mb-4">
                        <h4 class="fw-bold text-dark mb-2">Welcome to AcadLink</h4>
                        <div class="d-inline-flex align-items-center justify-content-center bg-light text-purple rounded-circle p-3 mb-1 shadow-sm" style="width: 70px; height: 70px;">
                            <i class="bi bi-mortarboard-fill fs-1 text-purple"></i>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label text-muted small fw-semibold">Nama Lengkap</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                                <input id="name" type="text" class="form-control bg-light border-start-0 @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus placeholder="Nama lengkap Anda">
                            </div>
                            @error('name')
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="nim_nip" class="form-label text-muted small fw-semibold">NIM / NIP</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-card-text text-muted"></i></span>
                                <input id="nim_nip" type="text" class="form-control bg-light border-start-0 @error('nim_nip') is-invalid @enderror" name="nim_nip" value="{{ old('nim_nip') }}" required placeholder="Masukkan NIM atau NIP Anda">
                            </div>
                            @error('nim_nip')
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label text-muted small fw-semibold">Pilih Peran</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-person-badge text-muted"></i></span>
                                <select id="role" class="form-select bg-light border-start-0 @error('role') is-invalid @enderror" name="role" required>
                                    <option value="" disabled selected>Pilih peran Anda</option>
                                    <option value="mahasiswa" {{ old('role') == 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                                    <option value="dosen" {{ old('role') == 'dosen' ? 'selected' : '' }}>Dosen</option>
                                </select>
                            </div>
                            @error('role')
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label text-muted small fw-semibold">Email</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                                <input id="email" type="email" class="form-control bg-light border-start-0 @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="Email Anda">
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
                                <input id="password" type="password" class="form-control bg-light border-start-0 border-end-0 @error('password') is-invalid @enderror" name="password" required autocomplete="new-password" placeholder="Password Anda">
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

                        <div class="mb-3">
                            <label for="password-confirm" class="form-label text-muted small fw-semibold">Konfirmasi Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock-fill text-muted"></i></span>
                                <input id="password-confirm" type="password" class="form-control bg-light border-start-0 border-end-0" name="password_confirmation" required autocomplete="new-password" placeholder="Konfirmasi Password Anda">
                                <button class="btn btn-light border border-start-0 text-muted toggle-password" type="button" data-target="password-confirm" aria-label="Tampilkan password">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary rounded-pill py-2 fw-semibold shadow-sm">
                                Daftar
                            </button>
                        </div>

                        <div class="text-center mt-4">
                            <p class="small text-muted">Sudah punya akun? <a href="{{ route('login') }}" class="text-decoration-none fw-semibold text-purple">Masuk di sini</a></p>
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