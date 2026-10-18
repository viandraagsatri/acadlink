<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Bimbingan extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'jadwal_bimbingan' => 'datetime',
    ];

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dosen_id');
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mahasiswa_id');
    }

    public function revisis(): HasMany
    {
        return $this->hasMany(Revisi::class);
    }

    public function pesanChats(): HasMany
    {
        return $this->hasMany(PesanChat::class, 'bimbingan_id');
    }

    /**
     * Relasi ke pertemuan bimbingan yang berasal
     * dari pengajuan ini.
     */
    public function pertemuan(): HasOne
    {
        return $this->hasOne(
            PertemuanBimbingan::class,
            'bimbingan_id'
        );
    }
}
