@extends('layout')
@section('title', 'Entry Gate')
@section('page-title', 'Entry Gate')

@section('content')
<div class="module-grid">
    {{-- Form Entry --}}
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon-sm">
                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                    <polyline points="10 17 15 12 10 7"/>
                    <line x1="15" y1="12" x2="3" y2="12"/>
                </svg>
                Masukkan Data Kendaraan
            </h2>
        </div>
        <div class="card-body">
            <form action="{{ route('entry.store') }}" method="POST" id="entryForm">
                @csrf
                <div class="form-group">
                    <label for="plate_number" class="form-label">Plat Nomor</label>
                    <input
                        type="text"
                        id="plate_number"
                        name="plate_number"
                        class="form-input"
                        placeholder="Contoh: B 1234 XYZ"
                        value="{{ old('plate_number') }}"
                        required
                        autofocus
                        autocomplete="off"
                        style="text-transform: uppercase"
                    >
                </div>

                <div class="form-group">
                    <label class="form-label">Jenis Kendaraan</label>
                    <div class="vehicle-selector">
                        @foreach(['motor' => ['🏍️', 'Motor', '2.000'], 'mobil' => ['🚗', 'Mobil', '5.000'], 'truk' => ['🚛', 'Truk', '10.000']] as $type => [$emoji, $name, $rate])
                            @php $cap = $capacity[$type]; @endphp
                            <label class="vehicle-option {{ $cap['is_full'] ? 'vehicle-full' : '' }}">
                                <input type="radio" name="vehicle_type" value="{{ $type }}"
                                    {{ old('vehicle_type', 'mobil') == $type ? 'checked' : '' }}
                                    {{ $cap['is_full'] ? 'disabled' : '' }}
                                    required>
                                <div class="vehicle-card">
                                    <span class="vehicle-emoji">{{ $emoji }}</span>
                                    <span class="vehicle-name">{{ $name }}</span>
                                    <span class="vehicle-rate">Rp {{ $rate }}/jam</span>
                                    <span class="vehicle-slot {{ $cap['is_full'] ? 'slot-full' : ($cap['available'] <= 5 ? 'slot-warn' : 'slot-ok') }}">
                                        {{ $cap['is_full'] ? 'PENUH' : $cap['available'] . ' slot' }}
                                    </span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg btn-block">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon-sm">
                        <polyline points="6 9 6 2 18 2 18 9"/>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                        <rect x="6" y="14" width="12" height="8"/>
                    </svg>
                    Cetak Tiket
                </button>
            </form>
        </div>
    </div>

    {{-- Preview Tiket with QR --}}
    @if(session('ticket'))
        @php $ticket = session('ticket'); @endphp
        <div class="card ticket-preview animate-in">
            <div class="card-header">
                <h2 class="card-title">🎫 Tiket Parkir</h2>
            </div>
            <div class="card-body">
                {{-- Gate Animation --}}
                <div class="gate-scene">
                    <div class="gate-glow entry"></div>
                    <div class="gate-confetti" id="entryConfetti"></div>
                    <div class="gate-pulse-ring entry">
                        <div class="gate-center-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="#818cf8" stroke-width="2.5">
                                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                                <polyline points="10 17 15 12 10 7"/>
                                <line x1="15" y1="12" x2="3" y2="12"/>
                            </svg>
                        </div>
                    </div>
                    <div class="gate-status">
                        <h3 class="text-indigo">Gate Terbuka!</h3>
                        <p>Silakan masuk area parkir</p>
                    </div>
                    <div class="gate-vehicle">
                        <span class="vehicle-emoji">{{ $ticket->vehicle_type == 'motor' ? '🏍️' : ($ticket->vehicle_type == 'mobil' ? '🚗' : '🚛') }}</span>
                        <span>{{ $ticket->plate_number }}</span>
                    </div>
                </div>
                <div class="ticket-card">
                    <div class="ticket-header-band">
                        <span class="ticket-brand">HAEZVL PARK</span>
                        <span class="ticket-type">{{ strtoupper($ticket->vehicle_type) }}</span>
                    </div>
                    <div class="ticket-body-content">
                        <div class="ticket-qr" id="ticketQR"></div>
                        <div class="ticket-code-display">{{ $ticket->ticket_code }}</div>
                        <div class="ticket-detail">
                            <span class="ticket-label">Plat Nomor</span>
                            <span class="ticket-value">{{ $ticket->plate_number }}</span>
                        </div>
                        <div class="ticket-detail">
                            <span class="ticket-label">Waktu Masuk</span>
                            <span class="ticket-value">{{ $ticket->entry_time->format('d/m/Y H:i:s') }}</span>
                        </div>
                        <div class="ticket-detail">
                            <span class="ticket-label">Status</span>
                            <span class="status-badge status-in">{{ $ticket->status }}</span>
                        </div>
                    </div>
                    <div class="ticket-footer-band">
                        <span>Scan QR untuk langsung bayar & keluar di Exit Gate</span>
                    </div>
                </div>
                <a href="{{ route('entry.ticket', $ticket->id) }}" class="btn btn-primary btn-block mt-md" target="_blank">
                    🖨 Cetak Tiket
                </a>
            </div>
        </div>
    @endif
</div>
@endsection

@if(session('ticket'))
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js"></script>
<script>
    (function() {
        var qr = qrcode(0, 'L');
        qr.addData('{{ url("/booth/" . session("ticket")->ticket_code) }}');
        qr.make();
        document.getElementById('ticketQR').innerHTML = qr.createImgTag(8, 4);

        // Confetti
        var colors = ['#818cf8','#6366f1','#a5b4fc','#10b981','#f59e0b','#ef4444'];
        var container = document.getElementById('entryConfetti');
        if (container) {
            for (var i = 0; i < 30; i++) {
                var span = document.createElement('span');
                span.style.left = Math.random() * 100 + '%';
                span.style.background = colors[Math.floor(Math.random() * colors.length)];
                span.style.animationDelay = (Math.random() * 2) + 's';
                span.style.animationDuration = (2 + Math.random() * 2) + 's';
                container.appendChild(span);
            }
        }
    })();
</script>
@endpush
@endif
