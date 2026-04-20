<x-layouts.student title="حضور السنتر" heading="حضور السنتر" subheading="متابعة الجلسات المسجلة وحالة الحضور ونتائج الاختبارات المرتبطة بها.">
    <section class="space-y-6">
        <x-student.account-nav current="attendance" />

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <x-student.summary-card label="إجمالي الجلسات" :value="$summary['count']" description="كل الجلسات المسجلة على الحساب" />
            <x-student.summary-card label="حاضر" :value="$summary['present_count']" description="جلسات تم تسجيل حضورك فيها" />
            <x-student.summary-card label="متأخر" :value="$summary['late_count']" description="جلسات وصلت إليها متأخرًا" />
            <x-student.summary-card label="متوسط الدرجة" :value="$summary['average_score'] > 0 ? $summary['average_score'] : '—'" description="متوسط الدرجات في الجلسات التي تحتوي على تقييم" />
        </section>

        <section class="table-shell">
            <div class="flex flex-col gap-3 px-5 py-5 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm font-semibold text-[var(--color-brand-700)]">سجل الحضور</p>
                    <h2 class="mt-2 text-xl font-bold">الحصص والاختبارات داخل السنتر</h2>
                </div>
                <a href="{{ route('student.profile.show') }}" class="btn-secondary">العودة إلى حسابي</a>
            </div>

            @if ($attendanceRecords->isEmpty())
                <div class="px-5 pb-5">
                    <x-student.empty-state title="لا توجد سجلات حضور" description="عند تسجيل حضورك في السنتر أو الاختبارات المرتبطة ستظهر هنا." />
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>الجلسة</th>
                                <th>السنتر / المجموعة</th>
                                <th>الحضور</th>
                                <th>حالة الاختبار</th>
                                <th>الدرجة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($attendanceRecords as $record)
                                <tr>
                                    <td class="font-semibold">
                                        <div class="flex flex-col gap-1">
                                            <span>{{ $record->session?->title }}</span>
                                            <span class="text-xs text-[var(--color-ink-500)]">{{ optional($record->recorded_at)->format('Y-m-d H:i') ?: '—' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        {{ $record->session?->group?->center?->name_ar ?: '—' }}
                                        @if ($record->session?->group?->name_ar)
                                            <div class="text-xs text-[var(--color-ink-500)]">{{ $record->session->group->name_ar }}</div>
                                        @endif
                                    </td>
                                    <td><x-admin.status-badge :label="$record->attendance_status->label()" :tone="$record->attendance_status->tone()" /></td>
                                    <td>{{ $record->exam_status_label ?: '—' }}</td>
                                    <td>
                                        @if ($record->score !== null && $record->max_score !== null)
                                            {{ rtrim(rtrim(number_format((float) $record->score, 2, '.', ''), '0'), '.') }}/{{ rtrim(rtrim(number_format((float) $record->max_score, 2, '.', ''), '0'), '.') }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-5 py-4">
                    {{ $attendanceRecords->links() }}
                </div>
            @endif
        </section>
    </section>
</x-layouts.student>
