<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Payment;
use App\Models\ParkingSetting;
use App\Services\TicketService;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct(
        protected TicketService $ticketService
    ) {}

    public function index()
    {
        $today = Carbon::today();

        // Kendaraan aktif (IN + PAID)
        $activeTickets = Ticket::active()->orderBy('entry_time', 'desc')->get();
        $totalActive = $activeTickets->count();

        // Breakdown per jenis kendaraan
        $activeByType = [
            'motor' => $activeTickets->where('vehicle_type', 'motor')->count(),
            'mobil' => $activeTickets->where('vehicle_type', 'mobil')->count(),
            'truk' => $activeTickets->where('vehicle_type', 'truk')->count(),
        ];

        // Total pemasukan hari ini
        $todayRevenue = Payment::whereDate('created_at', $today)->sum('final_amount');

        // Kendaraan keluar hari ini
        $todayExited = Ticket::where('status', 'OUT')
            ->whereDate('exit_time', $today)
            ->count();

        // Total kendaraan masuk hari ini
        $todayEntered = Ticket::whereDate('entry_time', $today)->count();

        // Kapasitas per jenis
        $capacity = [
            'motor' => $this->ticketService->checkCapacity('motor'),
            'mobil' => $this->ticketService->checkCapacity('mobil'),
            'truk' => $this->ticketService->checkCapacity('truk'),
        ];

        // Expire minutes setting
        $expireMinutes = ParkingSetting::getExpireMinutes();

        return view('dashboard', compact(
            'activeTickets',
            'totalActive',
            'activeByType',
            'todayRevenue',
            'todayExited',
            'todayEntered',
            'capacity',
            'expireMinutes'
        ));
    }
}
