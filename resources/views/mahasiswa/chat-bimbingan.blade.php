@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center mb-4">
        <a href="{{route('mahasiswa.jadwal')}}" class="btn btn-light border me-3 rounded-circle">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h5 class="fw-bold mb-0">{{ $bimbingan->topik_pembahasan }}</h5>
            <small class="text-muted">Bersama: {{ $bimbingan->dosen->name }}</small>
        </div>
    </div>

    <div class="card shadow-sm border-0" style="border-radius: 1rem;">
        
        <!-- area riwayat pesan -->
        <div class="card-body p-4" style="height: 60vh; overflow-y: auto; background-color: #f8f9fa;" id="chatArea">
            
            @forelse($bimbingan->pesanChats as $chat)
                @if($chat->pengirim_id == auth()->id())
                    <!-- chat mahasiswa (kanan) -->
                    <div class="d-flex justify-content-end mb-3">
                        <div class="p-3 text-white" style="background-color: #8E65B7; border-radius: 1rem 1rem 0 1rem; max-width: 75%;">
                            <p class="mb-1">{{ $chat->pesan }}</p>
                            <small class="text-white-50" style="font-size: 0.7rem;">{{ $chat->created_at->format('H:i') }}</small>
                        </div>
                    </div>
                @else
                    <!-- chat dosen (kiri) -->
                    <div class="d-flex justify-content-start mb-3">
                        <div class="p-3 bg-white border" style="border-radius: 1rem 1rem 1rem 0; max-width: 75%;">
                            <small class="fw-bold d-block text-muted mb-1" style="font-size: 0.75rem;">{{ $chat->pengirim->name }}</small>
                            <p class="mb-1">{{ $chat->pesan }}</p>
                            <small class="text-muted" style="font-size: 0.7rem;">{{ $chat->created_at->format('H:i') }}</small>
                        </div>
                    </div>
                @endif
            @empty
                <div class="text-center text-muted mt-5">
                    <i class="bi bi-chat-dots fs-1 d-block mb-2"></i>
                    Belum ada pesan. Silakan mulai diskusi dengan dosen pembimbing Anda.
                </div>
            @endforelse

        </div>

        <!-- form input pesan -->
        <div class="card-footer bg-white border-top-0 p-3" style="border-radius: 0 0 1rem 1rem;">
            <form action="{{ route('mahasiswa.bimbingan.kirim-pesan', $bimbingan->id) }}" method="POST">
                @csrf
                <div class="input-group">
                    <input type="text" name="pesan" class="form-control border-end-0 py-2" placeholder="Ketik pesan Anda di sini..." required autocomplete="off" style="border-radius: 2rem 0 0 2rem;">
                    <button type="submit" class="btn text-white px-4" style="background-color: #8E65B7; border-radius: 0 2rem 2rem 0;">
                        <i class="bi bi-send"></i> Kirim
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- auto scroll chat ke paling bawah pas dibuka -->
<script>
    const chatArea = document.getElementById('chatArea');
    chatArea.scrollTop = chatArea.scrollHeight;
</script>
@endsection