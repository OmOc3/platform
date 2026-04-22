<x-layouts.admin
    :title="$attendanceSession->title"
    :heading="$attendanceSession->title"
    subheading="تسجيل حضور الجلسة، تحديث درجات الاختبار إن وجدت، ومراجعة الروستر الحالي للمجموعة المرتبطة."
>
    <section class="space-y-6">
        <section class="admin-metric-grid">
            <x-admin.metric-card label="طلاب المجموعة" :value="$summary['students']" description="عدد الطلاب المرتبطين بالمجموعة الحالية." />
            <x-admin.metric-card label="حاضر" :value="$summary['present']" description="طلاب تم تسجيلهم كحاضرين." />
            <x-admin.metric-card label="متأخر" :value="$summary['late']" description="طلاب تم تسجيلهم كمتأخرين." />
            <x-admin.metric-card label="غائب" :value="$summary['absent']" description="طلاب تم تسجيلهم كغائبين." />
        </section>

        <section class="panel-tight">
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-ink-500)]">السنتر</p>
                    <p class="mt-2 font-semibold">{{ $attendanceSession->group?->center?->name_ar ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-ink-500)]">المجموعة</p>
                    <p class="mt-2 font-semibold">{{ $attendanceSession->group?->name_ar ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-ink-500)]">النوع</p>
                    <div class="mt-2">
                        <x-admin.status-badge :label="$attendanceSession->session_type === 'exam' ? 'اختبار' : 'محاضرة'" :tone="$attendanceSession->session_type === 'exam' ? 'warning' : 'neutral'" />
                    </div>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-ink-500)]">الوقت</p>
                    <p class="mt-2 font-semibold">{{ optional($attendanceSession->starts_at)->format('Y-m-d H:i') ?: '—' }}</p>
                </div>
            </div>
        </section>

        <form method="POST" action="{{ route('admin.attendance.update', $attendanceSession) }}">
            @csrf
            @method('PUT')

            <x-admin.table-shell title="روستر الجلسة" description="حدّث الحضور لكل طالب مرة واحدة. في جلسات الاختبار يمكنك أيضًا تسجيل الدرجة والنهاية العظمى.">
                <x-slot:actions>
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('admin.attendance.index') }}" class="btn-secondary">العودة إلى التقرير</a>
                        <button class="btn-primary">حفظ السجلات</button>
                    </div>
                </x-slot:actions>

                @if ($roster->isEmpty())
                    <div class="px-5 pb-5">
                        <x-student.empty-state title="لا يوجد طلاب في هذه المجموعة" description="أضف طلابًا إلى المجموعة أولًا حتى تتمكن من تسجيل حضور الجلسة." />
                    </div>
                @else
                    @if ($errors->any())
                        <div class="px-5 pt-5">
                            <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                                <p class="font-semibold">تعذر حفظ بعض السجلات.</p>
                                <ul class="mt-2 space-y-1">
                                    @foreach ($errors->all() as $message)
                                        <li>{{ $message }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>الطالب</th>
                                <th>الحالة</th>
                                @if ($attendanceSession->session_type === 'exam')
                                    <th>حالة الاختبار</th>
                                    <th>الدرجة</th>
                                    <th>النهاية العظمى</th>
                                @endif
                                <th>ملاحظات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($roster as $index => $entry)
                                @php
                                    $student = $entry['student'];
                                    $record = $entry['record'];
                                @endphp
                                <tr>
                                    <td>
                                        <input type="hidden" name="records[{{ $index }}][student_id]" value="{{ $student->id }}">
                                        <p class="font-semibold">{{ $student->name }}</p>
                                        <p class="mt-1 text-xs text-[var(--color-ink-500)]">{{ $student->student_number ?: '—' }}</p>
                                    </td>
                                    <td>
                                        <select name="records[{{ $index }}][attendance_status]" class="form-select min-w-[150px]">
                                            @foreach ($statuses as $status)
                                                <option value="{{ $status->value }}" @selected(old("records.$index.attendance_status", $record?->attendance_status?->value ?? \App\Shared\Enums\AttendanceStatus::Present->value) === $status->value)>
                                                    {{ $status->label() }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error("records.$index.attendance_status")
                                            <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    </td>
                                    @if ($attendanceSession->session_type === 'exam')
                                        <td>
                                            <input
                                                name="records[{{ $index }}][exam_status_label]"
                                                class="form-input min-w-[150px]"
                                                value="{{ old("records.$index.exam_status_label", $record?->exam_status_label) }}"
                                                placeholder="تم الاختبار"
                                            >
                                        </td>
                                        <td>
                                            <input
                                                type="number"
                                                min="0"
                                                step="0.01"
                                                name="records[{{ $index }}][score]"
                                                class="form-input min-w-[110px]"
                                                value="{{ old("records.$index.score", $record?->score) }}"
                                            >
                                        </td>
                                        <td>
                                            <input
                                                type="number"
                                                min="0"
                                                step="0.01"
                                                name="records[{{ $index }}][max_score]"
                                                class="form-input min-w-[110px]"
                                                value="{{ old("records.$index.max_score", $record?->max_score) }}"
                                            >
                                        </td>
                                    @endif
                                    <td>
                                        <textarea
                                            name="records[{{ $index }}][notes]"
                                            class="form-textarea min-w-[220px]"
                                            rows="2"
                                            placeholder="ملاحظات اختيارية"
                                        >{{ old("records.$index.notes", $record?->notes) }}</textarea>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    @error('records')
                        <div class="px-5 pt-4">
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        </div>
                    @enderror

                    <div class="px-5 py-4">
                        <button class="btn-primary">حفظ السجلات</button>
                    </div>
                @endif
            </x-admin.table-shell>
        </form>
    </section>
</x-layouts.admin>
