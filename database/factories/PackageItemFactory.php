<?php

namespace Database\Factories;

use App\Modules\Academic\Models\Lecture;
use App\Modules\Commerce\Models\Package;
use App\Modules\Commerce\Models\PackageItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PackageItem>
 */
class PackageItemFactory extends Factory
{
    protected $model = PackageItem::class;

    public function definition(): array
    {
        $lecture = Lecture::factory()->create();

        return [
            'package_id' => Package::factory(),
            'item_type' => Lecture::class,
            'item_id' => $lecture->id,
            'item_name_snapshot' => $lecture->title,
            'sort_order' => fake()->numberBetween(1, 12),
            'meta' => null,
        ];
    }
}
