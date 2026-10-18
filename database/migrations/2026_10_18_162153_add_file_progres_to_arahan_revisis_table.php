<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('arahan_revisis', function (Blueprint $table) {
            $table->string('file_progres')->nullable()->after('catatan_mahasiswa');
        });
    }

    public function down(): void
    {
        Schema::table('arahan_revisis', function (Blueprint $table) {
            $table->dropColumn('file_progres');
        });
    }
};
