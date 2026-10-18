<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('dosen.jadwal.*') ? 'active fw-semibold' : '' }}"
       href="{{ route('dosen.jadwal.index') }}">
        
        Kelola Jadwal
    </a>
</li>

<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('dosen.pertemuan.*') ? 'active fw-semibold' : '' }}"
       href="{{ route('dosen.pertemuan.index') }}">
        Pertemuan & Bimbingan
    </a>
</li>

<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('dosen.pengajuan.*') ? 'active fw-semibold' : '' }}"
       href="{{ route('dosen.pengajuan.index') }}">
        Pengajuan Bimbingan
    </a>
</li>

<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('dosen.mahasiswa-bimbingan.*') ? 'active fw-semibold' : '' }}"
       href="{{ route('dosen.mahasiswa-bimbingan.index') }}">
        Mahasiswa Bimbingan
    </a>
</li>
