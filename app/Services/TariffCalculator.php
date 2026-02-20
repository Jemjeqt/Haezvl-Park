<?php

namespace App\Services;

use App\Models\Tariff;
use Carbon\Carbon;

class TariffCalculator
{
    /**
     * Hitung tarif parkir berdasarkan jenis kendaraan dan durasi.
     *
     * @return array{duration_minutes: int, hours_charged: int, base_amount: int, discount: int, final_amount: int}
     */
    public function calculate(string $vehicleType, Carbon $entryTime, ?Carbon $currentTime = null): array
    {
        $currentTime = $currentTime ?? now();
        $tariff = Tariff::getByVehicleType($vehicleType);

        if (!$tariff) {
            throw new \InvalidArgumentException("Tarif untuk jenis kendaraan '{$vehicleType}' tidak ditemukan.");
        }

        $durationMinutes = max(0, $entryTime->diffInMinutes($currentTime));

        // Grace period: <= 5 menit gratis
        if ($durationMinutes <= 5) {
            return [
                'duration_minutes' => (int) $durationMinutes,
                'hours_charged' => 0,
                'base_amount' => 0,
                'discount' => 0,
                'final_amount' => 0,
            ];
        }

        // Setelah grace period, minimum charge = 1 jam
        $hoursCharged = max(1, (int) ceil($durationMinutes / 60));
        $baseAmount = $hoursCharged * $tariff->hourly_rate;

        // Cap maksimum harian
        $finalAmount = min($baseAmount, $tariff->daily_max);
        $discount = $baseAmount - $finalAmount;

        return [
            'duration_minutes' => (int) $durationMinutes,
            'hours_charged' => $hoursCharged,
            'base_amount' => $baseAmount,
            'discount' => $discount,
            'final_amount' => $finalAmount,
        ];
    }
}
