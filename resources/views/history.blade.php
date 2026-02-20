@extends('layout')
@section('title', 'History')
@section('page-title', 'Riwayat Parkir')

@section('content')
{{-- Filter --}}
<div class="card">
    <div class="card-header">
        <h2 class="card-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon-sm">
                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
            </svg>
            Filter & Cari
        </h2>
    </div>
    <div class="card-body">
        <form action="{{ route('history.index') }}" method="GET" class="filter-form">
            <div class="filter-grid">
                <div class="form-group">
                    <label class="form-label">Dari Tanggal</label>
                    <input type="date" name="date_from" class="form-input" value="{{ $filters['date_from'] ?? '' }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Sampai Tanggal</label>
                    <input type="date" name="date_to" class="form-input" value="{{ $filters['date_to'] ?? '' }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Jenis Kendaraan</label>
                    <select name="vehicle_type" class="form-input">
                        <option value="">Semua</option>
                        <option value="motor" {{ ($filters['vehicle_type'] ?? '') == 'motor' ? 'selected' : '' }}>🏍️ Motor</option>
                        <option value="mobil" {{ ($filters['vehicle_type'] ?? '') == 'mobil' ? 'selected' : '' }}>🚗 Mobil</option>
                        <option value="truk" {{ ($filters['vehicle_type'] ?? '') == 'truk' ? 'selected' : '' }}>🚛 Truk</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Cari (Plat / Kode)</label>
                    <input type="text" name="search" class="form-input" placeholder="Cari..." value="{{ $filters['search'] ?? '' }}">
                </div>
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon-sm">
                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    Terapkan
                </button>
                <a href="{{ route('history.index') }}" class="btn btn-secondary">Reset</a>
                <button type="button" class="btn btn-success" onclick="downloadPDF()">
                    📄 Download PDF
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Summary --}}
<div class="stats-grid mt-md" style="grid-template-columns: repeat(2, 1fr);">
    <div class="stat-card stat-exit">
        <div class="stat-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
            </svg>
        </div>
        <div class="stat-info">
            <span class="stat-number">{{ number_format($totalTickets) }}</span>
            <span class="stat-label">Total Transaksi</span>
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
            <span class="stat-number">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</span>
            <span class="stat-label">Total Pendapatan</span>
        </div>
    </div>
</div>

{{-- Table --}}
<div class="card mt-md" id="historyTable">
    <div class="card-header">
        <h2 class="card-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon-sm">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="16" y1="13" x2="8" y2="13"/>
                <line x1="16" y1="17" x2="8" y2="17"/>
            </svg>
            Data Riwayat
        </h2>
        <span class="badge badge-count">{{ $tickets->total() }} data</span>
    </div>
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode Tiket</th>
                    <th>Plat Nomor</th>
                    <th>Jenis</th>
                    <th>Masuk</th>
                    <th>Keluar</th>
                    <th>Durasi</th>
                    <th>Total Bayar</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tickets as $i => $ticket)
                    <tr>
                        <td>{{ $tickets->firstItem() + $i }}</td>
                        <td><code class="ticket-code">{{ $ticket->ticket_code }}</code></td>
                        <td><strong>{{ $ticket->plate_number }}</strong></td>
                        <td>
                            <span class="badge badge-{{ $ticket->vehicle_type }}">
                                {{ ucfirst($ticket->vehicle_type) }}
                            </span>
                        </td>
                        <td>{{ $ticket->entry_time->format('d/m/Y H:i') }}</td>
                        <td>{{ $ticket->exit_time ? $ticket->exit_time->format('d/m/Y H:i') : '-' }}</td>
                        <td>
                            @if($ticket->entry_time && $ticket->exit_time)
                                {{ $ticket->entry_time->diffForHumans($ticket->exit_time, true) }}
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($ticket->payment)
                                <strong>Rp {{ number_format($ticket->payment->final_amount, 0, ',', '.') }}</strong>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="empty-state">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="empty-icon">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <polyline points="14 2 14 8 20 8"/>
                            </svg>
                            <p>Belum ada riwayat parkir</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($tickets->hasPages())
        <div class="pagination-wrap">
            {{ $tickets->links() }}
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function downloadPDF() {
    // Buat window baru dengan tabel untuk di-print sebagai PDF
    const table = document.querySelector('#historyTable .data-table');
    if (!table) return;

    const win = window.open('', '_blank');
    win.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Riwayat Parkir — Haezvl Park</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body {
                    font-family: 'Segoe UI', Arial, sans-serif;
                    padding: 20px;
                    color: #1a1a2e;
                }
                .header {
                    text-align: center;
                    margin-bottom: 20px;
                    padding-bottom: 15px;
                    border-bottom: 2px solid #333;
                }
                .header h1 { font-size: 1.4rem; margin-bottom: 4px; }
                .header p { font-size: 0.85rem; color: #666; }
                .summary {
                    display: flex;
                    gap: 30px;
                    margin-bottom: 15px;
                    font-size: 0.9rem;
                }
                .summary strong { color: #10b981; }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    font-size: 0.8rem;
                }
                th, td {
                    padding: 6px 8px;
                    text-align: left;
                    border: 1px solid #ddd;
                }
                th {
                    background: #1e293b;
                    color: #fff;
                    font-weight: 600;
                }
                tr:nth-child(even) { background: #f8fafc; }
                .footer {
                    margin-top: 15px;
                    font-size: 0.75rem;
                    color: #999;
                    text-align: center;
                }
                @media print {
                    body { padding: 10px; }
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>📊 Riwayat Parkir — Haezvl Park</h1>
                <p>Dicetak: ${new Date().toLocaleString('id-ID')}</p>
            </div>
            <div class="summary">
                <span>Total Transaksi: <strong>{{ $totalTickets }}</strong></span>
                <span>Total Pendapatan: <strong>Rp {{ number_format($totalRevenue, 0, ',', '.') }}</strong></span>
            </div>
            ${table.outerHTML}
            <div class="footer">
                Haezvl Park — Sistem Manajemen Parkir Semi-Otomatis
            </div>
        </body>
        </html>
    `);
    win.document.close();

    // Tunggu render lalu print
    setTimeout(() => {
        win.print();
    }, 500);
}
</script>
@endpush
