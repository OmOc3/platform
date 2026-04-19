<?php

namespace App\Modules\Commerce\Queries;

use App\Modules\Commerce\Models\Product;
use App\Shared\Enums\ProductKind;
use Illuminate\Database\Eloquent\Collection;

class FeaturedPackagesQuery
{
    public function get(int $limit = 3): Collection
    {
        return Product::query()
            ->with('package')
            ->where('kind', ProductKind::Package->value)
            ->where('is_active', true)
            ->where('is_featured', true)
            ->latest('published_at')
            ->limit($limit)
            ->get();
    }
}
