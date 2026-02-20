<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tiket Parkir — {{ $ticket->ticket_code }}</title>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'JetBrains Mono', monospace;
            background: #0f172a;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
            padding: 2rem;
        }

        .receipt {
            background: #fff;
            color: #1e293b;
            width: 320px;
            padding: 1.5rem;
            border-radius: 4px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
            position: relative;
        }

        .receipt::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            right: 0;
            height: 10px;
            background: linear-gradient(135deg, #fff 33.33%, transparent 33.33%),
                        linear-gradient(225deg, #fff 33.33%, transparent 33.33%);
            background-size: 12px 10px;
        }

        .receipt-header {
            text-align: center;
            border-bottom: 2px dashed #cbd5e1;
            padding-bottom: 1rem;
            margin-bottom: 1rem;
        }

        .receipt-brand {
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: 0.2em;
        }

        .receipt-sub {
            font-size: 0.7rem;
            color: #64748b;
            margin-top: 0.25rem;
        }

        .receipt-qr {
            text-align: center;
            padding: 0.75rem 0;
        }

        .receipt-qr img {
            max-width: 200px;
            height: auto;
            border-radius: 6px;
        }

        .receipt-code {
            text-align: center;
            font-size: 1.2rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            padding: 0.5rem 0;
        }

        .receipt-row {
            display: flex;
            justify-content: space-between;
            padding: 0.35rem 0;
            font-size: 0.75rem;
        }

        .receipt-row .label {
            color: #64748b;
        }

        .receipt-divider {
            border: none;
            border-top: 1px dashed #e2e8f0;
            margin: 0.5rem 0;
        }

        .receipt-status-wrap {
            text-align: center;
            margin: 0.5rem 0;
        }

        .receipt-status {
            display: inline-block;
            background: #3b82f6;
            color: #fff;
            padding: 0.2rem 0.75rem;
            border-radius: 3px;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.1em;
        }

        .receipt-footer {
            text-align: center;
            border-top: 2px dashed #cbd5e1;
            margin-top: 1rem;
            padding-top: 1rem;
            font-size: 0.65rem;
            color: #94a3b8;
        }

        .receipt-notice {
            text-align: center;
            margin-top: 0.75rem;
            padding: 0.5rem;
            background: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 4px;
            font-size: 0.65rem;
            color: #92400e;
            font-weight: 500;
        }

        .btn-group {
            display: flex;
            gap: 0.5rem;
            margin-top: 1.5rem;
        }

        .btn {
            flex: 1;
            padding: 0.75rem;
            border: none;
            border-radius: 8px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
        }

        .btn-print { background: #3b82f6; color: #fff; }
        .btn-download { background: #10b981; color: #fff; }
        .btn-back { background: #334155; color: #cbd5e1; }

        @media print {
            body { background: #fff; padding: 0; }
            .receipt { box-shadow: none; width: 100%; }
            .btn-group { display: none; }
        }
    </style>
</head>
<body>
    <div>
        <div class="receipt">
            <div class="receipt-header">
                <div class="receipt-brand">HAEZVL PARK</div>
                <div class="receipt-sub">Parking Management System</div>
                <div class="receipt-sub">Tiket Masuk Parkir</div>
            </div>

            <div class="receipt-qr" id="entryQR"></div>

            <div class="receipt-code">{{ $ticket->ticket_code }}</div>

            <div class="receipt-status-wrap">
                <span class="receipt-status">🚗 MASUK</span>
            </div>

            <hr class="receipt-divider">

            <div class="receipt-body">
                <div class="receipt-row">
                    <span class="label">Plat Nomor</span>
                    <span>{{ $ticket->plate_number }}</span>
                </div>
                <div class="receipt-row">
                    <span class="label">Jenis</span>
                    <span>{{ ucfirst($ticket->vehicle_type) }}</span>
                </div>
                <div class="receipt-row">
                    <span class="label">Waktu Masuk</span>
                    <span>{{ $ticket->entry_time->format('d/m/Y H:i:s') }}</span>
                </div>
            </div>

            <div class="receipt-notice">
                ⚠️ Simpan tiket ini! Scan QR code di Exit Gate
                untuk bayar & keluar langsung.
            </div>

            <div class="receipt-footer">
                <p>Tiket ini adalah bukti masuk area parkir</p>
                <p style="margin-top: 0.25rem;">Kehilangan tiket dikenakan denda</p>
                <p style="margin-top: 0.25rem;">{{ $ticket->entry_time->format('d/m/Y H:i:s') }}</p>
            </div>
        </div>

        <div class="btn-group">
            <button class="btn btn-print" onclick="window.print()">🖨 Cetak</button>
            <button class="btn btn-download" onclick="downloadImage()">📥 Download</button>
            <a href="{{ route('entry.index') }}" class="btn btn-back">← Kembali</a>
        </div>
    </div>

    <script>
        var qr = qrcode(0, 'L');
        qr.addData('{{ url("/booth/" . $ticket->ticket_code) }}');
        qr.make();
        document.getElementById('entryQR').innerHTML = qr.createImgTag(8, 4);

        function downloadImage() {
            const el = document.querySelector('.receipt');
            html2canvas(el, { scale: 3, backgroundColor: '#ffffff', useCORS: true }).then(canvas => {
                const a = document.createElement('a');
                a.download = 'tiket-{{ $ticket->ticket_code }}.png';
                a.href = canvas.toDataURL('image/png');
                a.click();
            });
        }
    </script>
</body>
</html>
