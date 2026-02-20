<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'ticket_id',
        'duration_minutes',
        'base_amount',
        'discount',
        'final_amount',
        'payment_method',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }
}
