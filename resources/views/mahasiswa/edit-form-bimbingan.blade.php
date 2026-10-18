@extends('layouts.app')

@section('content')
<div class="container py-5 d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card shadow-sm border-0" style="width: 100%; max-width: 450px; border-radius: 1rem;">
        <div class="card-body p-5">
            
            <div class="text-center mb-4">
                <h3 class="fw-bold mb-3">Edit Pengajuan Bimbingan</h3>
                <div class="mx-auto rounded-circle bg-light d-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                    <i class="bi bi-journal-text" style="font-size: 2rem; color: #4a4a4a;"></i>
                </div>
            </div>

            <!-- form pengajuan -->
            <form action="{{ route('mahasiswa.bimbingan.update', $bimbingan->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="mb-3">
                    <label class="form-label fw-semibold" style="font-size: 0.9rem;">Jenis Bimbingan</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white text-muted border-end-0">
                            <i class="bi bi-card-list"></i>
                        </span>
                        <input type="text" class="form-control border-start-0 ps-0 text-muted" 
                               value="{{ $bimbingan->jenis_bimbingan === 'PA' ? 'Pembimbing Akademik (PA)' : ($bimbingan->jenis_bimbingan === 'skripsi' ? 'Skripsi' : 'Kerja Praktik (KP)') }}" readonly>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold" style="font-size: 0.9rem;">Dosen Pembimbing</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white text-muted border-end-0">
                            <i class="bi bi-person-badge"></i>
                        </span>
                        <input type="text" id="nama_dosen_tampil" class="form-control border-start-0 ps-0 text-muted" 
                               value="{{ $bimbingan->dosen->name ?? 'Belum ada dosen' }}" readonly>
                    </div>
                    <input type="hidden" name="dosen_id" id="id_dosen_hidden" value="{{ $bimbingan->dosen_id }}">
                </div>

                <!-- topik -->
                <div class="mb-4">
                    <label class="form-label fw-semibold" style="font-size: 0.9rem;">Topik Pembahasan</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white text-muted border-end-0">
                            <i class="bi bi-chat-right-text"></i>
                        </span>
                        <input type="text" name="topik_pembahasan" class="form-control border-start-0 ps-0" 
                               placeholder="Contoh: Revisi Bab 1" value="{{ old('topik_pembahasan', $bimbingan->topik_pembahasan) }}" required>
                    </div>
                </div>

                <!-- jadwal bimbingan -->
                <div class="mb-4">
                    <label class="form-label fw-semibold" style="font-size: 0.9rem;">Jadwal Bimbingan (Opsional)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white text-muted border-end-0">
                            <i class="bi bi-calendar-event"></i>
                        </span>
                        <input type="date" name="jadwal_bimbingan" class="form-control border-start-0 ps-0 text-muted" 
                        value="{{ old('jadwal_bimbingan', $bimbingan->jadwal_bimbingan?->format('Y-m-d')) }}">
                    </div>
                    <div class="form-text mt-1 text-muted" style="font-size: 0.8rem;">
                        Kosongkan jika kamu ingin dosen yang menentukan jadwalnya.
                    </div>
                </div>

                <div class="d-flex gap-3 mt-4">
                    <a href="{{ url('/mahasiswa/jadwal') }}" class="btn btn-outline-secondary w-50" style="border-radius: 0.5rem; padding: 10px 0;">
                        Batalkan
                    </a>
                    
                    <button type="submit" class="btn text-white w-50" style="background-color: #8E65B7; border-radius: 0.5rem; padding: 10px 0;">
                        Simpan Perubahan
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<style>
    .input-group-text {
        border-color: #ced4da;
    }
    .form-control:focus, .form-select:focus {
        border-color: #8E65B7;
        box-shadow: none;
    }
    .input-group:focus-within .input-group-text,
    .input-group:focus-within .form-control,
    .input-group:focus-within .form-select {
        border-color: #8E65B7;
    }
    .form-control[readonly] {
        background-color: #f8f9fa;
        cursor: not-allowed;
    }
</style>

<script>
    //ambil data dosen dari db then simpan ke variabel js
    const dataDosen = {
        PA: {
            id: "{{ auth()->user()->dosen_pa_id }}",
            nama: "{{ auth()->user()->dosenPA->name ?? 'Belum ada Dosen PA' }}"
        },
        skripsi: {
            id: "{{ auth()->user()->dosen_skripsi_id }}",
            nama: "{{ auth()->user()->dosenSkripsi->name ?? 'Belum ada Dosen Skripsi' }}"
        },
        KP: {
            id: "{{ auth()->user()->dosen_kp_id }}",
            nama: "{{ auth()->user()->dosenKP->name ?? 'Belum ada Dosen KP' }}"
        }
    };

    //tangkap elemen html-nya
    const selectJenis = document.getElementById('jenis_bimbingan_select');
    const inputNamaDosen = document.getElementById('nama_dosen_tampil');
    const inputIdDosen = document.getElementById('id_dosen_hidden');

    //buat perintah: "setiap kali dropdown berubah, jalankan fungsi ini"
    selectJenis.addEventListener('change', function() {
        const pilihan = this.value;
        
        //cek apakah mhs punya dosen di jenis tersebut (idnya ga kosong)
        if (dataDosen[pilihan].id) {
            inputNamaDosen.value = dataDosen[pilihan].nama;
            inputIdDosen.value = dataDosen[pilihan].id;
        } else {
            inputNamaDosen.value = "Dosen belum ditentukan oleh admin";
            inputIdDosen.value = "";
        }
    });
</script>
@endsection