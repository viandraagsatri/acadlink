
<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Dosen\JadwalKonsultasiController;
use App\Http\Controllers\Dosen\PertemuanBimbinganController;
use App\Http\Controllers\Dosen\PengajuanBimbinganController;
use App\Http\Controllers\MahasiswaBimbinganController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'role:dosen'])
    ->prefix('dosen')
    ->name('dosen.')
    ->group(function () {

        // JADWAL KONSULTASI
        Route::get('/jadwal', [JadwalKonsultasiController::class, 'index'])->name('jadwal.index');
        Route::get('/jadwal/create', [JadwalKonsultasiController::class, 'create'])->name('jadwal.create');
        Route::post('/jadwal', [JadwalKonsultasiController::class, 'store'])->name('jadwal.store');
        Route::get('/jadwal/{id}/edit', [JadwalKonsultasiController::class, 'edit'])->name('jadwal.edit');
        Route::put('/jadwal/{id}', [JadwalKonsultasiController::class, 'update'])->name('jadwal.update');
        Route::delete('/jadwal/{id}', [JadwalKonsultasiController::class, 'destroy'])->name('jadwal.destroy');

        // PERTEMUAN DAN BIMBINGAN
        Route::get('/pertemuan', [PertemuanBimbinganController::class, 'index'])->name('pertemuan.index');
        Route::get('/pertemuan/{id}', [PertemuanBimbinganController::class, 'show'])->name('pertemuan.show');
        Route::post('/pertemuan/{id}/konfirmasi', [PertemuanBimbinganController::class, 'konfirmasi'])->name('pertemuan.konfirmasi');
        Route::post('/pertemuan/{id}/tolak', [PertemuanBimbinganController::class, 'tolak'])->name('pertemuan.tolak');
        Route::put('/pertemuan/{id}/catatan', [PertemuanBimbinganController::class, 'updateCatatan'])->name('pertemuan.catatan');
        Route::post('/pertemuan/{id}/revisi', [PertemuanBimbinganController::class, 'tambahRevisi'])->name('pertemuan.revisi.store');
        Route::put('/revisi/{id}', [PertemuanBimbinganController::class, 'updateRevisi'])->name('pertemuan.revisi.update');
        Route::delete('/revisi/{id}', [PertemuanBimbinganController::class, 'hapusRevisi'])->name('pertemuan.revisi.destroy');

        // PENGAJUAN DAN PERSETUJUAN BIMBINGAN — ORANG 4
        Route::get('/pengajuan', [PengajuanBimbinganController::class, 'index'])
            ->name('pengajuan.index');

        Route::get('/pengajuan/{id}', [PengajuanBimbinganController::class, 'show'])
            ->name('pengajuan.show');

        Route::post('/pengajuan/{id}/setujui', [PengajuanBimbinganController::class, 'approve'])
            ->name('pengajuan.approve');

        Route::post('/pengajuan/{id}/tolak', [PengajuanBimbinganController::class, 'reject'])
            ->name('pengajuan.reject');

        Route::put('/pengajuan/{id}/jadwal', [PengajuanBimbinganController::class, 'schedule'])
            ->name('pengajuan.schedule');

        Route::post('/pengajuan/{id}/selesai', [PengajuanBimbinganController::class, 'complete'])
            ->name('pengajuan.complete');
    });

Route::middleware(['auth', 'role:mahasiswa'])
    ->prefix('mahasiswa')
    ->name('mahasiswa.')
    ->group(function () {
        Route::get('/dashboard', [MahasiswaBimbinganController::class, 'dashboard'])->name('dashboard');
        Route::get('/jadwal', [MahasiswaBimbinganController::class, 'jadwal'])->name('jadwal');
        Route::get('/riwayat', [MahasiswaBimbinganController::class, 'riwayat'])->name('riwayat');
        Route::get('/revisi', [MahasiswaBimbinganController::class, 'revisi'])->name('revisi');
        Route::post('/revisi/{id}/update', [MahasiswaBimbinganController::class, 'updateRevisiProgress'])->name('revisi.update');
        Route::get('/bimbingan/create', [MahasiswaBimbinganController::class, 'create'])->name('bimbingan.create');
    Route::post('/bimbingan', [MahasiswaBimbinganController::class, 'store'])->name('bimbingan.store');
    Route::get('/bimbingan/{id}/edit', [MahasiswaBimbinganController::class, 'edit'])->name('bimbingan.edit');
    Route::put('/bimbingan/{id}', [MahasiswaBimbinganController::class, 'update'])->name('bimbingan.update');
    Route::delete('/bimbingan/{id}', [MahasiswaBimbinganController::class, 'destroy'])->name('bimbingan.destroy');
});
