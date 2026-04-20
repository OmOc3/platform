<?php

namespace App\Modules\Commerce\Models;

use Database\Factories\PaymentWebhookReceiptFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentWebhookReceipt extends Model
{
    /** @use HasFactory<PaymentWebhookReceiptFactory> */
    use HasFactory;

    protected $fillable = [
        'provider',
        'event_key',
        'payment_id',
        'order_id',
        'status',
        'payload',
        'processed_at',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'processed_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    protected static function newFactory(): PaymentWebhookReceiptFactory
    {
        return PaymentWebhookReceiptFactory::new();
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
