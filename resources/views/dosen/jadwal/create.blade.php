@extends('layouts.app')

@section('title', 'Tambah Jadwal Konsultasi')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex align-items-center mb-3">
            <a href="{{ route('dosen.jadwal.index') }}" class="btn btn-outline-secondary btn-sm me-3">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <div>
                <h1 class="h3 fw-bold mb-0">Tambah Jadwal Konsultasi</h1>
                <small class="text-muted">Buat slot waktu baru untuk bimbingan mahasiswa</small>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <div class="alert alert-info d-flex align-items-center mb-4">
                    <i class="bi bi-shield-check me-2 fs-5"></i>
                    <div class="small">Sistem AcadLink akan secara otomatis memeriksa dan mencegah jadwal bentrok jika Anda telah memiliki jadwal lain pada tanggal dan rentang waktu yang sama.</div>
                </div>

                <form method="POST" action="{{ route('dosen.jadwal.store') }}">
                    @csrf

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Jenis Bimbingan <span class="text-danger">*</span></label>
                            <select name="jenis_bimbingan" class="form-select @error('jenis_bimbingan') is-invalid @enderror" required>
                                <option value="Semua" {{ old('jenis_bimbingan') === 'Semua' ? 'selected' : '' }}>Semua Jenis (Terbuka Umum)</option>
                                <option value="Skripsi" {{ old('jenis_bimbingan') === 'Skripsi' ? 'selected' : '' }}>Khusus Bimbingan Skripsi</option>
                                <option value="Kerja Praktik" {{ old('jenis_bimbingan') === 'Kerja Praktik' ? 'selected' : '' }}>Khusus Bimbingan Kerja Praktik</option>
                                <option value="Akademik" {{ old('jenis_bimbingan') === 'Akademik' ? 'selected' : '' }}>Khusus Bimbingan Akademik (PA)</option>
                            </select>
                            @error('jenis_bimbingan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tanggal Konsultasi <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" class="form-control @error('tanggal') is-invalid @enderror" required>
                            @error('tanggal')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Jam Mulai <span class="text-danger">*</span></label>
                            <input type="time" name="jam_mulai" value="{{ old('jam_mulai', '09:00') }}" class="form-control @error('jam_mulai') is-invalid @enderror" required>
                            @error('jam_mulai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Jam Selesai <span class="text-danger">*</span></label>
                            <input type="time" name="jam_selesai" value="{{ old('jam_selesai', '11:00') }}" class="form-control @error('jam_selesai') is-invalid @enderror" required>
                            @error('jam_selesai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Metode Bimbingan <span class="text-danger">*</span></label>
                            <select name="metode" class="form-select @error('metode') is-invalid @enderror" required>
                                <option value="tatap_muka" {{ old('metode') === 'tatap_muka' ? 'selected' : '' }}>Tatap Muka Langsung (Offline)</option>
                                <option value="online" {{ old('metode') === 'online' ? 'selected' : '' }}>Online (Google Meet / Zoom)</option>
                                <option value="keduanya" {{ old('metode') === 'keduanya' ? 'selected' : '' }}>Fleksibel (Tatap Muka / Online)</option>
                            </select>
                            @error('metode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Kuota Mahasiswa <span class="text-danger">*</span></label>
                            <input type="number" name="kuota" value="{{ old('kuota', 5) }}" min="1" max="50" class="form-control @error('kuota') is-invalid @enderror" required>
                            @error('kuota')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Ruangan / Link Pertemuan <span class="text-danger">*</span></label>
                        <input type="text" name="ruangan_atau_link" value="{{ old('ruangan_atau_link') }}" placeholder="Contoh: Ruang Dosen 302 Gedung A / https://meet.google.com/xyz-abcd-efg" class="form-control @error('ruangan_atau_link') is-invalid @enderror" required>
                        @error('ruangan_atau_link')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Catatan / Keterangan Tambahan</label>
                        <textarea name="keterangan" rows="3" placeholder="Informasi tambahan untuk mahasiswa (opsional)..." class="form-control @error('keterangan') is-invalid @enderror">{{ old('keterangan') }}</textarea>
                        @error('keterangan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('dosen.jadwal.index') }}" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check-lg me-1"></i> Simpan Jadwal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
