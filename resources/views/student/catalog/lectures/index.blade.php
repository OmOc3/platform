<x-layouts.student title="المحاضرات" heading="المحاضرات والمراجعات والاختبارات" subheading="كتالوج الطالب يعرض المحاضرات، المراجعات، والاختبارات مع السعر والمدة وحالة الوصول الحقيقية قبل الفتح أو الشراء.">
    <section class="space-y-6">
        <section class="panel-tight">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="section-kicker">كتالوج المحتوى الأكاديمي</p>
                    <h2 class="mt-2 text-2xl font-bold lg:text-3xl">المحاضرات / الامتحانات / المراجعات</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-8 text-[var(--color-ink-700)]">
                        بدّل بين أنواع المحتوى المختلفة، ثم صفّ النتائج حسب القسم أو اختر المحاضرات المجانية فقط عند الحاجة.
                    </p>
                </div>

                <nav class="flex flex-wrap gap-2">
                    <a href="{{ route('student.lectures.index', ['tab' => 'lecture']) }}" @class(['btn-primary' => $tab === 'lecture', 'btn-secondary' => $tab !== 'lecture'])>المحاضرات</a>
                    <a href="{{ route('student.lectures.index', ['tab' => 'exam']) }}" @class(['btn-primary' => $tab === 'exam', 'btn-secondary' => $tab !== 'exam'])>الامتحانات</a>
                    <a href="{{ route('student.lectures.index', ['tab' => 'review']) }}" @class(['btn-primary' => $tab === 'review', 'btn-secondary' => $tab !== 'review'])>المراجعات</a>
                </nav>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <x-student.summary-card label="المحاضرات" :value="$overview['lectures']" description="محاضرات شرح ومتابعة" />
                <x-student.summary-card label="المراجعات" :value="$overview['reviews']" description="وحدات ملخصة ومركزة" />
                <x-student.summary-card label="الاختبارات" :value="$overview['exams']" description="اختبارات مرتبطة بالمحتوى" />
                <x-student.summary-card label="المجاني" :value="$overview['free_lectures']" description="محاضرات مفتوحة بدون شراء" />
            </div>

            <form method="GET" class="mt-6 grid gap-3 lg:grid-cols-[1fr_1fr_auto_auto_auto]">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <div>
                    <label class="field-label" for="lecture_curriculum_section">قسم المنهج</label>
                    <select id="lecture_curriculum_section" name="curriculum_section" class="form-select">
                        <option value="">كل أقسام المنهج</option>
                        @foreach ($curriculumSections as $section)
                            <option value="{{ $section->id }}" @selected((string) request('curriculum_section') === (string) $section->id)>{{ $section->name_ar }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="field-label" for="lecture_section_id">قسم المحاضرات</label>
                    <select id="lecture_section_id" name="lecture_section" class="form-select">
                        <option value="">كل أقسام المحاضرات</option>
                        @foreach ($lectureSections as $section)
                            <option value="{{ $section->id }}" @selected((string) request('lecture_section') === (string) $section->id)>{{ $section->name_ar }}</option>
                        @endforeach
                    </select>
                </div>

                @if ($tab !== 'exam')
                    <label class="surface-inset flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold text-[var(--color-ink-700)] lg:self-end">
                        <input type="checkbox" name="scope" value="free" @checked($scope === 'free') class="h-4 w-4 rounded">
                        اعرض المجاني فقط
                    </label>
                @endif

                <label class="surface-inset flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold text-[var(--color-ink-700)] lg:self-end">
                    <input type="checkbox" name="featured" value="1" @checked(request()->boolean('featured')) class="h-4 w-4 rounded">
                    اعرض العناصر المميزة فقط
                </label>

                <button class="btn-secondary lg:self-end">تطبيق الفلاتر</button>
            </form>
        </section>

        @if ($items->isEmpty())
            <x-student.empty-state title="لا توجد عناصر مطابقة" description="جرّب تبديل التبويب أو إزالة الفلاتر الحالية لعرض محتوى أكثر." />
        @else
            <div class="grid gap-4 xl:grid-cols-2">
                @foreach ($items as $item)
                    @php($resource = $item['resource'])
                    @php($access = $item['access'])
                    @php($progress = $item['progress'] ?? null)
                    @php($isExam = $resource instanceof \App\Modules\Academic\Models\Exam)
                    @php($currentAttempt = $item['current_attempt'] ?? null)
                    @php($latestAttempt = $item['latest_attempt'] ?? null)
                    @php($isOpen = in_array($access['state']->value, ['open', 'free', 'owned_via_entitlement'], true))
                    @php($resourceTypeLabel = $isExam ? 'اختبار' : ($resource->type->value === 'review' ? 'مراجعة' : 'محاضرة'))

                    <article class="surface-card rounded-[2rem] p-5">
                        <div class="flex flex-col gap-5 lg:flex-row">
                            <div class="catalog-thumb max-w-[11rem] shrink-0 lg:w-[11rem]">
                                @if ($resource->thumbnail_url)
                                    <img src="{{ $resource->thumbnail_url }}" alt="{{ $resource->title }}" loading="lazy" decoding="async">
                                @else
                                    <div class="catalog-thumb__fallback">
                                        <span>{{ $resourceTypeLabel }}</span>
                                        <strong>{{ $resource->duration_minutes ? $resource->duration_minutes.' دقيقة' : 'بدون مدة' }}</strong>
                                    </div>
                                @endif
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <x-student.access-state :access="$access" />
                                    @if ($resource->is_featured)
                                        <x-admin.status-badge label="مميز" />
                                    @endif
                                    @if ($isExam && $latestAttempt)
                                        <x-admin.status-badge :label="$latestAttempt->total_score.'/'.$latestAttempt->max_score" tone="success" />
                                    @endif
                                    @if (! $isExam && $progress)
                                        <x-admin.status-badge
                                            :label="$progress['label']"
                                            :tone="$progress['status'] === 'completed' ? 'success' : ($progress['status'] === 'not_started' ? 'warning' : 'neutral')"
                                        />
                                    @endif
                                </div>

                                <h3 class="mt-4 text-xl font-bold">{{ $resource->title }}</h3>
                                <p class="mt-3 text-sm leading-8 text-[var(--color-ink-700)]">{{ $resource->short_description }}</p>

                                <div class="mt-5 grid gap-3 text-sm sm:grid-cols-3">
                                    <div class="stat-tile">
                                        <p class="text-xs text-[var(--color-ink-500)]">السعر</p>
                                        <p class="mt-2 font-semibold">
                                            @if (($resource->is_free ?? false) || (int) $resource->price_amount === 0)
                                                مجاني
                                            @else
                                                {{ number_format($resource->price_amount) }} {{ $resource->currency }}
                                            @endif
                                        </p>
                                    </div>
                                    <div class="stat-tile">
                                        <p class="text-xs text-[var(--color-ink-500)]">المدة</p>
                                        <p class="mt-2 font-semibold">{{ $resource->duration_minutes ? $resource->duration_minutes.' دقيقة' : 'غير محدد' }}</p>
                                    </div>
                                    <div class="stat-tile">
                                        <p class="text-xs text-[var(--color-ink-500)]">القسم</p>
                                        <p class="mt-2 font-semibold">{{ $isExam ? ($resource->lecture?->title ?? 'اختبار مستقل') : ($resource->lectureSection?->name_ar ?? 'عام') }}</p>
                                    </div>
                                </div>

                                @if ($access['reason'])
                                    <p class="mt-4 text-sm leading-7 text-[var(--color-ink-500)]">{{ $access['reason'] }}</p>
                                @endif

                                @if ($isExam && $latestAttempt)
                                    <div class="surface-tone surface-tone--success mt-4 rounded-[1.3rem] p-4">
                                        <div class="flex flex-wrap items-center justify-between gap-3">
                                            <div>
                                                <p class="text-sm font-semibold">آخر نتيجة مسجلة</p>
                                                <p class="mt-2 text-xs text-[var(--color-ink-500)]">{{ optional($latestAttempt->graded_at)->format('Y/m/d') ?: '—' }} · {{ optional($latestAttempt->graded_at)->format('H:i') ?: '—' }}</p>
                                            </div>
                                            <span class="text-xl font-bold">{{ $latestAttempt->total_score }}/{{ $latestAttempt->max_score }}</span>
                                        </div>
                                    </div>
                                @endif

                                @if (! $isExam && $progress)
                                    <div class="mt-4 space-y-2">
                                        <div class="flex items-center justify-between text-xs font-semibold text-[var(--color-ink-500)]">
                                            <span>حالة المتابعة</span>
                                            <span>{{ $progress['label'] }}</span>
                                        </div>
                                        <div class="lecture-progress-track">
                                            <span class="lecture-progress-fill" style="width: {{ $progress['percent'] }}%"></span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="mt-6 flex flex-wrap gap-3">
                            @if ($isExam && $currentAttempt)
                                <a href="{{ route('student.exam-attempts.show', $currentAttempt) }}" class="btn-primary">استكمال الاختبار</a>
                                <a href="{{ route('student.lectures.exams.show', $resource) }}" class="btn-secondary">تفاصيل الاختبار</a>
                            @elseif ($isExam && $latestAttempt)
                                <a href="{{ route('student.exam-attempts.result', $latestAttempt) }}" class="btn-primary">عرض النتائج</a>
                                <a href="{{ route('student.lectures.exams.show', $resource) }}" class="btn-secondary">تفاصيل الاختبار</a>
                            @elseif ($isOpen)
                                <a href="{{ $isExam ? route('student.lectures.exams.show', $resource) : route('student.lectures.show', $resource) }}" class="btn-primary">
                                    {{ $isExam ? 'افتح الاختبار' : 'فتح المحاضرة' }}
                                </a>
                            @elseif ($access['state']->value === 'buy' && ! $isExam && $resource->product)
                                <form method="POST" action="{{ route('student.cart.store') }}">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $resource->product->id }}">
                                    <button class="btn-primary">أضف للسلة</button>
                                </form>
                            @elseif ($access['state']->value === 'included_in_package')
                                <a href="{{ route('student.packages.index') }}" class="btn-secondary">استعرض الباقات</a>
                            @else
                                <span class="btn-secondary !cursor-default !opacity-70">غير متاح الآن</span>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="px-2">
                {{ $items->links() }}
            </div>
        @endif
    </section>
</x-layouts.student>
