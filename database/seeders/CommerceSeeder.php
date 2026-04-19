<?php

namespace Database\Seeders;

use App\Modules\Commerce\Enums\BookAvailability;
use App\Modules\Commerce\Models\Book;
use App\Modules\Commerce\Models\Package;
use App\Modules\Commerce\Models\Product;
use App\Shared\Enums\ProductKind;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CommerceSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            [
                'slug' => 'monthly-physics-package',
                'name_ar' => 'باقة الفيزياء الشهرية',
                'teaser' => 'محاضرات ومراجعات منظمة على مدار الشهر.',
                'description' => 'باقة شهرية مركزة تجمع الشرح والمراجعة والتكليفات التدريبية.',
                'price_amount' => 399,
                'billing_cycle_label' => 'شهري',
                'access_period_days' => 30,
            ],
            [
                'slug' => 'quarter-physics-package',
                'name_ar' => 'باقة الثلاثة شهور',
                'teaser' => 'خطة ممتدة للمذاكرة والتثبيت.',
                'description' => 'محتوى ممتد للمذاكرة على ثلاثة أشهر مع مراجعات وتدريب متدرج.',
                'price_amount' => 999,
                'billing_cycle_label' => '3 شهور',
                'access_period_days' => 90,
            ],
            [
                'slug' => 'intensive-camp-package',
                'name_ar' => 'معسكر المراجعة المكثف',
                'teaser' => 'تركيز نهائي قبل الامتحان.',
                'description' => 'معسكر قصير عالي الكثافة لتثبيت الأفكار ومراجعة النقاط الأكثر تكرارًا.',
                'price_amount' => 650,
                'billing_cycle_label' => 'معسكر خاص',
                'access_period_days' => 21,
            ],
        ] as $item) {
            $product = Product::query()->updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'uuid' => (string) Str::uuid(),
                    'kind' => ProductKind::Package,
                    'name_ar' => $item['name_ar'],
                    'name_en' => null,
                    'teaser' => $item['teaser'],
                    'description' => $item['description'],
                    'price_amount' => $item['price_amount'],
                    'currency' => 'EGP',
                    'thumbnail_url' => null,
                    'is_active' => true,
                    'is_featured' => true,
                    'published_at' => now(),
                ],
            );

            Package::query()->updateOrCreate(
                ['product_id' => $product->id],
                [
                    'billing_cycle_label' => $item['billing_cycle_label'],
                    'lecture_count' => 0,
                    'access_period_days' => $item['access_period_days'],
                    'is_featured' => true,
                    'metadata' => ['overlap_rule' => 'block'],
                ],
            );
        }

        foreach ([
            [
                'slug' => 'smart-solutions-book',
                'name_ar' => 'كتاب الحلول الذكية',
                'teaser' => 'ملخصات ونماذج تدريب منتقاة.',
                'description' => 'كتاب يركز على الأفكار الأساسية والأسئلة المتوقعة مع شرح موجز واضح.',
                'price_amount' => 180,
                'author_name' => 'أ. فيزياء',
                'page_count' => 164,
                'stock_quantity' => 25,
                'cover_badge' => 'الأكثر طلبًا',
                'availability_status' => BookAvailability::InStock,
            ],
            [
                'slug' => 'revision-notes-book',
                'name_ar' => 'كراسة المراجعة النهائية',
                'teaser' => 'مراجعة عملية في نسخة خفيفة.',
                'description' => 'كراسة مراجعة سريعة للمسائل والقوانين المهمة قبل الاختبارات.',
                'price_amount' => 140,
                'author_name' => 'أ. فيزياء',
                'page_count' => 96,
                'stock_quantity' => 12,
                'cover_badge' => 'طبعة 2026',
                'availability_status' => BookAvailability::PreOrder,
            ],
        ] as $item) {
            $product = Product::query()->updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'uuid' => (string) Str::uuid(),
                    'kind' => ProductKind::Book,
                    'name_ar' => $item['name_ar'],
                    'name_en' => null,
                    'teaser' => $item['teaser'],
                    'description' => $item['description'],
                    'price_amount' => $item['price_amount'],
                    'currency' => 'EGP',
                    'thumbnail_url' => null,
                    'is_active' => true,
                    'is_featured' => true,
                    'published_at' => now(),
                ],
            );

            Book::query()->updateOrCreate(
                ['product_id' => $product->id],
                [
                    'author_name' => $item['author_name'],
                    'page_count' => $item['page_count'],
                    'stock_quantity' => $item['stock_quantity'],
                    'cover_badge' => $item['cover_badge'],
                    'availability_status' => $item['availability_status'],
                    'metadata' => ['governorates' => ['القاهرة', 'الجيزة', 'الإسكندرية']],
                ],
            );
        }
    }
}
