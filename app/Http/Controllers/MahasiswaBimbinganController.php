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
            ->whereIn('status', ['approved', 'pending'])
            ->orderBy('jadwal_bimbingan', 'desc')
            ->get();

        return view('mahasiswa.jadwal', compact('jadwalList'));
    }

    public function create()
    {
        //cek udah punya dospem belum
        if (!Auth::user()->dosen_pembimbing_id) {
            return back()->with('error', 'Anda belum memiliki dosen pembimbing.');
        }

        return view('mahasiswa.form-bimbingan');
    }

    public function store(Request $request)
    {
        $mahasiswa = Auth::user();

        $request->validate([
            'jenis_bimbingan' => 'required|in:PA,skripsi,KP',
            'topik_pembahasan' => 'required|string|max:255',
            'dosen_id' => 'required|exists:users,id',
            'jadwal_bimbingan' => 'nullable|date'
        ]);

        if ($request->dosen_id != $mahasiswa->dosen_pembimbing_id) {
            abort(403, 'Anda tidak diizinkan mengajukan bimbingan ke dosen ini.');
        }

        // simpan data ke tabel bimbingan
        Bimbingan::create([
            'mahasiswa_id' => $mahasiswa->id,
            'dosen_id' => $request->dosen_id,
            'jenis_bimbingan' => $request->jenis_bimbingan,
            'topik_pembahasan' => $request->topik_pembahasan,
            'jadwal_bimbingan' => $request->jadwal_bimbingan,
            'status' => 'pending', 
        ]);

        return redirect()->route('mahasiswa.jadwal')->with('success', 'Pengajuan bimbingan berhasil dikirim!');
    }

    public function edit($id) //nampilin form lama
    {
        $bimbingan = Bimbingan::where('id', $id)
            ->where('mahasiswa_id', auth()->id())
            ->firstOrFail();

        if ($bimbingan->status !== 'pending') {
            return redirect()->route('mahasiswa.jadwal')->with('error', 'Pengajuan yang sudah diproses tidak dapat diedit.');
        }

        return view('mahasiswa.edit-form-bimbingan', compact('bimbingan'));
    }

    public function update(Request $request, $id) //simpen perubahan ke db
    {
        $bimbingan = Bimbingan::where('id', $id)
            ->where('mahasiswa_id', auth()->id())
            ->firstOrFail();

        if ($bimbingan->status !== 'pending') {
            return redirect()->route('mahasiswa.jadwal')->with('error', 'Pengajuan yang sudah diproses tidak dapat diedit.');
        }

        $request->validate([
            'jenis_bimbingan' => 'required|in:PA,skripsi,KP',
            'topik_pembahasan' => 'required|string|max:255',
            'jadwal_bimbingan' => 'nullable|date'
        ]);

        //update data
        $bimbingan->update([
            'jenis_bimbingan' => $request->jenis_bimbingan,
            'topik_pembahasan' => $request->topik_pembahasan,
            'jadwal_bimbingan' => $request->jadwal_bimbingan,
        ]);

        return redirect()->route('mahasiswa.jadwal')->with('success', 'Pengajuan berhasil diperbarui!');
    }

    public function destroy($id) //hapus
    {
        $bimbingan = Bimbingan::where('id', $id)
            ->where('mahasiswa_id', auth()->id())
            ->firstOrFail();

        if ($bimbingan->status !== 'pending') {
            return redirect()->route('mahasiswa.jadwal')->with('error', 'Pengajuan yang sudah diproses tidak dapat dibatalkan.');
        }

        $bimbingan->delete();

        return redirect()->route('mahasiswa.jadwal')->with('success', 'Pengajuan bimbingan berhasil dibatalkan.');
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
