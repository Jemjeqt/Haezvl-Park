<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Ticket extends Model
{
    protected $fillable = [
        'ticket_code',
        'plate_number',
        'vehicle_type',
        'status',
        'entry_time',
        'paid_time',
        'exit_time',
    ];

    protected function casts(): array
    {
        return [
            'entry_time' => 'datetime',
            'paid_time' => 'datetime',
            'exit_time' => 'datetime',
        ];
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['IN', 'PAID']);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('entry_time', today());
    }
}
