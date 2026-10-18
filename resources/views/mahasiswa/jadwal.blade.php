@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h3 class="fw-bold mb-4">Pengajuan Aktif</h3>
    
    <div class="row">
        @forelse($jadwalList as $jadwal)
            <div class="col-12 mb-4">
                <div class="card shadow-sm border-0" style="border-radius: 1rem;">
                    
                    <div class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                        
                        <div class="flex-grow-1 me-md-4">
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <h6 class="text-muted mb-0" style="font-size: 0.85rem;">
                                    Bimbingan {{ strtoupper($jadwal->jenis_bimbingan) }}
                                </h6>
                                @if($jadwal->status === 'approved')
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success rounded-pill px-3 py-1">Disetujui</span>
                                @else
                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning rounded-pill px-3 py-1">Menunggu</span>
                                @endif
                            </div>
                            
                            <h5 class="fw-bold mb-3">
                                {{ $jadwal->topik_pembahasan }}
                            </h5>
                            
                            <div class="d-flex flex-wrap gap-4">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-person-badge text-muted me-2 fs-5"></i>
                                    <div>
                                        <span class="fw-semibold d-block" style="font-size: 0.9rem;">{{ $jadwal->dosen->name ?? '-' }}</span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-clock-history text-muted me-2 fs-5"></i>
                                    <div>
                                        <span class="text-muted" style="font-size: 0.9rem;">{{ \Carbon\Carbon::parse($jadwal->created_at)->translatedFormat('d F Y, H:i') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4 mt-md-0 d-flex gap-2">
                            @if($jadwal->status === 'pending')
                                <a href="{{ route('mahasiswa.bimbingan.edit', $jadwal->id) }}" class="btn btn-outline-primary rounded-3 px-3">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </a>
                                <form action="{{ url('/mahasiswa/bimbingan/' . $jadwal->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin membatalkan pengajuan ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger rounded-3 px-3">
                                        <i class="bi bi-trash"></i> Batalkan
                                    </button>
                                </form>
                            @elseif($jadwal->status === 'approved')
                                <a href="{{ route('mahasiswa.bimbingan.chat', $jadwal->id) }}" class="btn text-white shadow-sm rounded-3 px-4" style="background-color: #8E65B7;">
                                    <i class="bi bi-chat-dots"></i> Buka Chat
                                </a>
                            @endif
                        </div>
                        
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-light border border-secondary border-opacity-25 text-center p-5 rounded-3">
                    <i class="bi bi-journal-x fs-1 text-muted mb-3 d-block"></i>
                    <h5>Belum ada pengajuan aktif</h5>
                    <p class="text-muted mb-0">Kamu belum memiliki pengajuan bimbingan yang sedang berjalan.</p>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection