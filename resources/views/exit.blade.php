@extends('layout')
@section('title', 'Exit Gate')
@section('page-title', 'Exit Gate')

@section('content')
<div class="module-grid">

    {{-- ====== STEP 3: Gate Terbuka (muncul setelah bayar) ====== --}}
    @if(session('exit_ticket'))
        @php
            $exitTicket = session('exit_ticket');
            $exitPayment = session('exit_payment');
            $change = session('change', 0);
        @endphp
        <div class="card animate-in">
            <div class="card-body text-center">
                {{-- Gate Animation --}}
                <div class="gate-scene">
                    <div class="gate-glow success"></div>
                    <div class="gate-confetti" id="exitConfetti"></div>
                    <div class="gate-pulse-ring success">
                        <div class="gate-center-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                        </div>
                    </div>
                    <div class="gate-status">
                        <h3 class="text-green">Gate Terbuka!</h3>
                        <p>Kendaraan diperbolehkan keluar</p>
                    </div>
                    <div class="gate-vehicle">
                        <span class="vehicle-emoji">{{ $exitTicket->vehicle_type == 'motor' ? '🏍️' : ($exitTicket->vehicle_type == 'mobil' ? '🚗' : '🚛') }}</span>
                        <span>{{ $exitTicket->plate_number }}</span>
                    </div>
                </div>

                <div class="payment-summary mt-md">
                    <div class="payment-row">
                        <span class="payment-label">Kode Tiket</span>
                        <code class="ticket-code">{{ $exitTicket->ticket_code }}</code>
                    </div>
                    <div class="payment-row">
                        <span class="payment-label">Plat Nomor</span>
                        <strong>{{ $exitTicket->plate_number }}</strong>
                    </div>
                    <div class="payment-row">
                        <span class="payment-label">Waktu Masuk</span>
                        <span>{{ $exitTicket->entry_time->format('d/m/Y H:i:s') }}</span>
                    </div>
                    <div class="payment-row">
                        <span class="payment-label">Waktu Keluar</span>
                        <span>{{ $exitTicket->exit_time->format('d/m/Y H:i:s') }}</span>
                    </div>
                    @if($exitPayment)
                        <hr class="payment-divider">
                        <div class="payment-row payment-total">
                            <span class="payment-label">Total Bayar</span>
                            <span class="payment-amount">Rp {{ number_format($exitPayment->final_amount, 0, ',', '.') }}</span>
                        </div>
                        @if($change > 0)
                            <div class="payment-row">
                                <span class="payment-label">Kembalian</span>
                                <span class="text-green">Rp {{ number_format($change, 0, ',', '.') }}</span>
                            </div>
                        @endif
                    @endif
                </div>

                <div class="exit-actions mt-md">
                    <a href="{{ route('exit.receipt', $exitTicket->id) }}" class="btn btn-primary btn-block" target="_blank">
                        🖨 Cetak Bukti Keluar
                    </a>
                    <a href="{{ route('exit.index') }}" class="btn btn-secondary btn-block mt-sm">
                        ← Kendaraan Berikutnya
                    </a>
                </div>
            </div>
        </div>

    {{-- ====== STEP 2: Payment Summary (muncul setelah lookup) ====== --}}
    @elseif(isset($ticket) && isset($calculation))
        <div class="card animate-in">
            <div class="card-header">
                <h2 class="card-title">💳 Pembayaran & Keluar</h2>
            </div>
            <div class="card-body">
                <div class="payment-summary">
                    <div class="payment-row">
                        <span class="payment-label">Kode Tiket</span>
                        <code class="ticket-code">{{ $ticket->ticket_code }}</code>
                    </div>
                    <div class="payment-row">
                        <span class="payment-label">Plat Nomor</span>
                        <strong>{{ $ticket->plate_number }}</strong>
                    </div>
                    <div class="payment-row">
                        <span class="payment-label">Jenis Kendaraan</span>
                        <span class="badge badge-{{ $ticket->vehicle_type }}">{{ ucfirst($ticket->vehicle_type) }}</span>
                    </div>
                    <div class="payment-row">
                        <span class="payment-label">Waktu Masuk</span>
                        <span>{{ $ticket->entry_time->format('d/m/Y H:i:s') }}</span>
                    </div>
                    <div class="payment-row">
                        <span class="payment-label">Durasi</span>
                        <span>{{ floor($calculation['duration_minutes'] / 60) }} jam {{ $calculation['duration_minutes'] % 60 }} menit</span>
                    </div>
                    <div class="payment-row">
                        <span class="payment-label">Jam Dikenakan</span>
                        <span>{{ $calculation['hours_charged'] }} jam</span>
                    </div>

                    <hr class="payment-divider">

                    <div class="payment-row">
                        <span class="payment-label">Tarif Dasar</span>
                        <span>Rp {{ number_format($calculation['base_amount'], 0, ',', '.') }}</span>
                    </div>
                    @if($calculation['discount'] > 0)
                        <div class="payment-row discount-row">
                            <span class="payment-label">🏷️ Diskon (Cap Harian)</span>
                            <span class="text-green">- Rp {{ number_format($calculation['discount'], 0, ',', '.') }}</span>
                        </div>
                    @endif
                    <div class="payment-row payment-total">
                        <span class="payment-label">TOTAL BAYAR</span>
                        <span class="payment-amount">Rp {{ number_format($calculation['final_amount'], 0, ',', '.') }}</span>
                    </div>
                </div>

                @if($already_paid ?? false)
                    {{-- Sudah bayar, tinggal keluar --}}
                    <form action="{{ route('exit.pay') }}" method="POST" class="mt-md">
                        @csrf
                        <input type="hidden" name="ticket_code" value="{{ $ticket->ticket_code }}">
                        <input type="hidden" name="payment_amount" value="0">
                        <button type="submit" class="btn btn-success btn-lg btn-block">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon-sm">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                            Sudah Lunas — Buka Gate
                        </button>
                    </form>
                @else
                    {{-- Belum bayar --}}
                    <form action="{{ route('exit.pay') }}" method="POST" class="mt-md">
                        @csrf
                        <input type="hidden" name="ticket_code" value="{{ $ticket->ticket_code }}">

                        <div class="form-group">
                            <label for="payment_amount" class="form-label">Nominal Pembayaran (Rp)</label>
                            <input
                                type="number"
                                id="payment_amount"
                                name="payment_amount"
                                class="form-input form-input-lg"
                                min="{{ $calculation['final_amount'] }}"
                                value="{{ $calculation['final_amount'] }}"
                                required
                            >
                        </div>

                        <button type="submit" class="btn btn-success btn-lg btn-block">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon-sm">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                            Bayar & Buka Gate
                        </button>
                    </form>
                @endif

                <a href="{{ route('exit.index') }}" class="btn btn-secondary btn-block mt-sm">
                    ← Scan Ulang
                </a>
            </div>
        </div>

    {{-- ====== STEP 1: Scan QR / Input Manual ====== --}}
    @else
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon-sm">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                    Scan Tiket Keluar
                </h2>
            </div>
            <div class="card-body">
                {{-- Tab Switcher --}}
                <div class="scan-tabs">
                    <button class="scan-tab active" data-tab="qr" onclick="switchTab('qr')">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon-sm">
                            <rect x="3" y="3" width="7" height="7"/>
                            <rect x="14" y="3" width="7" height="7"/>
                            <rect x="3" y="14" width="7" height="7"/>
                            <rect x="14" y="14" width="3" height="3"/>
                        </svg>
                        Scan QR
                    </button>
                    <button class="scan-tab" data-tab="manual" onclick="switchTab('manual')">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon-sm">
                            <rect x="2" y="4" width="20" height="16" rx="2"/>
                            <line x1="6" y1="8" x2="18" y2="8"/>
                            <line x1="6" y1="12" x2="14" y2="12"/>
                        </svg>
                        Input Manual
                    </button>
                </div>

                {{-- QR Scanner --}}
                <div class="scan-panel" id="panel-qr">
                    <div class="qr-scanner-container">
                        <div id="qr-reader" class="qr-reader"></div>
                        <div class="qr-status" id="qr-status">
                            <span class="qr-status-icon">📸</span>
                            <span>Arahkan kamera ke QR code tiket</span>
                        </div>
                    </div>
                    <p class="scan-hint">Gunakan kamera HP atau webcam untuk scan QR code pada tiket</p>
                </div>

                {{-- Manual Input --}}
                <div class="scan-panel" id="panel-manual" style="display:none;">
                    <form action="{{ route('exit.lookup') }}" method="POST" id="lookupForm">
                        @csrf
                        <div class="form-group">
                            <label for="ticket_code" class="form-label">Kode Tiket</label>
                            <input
                                type="text"
                                id="ticket_code"
                                name="ticket_code"
                                class="form-input"
                                placeholder="PKR-XXXXXXXX-XXXX"
                                value="{{ old('ticket_code', session('scanned_code', '')) }}"
                                required
                                autocomplete="off"
                                style="text-transform: uppercase"
                            >
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg btn-block">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon-sm">
                                <circle cx="11" cy="11" r="8"/>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                            </svg>
                            Cari & Hitung Tarif
                        </button>
                    </form>
                </div>

                {{-- Hidden form for QR --}}
                <form action="{{ route('exit.lookup') }}" method="POST" id="qrLookupForm" style="display:none;">
                    @csrf
                    <input type="hidden" name="ticket_code" id="qr-ticket-code">
                </form>
            </div>
        </div>
    @endif

</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
    let scanner = null;
    let isScanning = false;

    function switchTab(tab) {
        document.querySelectorAll('.scan-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.scan-panel').forEach(p => p.style.display = 'none');
        document.querySelector(`[data-tab="${tab}"]`).classList.add('active');
        document.getElementById(`panel-${tab}`).style.display = 'block';

        if (tab === 'qr') {
            startScanner();
        } else {
            stopScanner();
            document.getElementById('ticket_code')?.focus();
        }
    }

    function startScanner() {
        if (isScanning) return;

        const statusEl = document.getElementById('qr-status');
        statusEl.innerHTML = '<span class="qr-status-icon">⏳</span><span>Memuat kamera...</span>';

        scanner = new Html5Qrcode("qr-reader");

        scanner.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: { width: 250, height: 250 }, aspectRatio: 1.0 },
            (decodedText) => {
                statusEl.innerHTML = '<span class="qr-status-icon">✅</span><span>QR Terdeteksi!</span>';
                stopScanner();

                // QR bisa berisi URL /booth/xxx atau plain ticket code
                let ticketCode = decodedText;
                const boothMatch = decodedText.match(/booth\/([A-Z0-9\-]+)/i);
                if (boothMatch) {
                    ticketCode = boothMatch[1];
                }

                document.getElementById('qr-ticket-code').value = ticketCode;
                document.getElementById('qrLookupForm').submit();
            },
            () => {}
        ).then(() => {
            isScanning = true;
            statusEl.innerHTML = '<span class="qr-status-icon scanning">📸</span><span>Arahkan kamera ke QR code tiket</span>';
        }).catch((err) => {
            statusEl.innerHTML = '<span class="qr-status-icon">❌</span><span>Kamera tidak tersedia. <a href="#" onclick="switchTab(\'manual\');return false;">Input manual</a></span>';
        });
    }

    function stopScanner() {
        if (scanner && isScanning) {
            scanner.stop().catch(() => {});
            isScanning = false;
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Confetti for exit gate animation
        var colors = ['#10b981','#34d399','#6ee7b7','#f59e0b','#818cf8','#ef4444'];
        var container = document.getElementById('exitConfetti');
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

        @if(!isset($ticket) && !session('exit_ticket'))
            // Auto-submit jika ada scanned_code dari QR booth URL
            const scannedCode = '{{ session("scanned_code", "") }}';
            if (scannedCode) {
                document.getElementById('qr-ticket-code').value = scannedCode;
                document.getElementById('qrLookupForm').submit();
                return;
            }
            startScanner();
        @endif
    });

    window.addEventListener('beforeunload', stopScanner);
</script>
@endpush
