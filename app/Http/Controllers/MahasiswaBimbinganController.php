<?php

namespace App\Http\Controllers;

use App\Models\ArahanRevisi;
use App\Models\Bimbingan;
use App\Models\PertemuanBimbingan;
use App\Models\PesanChat;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MahasiswaBimbinganController extends Controller
{
    public function dashboard()
    {
        return redirect()->route('home');
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
        $user = Auth::user();
        if (! $user->dosen_pa_id && ! $user->dosen_skripsi_id && ! $user->dosen_kp_id) {
            return back()->with('error', 'Anda belum memiliki dosen pembimbing sama sekali. Silakan hubungi admin.');
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
            'jadwal_bimbingan' => 'nullable|date',
        ]);

        $dosenYangBenar = null;
        if ($request->jenis_bimbingan == 'PA') {
            $dosenYangBenar = $mahasiswa->dosen_pa_id;
        } elseif ($request->jenis_bimbingan == 'skripsi') {
            $dosenYangBenar = $mahasiswa->dosen_skripsi_id;
        } elseif ($request->jenis_bimbingan == 'KP') {
            $dosenYangBenar = $mahasiswa->dosen_kp_id;
        }

        if ($request->dosen_id != $dosenYangBenar) {
            abort(403, 'Anda tidak diizinkan mengajukan bimbingan ke dosen ini.');
        }

        $bimbingan = Bimbingan::create([
            'mahasiswa_id' => $mahasiswa->id,
            'dosen_id' => $request->dosen_id,
            'jenis_bimbingan' => $request->jenis_bimbingan,
            'topik_pembahasan' => $request->topik_pembahasan,
            'jadwal_bimbingan' => $request->jadwal_bimbingan,
            'status' => 'pending',
        ]);

        $jenisPertemuan = match (strtolower((string) $request->jenis_bimbingan)) {
            'pa', 'akademik' => 'akademik',
            'skripsi' => 'skripsi',
            'kp', 'kerja_praktik' => 'kerja_praktik',
            default => 'skripsi',
        };

        $waktu = $request->jadwal_bimbingan ? Carbon::parse($request->jadwal_bimbingan) : null;

        PertemuanBimbingan::create([
            'bimbingan_id' => $bimbingan->id,
            'dosen_id' => $bimbingan->dosen_id,
            'mahasiswa_id' => $bimbingan->mahasiswa_id,
            'jenis_bimbingan' => $jenisPertemuan,
            'judul_topik' => $bimbingan->topik_pembahasan,
            'deskripsi_singkat' => $bimbingan->topik_pembahasan,
            'metode' => 'tatap_muka',
            'tanggal' => $waktu ? $waktu->toDateString() : now()->toDateString(),
            'jam_mulai' => $waktu ? $waktu->format('H:i:s') : null,
            'status' => 'menunggu',
        ]);

        return redirect()->route('mahasiswa.jadwal')->with('success', 'Pengajuan bimbingan berhasil dikirim!');
    }

    public function edit($id)
    {
        $bimbingan = Bimbingan::where('id', $id)
            ->where('mahasiswa_id', auth()->id())
            ->firstOrFail();

        if ($bimbingan->status !== 'pending') {
            return redirect()->route('mahasiswa.jadwal')->with('error', 'Pengajuan yang sudah diproses tidak dapat diedit.');
        }

        return view('mahasiswa.edit-form-bimbingan', compact('bimbingan'));
    }

    public function update(Request $request, $id)
    {
        $mahasiswa = Auth::user();

        $bimbingan = Bimbingan::where('id', $id)
            ->where('mahasiswa_id', $mahasiswa->id)
            ->firstOrFail();

        if ($bimbingan->status !== 'pending') {
            return redirect()->route('mahasiswa.jadwal')->with('error', 'Pengajuan yang sudah diproses tidak dapat diedit.');
        }

        $request->validate([
            'topik_pembahasan' => 'required|string|max:255',
            'jadwal_bimbingan' => 'nullable|date',
        ]);

        $bimbingan->update([
            'topik_pembahasan' => $request->topik_pembahasan,
            'jadwal_bimbingan' => $request->jadwal_bimbingan,
        ]);

        if ($bimbingan->pertemuan) {
            $waktu = $request->jadwal_bimbingan ? Carbon::parse($request->jadwal_bimbingan) : null;
            $bimbingan->pertemuan->update([
                'judul_topik' => $request->topik_pembahasan,
                'deskripsi_singkat' => $request->topik_pembahasan,
                'tanggal' => $waktu ? $waktu->toDateString() : $bimbingan->pertemuan->tanggal,
                'jam_mulai' => $waktu ? $waktu->format('H:i:s') : null,
            ]);
        }

        return redirect()->route('mahasiswa.jadwal')->with('success', 'Pengajuan berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $bimbingan = Bimbingan::where('id', $id)
            ->where('mahasiswa_id', auth()->id())
            ->firstOrFail();

        if ($bimbingan->status !== 'pending') {
            return redirect()->route('mahasiswa.jadwal')->with('error', 'Pengajuan yang sudah diproses tidak dapat dibatalkan.');
        }

        if ($bimbingan->pertemuan) {
            $bimbingan->pertemuan->delete();
        }

        $bimbingan->delete();

        return redirect()->route('mahasiswa.jadwal')->with('success', 'Pengajuan bimbingan berhasil dibatalkan.');
    }

    public function chat($id)
    {
        $bimbingan = Bimbingan::with(['pesanChats.pengirim', 'dosen'])
            ->where('id', $id)
            ->where('mahasiswa_id', Auth::id())
            ->firstOrFail();

        // chat cuma boleh dibuka klo statusnya approved
        if ($bimbingan->status !== 'approved') {
            return redirect()->route('mahasiswa.jadwal')->with('error', 'Ruang chat belum tersedia untuk pengajuan ini.');
        }

        return view('mahasiswa.chat-bimbingan', compact('bimbingan'));
    }

    public function kirimPesan(Request $request, $id)
    {
        $bimbingan = Bimbingan::where('id', $id)
            ->where('mahasiswa_id', Auth::id())
            ->firstOrFail();

        if ($bimbingan->status !== 'approved') {
            abort(403, 'Akses ditolak.');
        }

        $request->validate([
            'pesan' => 'required|string',
        ]);

        PesanChat::create([
            'bimbingan_id' => $bimbingan->id,
            'pengirim_id' => Auth::id(),
            'pesan' => $request->pesan,
        ]);

        return back();
    }

    public function riwayat(Request $request)
    {
        $mahasiswaId = Auth::id();

        $query = PertemuanBimbingan::where('mahasiswa_id', $mahasiswaId)
            ->whereIn('status', ['selesai', 'ditolak'])
            ->with(['dosen', 'arahanRevisis']);

        $status = $request->query('status');
        if (is_string($status) && in_array($status, ['selesai', 'ditolak'], true)) {
            $query->where('status', $status);
        }

        $jenis = $request->query('jenis_bimbingan');
        if (is_string($jenis) && in_array($jenis, ['akademik', 'skripsi', 'kerja_praktik'], true)) {
            $query->where('jenis_bimbingan', $jenis);
        }

        $search = $request->query('search');
        if (is_string($search) && trim($search) !== '') {
            $keyword = '%'.mb_strtolower(trim($search)).'%';
            $query->whereRaw('LOWER(judul_topik) LIKE ?', [$keyword]);
        }

        $riwayatList = $query->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        $selesaiCount = PertemuanBimbingan::where('mahasiswa_id', $mahasiswaId)->where('status', 'selesai')->count();
        $ditolakCount = PertemuanBimbingan::where('mahasiswa_id', $mahasiswaId)->where('status', 'ditolak')->count();

        return view('mahasiswa.riwayat', compact('riwayatList', 'selesaiCount', 'ditolakCount'));
    }

    public function showRiwayat($id)
    {
        $pertemuan = PertemuanBimbingan::where('mahasiswa_id', Auth::id())
            ->with([
                'dosen',
                'arahanRevisis' => fn ($q) => $q->orderBy('tenggat_waktu')->orderBy('id'),
            ])
            ->find($id);

        if (! $pertemuan) {
            return redirect()->route('mahasiswa.riwayat')->with('error', 'Data bimbingan tidak ditemukan.');
        }

        $pertemuanSebelumnya = PertemuanBimbingan::where('mahasiswa_id', Auth::id())
            ->where('dosen_id', $pertemuan->dosen_id)
            ->where('id', '!=', $pertemuan->id)
            ->where('status', 'selesai')
            ->whereDate('tanggal', '<=', $pertemuan->tanggal->toDateString())
            ->with('arahanRevisis')
            ->orderBy('tanggal', 'desc')
            ->get();

        return view('mahasiswa.riwayat-detail', compact('pertemuan', 'pertemuanSebelumnya'));
    }

    public function revisi()
    {
        $revisis = $this->revisiMilikku()
            ->with('pertemuanBimbingan.dosen')
            ->get();

        $revisiPending = $revisis
            ->where('status', '!=', 'selesai')
            ->sortBy(fn ($r) => [
                $r->status === 'perlu_perbaikan' ? 0 : 1,
                $r->tenggat_waktu?->timestamp ?? PHP_INT_MAX,
            ])
            ->values();

        $revisiDone = $revisis
            ->where('status', 'selesai')
            ->sortByDesc('updated_at')
            ->values();

        $total = $revisis->count();
        $jumlahSelesai = $revisiDone->count();
        $progress = $total > 0 ? (int) round($jumlahSelesai / $total * 100) : 0;

        return view('mahasiswa.revisi', compact('revisiPending', 'revisiDone', 'total', 'jumlahSelesai', 'progress'));
    }

    public function updateRevisiProgress(Request $request, $id)
    {
        $request->validate([
            'status' => ['required', 'in:belum_selesai,sedang_dikerjakan,selesai'],
            'catatan_mahasiswa' => ['required_if:status,selesai', 'nullable', 'string', 'max:1000'],
            'file_progres' => ['nullable', 'file', 'mimes:pdf,docx,zip', 'max:5120'],
        ], [
            'status.required' => 'Status pengerjaan revisi wajib dipilih.',
            'status.in' => 'Status pengerjaan revisi tidak valid.',
            'catatan_mahasiswa.required_if' => 'Tulis catatan singkat sebelum menandai revisi selesai.',
            'catatan_mahasiswa.max' => 'Catatan maksimal 1000 karakter.',
            'file_progres.mimes' => 'File progres harus berformat PDF, DOCX, atau ZIP.',
            'file_progres.max' => 'Ukuran file progres maksimal 5 MB.',
        ]);

        $revisi = $this->revisiMilikku()->findOrFail($id);

        if ($revisi->status === 'selesai') {
            return back()->with('error', 'Progres revisi yang sudah selesai tidak dapat diperbarui.');
        }

        $disk = Storage::disk('public');
        $fileLama = $revisi->file_progres;
        $fileBaru = null;

        if ($request->hasFile('file_progres')) {
            $fileBaru = $request->file('file_progres')->store('revisi_files', 'public');

            if ($fileBaru === false) {
                return back()->withInput()->with('error', 'File progres gagal disimpan. Perubahan progres tidak diterapkan.');
            }

            $revisi->file_progres = $fileBaru;
        }

        $revisi->status = $request->status;
        $revisi->catatan_mahasiswa = $request->catatan_mahasiswa;

        try {
            $revisi->save();
        } catch (\Throwable $exception) {
            if ($fileBaru && ! $disk->delete($fileBaru)) {
                Log::warning('Failed to clean up uploaded revision file after database update failed.', [
                    'revisi_id' => $revisi->id,
                    'file_progres' => $fileBaru,
                ]);
            }

            throw $exception;
        }

        if ($fileBaru && $fileLama && ! $disk->delete($fileLama)) {
            Log::warning('Failed to delete replaced revision progress file.', [
                'revisi_id' => $revisi->id,
                'file_progres' => $fileLama,
            ]);

            return back()->with('warning', 'Progres berhasil diperbarui, tetapi file progres lama gagal dihapus.');
        }

        return redirect()->back()->with('success', 'Progress revisi berhasil diperbarui!');
    }

    private function revisiMilikku(): Builder
    {
        return ArahanRevisi::whereHas('pertemuanBimbingan', function ($q) {
            $q->where('mahasiswa_id', Auth::id());
        });
    }
}
