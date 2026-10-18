<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'nim_nip',
        'email',
        'role',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isMahasiswa(): bool
    {
        return $this->role === 'mahasiswa';
    }

    public function isDosen(): bool
    {
        return $this->role === 'dosen';
    }

    public function jadwalKonsultasis(): HasMany
    {
        return $this->hasMany(JadwalKonsultasi::class, 'dosen_id');
    }

    public function pertemuanBimbinganDosen(): HasMany
    {
        return $this->hasMany(PertemuanBimbingan::class, 'dosen_id');
    }

    public function pertemuanBimbinganMahasiswa(): HasMany
    {
        return $this->hasMany(PertemuanBimbingan::class, 'mahasiswa_id');
    }

    public function dosenPembimbing()
    {
        return $this->belongsTo(User::class, 'dosen_pembimbing_id');
    }

    public function anakBimbingan()
    {
        return $this->hasMany(User::class, 'dosen_pembimbing_id');
    }

    public function riwayatPengajuanMahasiswa()
    {
        return $this->hasMany(Bimbingan::class, 'mahasiswa_id');
    }

    public function riwayatPengajuanDosen()
    {
        return $this->hasMany(Bimbingan::class, 'dosen_id');
    }
}
