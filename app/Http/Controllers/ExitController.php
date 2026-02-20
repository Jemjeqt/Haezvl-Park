<?php

namespace App\Http\Controllers;

use App\Services\TicketService;
use App\Models\Ticket;
use Illuminate\Http\Request;

class ExitController extends Controller
{
    public function __construct(
        protected TicketService $ticketService
    ) {}

    /**
     * Halaman Exit Gate (scan/input → bayar → keluar)
     */
    public function index()
    {
        return view('exit');
    }

    /**
     * Booth: QR scan dari HP membuka URL ini
     * Redirect ke exit gate dengan ticket code pre-filled
     */
    public function booth(string $ticketCode)
    {
        return redirect()->route('exit.index')->with('scanned_code', strtoupper($ticketCode));
    }

    /**
     * Step 1: Lookup ticket — menampilkan data + tarif
     */
    public function lookup(Request $request)
    {
        $request->validate([
            'ticket_code' => 'required|string',
        ]);

        try {
            $result = $this->ticketService->lookupForExit($request->ticket_code);

            return view('exit', [
                'ticket' => $result['ticket'],
                'calculation' => $result['calculation'],
                'already_paid' => $result['already_paid'],
                'searched' => true,
            ]);
        } catch (\Exception $e) {
            return redirect()->route('exit.index')
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Step 2: Bayar + Keluar sekaligus
     */
    public function payAndExit(Request $request)
    {
        $request->validate([
            'ticket_code' => 'required|string',
            'payment_amount' => 'required|integer|min:0',
        ]);

        try {
            $result = $this->ticketService->processPaymentAndExit(
                $request->ticket_code,
                $request->payment_amount
            );

            return redirect()->route('exit.index')
                ->with('success', 'Pembayaran berhasil! Gate terbuka.')
                ->with('exit_ticket', $result['ticket'])
                ->with('exit_payment', $result['payment'])
                ->with('change', $result['change']);
        } catch (\Exception $e) {
            return redirect()->route('exit.index')
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Printable exit receipt
     */
    public function receipt(Ticket $ticket)
    {
        $ticket->load('payment');

        return view('exit-receipt', [
            'ticket' => $ticket,
            'payment' => $ticket->payment,
        ]);
    }
}
