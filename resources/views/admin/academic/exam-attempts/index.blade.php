<x-layouts.admin title="محاولات الاختبارات" heading="محاولات الاختبارات" subheading="متابعة نتائج الطلاب ومحاولاتهم الفعلية داخل الاختبارات المنشورة.">
    <x-admin.table-shell title="محاولات الاختبارات" description="اعرض المحاولات حسب الطالب أو الاختبار أو حالة التصحيح.">
        <x-slot:filters>
            <form method="GET" class="grid gap-3 md:grid-cols-[1fr_240px_220px_auto]">
                <input type="search" name="search" value="{{ request('search') }}" class="form-input" placeholder="ابحث باسم الطالب أو عنوان الاختبار">
                <select name="exam_id" class="form-select">
                    <option value="">كل الاختبارات</option>
                    @foreach ($exams as $exam)
                        <option value="{{ $exam->id }}" @selected((string) request('exam_id') === (string) $exam->id)>{{ $exam->title }}</option>
                    @endforeach
                </select>
                <select name="status" class="form-select">
                    <option value="">كل الحالات</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
                <button class="btn-secondary">تطبيق</button>
            </form>
        </x-slot:filters>

        <table class="data-table">
            <thead>
                <tr>
                    <th>الطالب</th>
                    <th>الاختبار</th>
                    <th>الحالة</th>
                    <th>المحاولة</th>
                    <th>النتيجة</th>
                    <th>وقت البدء</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($attempts as $attempt)
                    <tr>
                        <td>
                            <p class="font-semibold">{{ $attempt->student?->name }}</p>
                            <p class="mt-1 text-xs text-[var(--color-ink-500)]">{{ $attempt->student?->student_number }}</p>
                        </td>
                        <td>{{ $attempt->exam?->title }}</td>
                        <td><x-admin.status-badge :label="$attempt->status->label()" :tone="$attempt->status->tone()" /></td>
                        <td>{{ $attempt->attempt_number }}</td>
                        <td>{{ $attempt->total_score !== null ? $attempt->total_score.'/'.$attempt->max_score : '—' }}</td>
                        <td>{{ optional($attempt->started_at)->format('Y/m/d H:i') }}</td>
                        <td>
                            <a href="{{ route('admin.exam-attempts.show', $attempt) }}" class="btn-secondary !px-4 !py-2">تفاصيل</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-[var(--color-ink-500)]">لا توجد محاولات اختبارات حتى الآن.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-5 py-4">{{ $attempts->links() }}</div>
    </x-admin.table-shell>
</x-layouts.admin>
