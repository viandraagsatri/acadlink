<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JadwalKonsultasi extends Model
{
    use HasFactory;

    protected $table = 'jadwal_konsultasis';

    protected $fillable = [
        'dosen_id',
        'jenis_bimbingan',
        'hari',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'metode',
        'ruangan_atau_link',
        'kuota',
        'keterangan',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dosen_id');
    }

    public function pertemuanBimbingans(): HasMany
    {
        return $this->hasMany(PertemuanBimbingan::class, 'jadwal_konsultasi_id');
    }

    public function getKuotaTerisiAttribute(): int
    {
        return $this->pertemuanBimbingans()
            ->whereIn('status', ['menunggu', 'disetujui', 'selesai'])
            ->count();
    }

    public function getSisaKuotaAttribute(): int
    {
        return max(0, (int) $this->kuota - $this->kuota_terisi);
    }

    public function getIsPenuhAttribute(): bool
    {
        return $this->sisa_kuota <= 0;
    }

    public function getMetodeBadgeAttribute(): string
    {
        return match ($this->metode) {
            'tatap_muka' => 'Tatap Muka (Offline)',
            'online' => 'Online',
            'keduanya' => 'Tatap Muka & Online',
            default => ucfirst(str_replace('_', ' ', (string) $this->metode)),
        };
    }

    public function getJamMulaiFormatAttribute(): string
    {
        return Carbon::parse($this->jam_mulai)->format('H:i');
    }

    public function getJamSelesaiFormatAttribute(): string
    {
        return Carbon::parse($this->jam_selesai)->format('H:i');
    }
}
