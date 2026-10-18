<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class MahasiswaBimbinganController extends Controller
{
    public function index(Request $request)
    {
        $dosenId = Auth::id();

        $query = User::where('role', 'mahasiswa')
            ->with(['dosenPA', 'dosenSkripsi', 'dosenKP']);

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('nim_nip', 'ilike', "%{$search}%");
            });
        }

        $mahasiswas = $query
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('dosen.mahasiswa-bimbingan.index', compact(
            'mahasiswas',
            'dosenId'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'mahasiswa_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')
                    ->where('role', 'mahasiswa'),
            ],
            'jenis_bimbingan' => [
                'required',
                Rule::in(['PA', 'skripsi', 'KP']),
            ],
        ]);

        $mahasiswa = User::where('role', 'mahasiswa')
            ->findOrFail($validated['mahasiswa_id']);

        $dosenId = Auth::id();

        $kolom = match ($validated['jenis_bimbingan']) {
            'PA' => 'dosen_pa_id',
            'skripsi' => 'dosen_skripsi_id',
            'KP' => 'dosen_kp_id',
        };

        $pemilikSaatIni = $mahasiswa->{$kolom};

        if ($pemilikSaatIni && (int) $pemilikSaatIni !== $dosenId) {
            return back()->withErrors([
                'mahasiswa_id' => 'Mahasiswa sudah ditetapkan kepada dosen lain untuk jenis bimbingan ini.',
            ])->withInput();
        }

        // Simpan ID dosen yang sedang login.
        // forceFill digunakan karena kolom penetapan belum ada di $fillable.
        $mahasiswa->forceFill([
            $kolom => $dosenId,
        ])->save();

        return redirect()
            ->route('dosen.mahasiswa-bimbingan.index')
            ->with('success', 'Mahasiswa berhasil ditetapkan sebagai anak bimbingan.');
    }

    public function destroy(Request $request, int $id)
    {
        $validated = $request->validate([
            'jenis_bimbingan' => [
                'required',
                Rule::in(['PA', 'skripsi', 'KP']),
            ],
        ]);

        $kolom = match ($validated['jenis_bimbingan']) {
            'PA' => 'dosen_pa_id',
            'skripsi' => 'dosen_skripsi_id',
            'KP' => 'dosen_kp_id',
        };

        $mahasiswa = User::where('role', 'mahasiswa')
            ->findOrFail($id);

        // Dosen hanya dapat mencabut penetapan miliknya sendiri.
        if ((int) $mahasiswa->{$kolom} !== Auth::id()) {
            abort(403, 'Lu tidak berhak mengubah penetapan dosen ini.');
        }

        $mahasiswa->forceFill([
            $kolom => null,
        ])->save();

        return redirect()
            ->route('dosen.mahasiswa-bimbingan.index')
            ->with('success', 'Penetapan mahasiswa berhasil dicabut.');
    }
}
