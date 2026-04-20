<?php

namespace App\Modules\Students\Queries;

use App\Modules\Academic\Models\Exam;
use App\Modules\Academic\Models\ExamAttempt;
use App\Modules\Academic\Models\Lecture;
use App\Modules\Commerce\Models\Book;
use App\Modules\Commerce\Models\Cart;
use App\Modules\Commerce\Models\Entitlement;
use App\Modules\Commerce\Models\Package;
use App\Modules\Support\Models\ForumThread;
use App\Modules\Students\Models\Student;
use App\Shared\Enums\ContentKind;
use App\Shared\Enums\ExamAttemptStatus;
use App\Shared\Enums\ProductKind;
use App\Shared\Enums\StudentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class StudentDashboardQuery
{
    /**
     * @return array<string, mixed>
     */
    public function dataFor(Student $student): array
    {
        $packages = Package::query()
            ->with(['product', 'items'])
            ->whereHas('product', fn (Builder $query) => $query->where('is_active', true))
            ->get();

        $latestAccessibleContent = Entitlement::query()
            ->with('product')
            ->where('student_id', $student->id)
            ->where('status', 'active')
            ->latest('granted_at')
            ->limit(4)
            ->get();

        $featuredBooks = Book::query()
            ->select('books.*')
            ->join('products', 'products.id', '=', 'books.product_id')
            ->with('product')
            ->whereHas('product', fn (Builder $query) => $query->where('is_active', true))
            ->orderByDesc('products.is_featured')
            ->orderByDesc('products.published_at')
            ->limit(4)
            ->get();

        $latestExamResult = ExamAttempt::query()
            ->with('exam')
            ->where('student_id', $student->id)
            ->where('status', ExamAttemptStatus::Graded->value)
            ->latest('graded_at')
            ->first();

        $cartItemsCount = Cart::query()
            ->where('student_id', $student->id)
            ->withCount('items')
            ->first()?->items_count ?? 0;

        $sortedPackages = $packages
            ->sortBy(fn (Package $package): string => sprintf(
                '%02d-%02d-%03d-%s',
                $this->packageSortPriority($package),
                $package->is_featured ? 0 : 1,
                $package->lecture_count,
                $package->product?->name_ar ?? ''
            ))
            ->values();

        return [
            'heroSlides' => $this->heroSlidesFor($student, $latestExamResult, $cartItemsCount),
            'primaryAction' => $this->primaryActionFor($latestExamResult, $cartItemsCount),
            'notices' => $this->noticesFor($student, $cartItemsCount, $latestExamResult),
            'packageGroups' => $this->packageGroupsFor($sortedPackages),
            'sectionCards' => $this->sectionCardsFor($student),
            'featuredBooks' => $featuredBooks,
            'latestAccessibleContent' => $latestAccessibleContent,
            'featuredVideo' => config('platform.public.video', []),
            'footerLinks' => [
                ['label' => 'الرئيسية', 'href' => route('student.dashboard')],
                ['label' => 'المحاضرات', 'href' => route('student.lectures.index')],
                ['label' => 'الامتحانات', 'href' => route('student.lectures.index', ['tab' => 'exam'])],
                ['label' => 'كتب', 'href' => route('student.books.index')],
                ['label' => 'الشكاوي والاقتراحات', 'href' => route('student.complaints.index')],
                ['label' => 'أسئلة وأجوبة', 'href' => route('student.forum.index')],
                ['label' => 'حضور السنتر', 'href' => route('student.attendance.index')],
                ['label' => 'الإعدادات', 'href' => route('student.profile.show')],
            ],
            'socialLinks' => config('platform.public.social_links', []),
            'stats' => [
                [
                    'label' => 'المحتوى المفعل',
                    'value' => $student->entitlements()->where('status', 'active')->count(),
                    'description' => 'محاضرات وباقات تم فتحها على حسابك',
                ],
                [
                    'label' => 'نتائج الاختبارات',
                    'value' => $student->examAttempts()->where('status', ExamAttemptStatus::Graded->value)->count(),
                    'description' => 'محاولات تم تصحيحها ويمكن مراجعتها',
                ],
                [
                    'label' => 'طلبات الكتب',
                    'value' => $student->orders()->where('kind', ProductKind::Book->value)->count(),
                    'description' => 'طلبات شحن وسجل مشتريات الكتب',
                ],
                [
                    'label' => 'سجل الحضور',
                    'value' => $student->attendanceRecords()->count(),
                    'description' => 'جلسات السنتر والتقييمات المسجلة',
                ],
            ],
        ];
    }

    /**
     * @return array<int, array{eyebrow: string, title: string, description: string, metric: string, value: string, primary_label: string, primary_href: string, secondary_label: string, secondary_href: string}>
     */
    private function heroSlidesFor(Student $student, ?ExamAttempt $latestExamResult, int $cartItemsCount): array
    {
        $gradeLabel = $student->grade?->name_ar ?? 'رحلتك التعليمية';
        $trackLabel = $student->track?->name_ar ?? 'المسار العام';

        return [
            [
                'eyebrow' => 'رسالة المعلم',
                'title' => 'ابدأ يومك الدراسي بخطة واضحة داخل منصة واحدة.',
                'description' => sprintf(
                    'كل ما يخص %s داخل %s من محاضرات ومراجعات ونتائج وسجل متابعة، مع واجهة عربية مختصرة وسريعة.',
                    $gradeLabel,
                    $trackLabel
                ),
                'metric' => 'حالة الحساب',
                'value' => $student->status->label(),
                'primary_label' => 'استعرض المحاضرات',
                'primary_href' => route('student.lectures.index'),
                'secondary_label' => 'نتيجة المعسكر / الاختبارات',
                'secondary_href' => $latestExamResult instanceof ExamAttempt
                    ? route('student.exam-attempts.result', $latestExamResult)
                    : route('student.lectures.index', ['tab' => 'exam']),
            ],
            [
                'eyebrow' => 'المحتوى المتاح',
                'title' => 'المسار المفتوح لك الآن جاهز للمراجعة والمتابعة.',
                'description' => 'تابع آخر العناصر التي تم تفعيلها على حسابك، وانتقل مباشرة إلى المحاضرات أو إلى سجل المدفوعات دون لفّ كثير داخل البوابة.',
                'metric' => 'عناصر مفعلة',
                'value' => (string) $student->entitlements()->where('status', 'active')->count(),
                'primary_label' => 'مدفوعات المحاضرات',
                'primary_href' => route('student.payments.index'),
                'secondary_label' => 'أخطائي',
                'secondary_href' => route('student.mistakes.index'),
            ],
            [
                'eyebrow' => 'جاهزية الشراء',
                'title' => $cartItemsCount > 0 ? 'يوجد لديك عناصر محفوظة داخل السلة.' : 'اختر باقتك أو كتبك ثم أكمل الطلب بخطوات واضحة.',
                'description' => $cartItemsCount > 0
                    ? 'تم حفظ العناصر التي اخترتها بالفعل. يمكنك الآن مراجعة السلة، ضبط الكميات، ثم تجهيز الطلبات الرقمية وطلبات الكتب.'
                    : 'المنصة تفصل بين المحتوى الرقمي وطلبات الكتب، وتعرض لك حالة كل عنصر قبل الإضافة حتى لا تشتري شيئًا مكررًا.',
                'metric' => 'عناصر السلة',
                'value' => (string) $cartItemsCount,
                'primary_label' => $cartItemsCount > 0 ? 'الذهاب إلى السلة' : 'استعرض الباقات',
                'primary_href' => $cartItemsCount > 0 ? route('student.cart.index') : route('student.packages.index'),
                'secondary_label' => 'استعرض الكتب',
                'secondary_href' => route('student.books.index'),
            ],
        ];
    }

    /**
     * @return array{title: string, description: string, label: string, href: string}
     */
    private function primaryActionFor(?ExamAttempt $latestExamResult, int $cartItemsCount): array
    {
        if ($latestExamResult instanceof ExamAttempt) {
            return [
                'title' => 'آخر نتيجة جاهزة للمراجعة',
                'description' => sprintf(
                    'درجتك الأخيرة %s/%s، ويمكنك الآن مراجعة الإجابات والتعليقات النموذجية.',
                    $latestExamResult->total_score,
                    $latestExamResult->max_score
                ),
                'label' => 'عرض النتائج',
                'href' => route('student.exam-attempts.result', $latestExamResult),
            ];
        }

        if ($cartItemsCount > 0) {
            return [
                'title' => 'سلتك تحتاج خطوة أخيرة',
                'description' => 'راجع العناصر الحالية ثم جهز طلباتك الرقمية وطلبات الكتب من صفحة السلة.',
                'label' => 'إتمام الطلب',
                'href' => route('student.cart.index'),
            ];
        }

        return [
            'title' => 'ابدأ من الكتالوج',
            'description' => 'المحاضرات، الباقات، والكتب مرتبة لك حسب المرحلة الدراسية وحالة الوصول الحالية.',
            'label' => 'فتح الكتالوج',
            'href' => route('student.lectures.index'),
        ];
    }

    /**
     * @return array<int, array{tone: string, title: string, body: string}>
     */
    private function noticesFor(Student $student, int $cartItemsCount, ?ExamAttempt $latestExamResult): array
    {
        $notices = [];

        if ($student->status === StudentStatus::Pending) {
            $notices[] = [
                'tone' => 'warning',
                'title' => 'طلب التسجيل قيد المراجعة',
                'body' => 'يمكنك استخدام البوابة الأساسية الآن، وسيتم تفعيل المزايا المدفوعة والمحتوى الكامل بعد اعتماد الإدارة.',
            ];
        }

        if ($cartItemsCount > 0) {
            $notices[] = [
                'tone' => 'violet',
                'title' => 'عناصر محفوظة داخل السلة',
                'body' => 'العناصر التي اخترتها ما زالت محفوظة. راجع الكميات وبيانات الاستلام قبل تجهيز الطلب.',
            ];
        }

        if ($latestExamResult instanceof ExamAttempt && $latestExamResult->max_score > 0 && $latestExamResult->total_score < $latestExamResult->max_score) {
            $notices[] = [
                'tone' => 'violet',
                'title' => 'راجع نتيجة آخر اختبار',
                'body' => 'ما زالت لديك فرص لتحسين الأداء. راجع النتيجة ثم انتقل إلى صفحة أخطائي لتجميع الأسئلة التي تحتاج إعادة مذاكرة.',
            ];
        }

        if ($student->group) {
            $notices[] = [
                'tone' => 'default',
                'title' => 'مجموعة السنتر الحالية',
                'body' => 'أنت مسجل حاليًا في مجموعة '.$student->group->name_ar.'، ويمكنك مراجعة الحضور والتقييمات من سجل السنتر.',
            ];
        }

        return $notices;
    }

    /**
     * @param  Collection<int, Package>  $packages
     * @return array<int, array{key: string, title: string, description: string, packages: Collection<int, Package>}>
     */
    private function packageGroupsFor(Collection $packages): array
    {
        $definitions = [
            'quarterly' => [
                'title' => 'الباقات 3 شهور',
                'description' => 'عروض ممتدة للمذاكرة المنتظمة والمراجعة على فترة أطول.',
            ],
            'monthly' => [
                'title' => 'الباقات الشهرية',
                'description' => 'اختيارات شهرية سريعة لمن يريد خطة مركزة على المدى القريب.',
            ],
            'special' => [
                'title' => 'العروض الخاصة',
                'description' => 'معسكرات وعروض مركزة تظهر بحسب الموسم وخطة المراجعة.',
            ],
        ];

        return collect($definitions)
            ->map(function (array $definition, string $key) use ($packages): ?array {
                $items = $packages
                    ->filter(fn (Package $package): bool => $this->packageGroup($package) === $key)
                    ->values();

                if ($items->isEmpty()) {
                    return null;
                }

                return [
                    'key' => $key,
                    'title' => $definition['title'],
                    'description' => $definition['description'],
                    'packages' => $items,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{title: string, description: string, count: string, href: string, accent: string}>
     */
    private function sectionCardsFor(Student $student): array
    {
        $lectureCount = $this->lectureBaseQuery($student)
            ->where('type', ContentKind::Lecture->value)
            ->where('is_active', true)
            ->count();

        $reviewCount = $this->lectureBaseQuery($student)
            ->whereIn('type', [ContentKind::Review->value, ContentKind::Summary->value])
            ->where('is_active', true)
            ->count();

        $freeLectureCount = $this->lectureBaseQuery($student)
            ->where('type', ContentKind::Lecture->value)
            ->where('is_active', true)
            ->where('is_free', true)
            ->count();

        $examCount = $this->examBaseQuery($student)
            ->where('is_active', true)
            ->count();

        $forumCount = ForumThread::query()->count();

        return [
            [
                'title' => 'الملخصات',
                'description' => 'مراجعات مركزة وملخصات جاهزة قبل الاختبارات.',
                'count' => $reviewCount.' عنصر',
                'href' => route('student.lectures.index', ['tab' => 'review']),
                'accent' => 'sand',
            ],
            [
                'title' => 'محاضرات مجانية',
                'description' => 'ابدأ من المحاضرات المفتوحة قبل تفعيل أي باقة.',
                'count' => $freeLectureCount.' محاضرة',
                'href' => route('student.lectures.index', ['tab' => 'lecture', 'scope' => 'free']),
                'accent' => 'violet',
            ],
            [
                'title' => 'محاضرات السنتر',
                'description' => 'تابع المجموعة والحضور والتقييمات المرتبطة بالسنتر.',
                'count' => $student->attendanceRecords()->count().' جلسة',
                'href' => route('student.attendance.index'),
                'accent' => 'brand',
            ],
            [
                'title' => 'محاضرات اونلاين',
                'description' => 'الشرح الكامل والمحتوى المفتوح حسب حالة الوصول الحالية.',
                'count' => $lectureCount.' محاضرة',
                'href' => route('student.lectures.index', ['tab' => 'lecture']),
                'accent' => 'dark',
            ],
            [
                'title' => 'ملتقى الأسئلة',
                'description' => 'أسئلة الطلاب والردود الأكاديمية في مكان واحد.',
                'count' => $forumCount.' موضوع',
                'href' => route('student.forum.index'),
                'accent' => 'violet',
            ],
            [
                'title' => 'الامتحانات الشاملة',
                'description' => 'اختبارات قصيرة ومراجعات نهائية مرتبطة بالمحاضرات.',
                'count' => $examCount.' اختبار',
                'href' => route('student.lectures.index', ['tab' => 'exam']),
                'accent' => 'brand',
            ],
        ];
    }

    private function lectureBaseQuery(Student $student): Builder
    {
        return Lecture::query()
            ->where('grade_id', $student->grade_id)
            ->when($student->track_id, fn (Builder $query) => $query->where(function (Builder $builder) use ($student): void {
                $builder->whereNull('track_id')->orWhere('track_id', $student->track_id);
            }));
    }

    private function examBaseQuery(Student $student): Builder
    {
        return Exam::query()
            ->where('grade_id', $student->grade_id)
            ->when($student->track_id, fn (Builder $query) => $query->where(function (Builder $builder) use ($student): void {
                $builder->whereNull('track_id')->orWhere('track_id', $student->track_id);
            }));
    }

    private function packageGroup(Package $package): string
    {
        $label = mb_strtolower((string) $package->billing_cycle_label);

        if (str_contains($label, '3') || str_contains($label, 'ثلاث')) {
            return 'quarterly';
        }

        if (str_contains($label, 'شهر')) {
            return 'monthly';
        }

        return 'special';
    }

    private function packageSortPriority(Package $package): int
    {
        return match ($this->packageGroup($package)) {
            'quarterly' => 1,
            'monthly' => 2,
            default => 3,
        };
    }
}
