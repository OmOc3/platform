<?php

namespace App\Modules\Commerce\Actions\Packages;

use App\Modules\Commerce\Models\Package;
use App\Modules\Commerce\Models\PackageItem;
use App\Modules\Commerce\Models\Product;
use App\Shared\Contracts\AuditLogger;
use App\Shared\Enums\ProductKind;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class SavePackageAction
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function execute(array $data, mixed $actor, ?Package $package = null): Package
    {
        $package ??= new Package();
        $oldValues = $package->exists ? $package->load('items')->toArray() : [];

        $product = $package->product ?? new Product([
            'uuid' => (string) Str::uuid(),
            'kind' => ProductKind::Package,
        ]);

        $product->fill([
            'kind' => ProductKind::Package,
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

        $package->fill([
            'product_id' => $product->id,
            'billing_cycle_label' => $data['billing_cycle_label'] ?? null,
            'lecture_count' => count($data['item_ids']),
            'access_period_days' => $data['access_period_days'] ?? null,
            'is_featured' => $data['is_featured'],
            'metadata' => [
                'overlap_rule' => $data['overlap_rule'],
                ...($data['metadata'] ?? []),
            ],
        ]);
        $package->save();

        $package->items()->delete();

        foreach (Arr::wrap($data['item_ids']) as $index => $lectureId) {
            $package->items()->create([
                'item_type' => \App\Modules\Academic\Models\Lecture::class,
                'item_id' => $lectureId,
                'item_name_snapshot' => optional(\App\Modules\Academic\Models\Lecture::query()->find($lectureId))->title,
                'sort_order' => $index + 1,
                'meta' => null,
            ]);
        }

        $this->auditLogger->log(
            event: $package->wasRecentlyCreated ? 'commerce.package.created' : 'commerce.package.updated',
            actor: $actor,
            subject: $package,
            oldValues: $oldValues,
            newValues: $package->fresh('items')->toArray(),
        );

        return $package;
    }
}
