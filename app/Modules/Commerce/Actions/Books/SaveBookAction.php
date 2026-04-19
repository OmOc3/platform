<?php

namespace App\Modules\Commerce\Actions\Books;

use App\Modules\Commerce\Models\Book;
use App\Modules\Commerce\Models\Product;
use App\Shared\Contracts\AuditLogger;
use App\Shared\Enums\ProductKind;
use Illuminate\Support\Str;

class SaveBookAction
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function execute(array $data, mixed $actor, ?Book $book = null): Book
    {
        $book ??= new Book();
        $oldValues = $book->exists ? $book->toArray() : [];

        $product = $book->product ?? new Product([
            'uuid' => (string) Str::uuid(),
            'kind' => ProductKind::Book,
        ]);

        $product->fill([
            'kind' => ProductKind::Book,
            'slug' => $data['slug'],
            'name_ar' => $data['name_ar'],
            'name_en' => $data['name_en'] ?? null,
            'teaser' => $data['teaser'] ?? null,
            'description' => $data['description'] ?? null,
            'price_amount' => $data['price_amount'],
            'currency' => $data['currency'],
            'thumbnail_url' => $data['thumbnail_url'] ?? null,
            'is_active' => $data['is_active'],
            'is_featured' => $data['is_featured'],
            'published_at' => $data['published_at'] ?? null,
        ]);
        $product->save();

        $book->fill([
            'product_id' => $product->id,
            'author_name' => $data['author_name'] ?? null,
            'page_count' => $data['page_count'] ?? null,
            'stock_quantity' => $data['stock_quantity'],
            'cover_badge' => $data['cover_badge'] ?? null,
            'availability_status' => $data['availability_status'],
            'metadata' => $data['metadata'] ?? null,
        ]);
        $book->save();

        $this->auditLogger->log(
            event: $book->wasRecentlyCreated ? 'commerce.book.created' : 'commerce.book.updated',
            actor: $actor,
            subject: $book,
            oldValues: $oldValues,
            newValues: $book->fresh()->toArray(),
        );

        return $book;
    }
}
