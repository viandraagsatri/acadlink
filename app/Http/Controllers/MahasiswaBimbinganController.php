<?php

namespace App\Http\Controllers;

use App\Models\Bimbingan;
use App\Models\Revisi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MahasiswaBimbinganController extends Controller
{
    public function dashboard()
    {
        $mahasiswaId = Auth::id();

        $jadwalMendatang = Bimbingan::where('mahasiswa_id', $mahasiswaId)
            ->where('status', 'approved')
            ->where('jadwal_bimbingan', '>=', now())
            ->first();

        $totalRevisiPending = Revisi::whereHas('bimbingan', function ($query) use ($mahasiswaId) {
            $query->where('mahasiswa_id', $mahasiswaId);
        })->where('status_revisi', 'pending')->count();

        $totalSelesai = Bimbingan::where('mahasiswa_id', $mahasiswaId)
            ->where('status', 'completed')
            ->count();

        return view('mahasiswa.dashboard', compact('jadwalMendatang', 'totalRevisiPending', 'totalSelesai'));
    }

    public function jadwal()
    {
        $jadwalList = Bimbingan::with('dosen')
            ->where('mahasiswa_id', Auth::id())
            ->where('status', 'approved')
            ->orderBy('jadwal_bimbingan', 'asc')
            ->get();

        return view('mahasiswa.jadwal', compact('jadwalList'));
    }

    public function riwayat()
    {
        $riwayatList = Bimbingan::with(['dosen', 'revisis'])
            ->where('mahasiswa_id', Auth::id())
            ->whereIn('status', ['completed', 'rejected'])
            ->latest()
            ->get();

        return view('mahasiswa.riwayat', compact('riwayatList'));
    }

    public function revisi()
    {
        $mahasiswaId = Auth::id();

        $revisiPending = Revisi::whereHas('bimbingan', function ($q) use ($mahasiswaId) {
            $q->where('mahasiswa_id', $mahasiswaId);
        })->where('status_revisi', 'pending')->get();

        $revisiDone = Revisi::whereHas('bimbingan', function ($q) use ($mahasiswaId) {
            $q->where('mahasiswa_id', $mahasiswaId);
        })->where('status_revisi', 'done')->get();

        return view('mahasiswa.revisi', compact('revisiPending', 'revisiDone'));
    }

    public function updateRevisiProgress(Request $request, $id)
    {
        $request->validate([
            'catatan_mahasiswa' => 'required|string',
            'file_progres' => 'nullable|file|mimes:pdf,docx,zip|max:5120',
        ]);

        $revisi = Revisi::findOrFail($id);

        $data = [
            'catatan_mahasiswa' => $request->catatan_mahasiswa,
            'status_revisi' => 'done',
        ];

        if ($request->hasFile('file_progres')) {
            $path = $request->file('file_progres')->store('revisi_files', 'public');
            $data['file_progres'] = $path;
        }

        $revisi->update($data);

        return redirect()->back()->with('success', 'Progress revisi berhasil diperbarui!');
    }
}
