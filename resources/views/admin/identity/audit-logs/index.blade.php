<x-layouts.admin title="سجل المراجعة" heading="سجل المراجعة" subheading="توثيق العمليات الحساسة لحوكمة المنصة.">
    <x-admin.table-shell title="العمليات الأخيرة" description="يمكن استخدام هذا السجل لمراجعة التغييرات الإدارية وتتبع مصدرها.">
        <x-slot:actions>
            <a href="{{ route('admin.audit-logs.index', array_merge(request()->query(), ['export' => 'csv'])) }}" class="btn-secondary">تصدير CSV</a>
        </x-slot:actions>

        <x-slot:filters>
            <form method="GET" class="grid gap-3 md:grid-cols-[1fr_auto]">
        <input type="search" name="search" value="{{ request('search') }}" class="form-input" placeholder="ابحث باسم الحدث أو نوع الكيان" aria-label="ابحث في سجل العمليات">
                <button class="btn-secondary">تطبيق</button>
            </form>
        </x-slot:filters>

        <table class="data-table">
            <thead>
                <tr>
                    <th>الحدث</th>
                    <th>الفاعل</th>
                    <th>الكيان</th>
                    <th>الوقت</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($auditLogs as $log)
                    <tr>
                        <td class="font-semibold">{{ $log->event }}</td>
                        <td>{{ class_basename($log->actor_type ?? '') }} #{{ $log->actor_id ?? '-' }}</td>
                        <td>{{ class_basename($log->auditable_type ?? '') }} #{{ $log->auditable_id ?? '-' }}</td>
                        <td>{{ optional($log->created_at)->format('Y-m-d H:i:s') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-[var(--color-ink-500)]">لا توجد بيانات.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-5 py-4">
            {{ $auditLogs->links() }}
        </div>
    </x-admin.table-shell>
</x-layouts.admin>
