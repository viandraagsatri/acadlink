@php
    $terlambat = $revisi->status !== 'selesai'
        && $revisi->tenggat_waktu
        && $revisi->tenggat_waktu->lt(today());
    // status "perlu perbaikan" hanya bisa dipasang dosen, jadi pilihan awal di form dibuat "sedang dikerjakan"
    $statusAwal = $revisi->status === 'perlu_perbaikan' ? 'sedang_dikerjakan' : $revisi->status;
    $pertemuan = $revisi->pertemuanBimbingan;
@endphp

<div class="card shadow-sm mb-3 {{ $revisi->status === 'perlu_perbaikan' ? 'border-danger' : '' }}">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
            <div class="fw-semibold">{{ $revisi->isi_revisi }}</div>
            <span class="badge {{ $revisi->status_badge_class }} flex-shrink-0">{{ $revisi->status_label }}</span>
        </div>

        <div class="small text-muted mb-2">
            <span class="badge {{ $revisi->prioritas_badge_class }} me-2">{{ $revisi->prioritas_label }}</span>
            @if ($revisi->tenggat_waktu)
                <i class="bi bi-calendar-event me-1"></i> Tenggat {{ $revisi->tenggat_waktu->format('d M Y') }}
                @if ($terlambat)
                    <span class="badge bg-danger ms-1">Terlambat</span>
                @endif
            @else
                <span class="fst-italic">Tanpa tenggat</span>
            @endif
        </div>

        <div class="small text-muted">
            Dari bimbingan:
            <a href="{{ route('mahasiswa.riwayat.show', $pertemuan->id) }}">{{ $pertemuan->judul_topik }}</a>
            &bull; {{ $pertemuan->tanggal->format('d M Y') }} &bull; {{ optional($pertemuan->dosen)->name }}
        </div>

        @if ($pertemuan->catatan_hasil)
            <div class="mt-2">
                <button type="button" class="btn btn-link btn-sm p-0" data-bs-toggle="collapse" data-bs-target="#catatan{{ $revisi->id }}">
                    <i class="bi bi-journal-text me-1"></i> Lihat catatan pembahasan
                </button>
                <div class="collapse mt-2" id="catatan{{ $revisi->id }}">
                    <div class="small p-2 bg-light rounded border" style="white-space: pre-line;">{{ $pertemuan->catatan_hasil }}</div>
                </div>
            </div>
        @endif

        @if ($revisi->status === 'perlu_perbaikan')
            <div class="alert alert-danger small py-2 mt-3 mb-0">
                <i class="bi bi-exclamation-triangle me-1"></i> Dosen meminta perbaikan ulang untuk poin ini.
            </div>
        @endif

        @if ($revisi->catatan_mahasiswa || $revisi->file_progres)
            <div class="small mt-3 p-2 bg-light rounded border">
                @if ($revisi->catatan_mahasiswa)
                    <div><span class="text-muted">Catatanmu:</span> {{ $revisi->catatan_mahasiswa }}</div>
                @endif
                @if ($revisi->file_progres)
                    <div class="mt-1">
                        <i class="bi bi-paperclip me-1"></i>
                        <a href="{{ asset('storage/'.$revisi->file_progres) }}" target="_blank" rel="noopener noreferrer">File progres</a>
                    </div>
                @endif
            </div>
        @endif

        @if ($revisi->status === 'selesai')
            <div class="small text-muted mt-3">
                <i class="bi bi-lock-fill me-1"></i> Progres sudah selesai dan tidak dapat diperbarui.
            </div>
        @else
            <div class="mt-3">
                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalProgres{{ $revisi->id }}">
                    <i class="bi bi-pencil-square me-1"></i> Perbarui Progres
                </button>
            </div>
        @endif
    </div>
</div>

@if ($revisi->status !== 'selesai')
    <div class="modal fade" id="modalProgres{{ $revisi->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('mahasiswa.revisi.update', $revisi->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fs-6 fw-bold">Perbarui Progres Revisi</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="small text-muted fst-italic">"{{ $revisi->isi_revisi }}"</p>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold" for="status{{ $revisi->id }}">Status Pengerjaan</label>
                            <select name="status" id="status{{ $revisi->id }}" class="form-select form-select-sm" required>
                                <option value="belum_selesai" @selected($statusAwal === 'belum_selesai')>Belum Dikerjakan</option>
                                <option value="sedang_dikerjakan" @selected($statusAwal === 'sedang_dikerjakan')>Sedang Dikerjakan</option>
                                <option value="selesai" @selected($statusAwal === 'selesai')>Selesai Dikerjakan</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold" for="catatan_mahasiswa{{ $revisi->id }}">Catatan untuk Dosen</label>
                            <textarea name="catatan_mahasiswa" id="catatan_mahasiswa{{ $revisi->id }}" rows="3" maxlength="1000" class="form-control form-control-sm" placeholder="Contoh: Sudah diperbaiki di halaman 8 paragraf 3. (wajib diisi saat menandai selesai)">{{ $revisi->catatan_mahasiswa }}</textarea>
                        </div>

                        <div class="mb-0">
                            <label class="form-label small fw-semibold" for="file_progres{{ $revisi->id }}">File Progres (opsional)</label>
                            <input type="file" name="file_progres" id="file_progres{{ $revisi->id }}" class="form-control form-control-sm" accept=".pdf,.docx,.zip">
                            <div class="form-text">PDF, DOCX, atau ZIP. Maksimal 5 MB.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm">Simpan Progres</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
