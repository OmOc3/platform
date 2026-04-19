<?php

namespace App\Modules\Academic\Actions\Lectures;

use App\Modules\Academic\Models\Lecture;
use App\Modules\Commerce\Models\Product;
use App\Shared\Contracts\AuditLogger;
use App\Shared\Enums\ProductKind;
use Illuminate\Support\Str;

class SaveLectureAction
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function execute(array $data, mixed $actor, ?Lecture $lecture = null): Lecture
    {
        $lecture ??= new Lecture();
        $oldValues = $lecture->exists ? $lecture->toArray() : [];

        $normalizedPrice = $data['is_free'] ? 0 : $data['price_amount'];
        $product = $lecture->product ?? new Product([
            'uuid' => (string) Str::uuid(),
            'kind' => ProductKind::Lecture,
        ]);

        $product->fill([
            'kind' => ProductKind::Lecture,
            'slug' => $data['slug'],
            'name_ar' => $data['title'],
            'name_en' => null,
            'teaser' => $data['short_description'] ?? null,
            'description' => $data['long_description'] ?? null,
            'price_amount' => $normalizedPrice,
            'currency' => $data['currency'],
            'thumbnail_url' => $data['thumbnail_url'] ?? null,
            'is_active' => $data['is_active'],
            'is_featured' => $data['is_featured'],
            'published_at' => $data['published_at'] ?? null,
        ]);
        $product->save();

        $lecture->fill([
            ...$data,
            'product_id' => $product->id,
            'price_amount' => $normalizedPrice,
        ]);
        $lecture->save();

        $this->auditLogger->log(
            event: $lecture->wasRecentlyCreated ? 'academic.lecture.created' : 'academic.lecture.updated',
            actor: $actor,
            subject: $lecture,
            oldValues: $oldValues,
            newValues: $lecture->fresh()->toArray(),
        );

        return $lecture;
    }
}
