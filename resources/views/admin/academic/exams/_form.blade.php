@csrf
@if($exam->exists)
    @method('PUT')
@endif

@php
    $questionDrafts = old('questions', $exam->exists
        ? $exam->examQuestions->map(function ($examQuestion) {
            return [
                'question_id' => $examQuestion->question_id,
                'prompt' => $examQuestion->question?->prompt,
                'explanation' => $examQuestion->question?->explanation,
                'max_score' => $examQuestion->max_score,
                'choices' => $examQuestion->question?->choices
                    ->sortBy('sort_order')
                    ->values()
                    ->map(fn ($choice) => [
                        'choice_id' => $choice->id,
                        'content' => $choice->content,
                        'is_correct' => $choice->is_correct,
                    ])
                    ->all() ?? [],
            ];
        })->all()
        : []);

    if ($questionDrafts === []) {
        $questionDrafts = [[
            'question_id' => null,
            'prompt' => '',
            'explanation' => '',
            'max_score' => 1,
            'choices' => [
                ['choice_id' => null, 'content' => '', 'is_correct' => true],
                ['choice_id' => null, 'content' => '', 'is_correct' => false],
            ],
        ]];
    }
@endphp

<div class="grid gap-5 md:grid-cols-2">
    <div>
        <label class="field-label" for="title">العنوان</label>
        <input id="title" name="title" value="{{ old('title', $exam->title) }}" class="form-input" required>
    </div>
    <div>
        <label class="field-label" for="slug">الرابط المختصر</label>
        <input id="slug" name="slug" value="{{ old('slug', $exam->slug) }}" class="form-input" required>
    </div>
    <div>
        <label class="field-label" for="lecture_id">المحاضرة المرتبطة</label>
        <select id="lecture_id" name="lecture_id" class="form-select">
            <option value="">اختبار مستقل</option>
            @foreach ($lectures as $lecture)
                <option value="{{ $lecture->id }}" @selected((string) old('lecture_id', $exam->lecture_id) === (string) $lecture->id)>{{ $lecture->title }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="field-label" for="grade_id">الصف</label>
        <select id="grade_id" name="grade_id" class="form-select" required>
            <option value="">اختر الصف</option>
            @foreach ($grades as $grade)
                <option value="{{ $grade->id }}" @selected((string) old('grade_id', $exam->grade_id) === (string) $grade->id)>{{ $grade->name_ar }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="field-label" for="track_id">المسار</label>
        <select id="track_id" name="track_id" class="form-select">
            <option value="">عام</option>
            @foreach ($tracks as $track)
                <option value="{{ $track->id }}" @selected((string) old('track_id', $exam->track_id) === (string) $track->id)>{{ $track->name_ar }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="field-label" for="thumbnail_url">رابط الصورة</label>
        <input id="thumbnail_url" name="thumbnail_url" value="{{ old('thumbnail_url', $exam->thumbnail_url) }}" class="form-input">
    </div>
    <div>
        <label class="field-label" for="price_amount">السعر</label>
        <input id="price_amount" type="number" min="0" name="price_amount" value="{{ old('price_amount', $exam->price_amount ?? 0) }}" class="form-input" required>
    </div>
    <div>
        <label class="field-label" for="duration_minutes">المدة بالدقائق</label>
        <input id="duration_minutes" type="number" min="1" name="duration_minutes" value="{{ old('duration_minutes', $exam->duration_minutes) }}" class="form-input">
    </div>
    <div>
        <label class="field-label" for="max_attempts">الحد الأقصى للمحاولات</label>
        <input id="max_attempts" type="number" min="1" max="10" name="max_attempts" value="{{ old('max_attempts', data_get($exam->metadata, 'max_attempts', 1)) }}" class="form-input">
    </div>
    <div>
        <label class="field-label" for="currency">العملة</label>
        <input id="currency" name="currency" value="{{ old('currency', $exam->currency ?? 'EGP') }}" class="form-input" required>
    </div>
    <div>
        <label class="field-label" for="published_at">تاريخ النشر</label>
        <input id="published_at" type="datetime-local" name="published_at" value="{{ old('published_at', optional($exam->published_at)->format('Y-m-d\TH:i')) }}" class="form-input">
    </div>
    <div>
        <label class="field-label" for="sort_order">الترتيب</label>
        <input id="sort_order" type="number" min="0" name="sort_order" value="{{ old('sort_order', $exam->sort_order ?? 0) }}" class="form-input">
    </div>
    <div class="md:col-span-2">
        <label class="field-label" for="short_description">الوصف المختصر</label>
        <input id="short_description" name="short_description" value="{{ old('short_description', $exam->short_description) }}" class="form-input">
    </div>
    <div class="md:col-span-2">
        <label class="field-label" for="long_description">الوصف التفصيلي</label>
        <textarea id="long_description" name="long_description" class="form-textarea">{{ old('long_description', $exam->long_description) }}</textarea>
    </div>
</div>

<section class="surface-tone surface-tone--brand mt-8 rounded-[2rem] p-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-semibold">أسئلة الاختبار</h2>
            <p class="mt-1 text-sm text-[var(--color-ink-500)]">يدعم هذا الإصدار أسئلة الاختيار من متعدد مع تصحيح فوري بعد الإرسال.</p>
        </div>
        <button type="button" class="btn-secondary" data-add-question>إضافة سؤال</button>
    </div>

    @error('questions')
        <p class="mt-3 text-sm font-medium text-[var(--color-danger)]">{{ $message }}</p>
    @enderror

    <div class="mt-5 space-y-5" data-question-list>
        @foreach ($questionDrafts as $questionIndex => $questionDraft)
            <article class="surface-card rounded-[1.8rem] p-5" data-question-card>
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <p class="text-sm font-semibold text-[var(--color-brand-700)]">سؤال <span data-question-number>{{ $loop->iteration }}</span></p>
                    <button type="button" class="btn-danger !px-4 !py-2" data-remove-question>حذف السؤال</button>
                </div>

                <input type="hidden" name="questions[{{ $questionIndex }}][question_id]" value="{{ $questionDraft['question_id'] }}">

                <div class="mt-4 grid gap-4 md:grid-cols-[1fr_180px]">
                    <div>
                        <label class="field-label">نص السؤال</label>
                        <textarea name="questions[{{ $questionIndex }}][prompt]" class="form-textarea" rows="3" required>{{ $questionDraft['prompt'] }}</textarea>
                        @error("questions.$questionIndex.prompt")
                            <p class="mt-2 text-sm font-medium text-[var(--color-danger)]">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="field-label">درجة السؤال</label>
                        <input type="number" min="1" max="100" name="questions[{{ $questionIndex }}][max_score]" value="{{ $questionDraft['max_score'] ?? 1 }}" class="form-input" required>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="field-label">التفسير بعد التصحيح</label>
                    <textarea name="questions[{{ $questionIndex }}][explanation]" class="form-textarea" rows="2">{{ $questionDraft['explanation'] ?? '' }}</textarea>
                </div>

                <div class="surface-outline surface-outline--brand mt-5 rounded-[1.6rem] p-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <p class="text-sm font-semibold">الاختيارات</p>
                        <button type="button" class="btn-secondary !px-4 !py-2" data-add-choice>إضافة اختيار</button>
                    </div>

                    @error("questions.$questionIndex.choices")
                        <p class="mt-2 text-sm font-medium text-[var(--color-danger)]">{{ $message }}</p>
                    @enderror

                    <div class="mt-4 space-y-3" data-choice-list>
                        @foreach ($questionDraft['choices'] as $choiceIndex => $choiceDraft)
                            <div class="grid gap-3 rounded-[1.4rem] bg-[var(--color-brand-50)] p-3 md:grid-cols-[auto_1fr_auto] md:items-center" data-choice-row>
                                <div class="flex items-center gap-2">
                                    <input type="radio"
                                           name="questions[{{ $questionIndex }}][correct_choice]"
                                           value="{{ $choiceIndex }}"
                                           class="h-4 w-4"
                                           @checked($choiceDraft['is_correct'] ?? false)>
                                    <span class="text-xs font-semibold text-[var(--color-ink-500)]">إجابة صحيحة</span>
                                </div>
                                <div>
                                    <input type="hidden" name="questions[{{ $questionIndex }}][choices][{{ $choiceIndex }}][choice_id]" value="{{ $choiceDraft['choice_id'] ?? '' }}">
                                    <input type="text" name="questions[{{ $questionIndex }}][choices][{{ $choiceIndex }}][content]" value="{{ $choiceDraft['content'] ?? '' }}" class="form-input" placeholder="نص الاختيار" required>
                                    @error("questions.$questionIndex.choices.$choiceIndex.content")
                                        <p class="mt-2 text-sm font-medium text-[var(--color-danger)]">{{ $message }}</p>
                                    @enderror
                                </div>
                                <button type="button" class="btn-secondary !px-4 !py-2" data-remove-choice>حذف</button>
                            </div>
                        @endforeach
                    </div>
                </div>
            </article>
        @endforeach
    </div>
</section>

<div class="mt-5 grid gap-3 md:grid-cols-3">
    <label class="flex items-center gap-3 text-sm font-medium text-[var(--color-ink-700)]">
        <input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded" @checked(old('is_active', $exam->is_active ?? true))>
        الاختبار نشط
    </label>
    <label class="flex items-center gap-3 text-sm font-medium text-[var(--color-ink-700)]">
        <input type="checkbox" name="is_featured" value="1" class="h-4 w-4 rounded" @checked(old('is_featured', $exam->is_featured ?? false))>
        عنصر مميز
    </label>
    <label class="flex items-center gap-3 text-sm font-medium text-[var(--color-ink-700)]">
        <input type="checkbox" name="is_free" value="1" class="h-4 w-4 rounded" @checked(old('is_free', $exam->is_free ?? true))>
        مجاني
    </label>
</div>

<div class="mt-6 flex flex-wrap gap-3">
    <button type="submit" class="btn-primary">حفظ</button>
    <a href="{{ route('admin.exams.index') }}" class="btn-secondary">إلغاء</a>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const questionList = document.querySelector('[data-question-list]');

            if (! questionList) {
                return;
            }

            let nextQuestionIndex = {{ collect(array_keys($questionDrafts))->max() ?? -1 }} + 1;

            const renumberQuestions = () => {
                questionList.querySelectorAll('[data-question-card]').forEach((card, index) => {
                    const number = card.querySelector('[data-question-number]');

                    if (number) {
                        number.textContent = index + 1;
                    }
                });
            };

            const buildChoiceRow = (questionIndex, choiceIndex) => `
                <div class="grid gap-3 rounded-[1.4rem] bg-[var(--color-brand-50)] p-3 md:grid-cols-[auto_1fr_auto] md:items-center" data-choice-row>
                    <div class="flex items-center gap-2">
                        <input type="radio" name="questions[${questionIndex}][correct_choice]" value="${choiceIndex}" class="h-4 w-4" ${choiceIndex === 0 ? 'checked' : ''}>
                        <span class="text-xs font-semibold text-[var(--color-ink-500)]">إجابة صحيحة</span>
                    </div>
                    <div>
                        <input type="hidden" name="questions[${questionIndex}][choices][${choiceIndex}][choice_id]" value="">
                        <input type="text" name="questions[${questionIndex}][choices][${choiceIndex}][content]" class="form-input" placeholder="نص الاختيار" required>
                    </div>
                    <button type="button" class="btn-secondary !px-4 !py-2" data-remove-choice>حذف</button>
                </div>
            `;

            const buildQuestionCard = (questionIndex) => `
                <article class="surface-card rounded-[1.8rem] p-5" data-question-card>
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <p class="text-sm font-semibold text-[var(--color-brand-700)]">سؤال <span data-question-number></span></p>
                        <button type="button" class="btn-danger !px-4 !py-2" data-remove-question>حذف السؤال</button>
                    </div>
                    <input type="hidden" name="questions[${questionIndex}][question_id]" value="">
                    <div class="mt-4 grid gap-4 md:grid-cols-[1fr_180px]">
                        <div>
                            <label class="field-label">نص السؤال</label>
                            <textarea name="questions[${questionIndex}][prompt]" class="form-textarea" rows="3" required></textarea>
                        </div>
                        <div>
                            <label class="field-label">درجة السؤال</label>
                            <input type="number" min="1" max="100" name="questions[${questionIndex}][max_score]" value="1" class="form-input" required>
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="field-label">التفسير بعد التصحيح</label>
                        <textarea name="questions[${questionIndex}][explanation]" class="form-textarea" rows="2"></textarea>
                    </div>
                    <div class="surface-outline surface-outline--brand mt-5 rounded-[1.6rem] p-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <p class="text-sm font-semibold">الاختيارات</p>
                            <button type="button" class="btn-secondary !px-4 !py-2" data-add-choice>إضافة اختيار</button>
                        </div>
                        <div class="mt-4 space-y-3" data-choice-list>
                            ${buildChoiceRow(questionIndex, 0)}
                            ${buildChoiceRow(questionIndex, 1)}
                        </div>
                    </div>
                </article>
            `;

            document.querySelector('[data-add-question]')?.addEventListener('click', () => {
                questionList.insertAdjacentHTML('beforeend', buildQuestionCard(nextQuestionIndex));
                nextQuestionIndex += 1;
                renumberQuestions();
            });

            document.addEventListener('click', (event) => {
                const addChoiceButton = event.target.closest('[data-add-choice]');

                if (addChoiceButton) {
                    const card = addChoiceButton.closest('[data-question-card]');
                    const choiceList = card?.querySelector('[data-choice-list]');

                    if (! card || ! choiceList) {
                        return;
                    }

                    const promptInput = card.querySelector('textarea[name*="[prompt]"]');
                    const match = promptInput?.getAttribute('name')?.match(/questions\[(\d+)]\[prompt]/);

                    if (! match) {
                        return;
                    }

                    const questionIndex = Number(match[1]);
                    const choiceIndex = Math.max(
                        ...Array.from(choiceList.querySelectorAll('input[name*="[content]"]'))
                            .map((input) => {
                                const inputMatch = input.getAttribute('name')?.match(/\[choices]\[(\d+)]\[content]/);

                                return inputMatch ? Number(inputMatch[1]) : -1;
                            }),
                        -1,
                    ) + 1;

                    choiceList.insertAdjacentHTML('beforeend', buildChoiceRow(questionIndex, choiceIndex));
                    return;
                }

                const removeChoiceButton = event.target.closest('[data-remove-choice]');

                if (removeChoiceButton) {
                    const choiceList = removeChoiceButton.closest('[data-choice-list]');

                    if (choiceList && choiceList.querySelectorAll('[data-choice-row]').length > 2) {
                        removeChoiceButton.closest('[data-choice-row]')?.remove();
                    }

                    return;
                }

                const removeQuestionButton = event.target.closest('[data-remove-question]');

                if (removeQuestionButton && questionList.querySelectorAll('[data-question-card]').length > 1) {
                    removeQuestionButton.closest('[data-question-card]')?.remove();
                    renumberQuestions();
                }
            });

            renumberQuestions();
        });
    </script>
@endpush
