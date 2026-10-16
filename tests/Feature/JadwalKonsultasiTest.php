<?php

namespace Tests\Feature;

use App\Models\JadwalKonsultasi;
use App\Models\PertemuanBimbingan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JadwalKonsultasiTest extends TestCase
{
    use RefreshDatabase;

    public function test_dosen_can_view_jadwal_index(): void
    {
        $dosen = User::factory()->create(['role' => 'dosen']);

        $response = $this->actingAs($dosen)->get(route('dosen.jadwal.index'));

        $response->assertStatus(200);
        $response->assertSee('Kelola Jadwal Konsultasi');
    }

    public function test_mahasiswa_cannot_access_dosen_jadwal(): void
    {
        $mahasiswa = User::factory()->create(['role' => 'mahasiswa']);

        $response = $this->actingAs($mahasiswa)->get(route('dosen.jadwal.index'));

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('error');
    }

    public function test_dosen_can_create_jadwal_konsultasi(): void
    {
        $dosen = User::factory()->create(['role' => 'dosen']);
        $tomorrow = Carbon::tomorrow()->toDateString();

        $response = $this->actingAs($dosen)
            ->from(route('dosen.jadwal.create'))
            ->post(route('dosen.jadwal.store'), [
                'jenis_bimbingan' => 'Skripsi',
                'tanggal' => $tomorrow,
                'jam_mulai' => '09:00',
                'jam_selesai' => '11:00',
                'metode' => 'tatap_muka',
                'ruangan_atau_link' => 'Ruang 301',
                'kuota' => 5,
                'keterangan' => 'Konsultasi bab 1',
            ]);

        $response->assertRedirect(route('dosen.jadwal.index'));
        $response->assertSessionHas('success');

        $jadwal = JadwalKonsultasi::first();
        $this->assertNotNull($jadwal);
        $this->assertEquals($dosen->id, $jadwal->dosen_id);
        $this->assertEquals('Skripsi', $jadwal->jenis_bimbingan);
        $this->assertEquals($tomorrow, $jadwal->tanggal->format('Y-m-d'));
        $this->assertEquals('09:00:00', $jadwal->jam_mulai);
        $this->assertEquals('11:00:00', $jadwal->jam_selesai);
        $this->assertEquals('Ruang 301', $jadwal->ruangan_atau_link);
    }

    public function test_dosen_cannot_create_bentrok_jadwal(): void
    {
        $dosen = User::factory()->create(['role' => 'dosen']);
        $tomorrow = Carbon::tomorrow()->toDateString();

        JadwalKonsultasi::create([
            'dosen_id' => $dosen->id,
            'jenis_bimbingan' => 'Skripsi',
            'hari' => 'Senin',
            'tanggal' => $tomorrow,
            'jam_mulai' => '09:00:00',
            'jam_selesai' => '11:00:00',
            'metode' => 'tatap_muka',
            'ruangan_atau_link' => 'Ruang 301',
            'kuota' => 5,
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($dosen)
            ->from(route('dosen.jadwal.create'))
            ->post(route('dosen.jadwal.store'), [
                'jenis_bimbingan' => 'Kerja Praktik',
                'tanggal' => $tomorrow,
                'jam_mulai' => '10:00',
                'jam_selesai' => '12:00',
                'metode' => 'online',
                'ruangan_atau_link' => 'https://meet.google.com/test',
                'kuota' => 3,
            ]);

        $response->assertRedirect(route('dosen.jadwal.create'));
        $response->assertSessionHas('error');
        $this->assertDatabaseCount('jadwal_konsultasis', 1);
    }

    public function test_dosen_can_update_jadwal(): void
    {
        $dosen = User::factory()->create(['role' => 'dosen']);
        $tomorrow = Carbon::tomorrow()->toDateString();

        $jadwal = JadwalKonsultasi::create([
            'dosen_id' => $dosen->id,
            'jenis_bimbingan' => 'Akademik',
            'hari' => 'Selasa',
            'tanggal' => $tomorrow,
            'jam_mulai' => '13:00:00',
            'jam_selesai' => '15:00:00',
            'metode' => 'tatap_muka',
            'ruangan_atau_link' => 'Ruang 201',
            'kuota' => 4,
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($dosen)->put(route('dosen.jadwal.update', $jadwal->id), [
            'jenis_bimbingan' => 'Akademik',
            'tanggal' => $tomorrow,
            'jam_mulai' => '13:30',
            'jam_selesai' => '15:30',
            'metode' => 'online',
            'ruangan_atau_link' => 'https://zoom.us/test',
            'kuota' => 6,
            'status' => 'aktif',
        ]);

        $response->assertRedirect(route('dosen.jadwal.index'));
        $this->assertDatabaseHas('jadwal_konsultasis', [
            'id' => $jadwal->id,
            'metode' => 'online',
            'kuota' => 6,
        ]);
    }

    public function test_dosen_can_delete_unused_jadwal(): void
    {
        $dosen = User::factory()->create(['role' => 'dosen']);

        $jadwal = JadwalKonsultasi::create([
            'dosen_id' => $dosen->id,
            'jenis_bimbingan' => 'Skripsi',
            'hari' => 'Rabu',
            'tanggal' => Carbon::tomorrow()->toDateString(),
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '10:00:00',
            'metode' => 'tatap_muka',
            'ruangan_atau_link' => 'Ruang 102',
            'kuota' => 3,
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($dosen)->delete(route('dosen.jadwal.destroy', $jadwal->id));

        $response->assertRedirect(route('dosen.jadwal.index'));
        $this->assertDatabaseMissing('jadwal_konsultasis', ['id' => $jadwal->id]);
    }

    public function test_dosen_cannot_delete_jadwal_with_registered_students(): void
    {
        $dosen = User::factory()->create(['role' => 'dosen']);
        $mahasiswa = User::factory()->create(['role' => 'mahasiswa']);

        $jadwal = JadwalKonsultasi::create([
            'dosen_id' => $dosen->id,
            'jenis_bimbingan' => 'Skripsi',
            'hari' => 'Kamis',
            'tanggal' => Carbon::tomorrow()->toDateString(),
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '10:00:00',
            'metode' => 'tatap_muka',
            'ruangan_atau_link' => 'Ruang 102',
            'kuota' => 3,
            'status' => 'aktif',
        ]);

        PertemuanBimbingan::create([
            'dosen_id' => $dosen->id,
            'mahasiswa_id' => $mahasiswa->id,
            'jadwal_konsultasi_id' => $jadwal->id,
            'jenis_bimbingan' => 'skripsi',
            'judul_topik' => 'Diskusi Bab 1',
            'metode' => 'tatap_muka',
            'tanggal' => $jadwal->tanggal,
            'status' => 'disetujui',
        ]);

        $response = $this->actingAs($dosen)->delete(route('dosen.jadwal.destroy', $jadwal->id));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('jadwal_konsultasis', ['id' => $jadwal->id]);
    }
}
