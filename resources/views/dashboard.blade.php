@extends('layout')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard Monitoring')

@section('content')
<div class="stats-grid">
    <div class="stat-card stat-active">
        <div class="stat-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="1" y="3" width="15" height="13" rx="2"/>
                <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                <circle cx="5.5" cy="18.5" r="2.5"/>
                <circle cx="18.5" cy="18.5" r="2.5"/>
            </svg>
        </div>
        <div class="stat-info">
            <span class="stat-number">{{ $totalActive }}</span>
            <span class="stat-label">Kendaraan Aktif</span>
            <div class="stat-breakdown">
                <span class="badge badge-motor">🏍 Motor: {{ $activeByType['motor'] }}</span>
                <span class="badge badge-mobil">🚗 Mobil: {{ $activeByType['mobil'] }}</span>
                <span class="badge badge-truk">🚛 Truk: {{ $activeByType['truk'] }}</span>
            </div>
        </div>
    </div>

    <div class="stat-card stat-revenue">
        <div class="stat-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="1" x2="12" y2="23"/>
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
            </svg>
        </div>
        <div class="stat-info">
            <span class="stat-number">Rp {{ number_format($todayRevenue, 0, ',', '.') }}</span>
            <span class="stat-label">Pemasukan Hari Ini</span>
        </div>
    </div>

    <div class="stat-card stat-exit">
        <div class="stat-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                <polyline points="16 17 21 12 16 7"/>
                <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
        </div>
        <div class="stat-info">
            <span class="stat-number">{{ $todayExited }}</span>
            <span class="stat-label">Kendaraan Keluar Hari Ini</span>
        </div>
    </div>

    <div class="stat-card stat-entered">
        <div class="stat-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                <polyline points="10 17 15 12 10 7"/>
                <line x1="15" y1="12" x2="3" y2="12"/>
            </svg>
        </div>
        <div class="stat-info">
            <span class="stat-number">{{ $todayEntered }}</span>
            <span class="stat-label">Masuk Hari Ini</span>
        </div>
    </div>
</div>

{{-- Kapasitas Parkir --}}
<div class="card mt-lg">
    <div class="card-header">
        <h2 class="card-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon-sm">
                <rect x="3" y="3" width="18" height="18" rx="2"/>
                <path d="M3 9h18"/>
                <path d="M9 21V9"/>
            </svg>
            Kapasitas Parkir
        </h2>
        <span class="badge badge-count">⏱ Auto-expire: {{ $expireMinutes }} menit</span>
    </div>
    <div class="card-body">
        <div class="capacity-grid">
            @foreach(['motor' => '🏍️', 'mobil' => '🚗', 'truk' => '🚛'] as $type => $emoji)
                @php $cap = $capacity[$type]; @endphp
                <div class="capacity-item">
                    <div class="capacity-header">
                        <span class="capacity-emoji">{{ $emoji }}</span>
                        <span class="capacity-type">{{ ucfirst($type) }}</span>
                    </div>
                    <div class="capacity-bar-wrap">
                        <div class="capacity-bar" style="width: {{ $cap['capacity'] > 0 ? ($cap['occupied'] / $cap['capacity'] * 100) : 0 }}%"
                             data-status="{{ $cap['is_full'] ? 'full' : ($cap['occupied'] / max(1, $cap['capacity']) > 0.8 ? 'warn' : 'ok') }}">
                        </div>
                    </div>
                    <div class="capacity-numbers">
                        <span class="{{ $cap['is_full'] ? 'text-red' : '' }}">{{ $cap['occupied'] }} / {{ $cap['capacity'] }}</span>
                        <span class="capacity-avail">{{ $cap['available'] }} tersedia</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Tabel Tiket Aktif --}}
<div class="card mt-lg">
    <div class="card-header">
        <h2 class="card-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon-sm">
                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
            </svg>
            Tiket Aktif
        </h2>
        <span class="badge badge-count">{{ $totalActive }} kendaraan</span>
    </div>
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Kode Tiket</th>
                    <th>Plat Nomor</th>
                    <th>Jenis</th>
                    <th>Waktu Masuk</th>
                    <th>Durasi</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($activeTickets as $ticket)
                    <tr>
                        <td><code class="ticket-code">{{ $ticket->ticket_code }}</code></td>
                        <td><strong>{{ $ticket->plate_number }}</strong></td>
                        <td>
                            <span class="badge badge-{{ $ticket->vehicle_type }}">
                                {{ ucfirst($ticket->vehicle_type) }}
                            </span>
                        </td>
                        <td>{{ $ticket->entry_time->format('H:i:s') }}</td>
                        <td>{{ $ticket->entry_time->diffForHumans(null, true) }}</td>
                        <td>
                            <span class="status-badge status-{{ strtolower($ticket->status) }}">
                                {{ $ticket->status }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty-state">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="empty-icon">
                                <rect x="1" y="3" width="15" height="13" rx="2"/>
                                <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                                <circle cx="5.5" cy="18.5" r="2.5"/>
                                <circle cx="18.5" cy="18.5" r="2.5"/>
                            </svg>
                            <p>Belum ada kendaraan di area parkir</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function() {
        const INTERVAL = 30; // detik
        let remaining = INTERVAL;

        // Tambah timer di samping badge "Sistem Aktif"
        const existingBadge = document.querySelector('.main-header .header-badge');
        if (existingBadge) {
            const timer = document.createElement('span');
            timer.id = 'refreshTimer';
            timer.style.cssText = 'margin-left: 12px; color: #60a5fa; font-size: 0.85rem;';
            timer.textContent = '🔄 ' + remaining + 's';
            existingBadge.appendChild(timer);
        }

        const timerEl = document.getElementById('refreshTimer');

        setInterval(function() {
            remaining--;
            if (timerEl) timerEl.textContent = '🔄 ' + remaining + 's';
            if (remaining <= 0) {
                window.location.reload();
            }
        }, 1000);
    })();
</script>
@endpush
