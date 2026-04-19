<x-layouts.admin title="متابعة طالب" heading="متابعة طالب" subheading="تحديث حالة الطالب وبياناته الأكاديمية والتشغيلية الأساسية.">
    <div class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
        <section class="panel-tight">
            <form method="POST" action="{{ route('admin.students.update', $student) }}">
                @include('admin.students._form')
            </form>
        </section>

        <section class="panel-tight">
            <p class="text-sm font-semibold text-[var(--color-brand-700)]">سجل الحالة</p>
            <div class="mt-5 space-y-4">
                @forelse ($student->statusHistories as $history)
                    <article class="rounded-[1.8rem] bg-white p-4 ring-1 ring-[color-mix(in_oklch,var(--color-brand-100)_82%,white)]">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <p class="font-semibold">{{ $history->new_status->value }}</p>
                            <span class="text-xs text-[var(--color-ink-500)]">{{ optional($history->created_at)->format('Y-m-d H:i') }}</span>
                        </div>
                        <p class="mt-2 text-sm text-[var(--color-ink-700)]">
                            من {{ $history->previous_status?->value ?? '—' }} إلى {{ $history->new_status->value }}
                        </p>
                        @if ($history->reason)
                            <p class="mt-2 text-sm leading-8 text-[var(--color-ink-700)]">{{ $history->reason }}</p>
                        @endif
                        <p class="mt-2 text-xs text-[var(--color-ink-500)]">بواسطة: {{ $history->actor?->name ?? 'النظام / الطالب' }}</p>
                    </article>
                @empty
                    <x-student.empty-state title="لا يوجد سجل حالة" description="سيظهر هنا أي تغيير حالة يتم من الإدارة أو التسجيل الذاتي." />
                @endforelse
            </div>
        </section>
    </div>
</x-layouts.admin>
