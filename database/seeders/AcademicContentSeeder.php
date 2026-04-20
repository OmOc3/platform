<?php

namespace Database\Seeders;

use App\Modules\Academic\Actions\Exams\SyncExamQuestionsAction;
use App\Modules\Academic\Models\CurriculumSection;
use App\Modules\Academic\Models\Exam;
use App\Modules\Academic\Models\Grade;
use App\Modules\Academic\Models\Lecture;
use App\Modules\Academic\Models\LectureAsset;
use App\Modules\Academic\Models\LectureCheckpoint;
use App\Modules\Academic\Models\LectureSection;
use App\Modules\Academic\Models\Track;
use App\Modules\Commerce\Models\Package;
use App\Modules\Commerce\Models\Product;
use App\Shared\Enums\ContentKind;
use App\Shared\Enums\LectureAssetKind;
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

        $openExam = $this->upsertExam([
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

        $newtonExam = $this->upsertExam([
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

        $reviewExam = $this->upsertExam([
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

        $this->syncLectureDelivery($freeLecture, [
            [
                'kind' => LectureAssetKind::EmbedVideo,
                'title' => 'فيديو تمهيدي مباشر',
                'url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
                'body' => 'شاهد المقدمة العامة للوحدة وافتح الملاحظات المساندة بعدها.',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'kind' => LectureAssetKind::TextBlock,
                'title' => 'خطة المتابعة',
                'url' => null,
                'body' => 'ابدأ بالتمهيد، ثم راجع المفاهيم الأساسية، وبعدها انتقل مباشرة إلى الاختبار المفتوح للتأكد من الفهم الأولي.',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'kind' => LectureAssetKind::ResourceLink,
                'title' => 'ورقة النقاط الأساسية',
                'url' => 'https://example.com/foundation-kinematics-notes',
                'body' => 'ملخص خارجي سريع لأهم المصطلحات.',
                'sort_order' => 3,
                'is_active' => true,
            ],
        ], [
            [
                'title' => 'إنهاء التمهيد',
                'position_seconds' => 300,
                'sort_order' => 1,
                'is_required' => true,
            ],
            [
                'title' => 'مراجعة التعاريف الأساسية',
                'position_seconds' => 900,
                'sort_order' => 2,
                'is_required' => true,
            ],
        ]);

        $this->syncLectureDelivery($newtonLecture, [
            [
                'kind' => LectureAssetKind::ExternalVideo,
                'title' => 'الفيديو الرئيسي لقوانين نيوتن',
                'url' => 'https://example.com/newton-laws-core',
                'body' => 'رابط العرض الأساسي للمحاضرة مع الأمثلة التطبيقية.',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'kind' => LectureAssetKind::AttachmentLink,
                'title' => 'شيت تدريبات القوانين',
                'url' => 'https://example.com/newton-worksheet.pdf',
                'body' => 'حمّل الشيت وابدأ بحل الأمثلة بعد متابعة الشرح.',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'kind' => LectureAssetKind::TextBlock,
                'title' => 'ملحوظات سريعة قبل الاختبار',
                'url' => null,
                'body' => 'ركّز على التمييز بين القوة المحصلة والقوى المؤثرة، وارجع إلى الرسم الحر قبل التعويض في القانون.',
                'sort_order' => 3,
                'is_active' => true,
            ],
        ], [
            [
                'title' => 'إنهاء الشرح الأساسي',
                'position_seconds' => 900,
                'sort_order' => 1,
                'is_required' => true,
            ],
            [
                'title' => 'حل المثال الأول',
                'position_seconds' => 1800,
                'sort_order' => 2,
                'is_required' => true,
            ],
            [
                'title' => 'مراجعة النقاط الصعبة',
                'position_seconds' => 2700,
                'sort_order' => 3,
                'is_required' => false,
            ],
        ]);

        $this->syncLectureDelivery($electricityReview, [
            [
                'kind' => LectureAssetKind::TextBlock,
                'title' => 'خريطة مراجعة الوحدة',
                'url' => null,
                'body' => 'استخدم هذه المراجعة كنقطة تجميع سريعة قبل الاختبار، ثم راجع قائمة الأخطاء إن وُجدت.',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'kind' => LectureAssetKind::ResourceLink,
                'title' => 'ملف أمثلة إضافية',
                'url' => 'https://example.com/electricity-revision-examples',
                'body' => 'روابط مسائل إضافية للتدريب الذاتي.',
                'sort_order' => 2,
                'is_active' => true,
            ],
        ], []);

        $this->syncExamQuestions($openExam, [
            [
                'prompt' => 'أي كمية تعبّر عن مقدار تغيّر موضع الجسم بين نقطتين؟',
                'explanation' => 'الإزاحة هي التغيّر المباشر في موضع الجسم، بينما المسافة هي طول المسار المقطوع.',
                'max_score' => 1,
                'choices' => [
                    ['content' => 'الإزاحة', 'is_correct' => true],
                    ['content' => 'المسافة', 'is_correct' => false],
                    ['content' => 'العجلة', 'is_correct' => false],
                    ['content' => 'الكتلة', 'is_correct' => false],
                ],
            ],
            [
                'prompt' => 'عند ثبات الزمن، زيادة المسافة المقطوعة تعني أن السرعة المتوسطة قد...',
                'explanation' => 'السرعة المتوسطة تساوي المسافة على الزمن، فإذا ثبت الزمن وزادت المسافة زادت السرعة.',
                'max_score' => 1,
                'choices' => [
                    ['content' => 'زادت', 'is_correct' => true],
                    ['content' => 'قلت', 'is_correct' => false],
                    ['content' => 'لم تتغير دائمًا', 'is_correct' => false],
                    ['content' => 'تساوي صفرًا', 'is_correct' => false],
                ],
            ],
            [
                'prompt' => 'أي رسم بياني يساعدك مباشرة على استنتاج السرعة من الميل؟',
                'explanation' => 'ميل منحنى الإزاحة-الزمن يعبّر عن السرعة.',
                'max_score' => 1,
                'choices' => [
                    ['content' => 'الإزاحة مع الزمن', 'is_correct' => true],
                    ['content' => 'الكتلة مع الحجم', 'is_correct' => false],
                    ['content' => 'القوة مع الكتلة', 'is_correct' => false],
                    ['content' => 'الشحنة مع الجهد', 'is_correct' => false],
                ],
            ],
        ]);

        $this->syncExamQuestions($newtonExam, [
            [
                'prompt' => 'ينص القانون الثاني لنيوتن على أن القوة المحصلة تساوي...',
                'explanation' => 'القانون الثاني يربط القوة المحصلة بكتلة الجسم وعجلته.',
                'max_score' => 2,
                'choices' => [
                    ['content' => 'الكتلة × العجلة', 'is_correct' => true],
                    ['content' => 'الكتلة ÷ العجلة', 'is_correct' => false],
                    ['content' => 'الوزن × السرعة', 'is_correct' => false],
                    ['content' => 'المسافة × الزمن', 'is_correct' => false],
                ],
            ],
            [
                'prompt' => 'إذا كانت القوة المحصلة المؤثرة على جسم تساوي صفرًا، فإن الجسم...',
                'explanation' => 'القوة المحصلة الصفرية تعني عدم وجود عجلة، فيبقى الجسم ساكنًا أو يتحرك بسرعة منتظمة.',
                'max_score' => 2,
                'choices' => [
                    ['content' => 'يبقى على حالته من السكون أو الحركة المنتظمة', 'is_correct' => true],
                    ['content' => 'يتحرك بعجلة كبيرة', 'is_correct' => false],
                    ['content' => 'تتضاعف كتلته', 'is_correct' => false],
                    ['content' => 'يتوقف فورًا دائمًا', 'is_correct' => false],
                ],
            ],
            [
                'prompt' => 'عند ثبوت الكتلة، ماذا يحدث للعجلة إذا تضاعفت القوة المحصلة؟',
                'explanation' => 'من العلاقة F = m a، إذا ثبتت الكتلة وتضاعفت القوة تتضاعف العجلة.',
                'max_score' => 1,
                'choices' => [
                    ['content' => 'تتضاعف العجلة', 'is_correct' => true],
                    ['content' => 'تنخفض إلى النصف', 'is_correct' => false],
                    ['content' => 'لا تتغير', 'is_correct' => false],
                    ['content' => 'تنعدم', 'is_correct' => false],
                ],
            ],
        ]);

        $this->syncExamQuestions($reviewExam, [
            [
                'prompt' => 'طبقًا لقانون أوم، شدة التيار الكهربائي تساوي...',
                'explanation' => 'قانون أوم يربط شدة التيار بالجهد والمقاومة: I = V / R.',
                'max_score' => 1,
                'choices' => [
                    ['content' => 'الجهد ÷ المقاومة', 'is_correct' => true],
                    ['content' => 'الجهد × المقاومة', 'is_correct' => false],
                    ['content' => 'المقاومة ÷ الجهد', 'is_correct' => false],
                    ['content' => 'القدرة × الزمن', 'is_correct' => false],
                ],
            ],
            [
                'prompt' => 'عند ثبات المقاومة، زيادة فرق الجهد تؤدي إلى...',
                'explanation' => 'بثبات المقاومة، شدة التيار تتناسب طرديًا مع فرق الجهد.',
                'max_score' => 1,
                'choices' => [
                    ['content' => 'زيادة شدة التيار', 'is_correct' => true],
                    ['content' => 'نقص شدة التيار', 'is_correct' => false],
                    ['content' => 'انعدام التيار', 'is_correct' => false],
                    ['content' => 'ثبات القدرة دائمًا', 'is_correct' => false],
                ],
            ],
            [
                'prompt' => 'أي مكوّن يُستخدم عادةً للتحكم في شدة التيار داخل الدائرة؟',
                'explanation' => 'المقاومة المتغيرة تساعد على ضبط شدة التيار بزيادة أو تقليل المقاومة.',
                'max_score' => 1,
                'choices' => [
                    ['content' => 'المقاومة المتغيرة', 'is_correct' => true],
                    ['content' => 'المفتاح فقط', 'is_correct' => false],
                    ['content' => 'السلك الفائق', 'is_correct' => false],
                    ['content' => 'العدسة الزجاجية', 'is_correct' => false],
                ],
            ],
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
                'metadata' => [
                    'seeded' => true,
                    'completion_threshold_percent' => 90,
                ],
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

    /**
     * @param  array<int, array<string, mixed>>  $assets
     * @param  array<int, array<string, mixed>>  $checkpoints
     */
    private function syncLectureDelivery(Lecture $lecture, array $assets, array $checkpoints): void
    {
        LectureAsset::query()->where('lecture_id', $lecture->id)->delete();
        LectureCheckpoint::query()->where('lecture_id', $lecture->id)->delete();

        foreach ($assets as $asset) {
            LectureAsset::query()->create([
                'lecture_id' => $lecture->id,
                'kind' => $asset['kind'],
                'title' => $asset['title'],
                'url' => $asset['url'] ?? null,
                'body' => $asset['body'] ?? null,
                'sort_order' => $asset['sort_order'] ?? 0,
                'is_active' => (bool) ($asset['is_active'] ?? true),
                'metadata' => ['seeded' => true],
            ]);
        }

        foreach ($checkpoints as $checkpoint) {
            LectureCheckpoint::query()->create([
                'lecture_id' => $lecture->id,
                'title' => $checkpoint['title'],
                'position_seconds' => $checkpoint['position_seconds'] ?? null,
                'sort_order' => $checkpoint['sort_order'] ?? 0,
                'is_required' => (bool) ($checkpoint['is_required'] ?? true),
                'metadata' => ['seeded' => true],
            ]);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $questions
     */
    private function syncExamQuestions(Exam $exam, array $questions): void
    {
        $exam->loadMissing('examQuestions.question.choices');

        $payload = collect($questions)
            ->values()
            ->map(function (array $question, int $questionIndex) use ($exam): array {
                $existingExamQuestion = $exam->examQuestions->values()->get($questionIndex);
                $existingChoices = $existingExamQuestion?->question?->choices?->sortBy('sort_order')->values();

                return [
                    'question_id' => $existingExamQuestion?->question_id,
                    'prompt' => $question['prompt'],
                    'explanation' => $question['explanation'] ?? null,
                    'max_score' => $question['max_score'] ?? 1,
                    'choices' => collect($question['choices'])
                        ->values()
                        ->map(function (array $choice, int $choiceIndex) use ($existingChoices): array {
                            return [
                                'choice_id' => $existingChoices?->get($choiceIndex)?->id,
                                'content' => $choice['content'],
                                'is_correct' => (bool) ($choice['is_correct'] ?? false),
                            ];
                        })
                        ->all(),
                ];
            })
            ->all();

        app(SyncExamQuestionsAction::class)->execute($exam, $payload);
    }
}
