<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArahanRevisi extends Model
{
    use HasFactory;

    protected $table = 'arahan_revisis';

    protected $fillable = [
        'pertemuan_bimbingan_id',
        'isi_revisi',
        'tenggat_waktu',
        'prioritas',
        'status',
        'catatan_mahasiswa',
    ];

    protected function casts(): array
    {
        return [
            'tenggat_waktu' => 'date',
        ];
    }

    public function pertemuanBimbingan(): BelongsTo
    {
        return $this->belongsTo(PertemuanBimbingan::class, 'pertemuan_bimbingan_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'belum_selesai' => 'Belum Dikerjakan',
            'sedang_dikerjakan' => 'Sedang Dikerjakan',
            'selesai' => 'Selesai Dikerjakan',
            'perlu_perbaikan' => 'Perlu Perbaikan Ulang',
            default => ucfirst(str_replace('_', ' ', (string) $this->status)),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'belum_selesai' => 'bg-secondary',
            'sedang_dikerjakan' => 'bg-warning text-dark',
            'selesai' => 'bg-success',
            'perlu_perbaikan' => 'bg-danger',
            default => 'bg-light text-dark',
        };
    }

    public function getPrioritasLabelAttribute(): string
    {
        return match ($this->prioritas) {
            'tinggi' => 'Prioritas Tinggi',
            'sedang' => 'Prioritas Sedang',
            'rendah' => 'Prioritas Rendah',
            default => ucfirst((string) $this->prioritas),
        };
    }

    public function getPrioritasBadgeClassAttribute(): string
    {
        return match ($this->prioritas) {
            'tinggi' => 'bg-danger',
            'sedang' => 'bg-warning text-dark',
            'rendah' => 'bg-info text-dark',
            default => 'bg-secondary',
        };
    }
}
