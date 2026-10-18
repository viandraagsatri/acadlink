<?php

namespace Tests\Feature;

use App\Models\Bimbingan;
use App\Models\JadwalKonsultasi;
use App\Models\PertemuanBimbingan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PengajuanBimbinganTest extends TestCase
{
    use RefreshDatabase;

    public function test_dosen_can_view_pengajuan_index(): void
    {
        $dosen = User::factory()->create(['role' => 'dosen']);

        $response = $this->actingAs($dosen)->get(route('dosen.pengajuan.index'));

        $response->assertStatus(200);
        $response->assertSee('Pengajuan Bimbingan');
    }

    public function test_approved_pengajuan_with_schedule_syncs_to_pertemuan_and_jadwal(): void
    {
        $dosen = User::factory()->create(['role' => 'dosen']);
        $mahasiswa = User::factory()->create(['role' => 'mahasiswa']);

        $jadwalWaktu = Carbon::tomorrow()->setHour(10)->setMinute(0)->setSecond(0);

        $pengajuan = Bimbingan::create([
            'dosen_id' => $dosen->id,
            'mahasiswa_id' => $mahasiswa->id,
            'jenis_bimbingan' => 'skripsi',
            'topik_pembahasan' => 'Bimbingan Bab 1 Skripsi',
            'jadwal_bimbingan' => $jadwalWaktu,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($dosen)->post(route('dosen.pengajuan.approve', $pengajuan->id));

        $response->assertRedirect(route('dosen.pengajuan.show', $pengajuan->id));
        $this->assertEquals('approved', $pengajuan->fresh()->status);

        // Harus masuk ke pertemuan bimbingan
        $this->assertDatabaseHas('pertemuan_bimbingans', [
            'bimbingan_id' => $pengajuan->id,
            'dosen_id' => $dosen->id,
            'mahasiswa_id' => $mahasiswa->id,
            'status' => 'disetujui',
        ]);
        $pertemuan = PertemuanBimbingan::where('bimbingan_id', $pengajuan->id)->first();
        $this->assertNotNull($pertemuan);
        $this->assertEquals($jadwalWaktu->toDateString(), $pertemuan->tanggal->toDateString());

        // Harus masuk ke jadwal konsultasi
        $this->assertDatabaseHas('jadwal_konsultasis', [
            'dosen_id' => $dosen->id,
            'jam_mulai' => $jadwalWaktu->format('H:i:s'),
            'status' => 'aktif',
        ]);
        $jadwal = JadwalKonsultasi::where('dosen_id', $dosen->id)->first();
        $this->assertNotNull($jadwal);
        $this->assertEquals($jadwalWaktu->toDateString(), $jadwal->tanggal->toDateString());

        // Harus muncul di halaman pertemuan & bimbingan dosen
        $pertemuanResponse = $this->actingAs($dosen)->get(route('dosen.pertemuan.index'));
        $pertemuanResponse->assertStatus(200);
        $pertemuanResponse->assertSee($mahasiswa->name);
        $pertemuanResponse->assertSee('Bimbingan Bab 1 Skripsi');
    }

    public function test_dosen_can_schedule_pengajuan_and_it_enters_pertemuan_and_jadwal(): void
    {
        $dosen = User::factory()->create(['role' => 'dosen']);
        $mahasiswa = User::factory()->create(['role' => 'mahasiswa']);

        $pengajuan = Bimbingan::create([
            'dosen_id' => $dosen->id,
            'mahasiswa_id' => $mahasiswa->id,
            'jenis_bimbingan' => 'skripsi',
            'topik_pembahasan' => 'Diskusi Bab 2',
            'status' => 'approved',
        ]);

        $jadwalWaktu = Carbon::tomorrow()->setHour(14)->setMinute(30)->setSecond(0);

        $response = $this->actingAs($dosen)->put(route('dosen.pengajuan.schedule', $pengajuan->id), [
            'jadwal_bimbingan' => $jadwalWaktu->format('Y-m-d\TH:i'),
            'lokasi_atau_link' => 'Ruang Dosen 301',
        ]);

        $response->assertRedirect(route('dosen.pengajuan.show', $pengajuan->id));

        $this->assertDatabaseHas('pertemuan_bimbingans', [
            'bimbingan_id' => $pengajuan->id,
            'dosen_id' => $dosen->id,
            'mahasiswa_id' => $mahasiswa->id,
            'tempat' => 'Ruang Dosen 301',
            'status' => 'disetujui',
        ]);

        $this->assertDatabaseHas('jadwal_konsultasis', [
            'dosen_id' => $dosen->id,
            'jam_mulai' => '14:30:00',
            'status' => 'aktif',
        ]);
        $jadwal = JadwalKonsultasi::where('dosen_id', $dosen->id)->first();
        $this->assertNotNull($jadwal);
        $this->assertEquals($jadwalWaktu->toDateString(), $jadwal->tanggal->toDateString());
    }

    public function test_pertemuan_index_automatically_syncs_existing_approved_pengajuan(): void
    {
        $dosen = User::factory()->create(['role' => 'dosen']);
        $mahasiswa = User::factory()->create(['role' => 'mahasiswa']);

        $jadwalWaktu = Carbon::tomorrow()->setHour(9)->setMinute(0)->setSecond(0);

        // Simulasi bimbingan yang sudah approved dan memiliki jadwal di database
        $pengajuan = Bimbingan::create([
            'dosen_id' => $dosen->id,
            'mahasiswa_id' => $mahasiswa->id,
            'jenis_bimbingan' => 'skripsi',
            'topik_pembahasan' => 'Topik yang belum tersinkron',
            'jadwal_bimbingan' => $jadwalWaktu,
            'lokasi_atau_link' => 'Ruang Dosen',
            'status' => 'approved',
        ]);

        $this->assertEquals(0, PertemuanBimbingan::count());

        // Buka halaman pertemuan dosen
        $response = $this->actingAs($dosen)->get(route('dosen.pertemuan.index'));

        $response->assertStatus(200);
        $response->assertSee('Topik yang belum tersinkron');
        $this->assertEquals(1, PertemuanBimbingan::count());
        $this->assertEquals(1, JadwalKonsultasi::count());
    }

    public function test_completing_pengajuan_marks_pertemuan_and_jadwal_completed(): void
    {
        $dosen = User::factory()->create(['role' => 'dosen']);
        $mahasiswa = User::factory()->create(['role' => 'mahasiswa']);

        $jadwalWaktu = Carbon::tomorrow()->setHour(10)->setMinute(0)->setSecond(0);

        $pengajuan = Bimbingan::create([
            'dosen_id' => $dosen->id,
            'mahasiswa_id' => $mahasiswa->id,
            'jenis_bimbingan' => 'skripsi',
            'topik_pembahasan' => 'Selesai bimbingan',
            'jadwal_bimbingan' => $jadwalWaktu,
            'status' => 'approved',
        ]);

        // Sinkronkan dulu via schedule
        $this->actingAs($dosen)->put(route('dosen.pengajuan.schedule', $pengajuan->id), [
            'jadwal_bimbingan' => $jadwalWaktu->format('Y-m-d\TH:i'),
            'lokasi_atau_link' => 'Ruang Dosen',
        ]);

        // Selesaikan
        $response = $this->actingAs($dosen)->post(route('dosen.pengajuan.complete', $pengajuan->id));

        $response->assertRedirect(route('dosen.pengajuan.index'));
        $this->assertEquals('completed', $pengajuan->fresh()->status);
        $this->assertEquals('selesai', PertemuanBimbingan::where('bimbingan_id', $pengajuan->id)->first()->status);
        $this->assertEquals('selesai', JadwalKonsultasi::where('dosen_id', $dosen->id)->first()->status);
    }

    public function test_student_submitting_pending_pengajuan_creates_pertemuan_menunggu(): void
    {
        $dosen = User::factory()->create(['role' => 'dosen']);
        $mahasiswa = User::factory()->create(['role' => 'mahasiswa', 'dosen_skripsi_id' => $dosen->id]);

        $jadwalWaktu = Carbon::tomorrow()->setHour(13)->setMinute(0)->setSecond(0);

        $response = $this->actingAs($mahasiswa)->post(route('mahasiswa.bimbingan.store'), [
            'dosen_id' => $dosen->id,
            'jenis_bimbingan' => 'skripsi',
            'topik_pembahasan' => 'Pengajuan Menunggu Konfirmasi Test',
            'jadwal_bimbingan' => $jadwalWaktu->format('Y-m-d\TH:i'),
        ]);

        $response->assertRedirect(route('mahasiswa.jadwal'));

        // Harus masuk ke bimbingans dengan status pending
        $this->assertDatabaseHas('bimbingans', [
            'mahasiswa_id' => $mahasiswa->id,
            'dosen_id' => $dosen->id,
            'status' => 'pending',
            'topik_pembahasan' => 'Pengajuan Menunggu Konfirmasi Test',
        ]);

        $bimbingan = Bimbingan::where('mahasiswa_id', $mahasiswa->id)->first();

        // Harus langsung masuk ke pertemuan_bimbingans dengan status menunggu
        $this->assertDatabaseHas('pertemuan_bimbingans', [
            'bimbingan_id' => $bimbingan->id,
            'dosen_id' => $dosen->id,
            'mahasiswa_id' => $mahasiswa->id,
            'status' => 'menunggu',
            'judul_topik' => 'Pengajuan Menunggu Konfirmasi Test',
        ]);

        // Dosen melihat pertemuan index dan melihat pengajuan tersebut di daftar menunggu
        $pertemuanResponse = $this->actingAs($dosen)->get(route('dosen.pertemuan.index'));
        $pertemuanResponse->assertStatus(200);
        $pertemuanResponse->assertSee('Pengajuan Menunggu Konfirmasi Test');
        $pertemuanResponse->assertSee($mahasiswa->name);
    }

    public function test_pertemuan_index_auto_syncs_existing_pending_pengajuan(): void
    {
        $dosen = User::factory()->create(['role' => 'dosen']);
        $mahasiswa = User::factory()->create(['role' => 'mahasiswa']);

        // Bimbingan pending yang dibuat tanpa pertemuan_bimbingan
        $bimbingan = Bimbingan::create([
            'dosen_id' => $dosen->id,
            'mahasiswa_id' => $mahasiswa->id,
            'jenis_bimbingan' => 'PA',
            'topik_pembahasan' => 'Konsultasi KRS Semester Depan',
            'status' => 'pending',
        ]);

        $this->assertEquals(0, PertemuanBimbingan::count());

        // Saat dosen membuka halaman pertemuan, auto-sync mendeteksi bimbingan pending
        $response = $this->actingAs($dosen)->get(route('dosen.pertemuan.index'));

        $response->assertStatus(200);
        $response->assertSee('Konsultasi KRS Semester Depan');

        $this->assertDatabaseHas('pertemuan_bimbingans', [
            'bimbingan_id' => $bimbingan->id,
            'status' => 'menunggu',
            'jenis_bimbingan' => 'akademik',
        ]);
    }

    public function test_pengajuan_show_does_not_contain_mark_completed_button(): void
    {
        $dosen = User::factory()->create(['role' => 'dosen']);
        $mahasiswa = User::factory()->create(['role' => 'mahasiswa']);

        $bimbingan = Bimbingan::create([
            'dosen_id' => $dosen->id,
            'mahasiswa_id' => $mahasiswa->id,
            'jenis_bimbingan' => 'skripsi',
            'topik_pembahasan' => 'Uji Coba Bab 3',
            'jadwal_bimbingan' => Carbon::tomorrow(),
            'lokasi_atau_link' => 'Ruang Dosen',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($dosen)->get(route('dosen.pengajuan.show', $bimbingan->id));

        $response->assertStatus(200);
        $response->assertDontSee('Tandai Bimbingan Selesai');
    }
}
