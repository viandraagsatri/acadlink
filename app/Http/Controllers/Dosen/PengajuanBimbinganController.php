<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Bimbingan;
use App\Models\JadwalKonsultasi;
use App\Models\PertemuanBimbingan;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PengajuanBimbinganController extends Controller
{
    public function index(Request $request): View
    {
        $query = Bimbingan::with('mahasiswa')
            ->where('dosen_id', Auth::id());

        if (in_array($request->status, [
            'pending', 'approved', 'rejected', 'completed',
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

        $pengajuans = $query->latest()
            ->paginate(10)
            ->withQueryString();

        $totalPending = Bimbingan::where('dosen_id', Auth::id())
            ->where('status', 'pending')
            ->count();

        $totalApproved = Bimbingan::where('dosen_id', Auth::id())
            ->where('status', 'approved')
            ->count();

        $totalRejected = Bimbingan::where('dosen_id', Auth::id())
            ->where('status', 'rejected')
            ->count();

        $totalCompleted = Bimbingan::where('dosen_id', Auth::id())
            ->where('status', 'completed')
            ->count();

        return view('dosen.pengajuan.index', compact(
            'pengajuans',
            'totalPending',
            'totalApproved',
            'totalRejected',
            'totalCompleted'
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

        DB::transaction(function () use ($pengajuan) {
            $pengajuan->update([
                'status' => 'approved',
            ]);

            // Langsung sinkronkan ke jadwal & pertemuan dosen (mengubah 'menunggu' menjadi 'disetujui')
            $this->syncPertemuanAndJadwal($pengajuan);
        });

        return redirect()
            ->route('dosen.pengajuan.show', $id)
            ->with(
                'success',
                'Pengajuan berhasil disetujui. Silakan tentukan jadwal jika ingin menyesuaikan waktu atau ruangan.'
            );
    }

    public function reject(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'catatan_dosen' => ['required', 'string', 'max:2000'],
        ]);

        $pengajuan = Bimbingan::where('dosen_id', Auth::id())
            ->where('status', 'pending')
            ->findOrFail($id);

        DB::transaction(function () use ($pengajuan, $validated) {
            $pengajuan->update([
                'status' => 'rejected',
                'catatan_dosen' => $validated['catatan_dosen'],
            ]);

            if ($pengajuan->pertemuan) {
                $pengajuan->pertemuan->update([
                    'status' => 'ditolak',
                    'alasan_penolakan' => $validated['catatan_dosen'],
                ]);

                if ($pengajuan->pertemuan->jadwalKonsultasi) {
                    $pengajuan->pertemuan->jadwalKonsultasi->update([
                        'status' => 'nonaktif',
                    ]);
                }
            }
        });

        return redirect()
            ->route('dosen.pengajuan.index')
            ->with('success', 'Pengajuan berhasil ditolak.');
    }

    public function schedule(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'jadwal_bimbingan' => ['required', 'date', 'after:now'],
            'lokasi_atau_link' => ['required', 'string', 'max:255'],
        ]);

        $pengajuan = Bimbingan::where('dosen_id', Auth::id())
            ->where('status', 'approved')
            ->findOrFail($id);

        $waktu = Carbon::parse($validated['jadwal_bimbingan']);
        $lokasi = $validated['lokasi_atau_link'];

        // Periksa apakah dosen sudah memiliki bimbingan pada waktu yang sama.
        $bentrok = Bimbingan::where('dosen_id', Auth::id())
            ->where('id', '!=', $id)
            ->where('status', 'approved')
            ->where('jadwal_bimbingan', $waktu)
            ->exists();

        if ($bentrok) {
            return back()
                ->withInput()
                ->withErrors([
                    'jadwal_bimbingan' => 'Dosen sudah memiliki bimbingan pada waktu tersebut.',
                ]);
        }

        DB::transaction(function () use (
            $pengajuan,
            $waktu,
            $lokasi
        ) {
            // Simpan jadwal pada tabel bimbingans.
            $pengajuan->update([
                'jadwal_bimbingan' => $waktu,
                'lokasi_atau_link' => $lokasi,
            ]);

            // Sinkronkan ke pertemuan & jadwal dosen
            $this->syncPertemuanAndJadwal($pengajuan);
        });

        return redirect()
            ->route('dosen.pengajuan.show', $id)
            ->with(
                'success',
                'Jadwal berhasil disimpan dan otomatis masuk ke jadwal bimbingan dosen.'
            );
    }

    public function complete(int $id): RedirectResponse
    {
        $pengajuan = Bimbingan::where('dosen_id', Auth::id())
            ->where('status', 'approved')
            ->whereNotNull('jadwal_bimbingan')
            ->findOrFail($id);

        DB::transaction(function () use ($pengajuan) {
            $pengajuan->update([
                'status' => 'completed',
            ]);

            // Sinkronkan status pertemuan jika datanya tersedia.
            if ($pengajuan->pertemuan) {
                $pengajuan->pertemuan->update([
                    'status' => 'selesai',
                    'tanggal_bimbingan_selesai' => now(),
                ]);

                if ($pengajuan->pertemuan->jadwalKonsultasi) {
                    $pengajuan->pertemuan->jadwalKonsultasi->update([
                        'status' => 'selesai',
                    ]);
                }
            }
        });

        return redirect()
            ->route('dosen.pengajuan.index')
            ->with('success', 'Bimbingan berhasil ditandai selesai.');
    }

    /**
     * Sinkronkan pengajuan bimbingan ke jadwal konsultasi dan pertemuan bimbingan dosen.
     */
    protected function syncPertemuanAndJadwal(Bimbingan $pengajuan): ?PertemuanBimbingan
    {
        $waktu = $pengajuan->jadwal_bimbingan ? Carbon::parse($pengajuan->jadwal_bimbingan) : null;
        $lokasi = $pengajuan->lokasi_atau_link ?: ($pengajuan->status === 'approved' ? 'Ruang Dosen' : null);

        $jenisPertemuan = match (strtolower((string) $pengajuan->jenis_bimbingan)) {
            'pa', 'akademik' => 'akademik',
            'skripsi' => 'skripsi',
            'kp', 'kerja_praktik' => 'kerja_praktik',
            default => 'skripsi',
        };

        $jenisJadwal = match ($jenisPertemuan) {
            'akademik' => 'Akademik',
            'kerja_praktik' => 'Kerja Praktik',
            default => 'Skripsi',
        };

        $metode = filter_var($lokasi, FILTER_VALIDATE_URL) ? 'online' : 'tatap_muka';
        $jamMulai = $waktu ? $waktu->format('H:i:s') : null;
        $jamSelesai = $waktu ? $waktu->copy()->addHour()->format('H:i:s') : null;
        $hari = $waktu ? $waktu->locale('id')->isoFormat('dddd') : null;

        $jadwalKonsultasiId = null;
        if (in_array($pengajuan->status, ['approved', 'completed']) && $waktu && $jamMulai && $jamSelesai) {
            $jadwalKonsultasi = JadwalKonsultasi::updateOrCreate(
                [
                    'dosen_id' => $pengajuan->dosen_id,
                    'tanggal' => $waktu->toDateString(),
                    'jam_mulai' => $jamMulai,
                ],
                [
                    'jenis_bimbingan' => $jenisJadwal,
                    'hari' => $hari,
                    'jam_selesai' => $jamSelesai,
                    'metode' => $metode,
                    'ruangan_atau_link' => $lokasi ?: 'Ruang Dosen',
                    'kuota' => 1,
                    'keterangan' => 'Bimbingan Mahasiswa: '.($pengajuan->mahasiswa->name ?? 'Mahasiswa').' ('.$pengajuan->topik_pembahasan.')',
                    'status' => $pengajuan->status === 'completed' ? 'selesai' : 'aktif',
                ]
            );
            $jadwalKonsultasiId = $jadwalKonsultasi->id;
        }

        $statusPertemuan = match ($pengajuan->status) {
            'approved' => 'disetujui',
            'completed' => 'selesai',
            'rejected' => 'ditolak',
            default => 'menunggu',
        };

        $tanggal = $waktu ? $waktu->toDateString() : ($pengajuan->created_at ? $pengajuan->created_at->toDateString() : now()->toDateString());

        // Sinkronkan ke PertemuanBimbingan agar masuk ke "Pertemuan & Bimbingan" dosen
        return PertemuanBimbingan::updateOrCreate(
            [
                'bimbingan_id' => $pengajuan->id,
            ],
            [
                'dosen_id' => $pengajuan->dosen_id,
                'mahasiswa_id' => $pengajuan->mahasiswa_id,
                'jadwal_konsultasi_id' => $jadwalKonsultasiId,
                'jenis_bimbingan' => $jenisPertemuan,
                'judul_topik' => $pengajuan->topik_pembahasan,
                'deskripsi_singkat' => $pengajuan->topik_pembahasan,
                'metode' => $metode,
                'tanggal' => $tanggal,
                'jam_mulai' => $jamMulai,
                'jam_selesai' => $jamSelesai,
                'tempat' => $lokasi,
                'status' => $statusPertemuan,
                'tanggal_bimbingan_selesai' => $statusPertemuan === 'selesai' ? ($pengajuan->updated_at ?? now()) : null,
            ]
        );
    }
}
