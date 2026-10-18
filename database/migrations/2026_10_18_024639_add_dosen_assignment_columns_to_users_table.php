<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'dosen_pa_id')) {
                $table->foreignId('dosen_pa_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('users', 'dosen_skripsi_id')) {
                $table->foreignId('dosen_skripsi_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('users', 'dosen_kp_id')) {
                $table->foreignId('dosen_kp_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['dosen_pa_id']);
            $table->dropForeign(['dosen_skripsi_id']);
            $table->dropForeign(['dosen_kp_id']);

            $table->dropColumn([
                'dosen_pa_id',
                'dosen_skripsi_id',
                'dosen_kp_id',
            ]);
        });
    }
};
