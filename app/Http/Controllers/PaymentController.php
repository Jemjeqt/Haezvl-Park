<?php

namespace App\Http\Controllers;

use App\Services\TicketService;
use App\Models\Ticket;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        protected TicketService $ticketService
    ) {}

    public function index()
    {
        return view('payment');
    }

    public function lookup(Request $request)
    {
        $request->validate([
            'ticket_code' => 'required|string',
        ]);

        try {
            $result = $this->ticketService->lookupTicket($request->ticket_code);

            return view('payment', [
                'ticket' => $result['ticket'],
                'calculation' => $result['calculation'],
                'searched' => true,
            ]);
        } catch (\Exception $e) {
            return redirect()->route('payment.index')
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function pay(Request $request)
    {
        $request->validate([
            'ticket_code' => 'required|string',
            'payment_amount' => 'required|integer|min:0',
        ]);

        try {
            $result = $this->ticketService->processPayment(
                $request->ticket_code,
                $request->payment_amount
            );

            return redirect()->route('payment.receipt', $result['ticket']->id)
                ->with('success', 'Pembayaran berhasil!')
                ->with('change', $result['change']);
        } catch (\Exception $e) {
            return redirect()->route('payment.index')
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function receipt(Ticket $ticket)
    {
        $ticket->load('payment');

        return view('receipt', [
            'ticket' => $ticket,
            'payment' => $ticket->payment,
        ]);
    }
}
