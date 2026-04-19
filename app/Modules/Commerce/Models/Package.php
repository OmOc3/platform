<?php

namespace App\Modules\Commerce\Models;

use Database\Factories\PackageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    /** @use HasFactory<PackageFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'product_id',
        'billing_cycle_label',
        'lecture_count',
        'access_period_days',
        'is_featured',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'metadata' => 'array',
        ];
    }

    protected static function newFactory(): PackageFactory
    {
        return PackageFactory::new();
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PackageItem::class)->orderBy('sort_order');
    }
}
