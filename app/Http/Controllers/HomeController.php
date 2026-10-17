<?php

namespace App\Http\Controllers;

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

        if (!$user) {
            return view('home');
        }

        if (method_exists($user, 'isDosen') && $user->isDosen()) {
            $jadwalAktifCount = JadwalKonsultasi::where('dosen_id', $user->id)
                ->where('status', 'aktif')
                ->count();

            $pertemuanMenungguCount = PertemuanBimbingan::where('dosen_id', $user->id)
                ->where('status', 'menunggu')
                ->count();

            $pertemuanSelesaiCount = PertemuanBimbingan::where('dosen_id', $user->id)
                ->where('status', 'selesai')
                ->count();

            $jadwalMendatang = JadwalKonsultasi::where('dosen_id', $user->id)
                ->where('status', 'aktif')
                ->whereDate('tanggal', '>=', Carbon::today()->toDateString())
                ->orderBy('tanggal', 'asc')
                ->orderBy('jam_mulai', 'asc')
                ->take(3)
                ->get();

            $pertemuanTerbaru = PertemuanBimbingan::where('dosen_id', $user->id)
                ->with(['mahasiswa'])
                ->orderBy('id', 'desc')
                ->take(5)
                ->get();

            return view('home', compact(
                'jadwalAktifCount',
                'pertemuanMenungguCount',
                'pertemuanSelesaiCount',
                'jadwalMendatang',
                'pertemuanTerbaru'
            ));
        }

        if ((method_exists($user, 'isMahasiswa') && $user->isMahasiswa()) || (isset($user->role) && $user->role === 'mahasiswa')) {
            $pertemuanMenungguCount = PertemuanBimbingan::where('mahasiswa_id', $user->id)
                ->where('status', 'menunggu')
                ->count();

            $pertemuanDisetujuiCount = PertemuanBimbingan::where('mahasiswa_id', $user->id)
                ->where('status', 'disetujui')
                ->count();

            $pertemuanSelesaiCount = PertemuanBimbingan::where('mahasiswa_id', $user->id)
                ->where('status', 'selesai')
                ->count();

            $pertemuanTerbaru = PertemuanBimbingan::where('mahasiswa_id', $user->id)
                ->with(['dosen'])
                ->orderBy('id', 'desc')
                ->take(5)
                ->get();

            $jadwalMendatang = PertemuanBimbingan::where('mahasiswa_id', $user->id)
                ->where('status', 'disetujui')
                ->whereDate('tanggal', '>=', Carbon::today()->toDateString())
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