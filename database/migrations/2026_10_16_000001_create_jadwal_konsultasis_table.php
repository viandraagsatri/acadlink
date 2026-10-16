<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal_konsultasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dosen_id')->constrained('users')->cascadeOnDelete();
            $table->string('jenis_bimbingan')->default('Semua');
            $table->string('hari');
            $table->date('tanggal');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->enum('metode', ['tatap_muka', 'online', 'keduanya'])->default('tatap_muka');
            $table->string('ruangan_atau_link');
            $table->integer('kuota')->default(5);
            $table->text('keterangan')->nullable();
            $table->enum('status', ['aktif', 'nonaktif', 'selesai'])->default('aktif');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_konsultasis');
    }
};
