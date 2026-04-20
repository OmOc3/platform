<x-layouts.admin title="المحتوى الأكاديمي" heading="المحتوى الأكاديمي" subheading="إدارة المحاضرات والمراجعات والملخصات الحرة أو المدفوعة مع أصول التسليم.">
    <section class="admin-metric-grid">
        <x-admin.metric-card label="كل العناصر" :value="$overview['total']" description="إجمالي المحاضرات والمراجعات والملخصات." />
        <x-admin.metric-card label="عناصر نشطة" :value="$overview['active']" description="محتوى ظاهر حاليًا للطلاب." />
        <x-admin.metric-card label="محتوى مجاني" :value="$overview['free']" description="عناصر يمكن للطالب الوصول إليها دون شراء." />
        <x-admin.metric-card label="ملخصات" :value="$overview['summaries']" description="عناصر من نوع ملخص أو ملفات مساندة." />
    </section>

    <x-admin.table-shell title="المحتوى الأكاديمي" description="بحث، تصفية، وتتبّع سريع للمحاضرات والمراجعات والملخصات حسب الصف والنوع والحالة.">
        <x-slot:actions>
            <a href="{{ route('admin.lectures.create') }}" class="btn-primary">إضافة عنصر</a>
            <a href="{{ route('admin.lectures.index', array_merge(request()->query(), ['export' => 'csv'])) }}" class="btn-secondary">تصدير CSV</a>
        </x-slot:actions>

        <x-slot:filters>
            <form method="GET" class="grid gap-3 md:grid-cols-[1fr_220px_220px_220px_auto]">
                <input type="search" name="search" value="{{ request('search') }}" class="form-input" placeholder="ابحث بالعنوان أو الرابط أو الوصف">
                <select name="grade_id" class="form-select">
                    <option value="">كل الصفوف</option>
                    @foreach ($grades as $grade)
                        <option value="{{ $grade->id }}" @selected((string) request('grade_id') === (string) $grade->id)>{{ $grade->name_ar }}</option>
                    @endforeach
                </select>
                <select name="type" class="form-select">
                    <option value="">كل الأنواع</option>
                    @foreach ($types as $type)
                        <option value="{{ $type->value }}" @selected(request('type') === $type->value)>{{ $type->label() }}</option>
                    @endforeach
                </select>
                <select name="status" class="form-select">
                    <option value="">كل الحالات</option>
                    <option value="active" @selected(request('status') === 'active')>نشط</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>متوقف</option>
                </select>
                <button class="btn-secondary">تطبيق</button>
            </form>
        </x-slot:filters>

        <table class="data-table">
            <thead>
                <tr>
                    <th>العنوان</th>
                    <th>التصنيف</th>
                    <th>أصول التسليم</th>
                    <th>السعر</th>
                    <th>الحالة</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($lectures as $row)
                    <tr>
                        <td>
                            <div class="flex items-start gap-3">
                                <div class="catalog-thumb !min-h-20 !w-20 shrink-0 rounded-[1.4rem]">
                                    @if ($row->thumbnail_url)
                                        <img src="{{ $row->thumbnail_url }}" alt="{{ $row->title }}">
                                    @else
                                        <div class="catalog-thumb__fallback !min-h-20 !p-3">
                                            <span>{{ $row->type->label() }}</span>
                                            <strong class="!text-base">{{ $row->duration_minutes ?: '—' }} د</strong>
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <p class="font-semibold">{{ $row->title }}</p>
                                    <p class="mt-1 text-xs text-[var(--color-ink-500)]">{{ $row->grade?->name_ar ?? '—' }} / {{ $row->track?->name_ar ?? 'عام' }}</p>
                                    <p class="mt-2 text-sm leading-7 text-[var(--color-ink-700)]">{{ $row->short_description ?: 'بدون وصف مختصر' }}</p>
                                </div>
                            </div>
                        </td>
                        <td>
                            <p class="font-semibold">{{ $row->type->label() }}</p>
                            <p class="mt-1 text-xs text-[var(--color-ink-500)]">{{ $row->lectureSection?->name_ar ?? 'بدون قسم محاضرات' }}</p>
                        </td>
                        <td>
                            <p class="font-semibold">{{ $row->assets_count }} أصل</p>
                            <p class="mt-1 text-xs text-[var(--color-ink-500)]">{{ $row->checkpoints_count }} نقطة متابعة</p>
                        </td>
                        <td>
                            @if ($row->is_free)
                                <x-admin.status-badge label="مجاني" tone="success" />
                            @else
                                <span class="font-semibold">{{ number_format($row->price_amount) }} {{ $row->currency }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex flex-wrap gap-2">
                                <x-admin.status-badge :label="$row->is_active ? 'نشط' : 'متوقف'" :tone="$row->is_active ? 'success' : 'warning'" />
                                @if ($row->is_featured)
                                    <x-admin.status-badge label="مميز" />
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('admin.lectures.edit', $row) }}" class="btn-secondary !px-4 !py-2">تعديل</a>
                                <a href="{{ route('admin.lectures.progress.index', $row) }}" class="btn-secondary !px-4 !py-2">تقدم الطلاب</a>
                                <form method="POST" action="{{ route('admin.lectures.destroy', $row) }}" onsubmit="return confirm('تأكيد حذف المحتوى؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn-danger">حذف</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-[var(--color-ink-500)]">لا يوجد محتوى مطابق للفلاتر الحالية.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-5 py-4">{{ $lectures->links() }}</div>
    </x-admin.table-shell>
</x-layouts.admin>
