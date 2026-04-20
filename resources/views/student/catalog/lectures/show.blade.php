<x-layouts.student :title="$lecture->title" :heading="$lecture->title" subheading="صفحة التعلم، متابعة التقدم، والاختبارات المرتبطة بالمحاضرة في واجهة واحدة.">
    @php($progressPercent = $progress ? (int) round((float) $progress->completion_percent) : 0)
    @php($completionLabel = $progress?->completed_at ? 'مكتمل' : ($progress ? ($progressPercent > 0 ? $progressPercent.'% مكتمل' : 'بدأت') : 'لم تبدأ'))
    @php($resumeSeconds = (int) ($progress?->last_position_seconds ?? 0))

    @if (! $canConsume)
        <section class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
            <article class="panel-tight">
                <div class="flex flex-wrap items-center gap-3">
                    <x-student.access-state :access="$access" />
                    <x-admin.status-badge :label="$lecture->type->value === 'review' ? 'مراجعة' : 'محاضرة'" />
                </div>

                <p class="mt-4 text-sm text-[var(--color-ink-500)]">
                    {{ $lecture->grade?->name_ar }}{{ $lecture->track ? ' / '.$lecture->track->name_ar : '' }}
                </p>
                <p class="mt-6 text-base leading-9 text-[var(--color-ink-700)]">{{ $lecture->long_description ?: $lecture->short_description }}</p>

                <dl class="mt-8 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-[1.6rem] bg-[var(--color-brand-50)] p-4">
                        <dt class="text-xs text-[var(--color-ink-500)]">قسم المنهج</dt>
                        <dd class="mt-2 font-semibold">{{ $lecture->curriculumSection?->name_ar ?? 'عام' }}</dd>
                    </div>
                    <div class="rounded-[1.6rem] bg-[var(--color-brand-50)] p-4">
                        <dt class="text-xs text-[var(--color-ink-500)]">قسم المحاضرات</dt>
                        <dd class="mt-2 font-semibold">{{ $lecture->lectureSection?->name_ar ?? 'عام' }}</dd>
                    </div>
                    <div class="rounded-[1.6rem] bg-[var(--color-brand-50)] p-4">
                        <dt class="text-xs text-[var(--color-ink-500)]">المدة</dt>
                        <dd class="mt-2 font-semibold">{{ $lecture->duration_minutes ? $lecture->duration_minutes.' دقيقة' : 'غير محدد' }}</dd>
                    </div>
                    <div class="rounded-[1.6rem] bg-[var(--color-brand-50)] p-4">
                        <dt class="text-xs text-[var(--color-ink-500)]">السعر</dt>
                        <dd class="mt-2 font-semibold">{{ number_format($lecture->price_amount) }} {{ $lecture->currency }}</dd>
                    </div>
                </dl>
            </article>

            <aside class="panel-tight">
                <p class="text-sm font-semibold text-[var(--color-brand-700)]">حالة الوصول</p>
                <div class="mt-4"><x-student.access-state :access="$access" /></div>
                @if ($access['reason'])
                    <p class="mt-4 text-sm leading-8 text-[var(--color-ink-700)]">{{ $access['reason'] }}</p>
                @endif

                <div class="mt-6 flex flex-col gap-3">
                    @if ($access['state']->value === 'buy' && $lecture->product)
                        <form method="POST" action="{{ route('student.cart.store') }}">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $lecture->product->id }}">
                            <button class="btn-primary">أضف إلى السلة</button>
                        </form>
                    @elseif ($access['state']->value === 'included_in_package')
                        <a href="{{ route('student.packages.index') }}" class="btn-primary">استعرض الباقات المرتبطة</a>
                    @endif
                    <a href="{{ route('student.lectures.index', ['tab' => $lecture->type->value === 'review' ? 'review' : 'lecture']) }}" class="btn-secondary">العودة إلى الكتالوج</a>
                </div>
            </aside>
        </section>
    @else
        <section class="grid gap-6 xl:grid-cols-[1.5fr_0.7fr]">
            <article class="space-y-6">
                <section class="panel-tight">
                    <div class="flex flex-wrap items-center gap-3">
                        <x-student.access-state :access="$access" />
                        <x-admin.status-badge :label="$lecture->type->value === 'review' ? 'مراجعة' : 'محاضرة'" />
                        <x-admin.status-badge label="افتح المحتوى" />
                        @if ($progress?->completed_at)
                            <x-admin.status-badge label="مكتمل" tone="success" />
                        @endif
                    </div>

                    <div class="mt-6 rounded-[2rem] border border-[var(--color-border-soft)] bg-[var(--color-panel-strong)] p-4 lg:p-5">
                        @if ($primaryAsset?->kind === \App\Shared\Enums\LectureAssetKind::EmbedVideo)
                            <div class="overflow-hidden rounded-[1.6rem] border border-[var(--color-border-soft)]">
                                <iframe
                                    src="{{ $primaryAsset->url }}"
                                    class="aspect-video w-full"
                                    title="{{ $primaryAsset->title }}"
                                    allowfullscreen
                                    loading="lazy"
                                ></iframe>
                            </div>
                        @elseif ($primaryAsset?->kind === \App\Shared\Enums\LectureAssetKind::ExternalVideo)
                            <div class="surface-card-soft rounded-[1.6rem] p-6">
                                <p class="text-sm font-semibold text-[var(--color-brand-700)]">الفيديو الرئيسي</p>
                                <h2 class="mt-3 text-xl font-bold">{{ $primaryAsset->title }}</h2>
                                @if ($primaryAsset->body)
                                    <p class="mt-3 text-sm leading-8 text-[var(--color-ink-700)]">{{ $primaryAsset->body }}</p>
                                @endif
                                <a href="{{ $primaryAsset->url }}" target="_blank" rel="noreferrer" class="btn-primary mt-5">افتح الفيديو الخارجي</a>
                            </div>
                        @elseif ($primaryAsset?->kind === \App\Shared\Enums\LectureAssetKind::TextBlock)
                            <article class="surface-card-soft rounded-[1.6rem] p-6">
                                <p class="text-sm font-semibold text-[var(--color-brand-700)]">{{ $primaryAsset->title }}</p>
                                <div class="mt-4 space-y-4 leading-9 text-[var(--color-ink-900)]">
                                    {!! nl2br(e($primaryAsset->body ?? '')) !!}
                                </div>
                            </article>
                        @elseif ($primaryAsset?->kind)
                            <div class="surface-card-soft rounded-[1.6rem] p-6">
                                <p class="text-sm font-semibold text-[var(--color-brand-700)]">{{ $primaryAsset->title }}</p>
                                @if ($primaryAsset->body)
                                    <p class="mt-3 text-sm leading-8 text-[var(--color-ink-700)]">{{ $primaryAsset->body }}</p>
                                @endif
                                @if ($primaryAsset->url)
                                    <a href="{{ $primaryAsset->url }}" target="_blank" rel="noreferrer" class="btn-primary mt-5">افتح المورد</a>
                                @endif
                            </div>
                        @else
                            <div class="surface-card-soft rounded-[1.6rem] p-6">
                                <p class="text-sm font-semibold text-[var(--color-brand-700)]">لا توجد وسائط جاهزة بعد</p>
                                <p class="mt-3 text-sm leading-8 text-[var(--color-ink-700)]">سيتم عرض الأصول التعليمية هنا بمجرد إعدادها من لوحة الإدارة.</p>
                            </div>
                        @endif
                    </div>

                    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        <div class="surface-card-soft rounded-[1.6rem] p-4">
                            <p class="text-xs text-[var(--color-ink-500)]">الصف</p>
                            <p class="mt-2 font-semibold">{{ $lecture->grade?->name_ar }}</p>
                        </div>
                        <div class="surface-card-soft rounded-[1.6rem] p-4">
                            <p class="text-xs text-[var(--color-ink-500)]">القسم</p>
                            <p class="mt-2 font-semibold">{{ $lecture->lectureSection?->name_ar ?? 'عام' }}</p>
                        </div>
                        <div class="surface-card-soft rounded-[1.6rem] p-4">
                            <p class="text-xs text-[var(--color-ink-500)]">المدة</p>
                            <p class="mt-2 font-semibold">{{ $lecture->duration_minutes ? $lecture->duration_minutes.' دقيقة' : 'بدون مدة محددة' }}</p>
                        </div>
                        <div class="surface-card-soft rounded-[1.6rem] p-4">
                            <p class="text-xs text-[var(--color-ink-500)]">الاستكمال</p>
                            <p class="mt-2 font-semibold">{{ $completionLabel }}</p>
                        </div>
                    </div>

                    @if ($lecture->long_description || $lecture->short_description)
                        <div class="mt-6 rounded-[1.8rem] border border-[var(--color-border-soft)] bg-[var(--color-panel-muted)] p-5">
                            <p class="text-sm font-semibold text-[var(--color-brand-700)]">نبذة عن المحاضرة</p>
                            <p class="mt-3 text-sm leading-8 text-[var(--color-ink-700)]">{{ $lecture->long_description ?: $lecture->short_description }}</p>
                        </div>
                    @endif
                </section>

                @if ($supportingAssets->isNotEmpty())
                    <section class="panel-tight">
                        <div>
                            <p class="text-sm font-semibold text-[var(--color-brand-700)]">مرفقات وموارد مساندة</p>
                            <h2 class="mt-2 text-xl font-bold">كل ما تحتاجه بجوار المحاضرة</h2>
                        </div>

                        <div class="mt-5 grid gap-4 lg:grid-cols-2">
                            @foreach ($supportingAssets as $asset)
                                <article class="surface-card rounded-[1.6rem] p-4">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <p class="text-sm font-semibold">{{ $asset->title }}</p>
                                            <p class="mt-1 text-xs text-[var(--color-ink-500)]">{{ $asset->kind->value }}</p>
                                        </div>
                                        @if ($asset->url)
                                            <a href="{{ $asset->url }}" target="_blank" rel="noreferrer" class="btn-secondary !px-4 !py-2">فتح</a>
                                        @endif
                                    </div>
                                    @if ($asset->body)
                                        <p class="mt-3 text-sm leading-8 text-[var(--color-ink-700)]">{{ $asset->body }}</p>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($relatedExams->isNotEmpty())
                    <section class="panel-tight">
                        <div>
                            <p class="text-sm font-semibold text-[var(--color-brand-700)]">اختبارات مرتبطة</p>
                            <h2 class="mt-2 text-xl font-bold">أغلق الحلقة بين المذاكرة والتقييم</h2>
                        </div>

                        <div class="mt-5 grid gap-4 lg:grid-cols-2">
                            @foreach ($relatedExams as $examRow)
                                <article class="surface-card rounded-[1.6rem] p-4">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <x-student.access-state :access="$examRow['access']" />
                                    </div>
                                    <h3 class="mt-4 text-lg font-bold">{{ $examRow['exam']->title }}</h3>
                                    <p class="mt-3 text-sm leading-8 text-[var(--color-ink-700)]">{{ $examRow['exam']->short_description }}</p>
                                    <div class="mt-4 flex items-center justify-between gap-3">
                                        <span class="text-xs text-[var(--color-ink-500)]">{{ $examRow['exam']->duration_minutes ? $examRow['exam']->duration_minutes.' دقيقة' : 'بدون حد زمني' }}</span>
                                        <a href="{{ $examRow['cta']['href'] }}" class="btn-primary !px-4 !py-2">{{ $examRow['cta']['label'] }}</a>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif
            </article>

            <aside class="space-y-6">
                <section
                    class="panel-tight"
                    data-lecture-progress
                    data-update-url="{{ route('student.lectures.progress.update', $lecture) }}"
                    data-touch-url="{{ route('student.lectures.progress.touch', $lecture) }}"
                    data-complete-url="{{ route('student.lectures.progress.complete', $lecture) }}"
                    data-duration-seconds="{{ $lecture->duration_minutes ? $lecture->duration_minutes * 60 : '' }}"
                    data-initial-position="{{ $resumeSeconds }}"
                    data-initial-consumed="{{ (int) ($progress?->consumed_seconds ?? 0) }}"
                    data-initial-progress="{{ $progressPercent }}"
                    data-initial-checkpoint-order="{{ $progress?->lastCheckpoint?->sort_order ?? 0 }}"
                    data-completed="{{ $progress?->completed_at ? 1 : 0 }}"
                >
                    <p class="text-sm font-semibold text-[var(--color-brand-700)]">تقدمك في المحاضرة</p>
                    <div class="mt-4 flex items-center justify-between gap-3">
                        <p class="text-3xl font-bold" data-progress-percent>{{ $progressPercent }}%</p>
                        <x-admin.status-badge :label="$completionLabel" :tone="$progress?->completed_at ? 'success' : 'info'" data-progress-status />
                    </div>

                    <div class="mt-4 lecture-progress-track">
                        <span class="lecture-progress-fill" data-progress-fill style="width: {{ $progressPercent }}%"></span>
                    </div>

                    <dl class="mt-6 space-y-4 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-[var(--color-ink-500)]">آخر موضع محفوظ</dt>
                            <dd class="font-semibold" data-progress-position>{{ gmdate('H:i:s', $resumeSeconds) }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-[var(--color-ink-500)]">الوقت المستهلك</dt>
                            <dd class="font-semibold" data-progress-consumed>{{ gmdate('H:i:s', (int) ($progress?->consumed_seconds ?? 0)) }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-[var(--color-ink-500)]">آخر فتح</dt>
                            <dd class="font-semibold">{{ $progress?->last_opened_at?->diffForHumans() ?? 'الآن' }}</dd>
                        </div>
                    </dl>

                    <p class="mt-4 text-xs leading-7 text-[var(--color-ink-500)]" data-progress-message>
                        يتم حفظ التقدم تلقائيًا أثناء المتابعة، ويمكنك أيضًا الحفظ يدويًا أو تعليم المحاضرة كمكتملة.
                    </p>

                    <div class="mt-5 flex flex-wrap gap-3">
                        <button type="button" class="btn-secondary !px-4 !py-2" data-save-progress>حفظ التقدم الآن</button>
                        <button type="button" class="btn-primary !px-4 !py-2" data-complete-progress>تعليم كمكتمل</button>
                    </div>
                </section>

                @if ($lecture->checkpoints->isNotEmpty())
                    <section class="panel-tight">
                        <p class="text-sm font-semibold text-[var(--color-brand-700)]">النقاط المرحلية</p>
                        <div class="mt-4 space-y-3">
                            @foreach ($lecture->checkpoints as $checkpoint)
                                <button
                                    type="button"
                                    class="lecture-checkpoint-item {{ ($progress?->lastCheckpoint?->sort_order ?? 0) >= $checkpoint->sort_order ? 'lecture-checkpoint-item--active' : '' }}"
                                    data-checkpoint-button
                                    data-checkpoint-order="{{ $checkpoint->sort_order }}"
                                    data-checkpoint-url="{{ route('student.lectures.checkpoints.reach', [$lecture, $checkpoint]) }}"
                                >
                                    <span>
                                        <span class="block font-semibold">{{ $checkpoint->title }}</span>
                                        <span class="mt-1 block text-xs text-[var(--color-ink-500)]">
                                            {{ $checkpoint->position_seconds ? gmdate('H:i:s', $checkpoint->position_seconds) : 'بدون توقيت محدد' }}
                                        </span>
                                    </span>
                                    <span class="text-xs font-semibold">{{ $checkpoint->is_required ? 'مطلوبة' : 'اختيارية' }}</span>
                                </button>
                            @endforeach
                        </div>
                    </section>
                @endif

                <section class="panel-tight">
                    <p class="text-sm font-semibold text-[var(--color-brand-700)]">تنقّل سريع</p>
                    <div class="mt-4 flex flex-col gap-3">
                        <a href="{{ route('student.lectures.index', ['tab' => $lecture->type->value === 'review' ? 'review' : 'lecture']) }}" class="btn-secondary">العودة إلى الكتالوج</a>
                        <a href="{{ route('student.mistakes.index') }}" class="btn-secondary">راجع مركز الأخطاء</a>
                    </div>
                </section>
            </aside>
        </section>
    @endif

    @if ($canConsume)
        @push('scripts')
            <script>
                (() => {
                    const root = document.querySelector('[data-lecture-progress]');

                    if (!root) {
                        return;
                    }

                    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    const percentElement = root.querySelector('[data-progress-percent]');
                    const fillElement = root.querySelector('[data-progress-fill]');
                    const positionElement = root.querySelector('[data-progress-position]');
                    const consumedElement = root.querySelector('[data-progress-consumed]');
                    const messageElement = root.querySelector('[data-progress-message]');
                    const duration = Number(root.dataset.durationSeconds || 0) || null;
                    const updateUrl = root.dataset.updateUrl;
                    const touchUrl = root.dataset.touchUrl;
                    const completeUrl = root.dataset.completeUrl;
                    let baseConsumed = Number(root.dataset.initialConsumed || 0);
                    let basePosition = Number(root.dataset.initialPosition || 0);
                    let elapsed = 0;
                    let percent = Number(root.dataset.initialProgress || 0);
                    let completed = root.dataset.completed === '1';
                    let lastCheckpointOrder = Number(root.dataset.initialCheckpointOrder || 0);
                    let saveInFlight = false;

                    const formatTime = (seconds) => {
                        const value = Math.max(0, Number(seconds || 0));
                        const hrs = String(Math.floor(value / 3600)).padStart(2, '0');
                        const mins = String(Math.floor((value % 3600) / 60)).padStart(2, '0');
                        const secs = String(Math.floor(value % 60)).padStart(2, '0');

                        return `${hrs}:${mins}:${secs}`;
                    };

                    const computeConsumed = () => baseConsumed + elapsed;
                    const computePosition = () => duration ? Math.min(duration, basePosition + elapsed) : basePosition + elapsed;

                    const render = () => {
                        const consumed = computeConsumed();
                        const position = computePosition();
                        const derivedPercent = duration ? Math.min(100, Math.round((consumed / duration) * 100)) : percent;
                        const displayPercent = Math.max(percent, derivedPercent);

                        percentElement.textContent = `${displayPercent}%`;
                        fillElement.style.width = `${displayPercent}%`;
                        positionElement.textContent = formatTime(position);
                        consumedElement.textContent = formatTime(consumed);
                    };

                    const applyProgress = (progress) => {
                        baseConsumed = Number(progress.consumed_seconds || 0);
                        basePosition = Number(progress.last_position_seconds || 0);
                        percent = Math.round(Number(progress.completion_percent || 0));
                        completed = Boolean(progress.completed_at);
                        lastCheckpointOrder = Number(progress.last_checkpoint_sort_order || lastCheckpointOrder || 0);
                        elapsed = 0;

                        document.querySelectorAll('[data-checkpoint-button]').forEach((button) => {
                            const order = Number(button.dataset.checkpointOrder || 0);
                            button.classList.toggle('lecture-checkpoint-item--active', order <= lastCheckpointOrder);
                        });

                        render();
                    };

                    const post = async (url, payload = {}) => {
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrf,
                            },
                            body: JSON.stringify(payload),
                        });

                        if (!response.ok) {
                            throw new Error('save_failed');
                        }

                        return response.json();
                    };

                    const saveProgress = async ({ silent = false } = {}) => {
                        if (saveInFlight || completed) {
                            return;
                        }

                        saveInFlight = true;

                        try {
                            const result = await post(updateUrl, {
                                position_seconds: computePosition(),
                                consumed_seconds: computeConsumed(),
                                completion_percent: percent,
                            });

                            applyProgress(result.progress);

                            if (!silent && messageElement) {
                                messageElement.textContent = result.message;
                            }
                        } catch (error) {
                            if (messageElement) {
                                messageElement.textContent = 'تعذر حفظ التقدم الآن، حاول مرة أخرى خلال لحظات.';
                            }
                        } finally {
                            saveInFlight = false;
                        }
                    };

                    post(touchUrl).catch(() => {});
                    render();

                    const tick = window.setInterval(() => {
                        if (document.hidden || completed) {
                            return;
                        }

                        elapsed += 1;
                        render();

                        if (elapsed > 0 && elapsed % 30 === 0) {
                            saveProgress({ silent: true });
                        }
                    }, 1000);

                    root.querySelector('[data-save-progress]')?.addEventListener('click', () => saveProgress());

                    root.querySelector('[data-complete-progress]')?.addEventListener('click', async () => {
                        if (completed || saveInFlight) {
                            return;
                        }

                        saveInFlight = true;

                        try {
                            const result = await post(completeUrl);
                            applyProgress(result.progress);

                            if (messageElement) {
                                messageElement.textContent = result.message;
                            }
                        } catch (error) {
                            if (messageElement) {
                                messageElement.textContent = 'تعذر تعليم المحاضرة كمكتملة الآن.';
                            }
                        } finally {
                            saveInFlight = false;
                        }
                    });

                    document.querySelectorAll('[data-checkpoint-button]').forEach((button) => {
                        button.addEventListener('click', async () => {
                            try {
                                const result = await post(button.dataset.checkpointUrl);
                                applyProgress(result.progress);

                                if (messageElement) {
                                    messageElement.textContent = result.message;
                                }
                            } catch (error) {
                                if (messageElement) {
                                    messageElement.textContent = 'تعذر حفظ النقطة المرحلية.';
                                }
                            }
                        });
                    });

                    document.addEventListener('visibilitychange', () => {
                        if (document.hidden) {
                            saveProgress({ silent: true });
                        }
                    });

                    window.addEventListener('beforeunload', () => {
                        if (elapsed > 0 && !completed) {
                            saveProgress({ silent: true });
                        }

                        window.clearInterval(tick);
                    });
                })();
            </script>
        @endpush
    @endif
</x-layouts.student>
