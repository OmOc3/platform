<?php

namespace App\Modules\Commerce\Models;

use Database\Factories\PackageItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PackageItem extends Model
{
    /** @use HasFactory<PackageItemFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'package_id',
        'item_type',
        'item_id',
        'item_name_snapshot',
        'sort_order',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }

    protected static function newFactory(): PackageItemFactory
    {
        return PackageItemFactory::new();
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function item(): MorphTo
    {
        return $this->morphTo();
    }
}
