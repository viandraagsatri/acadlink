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

        if ($user && $user->isDosen()) {
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

        return view('home');
    }
}
