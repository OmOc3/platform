<?php

namespace App\Modules\Commerce\Models;

use App\Modules\Academic\Models\Lecture;
use App\Shared\Enums\ProductKind;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'kind',
        'slug',
        'name_ar',
        'name_en',
        'teaser',
        'description',
        'price_amount',
        'currency',
        'thumbnail_url',
        'is_active',
        'is_featured',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'kind' => ProductKind::class,
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    protected static function newFactory(): ProductFactory
    {
        return ProductFactory::new();
    }

    public function package(): HasOne
    {
        return $this->hasOne(Package::class);
    }

    public function book(): HasOne
    {
        return $this->hasOne(Book::class);
    }

    public function lecture(): HasOne
    {
        return $this->hasOne(Lecture::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function entitlements(): HasMany
    {
        return $this->hasMany(Entitlement::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }
}
