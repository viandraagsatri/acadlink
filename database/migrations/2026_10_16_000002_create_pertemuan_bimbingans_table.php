<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pertemuan_bimbingans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dosen_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('mahasiswa_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('jadwal_konsultasi_id')->nullable()->constrained('jadwal_konsultasis')->nullOnDelete();
            $table->enum('jenis_bimbingan', ['skripsi', 'kerja_praktik', 'akademik']);
            $table->string('judul_topik');
            $table->text('deskripsi_singkat')->nullable();
            $table->enum('metode', ['tatap_muka', 'online', 'chat'])->default('tatap_muka');
            $table->date('tanggal');
            $table->time('jam_mulai')->nullable();
            $table->time('jam_selesai')->nullable();
            $table->string('tempat')->nullable();
            $table->enum('status', ['menunggu', 'disetujui', 'selesai', 'ditolak'])->default('menunggu');
            $table->text('alasan_penolakan')->nullable();
            $table->text('catatan_hasil')->nullable();
            $table->dateTime('tanggal_bimbingan_selesai')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pertemuan_bimbingans');
    }
};
