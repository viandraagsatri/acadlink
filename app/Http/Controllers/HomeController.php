<?php

namespace App\Http\Controllers;

use App\Models\ArahanRevisi;
use App\Models\Bimbingan;
use App\Models\JadwalKonsultasi;
use App\Models\PertemuanBimbingan;
use App\Models\User;
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

            $pengajuanMenungguCount = Bimbingan::where('dosen_id', $user->id)
                ->where('status', 'pending')
                ->count();

            $pengajuanDisetujuiCount = Bimbingan::where('dosen_id', $user->id)
                ->where('status', 'approved')
                ->count();

            $pengajuanTerbaru = Bimbingan::with('mahasiswa')
                ->where('dosen_id', $user->id)
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

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
                'jadwalAktifCount',
                'pertemuanMenungguCount',
                'pertemuanSelesaiCount',
                'jadwalMendatang',
                'pertemuanTerbaru',

                'pengajuanMenungguCount',
                'pengajuanDisetujuiCount',
                'pengajuanTerbaru',
                'bimbinganMendatang'
            ));
        }

        if (
            (method_exists($user, 'isMahasiswa') && $user->isMahasiswa())
            || (isset($user->role) && $user->role === 'mahasiswa')
        ) {
            return $this->berandaMahasiswa($user);
        }

        return view('home');
    }

    private function berandaMahasiswa(User $user): View
    {
        $hariIni = Carbon::today();

        $jumlahPerStatus = PertemuanBimbingan::where('mahasiswa_id', $user->id)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $menungguCount = (int) ($jumlahPerStatus['menunggu'] ?? 0);
        $disetujuiCount = (int) ($jumlahPerStatus['disetujui'] ?? 0);
        $selesaiCount = (int) ($jumlahPerStatus['selesai'] ?? 0);

        $jadwalDisetujui = PertemuanBimbingan::where('mahasiswa_id', $user->id)
            ->where('status', 'disetujui')
            ->with('dosen')
            ->orderBy('tanggal', 'asc')
            ->orderBy('jam_mulai', 'asc')
            ->get();

        $jadwalMendatang = $jadwalDisetujui->filter(fn ($j) => $j->tanggal->gte($hariIni))->values();
        $jadwalTerlewat = $jadwalDisetujui->filter(fn ($j) => $j->tanggal->lt($hariIni))->values();

        $revisiMilikku = fn () => ArahanRevisi::whereHas('pertemuanBimbingan', function ($q) use ($user) {
            $q->where('mahasiswa_id', $user->id);
        });

        $revisiTotal = $revisiMilikku()->count();
        $revisiSelesaiCount = $revisiMilikku()->where('status', 'selesai')->count();
        $revisiBelumCount = $revisiTotal - $revisiSelesaiCount;
        $revisiProgress = $revisiTotal > 0 ? (int) round($revisiSelesaiCount / $revisiTotal * 100) : 0;

        $revisiAktif = $revisiMilikku()
            ->where('status', '!=', 'selesai')
            ->get()
            ->sortBy(fn ($r) => [
                $r->status === 'perlu_perbaikan' ? 0 : 1,
                $r->tenggat_waktu?->timestamp ?? PHP_INT_MAX,
            ])
            ->take(5)
            ->values();

        $pertemuanTerbaru = PertemuanBimbingan::where('mahasiswa_id', $user->id)
            ->with('dosen')
            ->orderBy('id', 'desc')
            ->take(5)
            ->get();

        return view('home', compact(
            'menungguCount',
            'disetujuiCount',
            'selesaiCount',
            'jadwalMendatang',
            'jadwalTerlewat',
            'revisiTotal',
            'revisiSelesaiCount',
            'revisiBelumCount',
            'revisiProgress',
            'revisiAktif',
            'pertemuanTerbaru'
        ));
    }
}
