<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\ArahanRevisi;
use App\Models\Bimbingan;
use App\Models\JadwalKonsultasi;
use App\Models\PertemuanBimbingan;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Throwable;

class PertemuanBimbinganController extends Controller
{
    public function index(Request $request): View
    {
        $dosenId = Auth::id();

        // Pastikan seluruh bimbingan dosen (menunggu, disetujui, selesai) tersinkron ke pertemuan dosen
        $unsynced = Bimbingan::where('dosen_id', $dosenId)
            ->whereDoesntHave('pertemuan')
            ->get();

        foreach ($unsynced as $item) {
            $waktu = $item->jadwal_bimbingan ? Carbon::parse($item->jadwal_bimbingan) : null;
            $lokasi = $item->lokasi_atau_link ?: ($item->status === 'approved' ? 'Ruang Dosen' : null);

            $jenisPertemuan = match (strtolower((string) $item->jenis_bimbingan)) {
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

            $jkId = null;
            if (in_array($item->status, ['approved', 'completed']) && $waktu && $jamMulai && $jamSelesai) {
                $jk = JadwalKonsultasi::updateOrCreate(
                    [
                        'dosen_id' => $item->dosen_id,
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
                        'keterangan' => 'Bimbingan Mahasiswa: '.($item->mahasiswa->name ?? 'Mahasiswa').' ('.$item->topik_pembahasan.')',
                        'status' => $item->status === 'completed' ? 'selesai' : 'aktif',
                    ]
                );
                $jkId = $jk->id;
            }

            $statusPertemuan = match ($item->status) {
                'approved' => 'disetujui',
                'completed' => 'selesai',
                'rejected' => 'ditolak',
                default => 'menunggu',
            };

            PertemuanBimbingan::create([
                'bimbingan_id' => $item->id,
                'dosen_id' => $item->dosen_id,
                'mahasiswa_id' => $item->mahasiswa_id,
                'jadwal_konsultasi_id' => $jkId,
                'jenis_bimbingan' => $jenisPertemuan,
                'judul_topik' => $item->topik_pembahasan,
                'deskripsi_singkat' => $item->topik_pembahasan,
                'metode' => $metode,
                'tanggal' => $waktu ? $waktu->toDateString() : ($item->created_at ? $item->created_at->toDateString() : now()->toDateString()),
                'jam_mulai' => $jamMulai,
                'jam_selesai' => $jamSelesai,
                'tempat' => $lokasi,
                'status' => $statusPertemuan,
                'tanggal_bimbingan_selesai' => $item->status === 'completed' ? $item->updated_at : null,
            ]);
        }

        $query = PertemuanBimbingan::where('dosen_id', $dosenId)
            ->with(['mahasiswa', 'jadwalKonsultasi', 'arahanRevisis']);

        if ($request->filled('status') && $request->status !== 'semua') {
            $query->where('status', $request->status);
        }

        if ($request->filled('jenis_bimbingan') && $request->jenis_bimbingan !== 'semua') {
            $query->where('jenis_bimbingan', $request->jenis_bimbingan);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('judul_topik', 'ilike', "%{$search}%")
                    ->orWhereHas('mahasiswa', function ($m) use ($search) {
                        $m->where('name', 'ilike', "%{$search}%")
                            ->orWhere('nim_nip', 'ilike', "%{$search}%");
                    });
            });
        }

        $pertemuans = $query->orderBy('tanggal', 'desc')
            ->orderBy('jam_mulai', 'asc')
            ->paginate(10)
            ->withQueryString();

        $menungguCount = PertemuanBimbingan::where('dosen_id', $dosenId)->where('status', 'menunggu')->count();
        $disetujuiCount = PertemuanBimbingan::where('dosen_id', $dosenId)->where('status', 'disetujui')->count();
        $selesaiCount = PertemuanBimbingan::where('dosen_id', $dosenId)->where('status', 'selesai')->count();

        return view('dosen.pertemuan.index', compact(
            'pertemuans',
            'menungguCount',
            'disetujuiCount',
            'selesaiCount'
        ));
    }

    public function show(int $id): View|RedirectResponse
    {
        $dosenId = Auth::id();
        $pertemuan = PertemuanBimbingan::where('dosen_id', $dosenId)
            ->with(['mahasiswa', 'jadwalKonsultasi', 'arahanRevisis'])
            ->find($id);

        if (! $pertemuan) {
            return redirect()
                ->route('dosen.pertemuan.index')
                ->with('error', 'Data pertemuan bimbingan tidak ditemukan.');
        }

        if (in_array($pertemuan->status, ['menunggu', 'ditolak'])) {
            return redirect()
                ->route('dosen.pertemuan.index')
                ->with('warning', 'Detail dan catatan bimbingan hanya dapat diakses untuk bimbingan yang telah disetujui atau selesai. Persetujuan dan penolakan hanya dapat dilakukan melalui menu Pengajuan Bimbingan.');
        }

        $riwayatSebelumnya = PertemuanBimbingan::where('mahasiswa_id', $pertemuan->mahasiswa_id)
            ->where('dosen_id', $dosenId)
            ->where('id', '!=', $pertemuan->id)
            ->where('status', 'selesai')
            ->where(function ($query) use ($pertemuan) {
                $query->where('tanggal', '<', $pertemuan->tanggal)
                    ->orWhere(function ($q) use ($pertemuan) {
                        $q->where('tanggal', '=', $pertemuan->tanggal)
                            ->where('created_at', '<', $pertemuan->created_at);
                    });
            })
            ->with('arahanRevisis')
            ->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('dosen.pertemuan.show', compact('pertemuan', 'riwayatSebelumnya'));
    }

    public function konfirmasi(Request $request, int $id): RedirectResponse
    {
        try {
            $pertemuan = PertemuanBimbingan::where('dosen_id', Auth::id())->find($id);

            if (! $pertemuan) {
                return back()->with('error', 'Pertemuan bimbingan tidak ditemukan.');
            }

            $request->validate([
                'tempat' => ['nullable', 'string', 'max:255'],
            ]);

            $pertemuan->status = 'disetujui';
            if ($request->filled('tempat')) {
                $pertemuan->tempat = $request->tempat;
            }
            $pertemuan->save();

            // Sinkronkan ke bimbingans jika terhubung
            if ($pertemuan->bimbingan) {
                $pertemuan->bimbingan->update([
                    'status' => 'approved',
                    'lokasi_atau_link' => $pertemuan->tempat,
                ]);
            }

            // Sinkronkan ke JadwalKonsultasi
            if ($pertemuan->tanggal) {
                $jamMulai = $pertemuan->jam_mulai ?: '08:00:00';
                $jamSelesai = $pertemuan->jam_selesai ?: Carbon::parse($jamMulai)->addHour()->format('H:i:s');
                $hari = $pertemuan->tanggal->locale('id')->isoFormat('dddd');
                $jenisJadwal = match ($pertemuan->jenis_bimbingan) {
                    'akademik' => 'Akademik',
                    'kerja_praktik' => 'Kerja Praktik',
                    default => 'Skripsi',
                };

                $jk = JadwalKonsultasi::updateOrCreate(
                    [
                        'dosen_id' => $pertemuan->dosen_id,
                        'tanggal' => $pertemuan->tanggal->toDateString(),
                        'jam_mulai' => $jamMulai,
                    ],
                    [
                        'jenis_bimbingan' => $jenisJadwal,
                        'hari' => $hari,
                        'jam_selesai' => $jamSelesai,
                        'metode' => $pertemuan->metode,
                        'ruangan_atau_link' => $pertemuan->tempat ?: 'Ruang Dosen',
                        'kuota' => 1,
                        'keterangan' => 'Bimbingan Mahasiswa: '.($pertemuan->mahasiswa->name ?? 'Mahasiswa').' ('.$pertemuan->judul_topik.')',
                        'status' => 'aktif',
                    ]
                );

                $pertemuan->update([
                    'jam_mulai' => $jamMulai,
                    'jam_selesai' => $jamSelesai,
                    'jadwal_konsultasi_id' => $jk->id,
                ]);
            }

            return back()->with('success', 'Pertemuan bimbingan berhasil disetujui.');
        } catch (Throwable $e) {
            return back()->with('error', 'Gagal mengonfirmasi bimbingan.');
        }
    }

    public function tolak(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'alasan_penolakan' => ['required', 'string', 'max:500'],
        ], [
            'alasan_penolakan.required' => 'Mohon sertakan alasan penolakan bimbingan.',
        ]);

        try {
            $pertemuan = PertemuanBimbingan::where('dosen_id', Auth::id())->find($id);

            if (! $pertemuan) {
                return back()->with('error', 'Pertemuan bimbingan tidak ditemukan.');
            }

            $pertemuan->status = 'ditolak';
            $pertemuan->alasan_penolakan = $request->alasan_penolakan;
            $pertemuan->save();

            if ($pertemuan->bimbingan) {
                $pertemuan->bimbingan->update([
                    'status' => 'rejected',
                    'catatan_dosen' => $request->alasan_penolakan,
                ]);
            }

            if ($pertemuan->jadwalKonsultasi) {
                $pertemuan->jadwalKonsultasi->update([
                    'status' => 'nonaktif',
                ]);
            }

            return back()->with('success', 'Pengajuan bimbingan telah ditolak dengan catatan.');
        } catch (Throwable $e) {
            return back()->with('error', 'Gagal memproses penolakan.');
        }
    }

    public function updateCatatan(Request $request, int $id): RedirectResponse
    {
        $isSelesai = $request->boolean('tandai_selesai') || $request->input('tandai_selesai') === '1' || $request->input('action_selesai') === '1';

        $request->validate([
            'catatan_hasil' => $isSelesai ? ['nullable', 'string'] : ['required', 'string', 'min:5'],
        ], [
            'catatan_hasil.required' => 'Catatan hasil pembahasan wajib diisi.',
            'catatan_hasil.min' => 'Catatan hasil minimal 5 karakter.',
        ]);

        try {
            $pertemuan = PertemuanBimbingan::where('dosen_id', Auth::id())->find($id);

            if (! $pertemuan) {
                return back()->with('error', 'Pertemuan bimbingan tidak ditemukan.');
            }

            if ($pertemuan->status !== 'disetujui') {
                return back()->with('error', 'Catatan hanya dapat ditambahkan jika status bimbingan telah disetujui.');
            }

            if ($request->has('catatan_hasil')) {
                $pertemuan->catatan_hasil = $request->catatan_hasil;
            }

            if ($isSelesai) {
                $pertemuan->status = 'selesai';
                $pertemuan->tanggal_bimbingan_selesai = Carbon::now('Asia/Jakarta');

                if ($pertemuan->bimbingan) {
                    $pertemuan->bimbingan->update([
                        'status' => 'completed',
                    ]);
                }

                if ($pertemuan->jadwalKonsultasi) {
                    $pertemuan->jadwalKonsultasi->update([
                        'status' => 'selesai',
                    ]);
                }
            }

            $pertemuan->save();

            $pesan = $isSelesai
                ? 'Sesi pertemuan bimbingan berhasil ditandai selesai.'
                : 'Catatan hasil pembahasan bimbingan berhasil disimpan.';

            return back()->with('success', $pesan);
        } catch (Throwable $e) {
            return back()->with('error', 'Gagal menyimpan catatan hasil bimbingan.');
        }
    }

    public function tambahRevisi(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'isi_revisi' => ['required', 'string', 'max:1000'],
            'tenggat_waktu' => ['nullable', 'date'],
            'prioritas' => ['required', 'in:tinggi,sedang,rendah'],
        ], [
            'isi_revisi.required' => 'Uraian arahan revisi wajib diisi.',
            'prioritas.required' => 'Prioritas revisi wajib dipilih.',
        ]);

        try {
            $pertemuan = PertemuanBimbingan::where('dosen_id', Auth::id())->find($id);

            if (! $pertemuan) {
                return back()->with('error', 'Pertemuan bimbingan tidak ditemukan.');
            }

            if ($pertemuan->status !== 'disetujui') {
                return back()->with('error', 'Arahan revisi hanya dapat ditambahkan jika status bimbingan telah disetujui.');
            }

            ArahanRevisi::create([
                'pertemuan_bimbingan_id' => $pertemuan->id,
                'isi_revisi' => $request->isi_revisi,
                'tenggat_waktu' => $request->tenggat_waktu,
                'prioritas' => $request->prioritas,
                'status' => 'belum_selesai',
            ]);

            return back()->with('success', 'Poin arahan revisi berhasil ditambahkan untuk mahasiswa.');
        } catch (Throwable $e) {
            return back()->with('error', 'Gagal menambahkan arahan revisi.');
        }
    }

    public function updateRevisi(Request $request, int $revisiId): RedirectResponse
    {
        $request->validate([
            'isi_revisi' => ['required', 'string', 'max:1000'],
            'tenggat_waktu' => ['nullable', 'date'],
            'prioritas' => ['required', 'in:tinggi,sedang,rendah'],
            'status' => ['required', 'in:belum_selesai,sedang_dikerjakan,selesai,perlu_perbaikan'],
        ], [
            'isi_revisi.required' => 'Uraian revisi wajib diisi.',
        ]);

        try {
            $dosenId = Auth::id();
            $revisi = ArahanRevisi::whereHas('pertemuanBimbingan', function ($q) use ($dosenId) {
                $q->where('dosen_id', $dosenId);
            })->find($revisiId);

            if (! $revisi) {
                return back()->with('error', 'Arahan revisi tidak ditemukan.');
            }

            $revisi->update([
                'isi_revisi' => $request->isi_revisi,
                'tenggat_waktu' => $request->tenggat_waktu,
                'prioritas' => $request->prioritas,
                'status' => $request->status,
            ]);

            return back()->with('success', 'Arahan revisi berhasil diperbarui.');
        } catch (Throwable $e) {
            return back()->with('error', 'Gagal memperbarui arahan revisi.');
        }
    }

    public function hapusRevisi(int $revisiId): RedirectResponse
    {
        try {
            $dosenId = Auth::id();
            $revisi = ArahanRevisi::whereHas('pertemuanBimbingan', function ($q) use ($dosenId) {
                $q->where('dosen_id', $dosenId);
            })->find($revisiId);

            if (! $revisi) {
                return back()->with('error', 'Arahan revisi tidak ditemukan.');
            }

            $revisi->delete();

            return back()->with('success', 'Poin arahan revisi berhasil dihapus.');
        } catch (Throwable $e) {
            return back()->with('error', 'Gagal menghapus arahan revisi.');
        }
    }
}
