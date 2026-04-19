<x-layouts.student title="المحاضرات" heading="المحاضرات والمراجعات والاختبارات" subheading="استعرض المحتوى المخصص لصفك وحدد ما هو مفتوح لك الآن وما يحتاج شراء أو تفعيل من باقة.">
    <section class="space-y-6">
        <section class="panel-tight">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm font-semibold text-[var(--color-brand-700)]">كتالوج المحتوى الأكاديمي</p>
                    <h2 class="mt-2 text-2xl font-bold">رحلة التعلم في مكان واحد</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-8 text-[var(--color-ink-700)]">
                        بدّل بين المحاضرات والمراجعات والاختبارات، ثم راجع حالة الوصول لكل عنصر قبل الفتح أو الشراء.
                    </p>
                </div>

                <nav class="flex flex-wrap gap-2">
                    <a href="{{ route('student.lectures.index', ['tab' => 'lecture']) }}" @class(['btn-primary' => $tab === 'lecture', 'btn-secondary' => $tab !== 'lecture'])>المحاضرات</a>
                    <a href="{{ route('student.lectures.index', ['tab' => 'review']) }}" @class(['btn-primary' => $tab === 'review', 'btn-secondary' => $tab !== 'review'])>المراجعات</a>
                    <a href="{{ route('student.lectures.index', ['tab' => 'exam']) }}" @class(['btn-primary' => $tab === 'exam', 'btn-secondary' => $tab !== 'exam'])>الاختبارات</a>
                </nav>
            </div>

            <form method="GET" class="mt-6 grid gap-3 lg:grid-cols-[1fr_1fr_auto_auto]">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <select name="curriculum_section" class="form-select">
                    <option value="">كل أقسام المنهج</option>
                    @foreach ($curriculumSections as $section)
                        <option value="{{ $section->id }}" @selected((string) request('curriculum_section') === (string) $section->id)>{{ $section->name_ar }}</option>
                    @endforeach
                </select>
                <select name="lecture_section" class="form-select">
                    <option value="">كل أقسام المحاضرات</option>
                    @foreach ($lectureSections as $section)
                        <option value="{{ $section->id }}" @selected((string) request('lecture_section') === (string) $section->id)>{{ $section->name_ar }}</option>
                    @endforeach
                </select>
                <label class="flex items-center gap-3 rounded-2xl border border-[color-mix(in_oklch,var(--color-brand-200)_70%,white)] bg-white px-4 py-3 text-sm font-semibold text-[var(--color-ink-700)]">
                    <input type="checkbox" name="featured" value="1" @checked(request()->boolean('featured')) class="h-4 w-4 rounded">
                    العناصر المميزة فقط
                </label>
                <button class="btn-secondary">تطبيق</button>
            </form>
        </section>

        @if ($items->isEmpty())
            <x-student.empty-state title="لا توجد عناصر مطابقة" description="جرّب تبديل التبويب أو إزالة الفلاتر الحالية لعرض محتوى أكثر." />
        @else
            <div class="grid gap-4 xl:grid-cols-2">
                @foreach ($items as $item)
                    @php($resource = $item['resource'])
                    @php($access = $item['access'])
                    @php($isExam = $resource instanceof \App\Modules\Academic\Models\Exam)
                    @php($isOpen = in_array($access['state']->value, ['open', 'free', 'owned_via_entitlement'], true))
                    <article class="panel-tight flex h-full flex-col justify-between">
                        <div class="flex flex-col gap-5 lg:flex-row">
                            <div class="flex h-24 w-24 shrink-0 items-center justify-center rounded-[1.8rem] bg-[var(--color-brand-50)] text-center text-xs font-semibold text-[var(--color-brand-700)]">
                                {{ $isExam ? 'اختبار' : ($resource->type->value === 'review' ? 'مراجعة' : 'محاضرة') }}
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <x-student.access-state :access="$access" />
                                    @if ($resource->is_featured)
                                        <x-admin.status-badge label="مميز" />
                                    @endif
                                </div>

                                <h3 class="mt-4 text-lg font-bold">{{ $resource->title }}</h3>
                                <p class="mt-3 text-sm leading-8 text-[var(--color-ink-700)]">{{ $resource->short_description }}</p>

                                <dl class="mt-4 grid gap-3 text-sm text-[var(--color-ink-700)] sm:grid-cols-3">
                                    <div>
                                        <dt class="text-xs text-[var(--color-ink-500)]">السعر</dt>
                                        <dd class="mt-1 font-semibold">{{ number_format($resource->price_amount) }} {{ $resource->currency }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs text-[var(--color-ink-500)]">المدة</dt>
                                        <dd class="mt-1 font-semibold">{{ $resource->duration_minutes ? $resource->duration_minutes.' دقيقة' : 'غير محدد' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs text-[var(--color-ink-500)]">القسم</dt>
                                        <dd class="mt-1 font-semibold">{{ $isExam ? ($resource->lecture?->title ?? 'اختبار مستقل') : ($resource->lectureSection?->name_ar ?? 'عام') }}</dd>
                                    </div>
                                </dl>

                                @if ($access['reason'])
                                    <p class="mt-4 text-sm leading-7 text-[var(--color-ink-500)]">{{ $access['reason'] }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="mt-6 flex flex-wrap gap-3">
                            @if ($isOpen)
                                <a href="{{ $isExam ? route('student.lectures.exams.show', $resource) : route('student.lectures.show', $resource) }}" class="btn-primary">افتح التفاصيل</a>
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
