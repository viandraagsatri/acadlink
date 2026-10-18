<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('dosen.jadwal.*') ? 'active fw-semibold' : '' }}"
       href="{{ route('dosen.jadwal.index') }}">
        <i class="bi bi-calendar-week me-1"></i>
        Kelola Jadwal
    </a>
</li>

<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('dosen.pertemuan.*') ? 'active fw-semibold' : '' }}"
       href="{{ route('dosen.pertemuan.index') }}">
        <i class="bi bi-journal-check me-1"></i>
        Pertemuan & Bimbingan
    </a>
</li>

<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('dosen.pengajuan.*') ? 'active fw-semibold' : '' }}"
       href="{{ route('dosen.pengajuan.index') }}">
        <i class="bi bi-inbox me-1"></i>
        Pengajuan Bimbingan
    </a>
</li>

<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('dosen.mahasiswa-bimbingan.*') ? 'active fw-semibold' : '' }}"
       href="{{ route('dosen.mahasiswa-bimbingan.index') }}">
        <i class="bi bi-people me-1"></i>
        Mahasiswa Bimbingan
    </a>
</li>
