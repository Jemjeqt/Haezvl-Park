@extends('layout')
@section('title', 'Payment')
@section('page-title', 'Payment Booth')

@section('content')
<div class="module-grid">
    {{-- Lookup Form with QR Scanner --}}
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon-sm">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                Cari Tiket
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
                        <line x1="21" y1="14" x2="21" y2="14.01"/>
                        <line x1="21" y1="21" x2="21" y2="21.01"/>
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

            {{-- QR Scanner Panel --}}
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

            {{-- Manual Input Panel --}}
            <div class="scan-panel" id="panel-manual" style="display:none;">
                <form action="{{ route('payment.lookup') }}" method="POST" id="lookupForm">
                    @csrf
                    <div class="form-group">
                        <label for="ticket_code" class="form-label">Kode Tiket</label>
                        <input
                            type="text"
                            id="ticket_code"
                            name="ticket_code"
                            class="form-input"
                            placeholder="PKR-XXXXXXXX-XXXX"
                            value="{{ old('ticket_code') }}"
                            required
                            autocomplete="off"
                            style="text-transform: uppercase"
                        >
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon-sm">
                            <circle cx="11" cy="11" r="8"/>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                        Cari Tiket
                    </button>
                </form>
            </div>

            {{-- Hidden form for QR scan submission --}}
            <form action="{{ route('payment.lookup') }}" method="POST" id="qrLookupForm" style="display:none;">
                @csrf
                <input type="hidden" name="ticket_code" id="qr-ticket-code">
            </form>
        </div>
    </div>

    {{-- Payment Detail --}}
    @if(isset($ticket) && isset($calculation))
        <div class="card animate-in">
            <div class="card-header">
                <h2 class="card-title">💳 Detail Pembayaran</h2>
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

                <form action="{{ route('payment.pay') }}" method="POST" class="mt-md">
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
                        Bayar Sekarang
                    </button>
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
            {
                fps: 10,
                qrbox: { width: 250, height: 250 },
                aspectRatio: 1.0,
            },
            (decodedText) => {
                // QR berhasil discan
                statusEl.innerHTML = '<span class="qr-status-icon">✅</span><span>QR Terdeteksi: <strong>' + decodedText + '</strong></span>';
                stopScanner();

                // Auto-submit lookup
                document.getElementById('qr-ticket-code').value = decodedText;
                document.getElementById('qrLookupForm').submit();
            },
            (errorMessage) => {
                // Still scanning...
            }
        ).then(() => {
            isScanning = true;
            statusEl.innerHTML = '<span class="qr-status-icon scanning">📸</span><span>Arahkan kamera ke QR code tiket</span>';
        }).catch((err) => {
            console.error('Camera error:', err);
            statusEl.innerHTML = '<span class="qr-status-icon">❌</span><span>Kamera tidak tersedia. Gunakan <a href="#" onclick="switchTab(\'manual\');return false;">input manual</a>.</span>';
        });
    }

    function stopScanner() {
        if (scanner && isScanning) {
            scanner.stop().catch(() => {});
            isScanning = false;
        }
    }

    // Auto-start scanner on page load
    document.addEventListener('DOMContentLoaded', () => {
        @if(!isset($ticket))
            startScanner();
        @endif
    });

    // Cleanup on page leave
    window.addEventListener('beforeunload', stopScanner);
</script>
@endpush
