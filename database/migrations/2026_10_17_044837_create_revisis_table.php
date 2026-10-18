<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('revisis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bimbingan_id')->constrained('bimbingans')->onDelete('cascade');
            $table->text('deskripsi_revisi');
            $table->enum('status_revisi', ['pending', 'done'])->default('pending');
            $table->text('catatan_mahasiswa')->nullable();
            $table->string('file_progres')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('revisis');
    }
};
