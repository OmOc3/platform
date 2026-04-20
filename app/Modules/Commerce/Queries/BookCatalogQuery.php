<?php

namespace App\Modules\Commerce\Queries;

use App\Modules\Commerce\Models\Book;
use App\Modules\Commerce\Models\CartItem;
use App\Modules\Students\Models\Student;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class BookCatalogQuery
{
    public function paginateFor(Student $student, Request $request): LengthAwarePaginator
    {
        $search = $request->string('search')->toString();
        $cartProductIds = CartItem::query()
            ->whereHas('cart', fn ($query) => $query->where('student_id', $student->id))
            ->pluck('product_id')
            ->map(fn ($productId) => (int) $productId)
            ->all();

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
            ->withQueryString()
            ->through(fn (Book $book): array => [
                'book' => $book,
                'in_cart' => in_array((int) $book->product_id, $cartProductIds, true),
            ]);
    }
}
