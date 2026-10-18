@extends('layouts.app')

@section('title', 'Mahasiswa Bimbingan')

@section('content')
<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-bold">Mahasiswa Bimbingan</h1>
            <p class="text-muted">
                Kelola mahasiswa yang menjadi anak bimbingan Anda.
            </p>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h5 class="fw-bold mb-3">Tetapkan Anak Bimbingan</h5>

            <form
                action="{{ route('dosen.mahasiswa-bimbingan.store') }}"
                method="POST"
            >
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="mahasiswa_id" class="form-label">
                            Pilih Mahasiswa
                        </label>

                        <select
                            name="mahasiswa_id"
                            id="mahasiswa_id"
                            class="form-select"
                            required
                        >
                            <option value="">-- Pilih Mahasiswa --</option>

                            @foreach ($mahasiswas as $mahasiswa)
                                <option
                                    value="{{ $mahasiswa->id }}"
                                    @selected(old('mahasiswa_id') == $mahasiswa->id)
                                >
                                    {{ $mahasiswa->name }}
                                    ({{ $mahasiswa->nim_nip }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="jenis_bimbingan" class="form-label">
                            Jenis Bimbingan
                        </label>

                        <select
                            name="jenis_bimbingan"
                            id="jenis_bimbingan"
                            class="form-select"
                            required
                        >
                            <option value="">-- Pilih Jenis --</option>
                            <option value="PA">Pembimbing PA</option>
                            <option value="skripsi">Pembimbing Skripsi</option>
                            <option value="KP">Pembimbing KP</option>
                        </select>
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            Tetapkan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="fw-bold mb-3">Daftar Mahasiswa Bimbingan</h5>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Nama Mahasiswa</th>
                            <th>NIM</th>
                            <th>Jenis Bimbingan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($mahasiswas as $mahasiswa)
                            @php
                                $jenis = [];

                                if ($mahasiswa->dosen_pa_id == $dosenId) {
                                    $jenis[] = 'PA';
                                }

                                if ($mahasiswa->dosen_skripsi_id == $dosenId) {
                                    $jenis[] = 'Skripsi';
                                }

                                if ($mahasiswa->dosen_kp_id == $dosenId) {
                                    $jenis[] = 'KP';
                                }
                            @endphp

                            @if (count($jenis) > 0)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $mahasiswa->name }}</td>
                                    <td>{{ $mahasiswa->nim_nip }}</td>
                                    <td>{{ implode(', ', $jenis) }}</td>
                                    <td>
                                        <form
                                            action="{{ route('dosen.mahasiswa-bimbingan.destroy', $mahasiswa->id) }}"
                                            method="POST"
                                            onsubmit="return confirm('Hapus penetapan bimbingan yang dipilih?')"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <input
                                                type="hidden"
                                                name="jenis_bimbingan"
                                                value="{{ count($jenis) === 1 ? strtolower($jenis[0]) : '' }}"
                                            >

                                            <button
                                                type="submit"
                                                class="btn btn-outline-danger btn-sm"
                                                {{ count($jenis) !== 1 ? 'disabled' : '' }}
                                                title="Hapus penetapan satu jenis bimbingan"
                                            >
                                                Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    Belum ada mahasiswa bimbingan.
                                </td>
                            </tr>
                        @endforelse

                        @if ($mahasiswas->isEmpty())
                            <tr>
                                <td colspan="5" class="text-center text-muted">
                                    Belum ada data mahasiswa.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            {{ $mahasiswas->links() }}
        </div>
    </div>

</div>
@endsection
