<?php

namespace Database\Seeders;

use App\Models\ArahanRevisi;
use App\Models\JadwalKonsultasi;
use App\Models\PertemuanBimbingan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $dosen1 = User::create([
            'name' => 'Dr. Budi Rahardjo, M.Kom',
            'nim_nip' => '197801012005011001',
            'email' => 'dosen1@acadlink.test',
            'role' => 'dosen',
            'password' => Hash::make('password'),
        ]);

        $dosen2 = User::create([
            'name' => 'Dr. Siti Aminah, M.T',
            'nim_nip' => '198203152008122002',
            'email' => 'dosen2@acadlink.test',
            'role' => 'dosen',
            'password' => Hash::make('password'),
        ]);

        $mhs1 = User::create([
            'name' => 'Ahmad Fauzi',
            'nim_nip' => '2200018001',
            'email' => 'mahasiswa1@acadlink.test',
            'role' => 'mahasiswa',
            'password' => Hash::make('password'),
            'dosen_pa_id' => $dosen2->id,
            'dosen_skripsi_id' => $dosen1->id,
            'dosen_kp_id' => $dosen1->id,
        ]);

        $mhs2 = User::create([
            'name' => 'Putri Lestari',
            'nim_nip' => '2200018002',
            'email' => 'mahasiswa2@acadlink.test',
            'role' => 'mahasiswa',
            'password' => Hash::make('password'),
            'dosen_pa_id' => $dosen1->id,
            'dosen_skripsi_id' => $dosen2->id,
            'dosen_kp_id' => $dosen2->id,
        ]);

        $mhs3 = User::create([
            'name' => 'Annisa Azzahra',
            'nim_nip' => '2417051059',
            'email' => 'mahasiswa3@acadlink.test',
            'role' => 'mahasiswa',
            'password' => Hash::make('password'),
            'dosen_pa_id' => $dosen1->id,
            'dosen_skripsi_id' => $dosen1->id,
            'dosen_kp_id' => $dosen2->id,
        ]);

        $jadwal1 = JadwalKonsultasi::create([
            'dosen_id' => $dosen1->id,
            'jenis_bimbingan' => 'Skripsi',
            'hari' => Carbon::now()->isoFormat('dddd'),
            'tanggal' => Carbon::now()->toDateString(),
            'jam_mulai' => '09:00:00',
            'jam_selesai' => '11:30:00',
            'metode' => 'tatap_muka',
            'ruangan_atau_link' => 'Ruang Dosen 302 Gedung A',
            'kuota' => 5,
            'keterangan' => 'Konsultasi tatap muka khusus pembahasan bab skripsi dan metodologi.',
            'status' => 'aktif',
        ]);

        $jadwal2 = JadwalKonsultasi::create([
            'dosen_id' => $dosen1->id,
            'jenis_bimbingan' => 'Kerja Praktik',
            'hari' => Carbon::now()->addDays(2)->isoFormat('dddd'),
            'tanggal' => Carbon::now()->addDays(2)->toDateString(),
            'jam_mulai' => '13:30:00',
            'jam_selesai' => '15:30:00',
            'metode' => 'online',
            'ruangan_atau_link' => 'https://meet.google.com/acad-budi-meet',
            'kuota' => 4,
            'keterangan' => 'Sesi review draft laporan KP dan pengesahan mingguan via GMeet.',
            'status' => 'aktif',
        ]);

        $jadwal3 = JadwalKonsultasi::create([
            'dosen_id' => $dosen2->id,
            'jenis_bimbingan' => 'Akademik',
            'hari' => Carbon::now()->addDays(3)->isoFormat('dddd'),
            'tanggal' => Carbon::now()->addDays(3)->toDateString(),
            'jam_mulai' => '10:00:00',
            'jam_selesai' => '12:00:00',
            'metode' => 'keduanya',
            'ruangan_atau_link' => 'Ruang Prodi 104 / Zoom ID 889210291',
            'kuota' => 6,
            'keterangan' => 'Konsultasi bimbingan akademik pra-KRS dan evaluasi indeks prestasi.',
            'status' => 'aktif',
        ]);

        $pertemuanSelesai = PertemuanBimbingan::create([
            'dosen_id' => $dosen1->id,
            'mahasiswa_id' => $mhs1->id,
            'jadwal_konsultasi_id' => $jadwal1->id,
            'jenis_bimbingan' => 'skripsi',
            'judul_topik' => 'Revisi Bab 1: Perumusan Masalah dan Batasan Sistem',
            'deskripsi_singkat' => 'Telah memperbaiki latar belakang, namun butuh validasi dosen untuk rumusan masalah nomor 2 dan 3.',
            'metode' => 'tatap_muka',
            'tanggal' => Carbon::now()->subDays(7)->toDateString(),
            'jam_mulai' => '09:00:00',
            'jam_selesai' => '10:00:00',
            'tempat' => 'Ruang Dosen 302 Gedung A',
            'status' => 'selesai',
            'catatan_hasil' => 'Rumusan masalah sudah lebih tajam. Perlu penambahan 3 referensi jurnal terindeks SINTA 2 terkait perbandingan algoritma. Siapkan draft Bab 2 untuk pertemuan berikutnya.',
            'tanggal_bimbingan_selesai' => Carbon::now()->subDays(7)->setTime(10, 15),
        ]);

        ArahanRevisi::create([
            'pertemuan_bimbingan_id' => $pertemuanSelesai->id,
            'isi_revisi' => 'Perbaiki rumusan masalah nomor 2 agar lebih spesifik pada target akurasi klasifikasi.',
            'tenggat_waktu' => Carbon::now()->subDays(2)->toDateString(),
            'prioritas' => 'tinggi',
            'status' => 'selesai',
            'catatan_mahasiswa' => 'Sudah disesuaikan di naskah halaman 8 paragraf 3.',
        ]);

        ArahanRevisi::create([
            'pertemuan_bimbingan_id' => $pertemuanSelesai->id,
            'isi_revisi' => 'Tambahkan minimal 3 studi literatur jurnal internasional atau SINTA 2 terbitan 5 tahun terakhir.',
            'tenggat_waktu' => Carbon::now()->addDays(3)->toDateString(),
            'prioritas' => 'sedang',
            'status' => 'sedang_dikerjakan',
            'catatan_mahasiswa' => 'Sedang mengumpulkan 2 referensi tambahan dari IEEE dan Springer.',
        ]);

        $pertemuanAktif = PertemuanBimbingan::create([
            'dosen_id' => $dosen1->id,
            'mahasiswa_id' => $mhs1->id,
            'jadwal_konsultasi_id' => $jadwal1->id,
            'jenis_bimbingan' => 'skripsi',
            'judul_topik' => 'Pemeriksaan Hasil Revisi Bab 1 & Draft Awal Bab 2',
            'deskripsi_singkat' => 'Melanjutkan konsultasi sebelumnya untuk cek revisi rumusan masalah dan mulai membahas bab tinjauan pustaka.',
            'metode' => 'tatap_muka',
            'tanggal' => Carbon::now()->toDateString(),
            'jam_mulai' => '10:00:00',
            'jam_selesai' => '11:00:00',
            'tempat' => 'Ruang Dosen 302 Gedung A',
            'status' => 'disetujui',
            'catatan_hasil' => null,
            'tanggal_bimbingan_selesai' => null,
        ]);

        PertemuanBimbingan::create([
            'dosen_id' => $dosen1->id,
            'mahasiswa_id' => $mhs2->id,
            'jadwal_konsultasi_id' => $jadwal2->id,
            'jenis_bimbingan' => 'kerja_praktik',
            'judul_topik' => 'Konsultasi Laporan Mingguan Kerja Praktik Minggu ke-4',
            'deskripsi_singkat' => 'Pengesahan kegiatan magang dan review dokumen activity log.',
            'metode' => 'online',
            'tanggal' => Carbon::now()->addDays(2)->toDateString(),
            'jam_mulai' => '13:30:00',
            'jam_selesai' => '14:30:00',
            'tempat' => 'https://meet.google.com/acad-budi-meet',
            'status' => 'menunggu',
            'catatan_hasil' => null,
            'tanggal_bimbingan_selesai' => null,
        ]);
    }
}
