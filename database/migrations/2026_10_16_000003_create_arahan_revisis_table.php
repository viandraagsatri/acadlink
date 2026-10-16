<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('arahan_revisis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pertemuan_bimbingan_id')->constrained('pertemuan_bimbingans')->cascadeOnDelete();
            $table->text('isi_revisi');
            $table->date('tenggat_waktu')->nullable();
            $table->enum('prioritas', ['tinggi', 'sedang', 'rendah'])->default('sedang');
            $table->enum('status', ['belum_selesai', 'sedang_dikerjakan', 'selesai', 'perlu_perbaikan'])->default('belum_selesai');
            $table->text('catatan_mahasiswa')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arahan_revisis');
    }
};
