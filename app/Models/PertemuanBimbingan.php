<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PertemuanBimbingan extends Model
{
    use HasFactory;

    protected $table = 'pertemuan_bimbingans';

    protected $fillable = [
        'dosen_id',
        'mahasiswa_id',
        'jadwal_konsultasi_id',
        'jenis_bimbingan',
        'judul_topik',
        'deskripsi_singkat',
        'metode',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'tempat',
        'status',
        'alasan_penolakan',
        'catatan_hasil',
        'tanggal_bimbingan_selesai',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'tanggal_bimbingan_selesai' => 'datetime',
        ];
    }

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dosen_id');
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mahasiswa_id');
    }

    public function jadwalKonsultasi(): BelongsTo
    {
        return $this->belongsTo(JadwalKonsultasi::class, 'jadwal_konsultasi_id');
    }

    public function arahanRevisis(): HasMany
    {
        return $this->hasMany(ArahanRevisi::class, 'pertemuan_bimbingan_id');
    }

    public function getJenisBimbinganLabelAttribute(): string
    {
        return match ($this->jenis_bimbingan) {
            'skripsi' => 'Bimbingan Skripsi',
            'kerja_praktik' => 'Bimbingan Kerja Praktik',
            'akademik' => 'Bimbingan Akademik (PA)',
            default => ucfirst(str_replace('_', ' ', (string) $this->jenis_bimbingan)),
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'menunggu' => 'Menunggu Konfirmasi',
            'disetujui' => 'Disetujui',
            'selesai' => 'Selesai',
            'ditolak' => 'Ditolak',
            default => ucfirst((string) $this->status),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'menunggu' => 'bg-warning text-dark',
            'disetujui' => 'bg-info text-dark',
            'selesai' => 'bg-success',
            'ditolak' => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    public function getMetodeLabelAttribute(): string
    {
        return match ($this->metode) {
            'tatap_muka' => 'Tatap Muka Langsung',
            'online' => 'Online Meeting',
            'chat' => 'Diskusi Chat',
            default => ucfirst(str_replace('_', ' ', (string) $this->metode)),
        };
    }

    public function getJamMulaiFormatAttribute(): ?string
    {
        return $this->jam_mulai ? Carbon::parse($this->jam_mulai)->format('H:i') : null;
    }

    public function getJamSelesaiFormatAttribute(): ?string
    {
        return $this->jam_selesai ? Carbon::parse($this->jam_selesai)->format('H:i') : null;
    }
}
