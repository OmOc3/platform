<?php

namespace App\Modules\Commerce\Queries;

use App\Modules\Commerce\Models\Book;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class BookCatalogQuery
{
    public function paginate(Request $request): LengthAwarePaginator
    {
        $search = $request->string('search')->toString();

        return Book::query()
            ->select('books.*')
            ->join('products', 'products.id', '=', 'books.product_id')
            ->with('product')
            ->whereHas('product', function ($query) use ($search): void {
                $query->where('is_active', true)
                    ->when($search !== '', function ($builder) use ($search): void {
                        $builder->where(function ($inner) use ($search): void {
                            $inner->where('name_ar', 'like', "%{$search}%")
                                ->orWhere('teaser', 'like', "%{$search}%");
                        });
                    });
            })
            ->orderByDesc('products.is_featured')
            ->orderByDesc('products.published_at')
            ->paginate(12)
            ->withQueryString();
    }
}
