<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\ArahanRevisi;
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

        $riwayatSebelumnya = PertemuanBimbingan::where('mahasiswa_id', $pertemuan->mahasiswa_id)
            ->where('dosen_id', $dosenId)
            ->where('id', '!=', $pertemuan->id)
            ->with('arahanRevisis')
            ->orderBy('tanggal', 'desc')
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

            return back()->with('success', 'Pengajuan bimbingan telah ditolak dengan catatan.');
        } catch (Throwable $e) {
            return back()->with('error', 'Gagal memproses penolakan.');
        }
    }

    public function updateCatatan(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'catatan_hasil' => ['required', 'string', 'min:5'],
        ], [
            'catatan_hasil.required' => 'Catatan hasil pembahasan wajib diisi.',
            'catatan_hasil.min' => 'Catatan hasil minimal 5 karakter.',
        ]);

        try {
            $pertemuan = PertemuanBimbingan::where('dosen_id', Auth::id())->find($id);

            if (! $pertemuan) {
                return back()->with('error', 'Pertemuan bimbingan tidak ditemukan.');
            }

            $pertemuan->catatan_hasil = $request->catatan_hasil;

            if ($request->boolean('tandai_selesai')) {
                $pertemuan->status = 'selesai';
                $pertemuan->tanggal_bimbingan_selesai = Carbon::now();
            }

            $pertemuan->save();

            return back()->with('success', 'Catatan hasil pembahasan bimbingan berhasil disimpan.');
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
