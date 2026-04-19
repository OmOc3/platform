<x-layouts.student title="الشكاوى والاقتراحات" heading="الشكاوى والاقتراحات" subheading="أرسل ملاحظتك أو اقتراحك وراجع السجل السابق من نفس الحساب.">
    <div class="grid gap-6 lg:grid-cols-[0.9fr_1.1fr]">
        <section class="panel-tight">
            <p class="text-sm font-semibold text-[var(--color-brand-700)]">إرسال رسالة جديدة</p>
            <form method="POST" action="{{ route('student.complaints.store') }}" class="mt-5 space-y-5">
                @csrf
                <div>
                    <label class="field-label" for="type">النوع</label>
                    <select id="type" name="type" class="form-select" required>
                        <option value="">اختر النوع</option>
                        @foreach ($types as $type)
                            <option value="{{ $type->value }}" @selected(old('type') === $type->value)>{{ $type->value }}</option>
                        @endforeach
                    </select>
                    @error('type') <p class="field-help text-[var(--color-danger)]">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="field-label" for="content">المحتوى</label>
                    <textarea id="content" name="content" class="form-textarea" required>{{ old('content') }}</textarea>
                    @error('content') <p class="field-help text-[var(--color-danger)]">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="btn-primary">إرسال</button>
            </form>
        </section>

        <section class="table-shell">
            <div class="px-5 py-5">
                <h2 class="text-lg font-bold">سجل الرسائل السابقة</h2>
            </div>

            @if ($complaints->isEmpty())
                <div class="px-5 pb-5">
                    <x-student.empty-state title="لا يوجد سجل حتى الآن" description="بعد إرسال أول شكوى أو اقتراح سيظهر هنا مع الحالة الحالية." />
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>النوع</th>
                                <th>المحتوى</th>
                                <th>الحالة</th>
                                <th>التاريخ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($complaints as $complaint)
                                <tr>
                                    <td class="font-semibold">{{ $complaint->type->value }}</td>
                                    <td class="max-w-[32rem] text-sm leading-8">{{ $complaint->content }}</td>
                                    <td><x-admin.status-badge :label="$complaint->status->value" tone="warning" /></td>
                                    <td>{{ optional($complaint->created_at)->format('Y-m-d') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-5 py-4">
                    {{ $complaints->links() }}
                </div>
            @endif
        </section>
    </div>
</x-layouts.student>
