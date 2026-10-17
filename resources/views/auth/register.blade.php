@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h4 mb-3">Register</h1>

                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama lengkap</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}"
                               class="form-control @error('name') is-invalid @enderror" required autofocus>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="nim_nip" class="form-label">NIM / NIP</label>
                        <input type="text" name="nim_nip" id="nim_nip" value="{{ old('nim_nip') }}"
                               class="form-control @error('nim_nip') is-invalid @enderror" required>
                        @error('nim_nip')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}"
                               class="form-control @error('email') is-invalid @enderror" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="role" class="form-label">Daftar sebagai</label>
                        <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required>
                            <option value="mahasiswa" @selected(old('role') === 'mahasiswa')>Mahasiswa</option>
                            <option value="dosen" @selected(old('role') === 'dosen')>Dosen</option>
                        </select>
                        @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <input type="password" name="password" id="password" autocomplete="new-password"
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

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Konfirmasi password</label>
                        <div class="input-group">
                            <input type="password" name="password_confirmation" id="password_confirmation" autocomplete="new-password"
                                   class="form-control @error('password_confirmation') is-invalid @enderror" required>
                            <button type="button" class="btn btn-outline-secondary toggle-password" data-target="password_confirmation"
                                    aria-label="Tampilkan password" title="Tampilkan password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Daftar</button>
                </form>

                <p class="text-center mt-3 mb-0 small">
                    Sudah punya akun? <a href="{{ route('login') }}">Login</a>
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