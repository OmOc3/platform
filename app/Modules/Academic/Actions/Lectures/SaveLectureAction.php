<?php

namespace App\Modules\Academic\Actions\Lectures;

use App\Modules\Academic\Models\LectureAsset;
use App\Modules\Academic\Models\LectureCheckpoint;
use App\Modules\Academic\Models\Lecture;
use App\Modules\Commerce\Models\Product;
use App\Shared\Contracts\AuditLogger;
use App\Shared\Enums\ProductKind;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SaveLectureAction
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function execute(array $data, mixed $actor, ?Lecture $lecture = null): Lecture
    {
        $lecture ??= new Lecture();
        $lecture->loadMissing(['product', 'assets', 'checkpoints']);
        $oldValues = $lecture->exists ? $this->snapshot($lecture) : [];

        return DB::transaction(function () use ($data, $actor, $lecture, $oldValues): Lecture {
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

            $this->syncAssets($lecture, $data['assets'] ?? []);
            $this->syncCheckpoints($lecture, $data['checkpoints'] ?? []);

            $freshLecture = $lecture->fresh(['product', 'assets', 'checkpoints']);

            $this->auditLogger->log(
                event: $lecture->wasRecentlyCreated ? 'academic.lecture.created' : 'academic.lecture.updated',
                actor: $actor,
                subject: $lecture,
                oldValues: $oldValues,
                newValues: $this->snapshot($freshLecture),
            );

            return $freshLecture;
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $assets
     */
    private function syncAssets(Lecture $lecture, array $assets): void
    {
        $lecture->assets()->delete();

        foreach ($assets as $index => $asset) {
            $lecture->assets()->create([
                'kind' => $asset['kind'],
                'title' => $asset['title'],
                'url' => $asset['url'] ?? null,
                'body' => $asset['body'] ?? null,
                'sort_order' => $asset['sort_order'] ?? ($index + 1),
                'is_active' => (bool) ($asset['is_active'] ?? true),
                'metadata' => $asset['metadata'] ?? null,
            ]);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $checkpoints
     */
    private function syncCheckpoints(Lecture $lecture, array $checkpoints): void
    {
        $lecture->checkpoints()->delete();

        foreach ($checkpoints as $index => $checkpoint) {
            $lecture->checkpoints()->create([
                'title' => $checkpoint['title'],
                'position_seconds' => $checkpoint['position_seconds'] ?? null,
                'sort_order' => $checkpoint['sort_order'] ?? ($index + 1),
                'is_required' => (bool) ($checkpoint['is_required'] ?? true),
                'metadata' => $checkpoint['metadata'] ?? null,
            ]);
        }
    }

    private function snapshot(Lecture $lecture): array
    {
        $lecture->loadMissing(['product', 'assets', 'checkpoints']);

        return [
            'lecture' => $lecture->toArray(),
            'product' => $lecture->product?->toArray(),
            'assets' => $lecture->assets
                ->map(fn (LectureAsset $asset): array => $asset->toArray())
                ->all(),
            'checkpoints' => $lecture->checkpoints
                ->map(fn (LectureCheckpoint $checkpoint): array => $checkpoint->toArray())
                ->all(),
        ];
    }
}
