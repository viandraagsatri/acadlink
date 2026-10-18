
@extends('layouts.app')

@section('title', 'Detail Pengajuan')

@section('content')
<div class="mb-4">
    <a href="{{ route('dosen.pengajuan.index') }}"
       class="btn btn-outline-secondary btn-sm mb-3">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>

    <h2 class="fw-bold mb-1">Detail Pengajuan Bimbingan</h2>
    <p class="text-secondary mb-0">
        Periksa pengajuan dan tentukan tindak lanjutnya.
    </p>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3">Informasi Pengajuan</h5>

                <div class="mb-3">
                    <div class="text-secondary small">Nama Mahasiswa</div>
                    <div class="fw-semibold">
                        {{ $pengajuan->mahasiswa->name ?? '-' }}
                    </div>
                </div>

                <div class="mb-3">
                    <div class="text-secondary small">NIM</div>
                    <div>{{ $pengajuan->mahasiswa->nim_nip ?? '-' }}</div>
                </div>

                <div class="mb-3">
                    <div class="text-secondary small">Email</div>
                    <div>{{ $pengajuan->mahasiswa->email ?? '-' }}</div>
                </div>

                <div class="mb-3">
                    <div class="text-secondary small">Jenis Bimbingan</div>
                    <div>{{ $pengajuan->jenis_bimbingan }}</div>
                </div>

                <div class="mb-3">
                    <div class="text-secondary small">Topik Pembahasan</div>
                    <div>{{ $pengajuan->topik_pembahasan }}</div>
                </div>

                <div class="mb-3">
                    <div class="text-secondary small">Diajukan Pada</div>
                    <div>{{ $pengajuan->created_at->format('d/m/Y H:i') }}</div>
                </div>

                <div>
                    <div class="text-secondary small">Status</div>
                    @php
                        $badge = [
                            'pending' => 'warning',
                            'approved' => 'success',
                            'rejected' => 'danger',
                            'completed' => 'primary',
                        ][$pengajuan->status] ?? 'secondary';
                    @endphp
                    <span class="badge text-bg-{{ $badge }}">
                        {{ ucfirst($pengajuan->status) }}
                    </span>
                </div>

                @if ($pengajuan->catatan_dosen)
                    <hr>
                    <div class="text-secondary small">Catatan Dosen</div>
                    <p class="mb-0">{{ $pengajuan->catatan_dosen }}</p>
                @endif
            </div>
        </div>

        @if ($pengajuan->status === 'pending')
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">Tindak Lanjut Pengajuan</h5>

                    <form method="POST"
                          action="{{ route('dosen.pengajuan.approve', $pengajuan->id) }}"
                          class="mb-4">
                        @csrf
                        <button class="btn btn-success w-100"
                                onclick="return confirm('Setujui pengajuan ini?')">
                            <i class="bi bi-check-circle me-1"></i>
                            Setujui Pengajuan
                        </button>
                    </form>

                    <form method="POST"
                          action="{{ route('dosen.pengajuan.reject', $pengajuan->id) }}">
                        @csrf

                        <label class="form-label fw-semibold">
                            Alasan Penolakan
                        </label>
                        <textarea name="catatan_dosen"
                                  class="form-control mb-3"
                                  rows="3"
                                  required
                                  maxlength="2000"
                                  placeholder="Jelaskan alasan penolakan...">{{ old('catatan_dosen') }}</textarea>

                        <button class="btn btn-outline-danger w-100"
                                onclick="return confirm('Tolak pengajuan ini?')">
                            <i class="bi bi-x-circle me-1"></i>
                            Tolak Pengajuan
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>

    <div class="col-lg-5">
        @if ($pengajuan->status === 'approved')
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">Konfirmasi Jadwal</h5>

                    <form method="POST"
                          action="{{ route('dosen.pengajuan.schedule', $pengajuan->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Tanggal dan Waktu</label>
                            <input type="datetime-local"
                                   name="jadwal_bimbingan"
                                   class="form-control"
                                   value="{{ old('jadwal_bimbingan', $pengajuan->jadwal_bimbingan ? \Carbon\Carbon::parse($pengajuan->jadwal_bimbingan)->format('Y-m-d\TH:i') : '') }}"
                                   min="{{ now()->format('Y-m-d\TH:i') }}"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Lokasi atau Link</label>
                            <input type="text"
                                   name="lokasi_atau_link"
                                   class="form-control"
                                   maxlength="255"
                                   value="{{ old('lokasi_atau_link', $pengajuan->lokasi_atau_link) }}"
                                   placeholder="Contoh: Ruang dosen atau link meeting"
                                   required>
                        </div>

                        <button class="btn btn-primary w-100">
                            <i class="bi bi-calendar-check me-1"></i>
                            Simpan Jadwal
                        </button>
                    </form>

                    @if ($pengajuan->jadwal_bimbingan)
                        <hr>
                        <p class="small text-secondary mb-1">Jadwal saat ini</p>
                        <p class="fw-semibold">
                            {{ \Carbon\Carbon::parse($pengajuan->jadwal_bimbingan)->format('d/m/Y H:i') }}
                        </p>
                        <p class="mb-0">
                            {{ $pengajuan->lokasi_atau_link }}
                        </p>

                        <form method="POST"
                              action="{{ route('dosen.pengajuan.complete', $pengajuan->id) }}"
                              class="mt-3">
                            @csrf
                            <button class="btn btn-success w-100"
                                    onclick="return confirm('Tandai bimbingan ini selesai?')">
                                <i class="bi bi-check2-all me-1"></i>
                                Tandai Bimbingan Selesai
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h6 class="fw-bold">Panduan Status</h6>
                <p class="small text-secondary mb-2">
                    <span class="badge text-bg-warning">Pending</span>
                    Menunggu keputusan dosen.
                </p>
                <p class="small text-secondary mb-2">
                    <span class="badge text-bg-success">Approved</span>
                    Disetujui, jadwal dapat diatur.
                </p>
                <p class="small text-secondary mb-2">
                    <span class="badge text-bg-danger">Rejected</span>
                    Pengajuan ditolak.
                </p>
                <p class="small text-secondary mb-0">
                    <span class="badge text-bg-primary">Completed</span>
                    Bimbingan selesai.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
