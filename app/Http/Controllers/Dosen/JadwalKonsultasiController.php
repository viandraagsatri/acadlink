<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\JadwalKonsultasi;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Throwable;

class JadwalKonsultasiController extends Controller
{
    public function index(Request $request): View
    {
        $dosenId = Auth::id();
        $query = JadwalKonsultasi::where('dosen_id', $dosenId)->withCount(['pertemuanBimbingans']);

        if ($request->filled('status') && $request->status !== 'semua') {
            $query->where('status', $request->status);
        }

        if ($request->filled('jenis_bimbingan') && $request->jenis_bimbingan !== 'semua') {
            $query->where('jenis_bimbingan', $request->jenis_bimbingan);
        }

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->tanggal);
        }

        $jadwals = $query->orderBy('tanggal', 'desc')
            ->orderBy('jam_mulai', 'asc')
            ->paginate(10)
            ->withQueryString();

        $totalJadwal = JadwalKonsultasi::where('dosen_id', $dosenId)->count();
        $totalAktif = JadwalKonsultasi::where('dosen_id', $dosenId)->where('status', 'aktif')->count();

        return view('dosen.jadwal.index', compact('jadwals', 'totalJadwal', 'totalAktif'));
    }

    public function create(): View
    {
        return view('dosen.jadwal.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'jenis_bimbingan' => ['required', 'string', 'max:50'],
            'tanggal' => ['required', 'date'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'metode' => ['required', 'in:tatap_muka,online,keduanya'],
            'ruangan_atau_link' => ['required', 'string', 'max:255'],
            'kuota' => ['required', 'integer', 'min:1', 'max:50'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ], [
            'jenis_bimbingan.required' => 'Jenis bimbingan wajib dipilih.',
            'tanggal.required' => 'Tanggal konsultasi wajib diisi.',
            'jam_mulai.required' => 'Jam mulai wajib diisi.',
            'jam_selesai.required' => 'Jam selesai wajib diisi.',
            'jam_selesai.after' => 'Jam selesai harus setelah jam mulai.',
            'metode.required' => 'Metode konsultasi wajib dipilih.',
            'ruangan_atau_link.required' => 'Lokasi ruangan atau link pertemuan wajib diisi.',
            'kuota.required' => 'Kuota mahasiswa wajib diisi.',
            'kuota.min' => 'Kuota minimal 1 mahasiswa.',
        ]);

        try {
            $dosenId = Auth::id();
            $jamMulai = Carbon::createFromFormat('H:i', $request->jam_mulai)->format('H:i:s');
            $jamSelesai = Carbon::createFromFormat('H:i', $request->jam_selesai)->format('H:i:s');

            $bentrok = JadwalKonsultasi::where('dosen_id', $dosenId)
                ->whereDate('tanggal', $request->tanggal)
                ->where('status', '!=', 'nonaktif')
                ->where(function ($q) use ($jamMulai, $jamSelesai) {
                    $q->where('jam_mulai', '<', $jamSelesai)
                        ->where('jam_selesai', '>', $jamMulai);
                })
                ->first();

            if ($bentrok) {
                return back()
                    ->withInput()
                    ->with('error', "Jadwal bentrok! Anda telah memiliki jadwal aktif pada tanggal {$request->tanggal} pukul {$bentrok->jam_mulai_format} - {$bentrok->jam_selesai_format} ({$bentrok->ruangan_atau_link}). Silakan tentukan waktu lain.");
            }

            $carbonDate = Carbon::parse($request->tanggal)->locale('id');
            $hari = $carbonDate->isoFormat('dddd');

            JadwalKonsultasi::create([
                'dosen_id' => $dosenId,
                'jenis_bimbingan' => $request->jenis_bimbingan,
                'hari' => $hari,
                'tanggal' => $request->tanggal,
                'jam_mulai' => $jamMulai,
                'jam_selesai' => $jamSelesai,
                'metode' => $request->metode,
                'ruangan_atau_link' => $request->ruangan_atau_link,
                'kuota' => $request->kuota,
                'keterangan' => $request->keterangan,
                'status' => 'aktif',
            ]);

            return redirect()
                ->route('dosen.jadwal.index')
                ->with('success', 'Jadwal konsultasi baru berhasil ditambahkan.');
        } catch (Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Terjadi kendala saat menyimpan jadwal. Silakan periksa kembali data Anda.');
        }
    }

    public function edit(int $id): View|RedirectResponse
    {
        $jadwal = JadwalKonsultasi::where('dosen_id', Auth::id())->find($id);

        if (! $jadwal) {
            return redirect()
                ->route('dosen.jadwal.index')
                ->with('error', 'Jadwal konsultasi tidak ditemukan.');
        }

        return view('dosen.jadwal.edit', compact('jadwal'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $jadwal = JadwalKonsultasi::where('dosen_id', Auth::id())->find($id);

        if (! $jadwal) {
            return redirect()
                ->route('dosen.jadwal.index')
                ->with('error', 'Jadwal konsultasi tidak ditemukan.');
        }

        $request->validate([
            'jenis_bimbingan' => ['required', 'string', 'max:50'],
            'tanggal' => ['required', 'date'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'metode' => ['required', 'in:tatap_muka,online,keduanya'],
            'ruangan_atau_link' => ['required', 'string', 'max:255'],
            'kuota' => ['required', 'integer', 'min:1', 'max:50'],
            'status' => ['required', 'in:aktif,nonaktif,selesai'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ], [
            'jenis_bimbingan.required' => 'Jenis bimbingan wajib dipilih.',
            'tanggal.required' => 'Tanggal konsultasi wajib diisi.',
            'jam_mulai.required' => 'Jam mulai wajib diisi.',
            'jam_selesai.required' => 'Jam selesai wajib diisi.',
            'jam_selesai.after' => 'Jam selesai harus setelah jam mulai.',
            'metode.required' => 'Metode konsultasi wajib dipilih.',
            'ruangan_atau_link.required' => 'Lokasi ruangan atau link pertemuan wajib diisi.',
            'kuota.required' => 'Kuota mahasiswa wajib diisi.',
            'status.required' => 'Status jadwal wajib dipilih.',
        ]);

        try {
            $dosenId = Auth::id();
            $jamMulai = Carbon::createFromFormat('H:i', $request->jam_mulai)->format('H:i:s');
            $jamSelesai = Carbon::createFromFormat('H:i', $request->jam_selesai)->format('H:i:s');

            if ($request->status !== 'nonaktif') {
                $bentrok = JadwalKonsultasi::where('dosen_id', $dosenId)
                    ->where('id', '!=', $jadwal->id)
                    ->whereDate('tanggal', $request->tanggal)
                    ->where('status', '!=', 'nonaktif')
                    ->where(function ($q) use ($jamMulai, $jamSelesai) {
                        $q->where('jam_mulai', '<', $jamSelesai)
                            ->where('jam_selesai', '>', $jamMulai);
                    })
                    ->first();

                if ($bentrok) {
                    return back()
                        ->withInput()
                        ->with('error', "Perubahan jadwal bentrok! Jadwal lain Anda ada pada tanggal {$request->tanggal} pukul {$bentrok->jam_mulai_format} - {$bentrok->jam_selesai_format}.");
                }
            }

            $carbonDate = Carbon::parse($request->tanggal)->locale('id');
            $hari = $carbonDate->isoFormat('dddd');

            $jadwal->update([
                'jenis_bimbingan' => $request->jenis_bimbingan,
                'hari' => $hari,
                'tanggal' => $request->tanggal,
                'jam_mulai' => $jamMulai,
                'jam_selesai' => $jamSelesai,
                'metode' => $request->metode,
                'ruangan_atau_link' => $request->ruangan_atau_link,
                'kuota' => $request->kuota,
                'keterangan' => $request->keterangan,
                'status' => $request->status,
            ]);

            return redirect()
                ->route('dosen.jadwal.index')
                ->with('success', 'Jadwal konsultasi berhasil diperbarui.');
        } catch (Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Terjadi kendala saat memperbarui jadwal.');
        }
    }

    public function destroy(int $id): RedirectResponse
    {
        try {
            $jadwal = JadwalKonsultasi::where('dosen_id', Auth::id())->find($id);

            if (! $jadwal) {
                return redirect()
                    ->route('dosen.jadwal.index')
                    ->with('error', 'Jadwal konsultasi tidak ditemukan.');
            }

            $adaMahasiswa = $jadwal->pertemuanBimbingans()
                ->whereIn('status', ['menunggu', 'disetujui', 'selesai'])
                ->exists();

            if ($adaMahasiswa) {
                return back()->with('error', 'Jadwal tidak dapat dihapus karena sudah ada mahasiswa yang terdaftar pada jadwal ini. Anda dapat mengubah statusnya menjadi nonaktif.');
            }

            $jadwal->delete();

            return redirect()
                ->route('dosen.jadwal.index')
                ->with('success', 'Jadwal konsultasi berhasil dihapus.');
        } catch (Throwable $e) {
            return back()->with('error', 'Gagal menghapus jadwal konsultasi.');
        }
    }
}
