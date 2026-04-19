<?php

namespace App\Modules\Commerce\Queries;

use App\Modules\Commerce\Models\Book;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class BooksIndexQuery
{
    public function builder(Request $request): Builder
    {
        $search = $request->string('search')->toString();
        $availability = $request->string('availability')->toString();

        return Book::query()
            ->select('books.*')
            ->join('products', 'products.id', '=', 'books.product_id')
            ->with('product')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->whereHas('product', function (Builder $builder) use ($search): void {
                    $builder->where('name_ar', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->when($availability !== '', fn (Builder $query) => $query->where('availability_status', $availability))
            ->orderByDesc('products.is_featured')
            ->orderByDesc('products.published_at')
            ->orderBy('id');
    }
}
