@extends('layouts.app')

@section('title', 'Detail Pertemuan Bimbingan')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div class="d-flex align-items-center">
        <a href="{{ route('dosen.pertemuan.index') }}" class="btn btn-outline-secondary btn-sm me-3">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
        <div>
            <h1 class="h3 fw-bold mb-0">Detail Pertemuan Bimbingan</h1>
            <span class="text-muted small">{{ $pertemuan->jenis_bimbingan_label }} &bull; {{ $pertemuan->tanggal->format('d F Y') }}</span>
        </div>
    </div>
    <div>
        <span class="badge {{ $pertemuan->status_badge_class }} fs-6 px-3 py-2">
            {{ $pertemuan->status_label }}
        </span>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header py-3">
                <h5 class="card-title fs-6 fw-bold mb-0">
                    <i class="bi bi-person-circle me-1 text-primary"></i> Data Mahasiswa
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="text-muted small">Nama Mahasiswa</div>
                    <div class="fw-bold">{{ $pertemuan->mahasiswa->name }}</div>
                </div>
                <div class="mb-3">
                    <div class="text-muted small">Nomor Induk Mahasiswa (NIM)</div>
                    <div class="fw-semibold">{{ $pertemuan->mahasiswa->nim_nip }}</div>
                </div>
                <div class="mb-0">
                    <div class="text-muted small">Email Mahasiswa</div>
                    <div class="small">{{ $pertemuan->mahasiswa->email }}</div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header py-3">
                <h5 class="card-title fs-6 fw-bold mb-0">
                    <i class="bi bi-calendar-event me-1 text-primary"></i> Info Pertemuan
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="text-muted small">Jenis Bimbingan</div>
                    <span class="badge badge-lilac">{{ $pertemuan->jenis_bimbingan_label }}</span>
                </div>
                <div class="mb-3">
                    <div class="text-muted small">Metode Konsultasi</div>
                    <div class="fw-semibold">{{ $pertemuan->metode_label }}</div>
                </div>
                <div class="mb-3">
                    <div class="text-muted small">Waktu Pelaksanaan</div>
                    <div>
                        <i class="bi bi-calendar3 me-1"></i> {{ $pertemuan->tanggal->format('d M Y') }}
                    </div>
                    @if ($pertemuan->jam_mulai_format)
                        <div class="small text-muted">
                            <i class="bi bi-clock me-1"></i> {{ $pertemuan->jam_mulai_format }} @if($pertemuan->jam_selesai_format) - {{ $pertemuan->jam_selesai_format }} @endif WIB
                        </div>
                    @endif
                </div>
                <div class="mb-3">
                    <div class="text-muted small">Lokasi / Ruangan / Link Pertemuan</div>
                    <div class="fw-semibold">
                        @if ($pertemuan->tempat)
                            @if (str_starts_with($pertemuan->tempat, 'http'))
                                <a href="{{ $pertemuan->tempat }}" target="_blank" class="text-primary">
                                    <i class="bi bi-box-arrow-up-right me-1"></i> Buka Tautan Online
                                </a>
                            @else
                                <i class="bi bi-geo-alt me-1"></i> {{ $pertemuan->tempat }}
                            @endif
                        @else
                            <span class="text-muted fst-italic">Belum ditentukan</span>
                        @endif
                    </div>
                </div>
                @if ($pertemuan->tanggal_bimbingan_selesai)
                    <div class="mb-0">
                        <div class="text-muted small">Diselesaikan Pada</div>
                        <div class="small text-success fw-semibold">
                            <i class="bi bi-check-circle me-1"></i> {{ $pertemuan->tanggal_bimbingan_selesai->format('d M Y H:i') }} WIB
                        </div>
                    </div>
                @endif
            </div>
        </div>

        @if ($pertemuan->status === 'ditolak' && $pertemuan->alasan_penolakan)
            <div class="card border-danger shadow-sm mb-4">
                <div class="card-body">
                    <div class="text-danger fw-bold small mb-1">
                        <i class="bi bi-x-circle me-1"></i> Alasan Penolakan:
                    </div>
                    <p class="small mb-0 text-muted">{{ $pertemuan->alasan_penolakan }}</p>
                </div>
            </div>
        @endif
    </div>

    <div class="col-lg-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header py-3">
                <h5 class="card-title fs-6 fw-bold mb-0">
                    <i class="bi bi-journal-text me-1 text-primary"></i> Topik & Materi Pengajuan
                </h5>
            </div>
            <div class="card-body">
                <h6 class="fw-bold mb-2">{{ $pertemuan->judul_topik }}</h6>
                <p class="text-muted mb-0">
                    {{ $pertemuan->deskripsi_singkat ?: 'Tidak ada uraian pengantar tambahan dari mahasiswa.' }}
                </p>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title fs-6 fw-bold mb-0">
                    <i class="bi bi-pencil-square me-1 text-primary"></i> Catatan Hasil Pembahasan Pertemuan
                </h5>
                @if ($pertemuan->status === 'selesai')
                    <span class="badge bg-success small">Telah Diselesaikan</span>
                @endif
            </div>
            <div class="card-body">
                <form action="{{ route('dosen.pertemuan.catatan', $pertemuan->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Catatan / Ringkasan Diskusi Bimbingan <span class="text-danger">*</span></label>
                        <textarea name="catatan_hasil" rows="5" class="form-control @error('catatan_hasil') is-invalid @enderror" placeholder="Tuliskan poin pembahasan, temuan masalah, masukan dosen, dan saran solusi hasil bimbingan..." required>{{ old('catatan_hasil', $pertemuan->catatan_hasil) }}</textarea>
                        @error('catatan_hasil')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="tandai_selesai" value="1" id="tandaiSelesai" {{ $pertemuan->status === 'selesai' ? 'checked' : '' }}>
                            <label class="form-check-label small fw-semibold" for="tandaiSelesai">
                                Tandai sesi pertemuan bimbingan ini selesai
                            </label>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm px-3">
                            <i class="bi bi-save me-1"></i> Simpan Catatan Hasil
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title fs-6 fw-bold mb-0">
                        <i class="bi bi-list-check me-1 text-primary"></i> Daftar Arahan & Revisi Mahasiswa
                    </h5>
                    <small class="text-muted">Butir perbaikan yang wajib dikerjakan oleh mahasiswa</small>
                </div>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahRevisi">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Arahan Revisi
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3" style="width: 45%;">Poin Arahan / Revisi</th>
                                <th>Prioritas</th>
                                <th>Batas Waktu</th>
                                <th>Status Pengerjaan</th>
                                <th class="text-end pe-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pertemuan->arahanRevisis as $revisi)
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-semibold text-wrap">{{ $revisi->isi_revisi }}</div>
                                        @if ($revisi->catatan_mahasiswa)
                                            <div class="mt-1 small bg-light p-2 rounded border">
                                                <i class="bi bi-chat-quote me-1 text-muted"></i>
                                                <span class="text-muted">Respon Mahasiswa:</span> {{ $revisi->catatan_mahasiswa }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $revisi->prioritas_badge_class }}">
                                            {{ $revisi->prioritas_label }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="small">
                                            @if ($revisi->tenggat_waktu)
                                                <i class="bi bi-calendar-event me-1"></i> {{ $revisi->tenggat_waktu->format('d M Y') }}
                                            @else
                                                <span class="text-muted">Tidak ditentukan</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $revisi->status_badge_class }}">
                                            {{ $revisi->status_label }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalEditRevisi{{ $revisi->id }}" title="Ubah Revisi">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalHapusRevisi{{ $revisi->id }}" title="Hapus Revisi">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>

                                        <div class="modal fade text-start" id="modalEditRevisi{{ $revisi->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <form action="{{ route('dosen.pertemuan.revisi.update', $revisi->id) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-header">
                                                            <h5 class="modal-title fs-6 fw-bold">Ubah Arahan Revisi</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label small fw-semibold">Uraian Revisi <span class="text-danger">*</span></label>
                                                                <textarea name="isi_revisi" rows="3" class="form-control form-control-sm" required>{{ old('isi_revisi', $revisi->isi_revisi) }}</textarea>
                                                            </div>
                                                            <div class="row g-2 mb-3">
                                                                <div class="col-6">
                                                                    <label class="form-label small fw-semibold">Prioritas <span class="text-danger">*</span></label>
                                                                    <select name="prioritas" class="form-select form-select-sm" required>
                                                                        <option value="tinggi" {{ $revisi->prioritas === 'tinggi' ? 'selected' : '' }}>Tinggi</option>
                                                                        <option value="sedang" {{ $revisi->prioritas === 'sedang' ? 'selected' : '' }}>Sedang</option>
                                                                        <option value="rendah" {{ $revisi->prioritas === 'rendah' ? 'selected' : '' }}>Rendah</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-6">
                                                                    <label class="form-label small fw-semibold">Tenggat Waktu</label>
                                                                    <input type="date" name="tenggat_waktu" value="{{ $revisi->tenggat_waktu ? $revisi->tenggat_waktu->format('Y-m-d') : '' }}" class="form-control form-control-sm">
                                                                </div>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label small fw-semibold">Status Pengerjaan <span class="text-danger">*</span></label>
                                                                <select name="status" class="form-select form-select-sm" required>
                                                                    <option value="belum_selesai" {{ $revisi->status === 'belum_selesai' ? 'selected' : '' }}>Belum Dikerjakan</option>
                                                                    <option value="sedang_dikerjakan" {{ $revisi->status === 'sedang_dikerjakan' ? 'selected' : '' }}>Sedang Dikerjakan</option>
                                                                    <option value="selesai" {{ $revisi->status === 'selesai' ? 'selected' : '' }}>Selesai Dikerjakan</option>
                                                                    <option value="perlu_perbaikan" {{ $revisi->status === 'perlu_perbaikan' ? 'selected' : '' }}>Perlu Perbaikan Ulang</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-primary btn-sm">Perbarui Revisi</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal fade text-start" id="modalHapusRevisi{{ $revisi->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title fs-6 fw-bold">Hapus Arahan Revisi</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p class="mb-0">Hapus poin arahan revisi ini?</p>
                                                        <div class="text-muted small mt-2 fst-italic">"{{ $revisi->isi_revisi }}"</div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                                        <form action="{{ route('dosen.pertemuan.revisi.destroy', $revisi->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-sm">Ya, Hapus</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="bi bi-clipboard-check fs-2 d-block mb-1"></i>
                                        Belum ada daftar arahan atau revisi yang dicatat untuk pertemuan ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header py-3">
                <h5 class="card-title fs-6 fw-bold mb-0">
                    <i class="bi bi-clock-history me-1 text-primary"></i> Riwayat Pertemuan Terdahulu Mahasiswa Ini
                </h5>
                <small class="text-muted">Periksa catatan dan progres revisi dari sesi-sesi sebelumnya</small>
            </div>
            <div class="card-body">
                @forelse ($riwayatSebelumnya as $prev)
                    <div class="border rounded p-3 mb-3 bg-white">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="badge badge-lilac me-1">{{ $prev->jenis_bimbingan_label }}</span>
                                <span class="badge {{ $prev->status_badge_class }}">{{ $prev->status_label }}</span>
                                <h6 class="fw-bold mt-2 mb-1">{{ $prev->judul_topik }}</h6>
                                <div class="text-muted small">
                                    <i class="bi bi-calendar3 me-1"></i> {{ $prev->tanggal->format('d M Y') }} &bull; Metode: {{ $prev->metode_label }}
                                </div>
                            </div>
                            <a href="{{ route('dosen.pertemuan.show', $prev->id) }}" class="btn btn-outline-primary btn-sm">
                                Buka Sesi Ini
                            </a>
                        </div>
                        @if ($prev->catatan_hasil)
                            <div class="bg-light p-2 rounded small mb-2">
                                <strong>Catatan Dosen Sesi Ini:</strong>
                                <div>{{ $prev->catatan_hasil }}</div>
                            </div>
                        @endif
                        @if ($prev->arahanRevisis->count())
                            <div class="small">
                                <span class="text-muted fw-semibold">Status Revisi Sesi Terdahulu:</span>
                                <div class="mt-1">
                                    @foreach ($prev->arahanRevisis as $prevRev)
                                        <div class="d-flex align-items-center justify-content-between py-1 border-bottom">
                                            <div class="text-truncate me-2" style="max-width: 80%;">
                                                <i class="bi bi-check2-square text-muted me-1"></i> {{ $prevRev->isi_revisi }}
                                            </div>
                                            <span class="badge {{ $prevRev->status_badge_class }}">{{ $prevRev->status_label }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-info-circle fs-3 d-block mb-1"></i>
                        Ini merupakan pertemuan bimbingan pertama mahasiswa ini pada sistem.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambahRevisi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('dosen.pertemuan.revisi.store', $pertemuan->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fs-6 fw-bold">Tambah Arahan Revisi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Uraian Arahan / Revisi <span class="text-danger">*</span></label>
                        <textarea name="isi_revisi" rows="3" class="form-control form-control-sm" placeholder="Contoh: Perbaiki rumusan masalah nomor 2 dan tambahkan 3 referensi jurnal terindeks..." required>{{ old('isi_revisi') }}</textarea>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Tingkat Prioritas <span class="text-danger">*</span></label>
                            <select name="prioritas" class="form-select form-select-sm" required>
                                <option value="sedang">Sedang</option>
                                <option value="tinggi">Tinggi (Mendesak)</option>
                                <option value="rendah">Rendah</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Tenggat Waktu</label>
                            <input type="date" name="tenggat_waktu" value="{{ old('tenggat_waktu') }}" class="form-control form-control-sm">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm">Tambahkan Revisi</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
