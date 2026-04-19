<?php

namespace Database\Seeders;

use App\Modules\Academic\Models\CurriculumSection;
use App\Modules\Academic\Models\Exam;
use App\Modules\Academic\Models\Grade;
use App\Modules\Academic\Models\Lecture;
use App\Modules\Academic\Models\LectureSection;
use App\Modules\Academic\Models\Track;
use App\Modules\Commerce\Models\Package;
use App\Modules\Commerce\Models\Product;
use App\Shared\Enums\ContentKind;
use App\Shared\Enums\ProductKind;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AcademicContentSeeder extends Seeder
{
    public function run(): void
    {
        $grade = Grade::query()->where('code', 'grade-1-secondary')->firstOrFail();
        $track = Track::query()->where('code', 'foundation-track')->firstOrFail();

        $motionSection = CurriculumSection::query()->updateOrCreate(
            ['slug' => 'motion-foundations'],
            [
                'grade_id' => $grade->id,
                'track_id' => $track->id,
                'name_ar' => 'أساسيات الحركة',
                'name_en' => 'Motion Foundations',
                'description' => 'بداية منهج الحركة والقوانين الأساسية للصف الأول الثانوي.',
                'sort_order' => 1,
                'is_active' => true,
            ],
        );

        $electricitySection = CurriculumSection::query()->updateOrCreate(
            ['slug' => 'electricity-basics'],
            [
                'grade_id' => $grade->id,
                'track_id' => $track->id,
                'name_ar' => 'أساسيات الكهرباء',
                'name_en' => 'Electricity Basics',
                'description' => 'مراجعة المفاهيم التأسيسية في الكهرباء وربطها بالمسائل.',
                'sort_order' => 2,
                'is_active' => true,
            ],
        );

        $motionLectureSection = LectureSection::query()->updateOrCreate(
            ['slug' => 'motion-core-lessons'],
            [
                'grade_id' => $grade->id,
                'track_id' => $track->id,
                'curriculum_section_id' => $motionSection->id,
                'name_ar' => 'محاضرات الحركة',
                'name_en' => 'Motion Lessons',
                'description' => 'محاضرات تأسيسية وتدريبات عملية في وحدة الحركة.',
                'sort_order' => 1,
                'is_active' => true,
            ],
        );

        $revisionLectureSection = LectureSection::query()->updateOrCreate(
            ['slug' => 'electricity-revisions'],
            [
                'grade_id' => $grade->id,
                'track_id' => $track->id,
                'curriculum_section_id' => $electricitySection->id,
                'name_ar' => 'مراجعات الكهرباء',
                'name_en' => 'Electricity Revisions',
                'description' => 'مراجعات مركزة ومسائل مختارة قبل الاختبار.',
                'sort_order' => 2,
                'is_active' => true,
            ],
        );

        $freeLecture = $this->upsertLecture([
            'slug' => 'foundation-kinematics-free',
            'title' => 'مدخل مجاني إلى الحركة',
            'short_description' => 'شرح تمهيدي مجاني لفهم الإزاحة والسرعة بطريقة عملية.',
            'long_description' => 'محاضرة تعريفية مجانية توضح مدخل وحدة الحركة وخطة الدراسة وكيف يبدأ الطالب في بناء الفهم الصحيح قبل الدخول في المسائل الأطول.',
            'type' => ContentKind::Lecture,
            'price_amount' => 0,
            'duration_minutes' => 32,
            'is_featured' => true,
            'is_free' => true,
            'grade_id' => $grade->id,
            'track_id' => $track->id,
            'curriculum_section_id' => $motionSection->id,
            'lecture_section_id' => $motionLectureSection->id,
            'sort_order' => 1,
        ]);

        $newtonLecture = $this->upsertLecture([
            'slug' => 'newton-laws-core',
            'title' => 'قوانين نيوتن الأساسية',
            'short_description' => 'محاضرة أساسية تشرح قوانين نيوتن وربطها بالرسوم والمسائل.',
            'long_description' => 'محاضرة أساسية للصف الأول الثانوي تتناول قوانين نيوتن مع أمثلة عملية وتدريبات تصاعدية، وتعد نقطة الدخول الرئيسية لبقية الوحدة.',
            'type' => ContentKind::Lecture,
            'price_amount' => 140,
            'duration_minutes' => 58,
            'is_featured' => true,
            'is_free' => false,
            'grade_id' => $grade->id,
            'track_id' => $track->id,
            'curriculum_section_id' => $motionSection->id,
            'lecture_section_id' => $motionLectureSection->id,
            'sort_order' => 2,
        ]);

        $electricityReview = $this->upsertLecture([
            'slug' => 'electricity-review-essentials',
            'title' => 'مراجعة أساسيات الكهرباء',
            'short_description' => 'مراجعة مركزة لأكثر النقاط تكرارًا في أسئلة الكهرباء.',
            'long_description' => 'مراجعة سريعة لكنها عملية تتناول أكثر أفكار الكهرباء ورودًا في الأسئلة، مع صياغة مختصرة تساعد الطالب قبل الاختبار.',
            'type' => ContentKind::Review,
            'price_amount' => 110,
            'duration_minutes' => 46,
            'is_featured' => false,
            'is_free' => false,
            'grade_id' => $grade->id,
            'track_id' => $track->id,
            'curriculum_section_id' => $electricitySection->id,
            'lecture_section_id' => $revisionLectureSection->id,
            'sort_order' => 3,
        ]);

        $problemSolvingLecture = $this->upsertLecture([
            'slug' => 'accelerated-motion-problem-solving',
            'title' => 'تدريب حل مسائل الحركة المتسارعة',
            'short_description' => 'وحدة تدريبية لحل مسائل الحركة بخطوات منظمة.',
            'long_description' => 'جلسة تدريبية تركز على نقل الطالب من الفهم النظري إلى التطبيق الفعلي عبر مسائل حركة متدرجة الصعوبة مع نموذج تفكير واضح.',
            'type' => ContentKind::Lecture,
            'price_amount' => 150,
            'duration_minutes' => 64,
            'is_featured' => true,
            'is_free' => false,
            'grade_id' => $grade->id,
            'track_id' => $track->id,
            'curriculum_section_id' => $motionSection->id,
            'lecture_section_id' => $motionLectureSection->id,
            'sort_order' => 4,
        ]);

        $this->upsertExam([
            'slug' => 'kinematics-quiz-open',
            'title' => 'اختبار تمهيدي مفتوح',
            'short_description' => 'اختبار قصير مجاني لقياس فهم أساسيات الحركة.',
            'long_description' => 'اختبار تمهيدي يساعد الطالب على تقييم فهمه الأولي قبل الدخول إلى المحاضرات المدفوعة.',
            'lecture_id' => $freeLecture->id,
            'grade_id' => $grade->id,
            'track_id' => $track->id,
            'price_amount' => 0,
            'duration_minutes' => 12,
            'question_count' => 6,
            'is_featured' => true,
            'is_free' => true,
            'sort_order' => 1,
        ]);

        $this->upsertExam([
            'slug' => 'newton-laws-weekly-quiz',
            'title' => 'اختبار قوانين نيوتن الأسبوعي',
            'short_description' => 'اختبار مرتبط بمحاضرة قوانين نيوتن لتثبيت الأفكار الأساسية.',
            'long_description' => 'اختبار قصير بعد المحاضرة الأساسية لمراجعة الفهم قبل الانتقال إلى التدريب المكثف.',
            'lecture_id' => $newtonLecture->id,
            'grade_id' => $grade->id,
            'track_id' => $track->id,
            'price_amount' => 0,
            'duration_minutes' => 18,
            'question_count' => 10,
            'is_featured' => false,
            'is_free' => false,
            'sort_order' => 2,
        ]);

        $this->upsertExam([
            'slug' => 'electricity-review-quiz',
            'title' => 'اختبار مراجعة الكهرباء',
            'short_description' => 'اختبار مراجعة قصير بعد وحدة الكهرباء.',
            'long_description' => 'اختبار مراجعة سريع مرتبط بمراجعة الكهرباء لمساعدة الطالب على تثبيت النقاط الأكثر تكرارًا.',
            'lecture_id' => $electricityReview->id,
            'grade_id' => $grade->id,
            'track_id' => $track->id,
            'price_amount' => 0,
            'duration_minutes' => 15,
            'question_count' => 8,
            'is_featured' => false,
            'is_free' => false,
            'sort_order' => 3,
        ]);

        $monthlyPackage = Package::query()->whereHas('product', fn ($query) => $query->where('slug', 'monthly-physics-package'))->first();
        $quarterPackage = Package::query()->whereHas('product', fn ($query) => $query->where('slug', 'quarter-physics-package'))->first();
        $campPackage = Package::query()->whereHas('product', fn ($query) => $query->where('slug', 'intensive-camp-package'))->first();

        if ($monthlyPackage) {
            $this->syncPackageItems($monthlyPackage, [$newtonLecture, $electricityReview], 30, 'block');
        }

        if ($quarterPackage) {
            $this->syncPackageItems($quarterPackage, [$newtonLecture, $electricityReview, $problemSolvingLecture], 90, 'block');
        }

        if ($campPackage) {
            $this->syncPackageItems($campPackage, [$problemSolvingLecture, $electricityReview], 21, 'allow');
        }
    }

    private function upsertLecture(array $data): Lecture
    {
        $product = Product::query()->updateOrCreate(
            ['slug' => $data['slug']],
            [
                'uuid' => (string) Str::uuid(),
                'kind' => ProductKind::Lecture,
                'name_ar' => $data['title'],
                'name_en' => null,
                'teaser' => $data['short_description'],
                'description' => $data['long_description'],
                'price_amount' => $data['is_free'] ? 0 : $data['price_amount'],
                'currency' => 'EGP',
                'thumbnail_url' => null,
                'is_active' => true,
                'is_featured' => $data['is_featured'],
                'published_at' => now(),
            ],
        );

        return Lecture::query()->updateOrCreate(
            ['slug' => $data['slug']],
            [
                'product_id' => $product->id,
                'grade_id' => $data['grade_id'],
                'track_id' => $data['track_id'],
                'curriculum_section_id' => $data['curriculum_section_id'],
                'lecture_section_id' => $data['lecture_section_id'],
                'title' => $data['title'],
                'short_description' => $data['short_description'],
                'long_description' => $data['long_description'],
                'thumbnail_url' => null,
                'type' => $data['type'],
                'price_amount' => $data['is_free'] ? 0 : $data['price_amount'],
                'currency' => 'EGP',
                'duration_minutes' => $data['duration_minutes'],
                'is_active' => true,
                'is_featured' => $data['is_featured'],
                'is_free' => $data['is_free'],
                'published_at' => now(),
                'sort_order' => $data['sort_order'],
                'metadata' => ['seeded' => true],
            ],
        );
    }

    private function upsertExam(array $data): Exam
    {
        return Exam::query()->updateOrCreate(
            ['slug' => $data['slug']],
            [
                'lecture_id' => $data['lecture_id'],
                'grade_id' => $data['grade_id'],
                'track_id' => $data['track_id'],
                'title' => $data['title'],
                'short_description' => $data['short_description'],
                'long_description' => $data['long_description'],
                'thumbnail_url' => null,
                'price_amount' => $data['is_free'] ? 0 : $data['price_amount'],
                'currency' => 'EGP',
                'duration_minutes' => $data['duration_minutes'],
                'question_count' => $data['question_count'],
                'is_active' => true,
                'is_featured' => $data['is_featured'],
                'is_free' => $data['is_free'],
                'published_at' => now(),
                'sort_order' => $data['sort_order'],
                'metadata' => ['seeded' => true],
            ],
        );
    }

    private function syncPackageItems(Package $package, array $lectures, int $accessPeriodDays, string $overlapRule): void
    {
        $package->items()->delete();

        foreach ($lectures as $index => $lecture) {
            $package->items()->create([
                'item_type' => Lecture::class,
                'item_id' => $lecture->id,
                'item_name_snapshot' => $lecture->title,
                'sort_order' => $index + 1,
                'meta' => null,
            ]);
        }

        $package->update([
            'lecture_count' => count($lectures),
            'access_period_days' => $accessPeriodDays,
            'metadata' => ['overlap_rule' => $overlapRule],
        ]);
    }
}
