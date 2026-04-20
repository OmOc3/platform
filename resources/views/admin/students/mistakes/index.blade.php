<x-layouts.admin title="مركز الأخطاء" heading="مركز الأخطاء" subheading="مراجعة السجل الحالي للأخطاء المرتبطة بالطلاب والمحاضرات تمهيدًا لربطه لاحقًا بمحاولات الاختبارات.">
    <x-admin.table-shell title="سجل الأخطاء" description="بحث سريع في الأسئلة المسجلة وأسماء الطلاب والمحاضرات المرتبطة بها.">
        <x-slot:filters>
            <form method="GET" class="grid gap-3 md:grid-cols-[1fr_auto]">
    <input type="search" name="search" value="{{ request('search') }}" class="form-input" placeholder="ابحث بنص السؤال أو الطالب أو المحاضرة" aria-label="ابحث في أخطاء الطلاب">
                <button class="btn-secondary">بحث</button>
            </form>
        </x-slot:filters>

        <table class="data-table">
            <thead>
                <tr>
                    <th>الطالب</th>
                    <th>المحاضرة</th>
                    <th>الدرجة المفقودة</th>
                    <th>التاريخ</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    <tr>
                        <td class="font-semibold">{{ $item->student?->name }}</td>
                        <td>{{ $item->lecture?->title ?? 'غير محدد' }}</td>
                        <td>{{ $item->score_lost ?? 0 }}</td>
                        <td>{{ $item->created_at->format('Y-m-d') }}</td>
                        <td><a href="{{ route('admin.mistakes.show', $item) }}" class="btn-secondary !px-4 !py-2">عرض</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-[var(--color-ink-500)]">لا توجد سجلات بعد.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-5 py-4">{{ $items->links() }}</div>
    </x-admin.table-shell>
</x-layouts.admin>
