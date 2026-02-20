<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tariff extends Model
{
    protected $fillable = [
        'vehicle_type',
        'hourly_rate',
        'daily_max',
        'grace_period_minutes',
    ];

    public static function getByVehicleType(string $type): ?self
    {
        return static::where('vehicle_type', $type)->first();
    }
}
