<?php

namespace App\Modules\Commerce\Models;

use App\Modules\Students\Models\Student;
use App\Shared\Enums\OrderKind;
use App\Shared\Enums\OrderStatus;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'student_id',
        'kind',
        'status',
        'subtotal_amount',
        'total_amount',
        'currency',
        'placed_at',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'kind' => OrderKind::class,
            'status' => OrderStatus::class,
            'placed_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    protected static function newFactory(): OrderFactory
    {
        return OrderFactory::new();
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->latest('created_at');
    }

    public function shipment(): HasOne
    {
        return $this->hasOne(Shipment::class);
    }
}
