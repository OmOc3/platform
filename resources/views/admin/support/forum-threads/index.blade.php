<x-layouts.admin title="ملتقى الأسئلة" heading="ملتقى الأسئلة" subheading="مراجعة الموضوعات العامة، متابعة حالتها، والانتقال السريع للرد الإداري عند الحاجة.">
    <x-admin.table-shell title="موضوعات المنتدى" description="تصفية حسب الحالة والرؤية والبحث باسم الطالب أو عنوان الموضوع.">
        <x-slot:filters>
            <form method="GET" class="grid gap-3 md:grid-cols-[1fr_220px_220px_auto]">
                <input type="search" name="search" value="{{ request('search') }}" class="form-input" placeholder="ابحث بعنوان الموضوع أو الطالب">
                <select name="status" class="form-select">
                    <option value="">كل الحالات</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->value }}</option>
                    @endforeach
                </select>
                <select name="visibility" class="form-select">
                    <option value="">كل مستويات الظهور</option>
                    @foreach ($visibilities as $visibility)
                        <option value="{{ $visibility->value }}" @selected(request('visibility') === $visibility->value)>{{ $visibility->value }}</option>
                    @endforeach
                </select>
                <button class="btn-secondary">تطبيق</button>
            </form>
        </x-slot:filters>

        <table class="data-table">
            <thead>
                <tr>
                    <th>الموضوع</th>
                    <th>الطالب</th>
                    <th>الحالة</th>
                    <th>آخر نشاط</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($threads as $thread)
                    <tr>
                        <td class="font-semibold">{{ $thread->title }}</td>
                        <td>{{ $thread->student?->name }}</td>
                        <td><x-admin.status-badge :label="$thread->status->value" :tone="$thread->status->value === 'answered' ? 'success' : ($thread->status->value === 'closed' ? 'warning' : 'neutral')" /></td>
                        <td>{{ optional($thread->last_activity_at)->format('Y-m-d H:i') }}</td>
                        <td><a href="{{ route('admin.forum-threads.show', $thread) }}" class="btn-secondary !px-4 !py-2">فتح</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-[var(--color-ink-500)]">لا توجد موضوعات بعد.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-5 py-4">{{ $threads->links() }}</div>
    </x-admin.table-shell>
</x-layouts.admin>
