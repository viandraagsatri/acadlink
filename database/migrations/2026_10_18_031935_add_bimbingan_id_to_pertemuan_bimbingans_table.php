
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pertemuan_bimbingans', function (Blueprint $table) {
            $table->foreignId('bimbingan_id')
                ->nullable()
                ->unique()
                ->constrained('bimbingans')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pertemuan_bimbingans', function (Blueprint $table) {
            $table->dropForeign(['bimbingan_id']);
            $table->dropUnique(['pertemuan_bimbingans_bimbingan_id_unique']);
            $table->dropColumn('bimbingan_id');
        });
    }
};
