<nav class="navbar navbar-expand-md navbar-lilac">
    <div class="container">
        <a class="navbar-brand fw-bold" href="{{ route('home') }}">AcadLink</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav me-auto">
                {{-- Sementara tampil semua. Nanti dipisah per role (Step 5) --}}
                @include('partials.menu-mahasiswa')
                @include('partials.menu-dosen')
            </ul>

            <ul class="navbar-nav ms-auto align-items-md-center">
                @guest
                    <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('register') }}">Register</a></li>
                @endguest

                @auth
                    <li class="nav-item">
                        <span class="navbar-text me-md-3">{{ auth()->user()->name }} ({{ auth()->user()->role }})</span>
                    </li>
                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-dark btn-sm">Logout</button>
                        </form>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>