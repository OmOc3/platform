<x-layouts.student title="حضور السنتر" heading="حضور السنتر" subheading="متابعة الجلسات المسجلة وحالة الحضور والاختبارات المرتبطة بها.">
    <section class="table-shell">
        <div class="px-5 py-5">
            <h2 class="text-lg font-bold">سجل الحضور</h2>
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
                            <th>المجموعة</th>
                            <th>الحضور</th>
                            <th>حالة الاختبار</th>
                            <th>الدرجة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($attendanceRecords as $record)
                            <tr>
                                <td class="font-semibold">{{ $record->session?->title }}</td>
                                <td>{{ $record->session?->group?->name_ar ?: '—' }}</td>
                                <td>{{ $record->attendance_status->value }}</td>
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
</x-layouts.student>
