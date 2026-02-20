<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Payment;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Ticket::with('payment')
            ->where('status', 'OUT')
            ->orderBy('exit_time', 'desc');

        // Filter tanggal
        if ($request->filled('date_from')) {
            $query->whereDate('entry_time', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('entry_time', '<=', $request->date_to);
        }

        // Filter jenis kendaraan
        if ($request->filled('vehicle_type')) {
            $query->where('vehicle_type', $request->vehicle_type);
        }

        // Search plat / kode tiket
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('plate_number', 'like', "%{$s}%")
                  ->orWhere('ticket_code', 'like', "%{$s}%");
            });
        }

        $tickets = $query->paginate(20)->appends($request->query());

        // Summary
        $summaryQuery = Ticket::where('status', 'OUT');
        if ($request->filled('date_from')) {
            $summaryQuery->whereDate('entry_time', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $summaryQuery->whereDate('entry_time', '<=', $request->date_to);
        }
        if ($request->filled('vehicle_type')) {
            $summaryQuery->where('vehicle_type', $request->vehicle_type);
        }

        $totalTickets = $summaryQuery->count();
        $totalRevenue = Payment::whereIn('ticket_id', (clone $summaryQuery)->pluck('id'))->sum('final_amount');

        return view('history', [
            'tickets' => $tickets,
            'totalTickets' => $totalTickets,
            'totalRevenue' => $totalRevenue,
            'filters' => $request->only(['date_from', 'date_to', 'vehicle_type', 'search']),
        ]);
    }
}
