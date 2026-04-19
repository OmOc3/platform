<?php

namespace App\Modules\Commerce\Models;

use App\Modules\Commerce\Enums\BookAvailability;
use Database\Factories\BookFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Book extends Model
{
    /** @use HasFactory<BookFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'product_id',
        'author_name',
        'page_count',
        'stock_quantity',
        'cover_badge',
        'availability_status',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'availability_status' => BookAvailability::class,
            'metadata' => 'array',
        ];
    }

    protected static function newFactory(): BookFactory
    {
        return BookFactory::new();
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
