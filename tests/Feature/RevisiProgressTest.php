<?php

namespace Tests\Feature;

use App\Models\ArahanRevisi;
use App\Models\PertemuanBimbingan;
use App\Models\User;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RevisiProgressTest extends TestCase
{
    use RefreshDatabase;

    public function test_completed_revision_cannot_be_updated_and_has_no_update_form(): void
    {
        $mahasiswa = User::factory()->create(['role' => 'mahasiswa']);
        $dosen = User::factory()->create(['role' => 'dosen']);
        $pertemuan = PertemuanBimbingan::create([
            'dosen_id' => $dosen->id,
            'mahasiswa_id' => $mahasiswa->id,
            'jenis_bimbingan' => 'skripsi',
            'judul_topik' => 'Revisi Bab 1',
            'metode' => 'tatap_muka',
            'tanggal' => today(),
            'status' => 'selesai',
        ]);
        $revisi = ArahanRevisi::create([
            'pertemuan_bimbingan_id' => $pertemuan->id,
            'isi_revisi' => 'Perbaiki rumusan masalah.',
            'status' => 'selesai',
            'catatan_mahasiswa' => 'Sudah diperbaiki.',
        ]);

        $this->actingAs($mahasiswa)
            ->get(route('mahasiswa.revisi'))
            ->assertOk()
            ->assertSee('Progres sudah selesai dan tidak dapat diperbarui.')
            ->assertDontSee('Perbarui Progres')
            ->assertDontSee('Simpan Progres');

        $response = $this->from(route('mahasiswa.revisi'))
            ->post(route('mahasiswa.revisi.update', $revisi->id), [
                'status' => 'sedang_dikerjakan',
                'catatan_mahasiswa' => 'Mencoba mengubah revisi yang sudah selesai.',
            ]);

        $response->assertRedirect(route('mahasiswa.revisi'));
        $response->assertSessionHas('error', 'Progres revisi yang sudah selesai tidak dapat diperbarui.');
        $this->assertDatabaseHas('arahan_revisis', [
            'id' => $revisi->id,
            'status' => 'selesai',
            'catatan_mahasiswa' => 'Sudah diperbaiki.',
        ]);
    }

    public function test_incomplete_revision_can_still_be_marked_completed(): void
    {
        $mahasiswa = User::factory()->create(['role' => 'mahasiswa']);
        $dosen = User::factory()->create(['role' => 'dosen']);
        $pertemuan = PertemuanBimbingan::create([
            'dosen_id' => $dosen->id,
            'mahasiswa_id' => $mahasiswa->id,
            'jenis_bimbingan' => 'skripsi',
            'judul_topik' => 'Revisi Bab 2',
            'metode' => 'tatap_muka',
            'tanggal' => today(),
            'status' => 'selesai',
        ]);
        $revisi = ArahanRevisi::create([
            'pertemuan_bimbingan_id' => $pertemuan->id,
            'isi_revisi' => 'Tambahkan referensi.',
            'status' => 'sedang_dikerjakan',
        ]);

        $response = $this->actingAs($mahasiswa)
            ->post(route('mahasiswa.revisi.update', $revisi->id), [
                'status' => 'selesai',
                'catatan_mahasiswa' => 'Referensi sudah ditambahkan.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Progress revisi berhasil diperbarui!');
        $this->assertDatabaseHas('arahan_revisis', [
            'id' => $revisi->id,
            'status' => 'selesai',
            'catatan_mahasiswa' => 'Referensi sudah ditambahkan.',
        ]);
    }

    public function test_failed_upload_keeps_existing_file_and_does_not_update_progress(): void
    {
        $mahasiswa = User::factory()->create(['role' => 'mahasiswa']);
        $dosen = User::factory()->create(['role' => 'dosen']);
        $pertemuan = PertemuanBimbingan::create([
            'dosen_id' => $dosen->id,
            'mahasiswa_id' => $mahasiswa->id,
            'jenis_bimbingan' => 'skripsi',
            'judul_topik' => 'Revisi Bab 3',
            'metode' => 'tatap_muka',
            'tanggal' => today(),
            'status' => 'selesai',
        ]);
        $revisi = ArahanRevisi::create([
            'pertemuan_bimbingan_id' => $pertemuan->id,
            'isi_revisi' => 'Perbaiki kesimpulan.',
            'status' => 'sedang_dikerjakan',
            'catatan_mahasiswa' => 'Catatan lama.',
        ]);
        $revisi->file_progres = 'revisi_files/file-lama.pdf';
        $revisi->save();
        $disk = \Mockery::mock(FilesystemAdapter::class);
        $disk->shouldReceive('putFileAs')->once()->andReturn(false);
        $disk->shouldNotReceive('delete');
        app(FilesystemManager::class)->set('public', $disk);

        $response = $this->actingAs($mahasiswa)
            ->from(route('mahasiswa.revisi'))
            ->post(route('mahasiswa.revisi.update', $revisi->id), [
                'status' => 'selesai',
                'catatan_mahasiswa' => 'Catatan baru.',
                'file_progres' => UploadedFile::fake()->create('progres.pdf', 10, 'application/pdf'),
            ]);

        $response->assertRedirect(route('mahasiswa.revisi'));
        $response->assertSessionHas('error', 'File progres gagal disimpan. Perubahan progres tidak diterapkan.');
        $this->assertDatabaseHas('arahan_revisis', [
            'id' => $revisi->id,
            'status' => 'sedang_dikerjakan',
            'catatan_mahasiswa' => 'Catatan lama.',
            'file_progres' => 'revisi_files/file-lama.pdf',
        ]);
    }

    public function test_successful_file_replacement_saves_new_file_before_deleting_old_file(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('revisi_files/file-lama.pdf', 'file lama');

        $mahasiswa = User::factory()->create(['role' => 'mahasiswa']);
        $dosen = User::factory()->create(['role' => 'dosen']);
        $pertemuan = PertemuanBimbingan::create([
            'dosen_id' => $dosen->id,
            'mahasiswa_id' => $mahasiswa->id,
            'jenis_bimbingan' => 'skripsi',
            'judul_topik' => 'Revisi Bab 4',
            'metode' => 'tatap_muka',
            'tanggal' => today(),
            'status' => 'selesai',
        ]);
        $revisi = ArahanRevisi::create([
            'pertemuan_bimbingan_id' => $pertemuan->id,
            'isi_revisi' => 'Perbaiki daftar pustaka.',
            'status' => 'sedang_dikerjakan',
        ]);
        $revisi->file_progres = 'revisi_files/file-lama.pdf';
        $revisi->save();

        $response = $this->actingAs($mahasiswa)
            ->post(route('mahasiswa.revisi.update', $revisi->id), [
                'status' => 'sedang_dikerjakan',
                'catatan_mahasiswa' => 'Sudah diperbarui.',
                'file_progres' => UploadedFile::fake()->create('progres-baru.pdf', 10, 'application/pdf'),
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Progress revisi berhasil diperbarui!');
        $this->assertDatabaseMissing('arahan_revisis', [
            'id' => $revisi->id,
            'file_progres' => 'revisi_files/file-lama.pdf',
        ]);
        $newFilePath = $revisi->fresh()->file_progres;
        $this->assertNotSame('revisi_files/file-lama.pdf', $newFilePath);
        Storage::disk('public')->assertExists($newFilePath);
        Storage::disk('public')->assertMissing('revisi_files/file-lama.pdf');
    }
}
