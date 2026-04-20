<x-layouts.admin
    :title="'تقدم الطلاب - '.$lecture->title"
    :heading="'تقدم الطلاب في '.$lecture->title"
    subheading="متابعة حالة استهلاك المحاضرة ونقاط التوقف ونسب الاكتمال للطلاب الذين فتحوا هذا المحتوى."
>
    <x-admin.table-shell title="سجل التقدم" description="عرض مبسط لحالة الطلاب داخل المحاضرة الحالية.">
        <x-slot:actions>
            <a href="{{ route('admin.lectures.edit', $lecture) }}" class="btn-secondary">العودة إلى المحاضرة</a>
        </x-slot:actions>

        <x-slot:filters>
            <form method="GET" class="grid gap-3 md:grid-cols-[1fr_220px_auto]">
                <input type="search" name="search" value="{{ request('search') }}" class="form-input" placeholder="ابحث بالطالب أو البريد أو الرقم" aria-label="ابحث في تقدم الطلاب داخل المحاضرات">
                <select name="status" class="form-select">
                    <option value="">كل الحالات</option>
                    <option value="started" @selected(request('status') === 'started')>بدأت ولم تكتمل</option>
                    <option value="completed" @selected(request('status') === 'completed')>مكتملة</option>
                </select>
                <button class="btn-secondary">تطبيق</button>
            </form>
        </x-slot:filters>

        <table class="data-table">
            <thead>
                <tr>
                    <th>الطالب</th>
                    <th>بدأ في</th>
                    <th>آخر فتح</th>
                    <th>التقدم</th>
                    <th>الموضع الأخير</th>
                    <th>آخر نقطة</th>
                    <th>الاكتمل</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($progressRecords as $progress)
                    <tr>
                        <td>
                            <p class="font-semibold">{{ $progress->student?->name }}</p>
                            <p class="mt-1 text-xs text-[var(--color-ink-500)]">{{ $progress->student?->student_number }}</p>
                        </td>
                        <td>{{ $progress->started_at?->format('Y-m-d H:i') ?? '—' }}</td>
                        <td>{{ $progress->last_opened_at?->diffForHumans() ?? '—' }}</td>
                        <td class="min-w-56">
                            <div class="flex items-center justify-between text-xs font-semibold text-[var(--color-ink-500)]">
                                <span>{{ round((float) $progress->completion_percent) }}%</span>
                                <span>{{ $progress->completed_at ? 'مكتمل' : 'قيد المتابعة' }}</span>
                            </div>
                            <div class="mt-2 lecture-progress-track">
                                <span class="lecture-progress-fill" style="width: {{ round((float) $progress->completion_percent) }}%"></span>
                            </div>
                        </td>
                        <td>{{ gmdate('H:i:s', (int) $progress->last_position_seconds) }}</td>
                        <td>{{ $progress->lastCheckpoint?->title ?? '—' }}</td>
                        <td>{{ $progress->completed_at?->format('Y-m-d H:i') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-[var(--color-ink-500)]">لا توجد سجلات تقدم لهذه المحاضرة بعد.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-5 py-4">{{ $progressRecords->links() }}</div>
    </x-admin.table-shell>
</x-layouts.admin>
