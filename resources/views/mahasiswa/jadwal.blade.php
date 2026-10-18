@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h3 class="fw-bold mb-4">Pengajuan Aktif</h3>
    
    <div class="row">
        @forelse($jadwalList as $jadwal)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card shadow-sm border-0 h-100" style="border-radius: 1rem;">
                    <div class="card-body p-4">
                        
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h6 class="text-muted mb-1" style="font-size: 0.85rem;">
                                    Bimbingan {{ strtoupper($jadwal->jenis_bimbingan) }}
                                </h6>
                                <h5 class="fw-bold text-truncate" style="max-width: 200px;" title="{{ $jadwal->topik_pembahasan }}">
                                    {{ $jadwal->topik_pembahasan }}
                                </h5>
                            </div>
                            
                            <!-- status -->
                            @if($jadwal->status === 'approved')
                                <span class="badge bg-success bg-opacity-10 text-success border border-success rounded-pill px-3 py-2">
                                    Disetujui
                                </span>
                            @else
                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning rounded-pill px-3 py-2">
                                    Menunggu
                                </span>
                            @endif
                        </div>
                        
                        <hr class="text-muted opacity-25">
                        
                        <!-- info dosen + waktu -->
                        <div class="mb-4">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-person-badge text-muted me-3 fs-5"></i>
                                <div>
                                    <small class="text-muted d-block" style="font-size: 0.75rem;">Dosen Pembimbing</small>
                                    <span class="fw-semibold">{{ $jadwal->dosen->name ?? '-' }}</span>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-clock-history text-muted me-3 fs-5"></i>
                                <div>
                                    <small class="text-muted d-block" style="font-size: 0.75rem;">Tanggal Pengajuan</small>
                                    <span>{{ \Carbon\Carbon::parse($jadwal->created_at)->translatedFormat('d F Y, H:i') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- footer card -->
                    <div class="card-footer bg-white border-top-0 px-4 pb-4 pt-0 d-flex justify-content-end gap-2">
                        
                        @if($jadwal->status === 'pending')
                            <!-- edit -->
                            <a href="{{ route ('mahasiswa.bimbingan.edit', $jadwal->id) }}" class="btn btn-outline-primary btn-sm rounded-3">
                                <i class="bi bi-pencil-square"></i> Edit
                            </a>
                            
                            <!-- delete -->
                            <form action="{{ url('/mahasiswa/bimbingan/' . $jadwal->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin membatalkan pengajuan ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm rounded-3">
                                    <i class="bi bi-trash"></i> Batalkan
                                </button>
                            </form>
                            
                        @elseif($jadwal->status === 'approved')
                            <a href="{{ url('/mahasiswa/bimbingan/' . $jadwal->id . '/chat') }}" class="btn text-white shadow-sm rounded-3" style="background-color: #8E65B7;">
                                <i class="bi bi-chat-dots"></i> Buka Chat
                            </a>
                        @endif
                        
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