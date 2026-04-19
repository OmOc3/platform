<?php

namespace App\Modules\Commerce\Models;

use App\Modules\Identity\Models\Admin;
use App\Modules\Students\Models\Student;
use App\Shared\Enums\EntitlementSource;
use Database\Factories\EntitlementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Entitlement extends Model
{
    /** @use HasFactory<EntitlementFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'student_id',
        'product_id',
        'order_item_id',
        'source',
        'status',
        'item_name_snapshot',
        'price_amount',
        'currency',
        'granted_by_admin_id',
        'granted_at',
        'starts_at',
        'ends_at',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'source' => EntitlementSource::class,
            'granted_at' => 'datetime',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    protected static function newFactory(): EntitlementFactory
    {
        return EntitlementFactory::new();
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function grantedByAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'granted_by_admin_id');
    }
}
