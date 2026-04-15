<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'provider',
        'payment_method',
        'reference',
        'provider_reference',
        'amount',
        'currency',
        'status',
        'checkout_url',
        'provider_payload',
        'last_webhook_at',
        'reconciled_at',
        'paid_at',
        'failed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'provider_payload' => 'array',
        'last_webhook_at' => 'datetime',
        'reconciled_at' => 'datetime',
        'paid_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    public function order(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
