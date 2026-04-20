<?php

namespace App\Modules\Commerce\Models;

use App\Shared\Enums\ShipmentStatus;
use Database\Factories\ShipmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shipment extends Model
{
    /** @use HasFactory<ShipmentFactory> */
    use HasFactory;

    protected $fillable = [
        'order_id',
        'status',
        'recipient_name',
        'phone',
        'alternate_phone',
        'governorate',
        'city',
        'address_line1',
        'address_line2',
        'landmark',
        'shipping_fee_amount',
        'currency',
        'carrier_name',
        'carrier_reference',
        'prepared_at',
        'handed_to_carrier_at',
        'in_transit_at',
        'delivered_at',
        'returned_at',
        'canceled_at',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'status' => ShipmentStatus::class,
            'prepared_at' => 'datetime',
            'handed_to_carrier_at' => 'datetime',
            'in_transit_at' => 'datetime',
            'delivered_at' => 'datetime',
            'returned_at' => 'datetime',
            'canceled_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    protected static function newFactory(): ShipmentFactory
    {
        return ShipmentFactory::new();
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
