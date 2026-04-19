<?php

namespace App\Modules\Commerce\Queries;

use App\Modules\Commerce\Models\Product;
use App\Shared\Enums\ProductKind;
use Illuminate\Database\Eloquent\Collection;

class FeaturedBooksQuery
{
    public function get(int $limit = 3): Collection
    {
        return Product::query()
            ->with('book')
            ->where('kind', ProductKind::Book->value)
            ->where('is_active', true)
            ->where('is_featured', true)
            ->latest('published_at')
            ->limit($limit)
            ->get();
    }
}
