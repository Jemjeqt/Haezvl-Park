<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\Payment;
use App\Models\ParkingSetting;
use Carbon\Carbon;
use Illuminate\Support\Str;

class TicketService
{
    public function __construct(
        protected TariffCalculator $tariffCalculator
    ) {}

    /**
     * Generate kode tiket unik: PKR-YYYYMMDD-XXXX
     */
    protected function generateTicketCode(): string
    {
        $date = now()->format('Ymd');
        $random = strtoupper(Str::random(4));
        $code = "PKR-{$date}-{$random}";

        while (Ticket::where('ticket_code', $code)->exists()) {
            $random = strtoupper(Str::random(4));
            $code = "PKR-{$date}-{$random}";
        }

        return $code;
    }

    /**
     * Cek ketersediaan slot parkir
     */
    public function checkCapacity(string $vehicleType): array
    {
        $capacity = ParkingSetting::getCapacity($vehicleType);
        $occupied = Ticket::where('vehicle_type', $vehicleType)->active()->count();
        $available = max(0, $capacity - $occupied);

        return [
            'capacity' => $capacity,
            'occupied' => $occupied,
            'available' => $available,
            'is_full' => $available <= 0,
        ];
    }

    /**
     * Cek apakah tiket PAID sudah expired
     */
    public function checkAndExpireTicket(Ticket $ticket): Ticket
    {
        if ($ticket->status !== 'PAID' || !$ticket->paid_time) {
            return $ticket;
        }

        $expireMinutes = ParkingSetting::getExpireMinutes();
        $expiredAt = $ticket->paid_time->addMinutes($expireMinutes);

        if (now()->greaterThan($expiredAt)) {
            $ticket->payment?->delete();
            $ticket->update([
                'status' => 'IN',
                'paid_time' => null,
            ]);
            return $ticket->fresh();
        }

        return $ticket;
    }

    /**
     * Buat tiket baru (Entry Gate)
     */
    public function issueTicket(string $plateNumber, string $vehicleType): Ticket
    {
        $plateNumber = strtoupper(trim($plateNumber));

        $existing = Ticket::where('plate_number', $plateNumber)
            ->active()
            ->first();

        if ($existing) {
            throw new \Exception("Kendaraan dengan plat {$plateNumber} masih berada di area parkir (Tiket: {$existing->ticket_code}).");
        }

        $capacity = $this->checkCapacity($vehicleType);
        if ($capacity['is_full']) {
            throw new \Exception("Slot parkir untuk " . ucfirst($vehicleType) . " sudah penuh ({$capacity['occupied']}/{$capacity['capacity']}).");
        }

        return Ticket::create([
            'ticket_code' => $this->generateTicketCode(),
            'plate_number' => $plateNumber,
            'vehicle_type' => $vehicleType,
            'status' => 'IN',
            'entry_time' => now(),
        ]);
    }

    /**
     * Lookup tiket untuk exit gate (pembayaran + keluar)
     * Menampilkan data tiket dan kalkulasi tarif
     */
    public function lookupForExit(string $ticketCode): array
    {
        $ticket = Ticket::where('ticket_code', strtoupper(trim($ticketCode)))->first();

        if (!$ticket) {
            throw new \Exception('Tiket tidak ditemukan.');
        }

        if ($ticket->status === 'OUT') {
            throw new \Exception('Tiket sudah digunakan (OUT).');
        }

        // Cek auto-expire
        $ticket = $this->checkAndExpireTicket($ticket);

        $calculation = $this->tariffCalculator->calculate(
            $ticket->vehicle_type,
            $ticket->entry_time
        );

        // Jika sudah PAID (belum expire), tampilkan info bahwa tinggal keluar
        $alreadyPaid = $ticket->status === 'PAID';

        return [
            'ticket' => $ticket,
            'calculation' => $calculation,
            'already_paid' => $alreadyPaid,
        ];
    }

    /**
     * Proses pembayaran + keluar sekaligus (Exit Gate)
     * Bayar → status langsung OUT → gate terbuka
     */
    public function processPaymentAndExit(string $ticketCode, int $paymentAmount): array
    {
        $ticket = Ticket::where('ticket_code', strtoupper(trim($ticketCode)))->first();

        if (!$ticket) {
            throw new \Exception('Tiket tidak ditemukan.');
        }

        if ($ticket->status === 'OUT') {
            throw new \Exception('Tiket sudah digunakan (OUT).');
        }

        // Cek auto-expire
        $ticket = $this->checkAndExpireTicket($ticket);

        // Jika sudah PAID dan belum expire → langsung keluar tanpa bayar lagi
        if ($ticket->status === 'PAID') {
            $ticket->load('payment');
            $ticket->update([
                'status' => 'OUT',
                'exit_time' => now(),
            ]);

            return [
                'ticket' => $ticket->fresh(),
                'payment' => $ticket->payment,
                'change' => 0,
                'already_paid' => true,
            ];
        }

        // Status IN → hitung tarif, bayar, langsung keluar
        $calculation = $this->tariffCalculator->calculate(
            $ticket->vehicle_type,
            $ticket->entry_time
        );

        if ($paymentAmount < $calculation['final_amount']) {
            $sisa = $calculation['final_amount'] - $paymentAmount;
            throw new \Exception("Pembayaran kurang. Sisa: Rp " . number_format($sisa, 0, ',', '.'));
        }

        // Simpan pembayaran
        $payment = Payment::create([
            'ticket_id' => $ticket->id,
            'duration_minutes' => $calculation['duration_minutes'],
            'base_amount' => $calculation['base_amount'],
            'discount' => $calculation['discount'],
            'final_amount' => $calculation['final_amount'],
            'payment_method' => 'cash',
        ]);

        // Langsung update status ke OUT (bayar + keluar sekaligus)
        $ticket->update([
            'status' => 'OUT',
            'paid_time' => now(),
            'exit_time' => now(),
        ]);

        $change = $paymentAmount - $calculation['final_amount'];

        return [
            'ticket' => $ticket->fresh(),
            'payment' => $payment,
            'change' => $change,
            'already_paid' => false,
        ];
    }
}
