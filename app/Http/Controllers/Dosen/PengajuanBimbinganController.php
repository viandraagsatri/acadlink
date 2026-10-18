<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Bimbingan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class PengajuanBimbinganController extends Controller
{
    public function index(Request $request): View
    {
        $query = Bimbingan::with('mahasiswa')
            ->where('dosen_id', Auth::id());

        if (in_array($request->status, [
            'pending', 'approved', 'rejected', 'completed'
        ])) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('topik_pembahasan', 'ilike', "%{$search}%")
                    ->orWhereHas('mahasiswa', function ($m) use ($search) {
                        $m->where('name', 'ilike', "%{$search}%")
                            ->orWhere('nim_nip', 'ilike', "%{$search}%");
                    });
            });
        }

        $pengajuans = $query->latest()->paginate(10)->withQueryString();

        $totalPending = Bimbingan::where('dosen_id', Auth::id())->where('status', 'pending')->count();
        $totalApproved = Bimbingan::where('dosen_id', Auth::id())->where('status', 'approved')->count();
        $totalRejected = Bimbingan::where('dosen_id', Auth::id())->where('status', 'rejected')->count();
        $totalCompleted = Bimbingan::where('dosen_id', Auth::id())->where('status', 'completed')->count();

        return view('dosen.pengajuan.index', compact(
            'pengajuans', 'totalPending', 'totalApproved',
            'totalRejected', 'totalCompleted'
        ));
    }

    public function show(int $id): View
    {
        $pengajuan = Bimbingan::with(['mahasiswa', 'dosen'])
            ->where('dosen_id', Auth::id())
            ->findOrFail($id);

        return view('dosen.pengajuan.show', compact('pengajuan'));
    }

    public function approve(int $id): RedirectResponse
    {
        $pengajuan = Bimbingan::where('dosen_id', Auth::id())
            ->where('status', 'pending')
            ->findOrFail($id);

        $pengajuan->update(['status' => 'approved']);

        return redirect()->route('dosen.pengajuan.show', $id)
            ->with('success', 'Pengajuan berhasil disetujui. Silakan tentukan jadwal.');
    }

    public function reject(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'catatan_dosen' => ['required', 'string', 'max:2000'],
        ]);

        $pengajuan = Bimbingan::where('dosen_id', Auth::id())
            ->where('status', 'pending')
            ->findOrFail($id);

        $pengajuan->update([
            'status' => 'rejected',
            'catatan_dosen' => $request->catatan_dosen,
        ]);

        return redirect()->route('dosen.pengajuan.index')
            ->with('success', 'Pengajuan berhasil ditolak.');
    }

    public function schedule(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'jadwal_bimbingan' => ['required', 'date', 'after:now'],
            'lokasi_atau_link' => ['required', 'string', 'max:255'],
        ]);

        $pengajuan = Bimbingan::where('dosen_id', Auth::id())
            ->where('status', 'approved')
            ->findOrFail($id);

        $bentrok = Bimbingan::where('dosen_id', Auth::id())
            ->where('id', '!=', $id)
            ->where('status', 'approved')
            ->where('jadwal_bimbingan', $request->jadwal_bimbingan)
            ->exists();

        if ($bentrok) {
            return back()->withInput()->withErrors([
                'jadwal_bimbingan' => 'Dosen sudah memiliki bimbingan pada waktu tersebut.',
            ]);
        }

        $pengajuan->update([
            'jadwal_bimbingan' => $request->jadwal_bimbingan,
            'lokasi_atau_link' => $request->lokasi_atau_link,
        ]);

        return back()->with('success', 'Jadwal berhasil disimpan.');
    }

    public function complete(int $id): RedirectResponse
    {
        $pengajuan = Bimbingan::where('dosen_id', Auth::id())
            ->where('status', 'approved')
            ->whereNotNull('jadwal_bimbingan')
            ->findOrFail($id);

        $pengajuan->update(['status' => 'completed']);

        return redirect()->route('dosen.pengajuan.index')
            ->with('success', 'Bimbingan berhasil ditandai selesai.');
    }
}
