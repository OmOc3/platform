<?php

namespace App\Modules\Commerce\Models;

use App\Shared\Enums\ProductKind;
use Database\Factories\OrderItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OrderItem extends Model
{
    /** @use HasFactory<OrderItemFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'order_id',
        'product_id',
        'product_kind',
        'product_name_snapshot',
        'quantity',
        'unit_price_amount',
        'total_price_amount',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'product_kind' => ProductKind::class,
            'meta' => 'array',
        ];
    }

    protected static function newFactory(): OrderItemFactory
    {
        return OrderItemFactory::new();
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function entitlement(): HasOne
    {
        return $this->hasOne(Entitlement::class);
    }
}
