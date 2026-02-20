<?php

namespace App\Http\Controllers;

use App\Services\TicketService;
use App\Models\Ticket;
use Illuminate\Http\Request;

class EntryController extends Controller
{
    public function __construct(
        protected TicketService $ticketService
    ) {}

    public function index()
    {
        $capacity = [
            'motor' => $this->ticketService->checkCapacity('motor'),
            'mobil' => $this->ticketService->checkCapacity('mobil'),
            'truk' => $this->ticketService->checkCapacity('truk'),
        ];

        return view('entry', compact('capacity'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'plate_number' => 'required|string|max:20',
            'vehicle_type' => 'required|in:motor,mobil,truk',
        ]);

        try {
            $ticket = $this->ticketService->issueTicket(
                $request->plate_number,
                $request->vehicle_type
            );

            return redirect()->route('entry.index')
                ->with('success', 'Tiket berhasil dicetak!')
                ->with('ticket', $ticket);
        } catch (\Exception $e) {
            return redirect()->route('entry.index')
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Printable entry ticket
     */
    public function ticket(Ticket $ticket)
    {
        return view('entry-ticket', compact('ticket'));
    }
}
