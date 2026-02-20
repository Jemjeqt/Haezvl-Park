<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParkingSetting extends Model
{
    protected $table = 'parking_settings';

    protected $fillable = ['key', 'value'];

    /**
     * Get setting value by key
     */
    public static function getValue(string $key, $default = null): ?string
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Get capacity for a vehicle type
     */
    public static function getCapacity(string $vehicleType): int
    {
        return (int) static::getValue("capacity_{$vehicleType}", 0);
    }

    /**
     * Get expire minutes after payment
     */
    public static function getExpireMinutes(): int
    {
        return (int) static::getValue('expire_minutes', 15);
    }
}
