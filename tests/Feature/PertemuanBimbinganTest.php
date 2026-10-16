<?php

namespace Tests\Feature;

use App\Models\ArahanRevisi;
use App\Models\PertemuanBimbingan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PertemuanBimbinganTest extends TestCase
{
    use RefreshDatabase;

    public function test_dosen_can_view_pertemuan_index(): void
    {
        $dosen = User::factory()->create(['role' => 'dosen']);

        $response = $this->actingAs($dosen)->get(route('dosen.pertemuan.index'));

        $response->assertStatus(200);
        $response->assertSee('Pertemuan & Bimbingan Mahasiswa', false);
    }

    public function test_dosen_can_view_pertemuan_detail(): void
    {
        $dosen = User::factory()->create(['role' => 'dosen']);
        $mahasiswa = User::factory()->create(['role' => 'mahasiswa']);

        $pertemuan = PertemuanBimbingan::create([
            'dosen_id' => $dosen->id,
            'mahasiswa_id' => $mahasiswa->id,
            'jenis_bimbingan' => 'skripsi',
            'judul_topik' => 'Review Latar Belakang & Bab 1',
            'metode' => 'tatap_muka',
            'tanggal' => Carbon::tomorrow()->toDateString(),
            'status' => 'disetujui',
        ]);

        $response = $this->actingAs($dosen)->get(route('dosen.pertemuan.show', $pertemuan->id));

        $response->assertStatus(200);
        $response->assertSee('Detail Pertemuan Bimbingan');
        $response->assertSee($mahasiswa->name);
        $response->assertSee('Review Latar Belakang &amp; Bab 1', false);
    }

    public function test_dosen_can_approve_and_reject_bimbingan(): void
    {
        $dosen = User::factory()->create(['role' => 'dosen']);
        $mahasiswa = User::factory()->create(['role' => 'mahasiswa']);

        $pertemuan = PertemuanBimbingan::create([
            'dosen_id' => $dosen->id,
            'mahasiswa_id' => $mahasiswa->id,
            'jenis_bimbingan' => 'skripsi',
            'judul_topik' => 'Review Latar Belakang',
            'metode' => 'tatap_muka',
            'tanggal' => Carbon::tomorrow()->toDateString(),
            'status' => 'menunggu',
        ]);

        $response = $this->actingAs($dosen)->post(route('dosen.pertemuan.konfirmasi', $pertemuan->id), [
            'tempat' => 'Ruang Dosen 302',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals('disetujui', $pertemuan->fresh()->status);
        $this->assertEquals('Ruang Dosen 302', $pertemuan->fresh()->tempat);

        $pertemuan2 = PertemuanBimbingan::create([
            'dosen_id' => $dosen->id,
            'mahasiswa_id' => $mahasiswa->id,
            'jenis_bimbingan' => 'skripsi',
            'judul_topik' => 'Review Bab 2',
            'metode' => 'tatap_muka',
            'tanggal' => Carbon::tomorrow()->toDateString(),
            'status' => 'menunggu',
        ]);

        $responseReject = $this->actingAs($dosen)->post(route('dosen.pertemuan.tolak', $pertemuan2->id), [
            'alasan_penolakan' => 'Jadwal hari tersebut ada rapat prodi.',
        ]);

        $responseReject->assertSessionHas('success');
        $this->assertEquals('ditolak', $pertemuan2->fresh()->status);
        $this->assertEquals('Jadwal hari tersebut ada rapat prodi.', $pertemuan2->fresh()->alasan_penolakan);
    }

    public function test_dosen_can_save_meeting_notes_and_mark_completed(): void
    {
        $dosen = User::factory()->create(['role' => 'dosen']);
        $mahasiswa = User::factory()->create(['role' => 'mahasiswa']);

        $pertemuan = PertemuanBimbingan::create([
            'dosen_id' => $dosen->id,
            'mahasiswa_id' => $mahasiswa->id,
            'jenis_bimbingan' => 'skripsi',
            'judul_topik' => 'Revisi Bab 1',
            'metode' => 'tatap_muka',
            'tanggal' => Carbon::today()->toDateString(),
            'status' => 'disetujui',
        ]);

        $response = $this->actingAs($dosen)->put(route('dosen.pertemuan.catatan', $pertemuan->id), [
            'catatan_hasil' => 'Perbaiki perumusan masalah dan tambahkan 3 jurnal pendukung.',
            'tandai_selesai' => '1',
        ]);

        $response->assertSessionHas('success');
        $fresh = $pertemuan->fresh();
        $this->assertEquals('selesai', $fresh->status);
        $this->assertNotNull($fresh->tanggal_bimbingan_selesai);
        $this->assertEquals('Perbaiki perumusan masalah dan tambahkan 3 jurnal pendukung.', $fresh->catatan_hasil);
    }

    public function test_dosen_can_add_update_delete_revisi(): void
    {
        $dosen = User::factory()->create(['role' => 'dosen']);
        $mahasiswa = User::factory()->create(['role' => 'mahasiswa']);

        $pertemuan = PertemuanBimbingan::create([
            'dosen_id' => $dosen->id,
            'mahasiswa_id' => $mahasiswa->id,
            'jenis_bimbingan' => 'skripsi',
            'judul_topik' => 'Revisi Bab 1',
            'metode' => 'tatap_muka',
            'tanggal' => Carbon::today()->toDateString(),
            'status' => 'disetujui',
        ]);

        $responseAdd = $this->actingAs($dosen)->post(route('dosen.pertemuan.revisi.store', $pertemuan->id), [
            'isi_revisi' => 'Perbaiki batasan masalah nomor 2.',
            'prioritas' => 'tinggi',
            'tenggat_waktu' => Carbon::now()->addDays(5)->toDateString(),
        ]);

        $responseAdd->assertSessionHas('success');
        $this->assertDatabaseHas('arahan_revisis', [
            'pertemuan_bimbingan_id' => $pertemuan->id,
            'isi_revisi' => 'Perbaiki batasan masalah nomor 2.',
            'prioritas' => 'tinggi',
        ]);

        $revisi = ArahanRevisi::first();

        $responseUpdate = $this->actingAs($dosen)->put(route('dosen.pertemuan.revisi.update', $revisi->id), [
            'isi_revisi' => 'Perbaiki batasan masalah nomor 2 dan 3.',
            'prioritas' => 'sedang',
            'status' => 'sedang_dikerjakan',
            'tenggat_waktu' => Carbon::now()->addDays(7)->toDateString(),
        ]);

        $responseUpdate->assertSessionHas('success');
        $this->assertEquals('Perbaiki batasan masalah nomor 2 dan 3.', $revisi->fresh()->isi_revisi);
        $this->assertEquals('sedang_dikerjakan', $revisi->fresh()->status);

        $responseDelete = $this->actingAs($dosen)->delete(route('dosen.pertemuan.revisi.destroy', $revisi->id));
        $responseDelete->assertSessionHas('success');
        $this->assertDatabaseCount('arahan_revisis', 0);
    }
}
