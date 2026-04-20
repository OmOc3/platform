<?php

namespace App\Modules\Commerce\Models;

use App\Shared\Enums\PaymentStatus;
use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    /** @use HasFactory<PaymentFactory> */
    use HasFactory;

    protected $fillable = [
        'order_id',
        'attempt_number',
        'provider',
        'status',
        'amount',
        'currency',
        'provider_reference',
        'provider_transaction_reference',
        'checkout_url',
        'expires_at',
        'paid_at',
        'failed_at',
        'canceled_at',
        'refunded_at',
        'failure_code',
        'failure_message',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'status' => PaymentStatus::class,
            'expires_at' => 'datetime',
            'paid_at' => 'datetime',
            'failed_at' => 'datetime',
            'canceled_at' => 'datetime',
            'refunded_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    protected static function newFactory(): PaymentFactory
    {
        return PaymentFactory::new();
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function webhookReceipts(): HasMany
    {
        return $this->hasMany(PaymentWebhookReceipt::class);
    }
}
