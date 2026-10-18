<?php

namespace App\Http\Controllers;

use App\Models\Bimbingan;
use App\Models\JadwalKonsultasi;
use App\Models\PertemuanBimbingan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        if (! $user) {
            return view('home');
        }

        /*
        |--------------------------------------------------------------------------
        | DASHBOARD DOSEN
        |--------------------------------------------------------------------------
        */

        if (method_exists($user, 'isDosen') && $user->isDosen()) {

            // Statistik jadwal konsultasi
            $jadwalAktifCount = JadwalKonsultasi::where('dosen_id', $user->id)
                ->where('status', 'aktif')
                ->count();

            // Statistik pertemuan
            $pertemuanMenungguCount = PertemuanBimbingan::where('dosen_id', $user->id)
                ->where('status', 'menunggu')
                ->count();

            $pertemuanSelesaiCount = PertemuanBimbingan::where('dosen_id', $user->id)
                ->where('status', 'selesai')
                ->count();

            // Jadwal konsultasi mendatang
            $jadwalMendatang = JadwalKonsultasi::where('dosen_id', $user->id)
                ->where('status', 'aktif')
                ->whereDate('tanggal', '>=', Carbon::today()->toDateString())
                ->orderBy('tanggal', 'asc')
                ->orderBy('jam_mulai', 'asc')
                ->take(3)
                ->get();

            // Pertemuan terbaru
            $pertemuanTerbaru = PertemuanBimbingan::where('dosen_id', $user->id)
                ->with(['mahasiswa'])
                ->orderBy('id', 'desc')
                ->take(5)
                ->get();

            /*
            |--------------------------------------------------------------------------
            | FITUR ORANG 4: PENGAJUAN DAN PERSETUJUAN
            |--------------------------------------------------------------------------
            */

            // Jumlah pengajuan yang menunggu persetujuan
            $pengajuanMenungguCount = Bimbingan::where('dosen_id', $user->id)
                ->where('status', 'pending')
                ->count();

            // Jumlah pengajuan yang telah disetujui
            $pengajuanDisetujuiCount = Bimbingan::where('dosen_id', $user->id)
                ->where('status', 'approved')
                ->count();

            // Daftar pengajuan terbaru yang menunggu keputusan
            $pengajuanTerbaru = Bimbingan::with('mahasiswa')
                ->where('dosen_id', $user->id)
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

            // Jadwal bimbingan dari pengajuan yang telah disetujui
            $bimbinganMendatang = Bimbingan::with('mahasiswa')
                ->where('dosen_id', $user->id)
                ->where('status', 'approved')
                ->whereNotNull('jadwal_bimbingan')
                ->whereDate(
                    'jadwal_bimbingan',
                    '>=',
                    Carbon::today()->toDateString()
                )
                ->orderBy('jadwal_bimbingan', 'asc')
                ->take(5)
                ->get();

            return view('home', compact(
                // Statistik dan jadwal lama
                'jadwalAktifCount',
                'pertemuanMenungguCount',
                'pertemuanSelesaiCount',
                'jadwalMendatang',
                'pertemuanTerbaru',

                // Fitur pengajuan Orang 4
                'pengajuanMenungguCount',
                'pengajuanDisetujuiCount',
                'pengajuanTerbaru',
                'bimbinganMendatang'
            ));
        }

        /*
        |--------------------------------------------------------------------------
        | DASHBOARD MAHASISWA
        |--------------------------------------------------------------------------
        */

        if (
            (method_exists($user, 'isMahasiswa') && $user->isMahasiswa())
            || (isset($user->role) && $user->role === 'mahasiswa')
        ) {

            // Statistik pertemuan mahasiswa
            $pertemuanMenungguCount = PertemuanBimbingan::where('mahasiswa_id', $user->id)
                ->where('status', 'menunggu')
                ->count();

            $pertemuanDisetujuiCount = PertemuanBimbingan::where('mahasiswa_id', $user->id)
                ->where('status', 'disetujui')
                ->count();

            $pertemuanSelesaiCount = PertemuanBimbingan::where('mahasiswa_id', $user->id)
                ->where('status', 'selesai')
                ->count();

            // Pertemuan terbaru mahasiswa
            $pertemuanTerbaru = PertemuanBimbingan::where('mahasiswa_id', $user->id)
                ->with(['dosen'])
                ->orderBy('id', 'desc')
                ->take(5)
                ->get();

            // Jadwal mendatang mahasiswa
            $jadwalMendatang = PertemuanBimbingan::where('mahasiswa_id', $user->id)
                ->where('status', 'disetujui')
                ->whereDate(
                    'tanggal',
                    '>=',
                    Carbon::today()->toDateString()
                )
                ->orderBy('tanggal', 'asc')
                ->take(3)
                ->get();

            return view('home', compact(
                'pertemuanMenungguCount',
                'pertemuanDisetujuiCount',
                'pertemuanSelesaiCount',
                'pertemuanTerbaru',
                'jadwalMendatang'
            ));
        }

        return view('home');
    }
}
