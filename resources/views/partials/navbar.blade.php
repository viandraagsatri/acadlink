<nav class="navbar navbar-expand-md navbar-lilac sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="{{ route('home') }}">
            <i class="bi bi-mortarboard-fill me-2 fs-4 text-purple"></i>
            <span>AcadLink</span>
        </a>
        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('home') ? 'active fw-semibold' : '' }}" href="{{ route('home') }}">
                    Beranda
                    </a>
                </li>
                @auth
                    @if (auth()->user()->role === 'dosen')
                        @include('partials.menu-dosen')
                    @endif

                    @if (auth()->user()->role === 'mahasiswa')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('mahasiswa.jadwal') ? 'active fw-semibold' : '' }}" href="{{ route('mahasiswa.jadwal') }}">Jadwal</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('mahasiswa.riwayat') ? 'active fw-semibold' : '' }}" href="{{ route('mahasiswa.riwayat') }}">Riwayat</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('mahasiswa.revisi') ? 'active fw-semibold' : '' }}" href="{{ route('mahasiswa.revisi') }}">Revisi</a>
                        </li>
                    @endif
                @endauth
            </ul>

            <ul class="navbar-nav ms-auto align-items-md-center gap-2">
                @guest
                    <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Masuk</a></li>
                    <li class="nav-item"><a class="btn btn-primary btn-sm px-3" href="{{ route('register') }}">Daftar</a></li>
                @endguest

                @auth
                    <li class="nav-item">
                        <div class="navbar-text d-flex align-items-center me-md-2">
                            <span class="badge bg-white text-dark border me-2 py-1 px-2">
                                <i class="bi {{ auth()->user()->role === 'dosen' ? 'bi-person-badge' : 'bi-mortarboard' }} me-1"></i>
                                {{ ucfirst(auth()->user()->role) }}
                            </span>
                            <span class="fw-semibold">{{ auth()->user()->name }}</span>
                        </div>
                    </li>
                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-dark btn-sm rounded-pill px-3">
                                <i class="bi bi-box-arrow-right me-1"></i> Keluar
                            </button>
                        </form>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>