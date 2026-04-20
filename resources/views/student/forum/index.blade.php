<x-layouts.student title="ملتقى الأسئلة" heading="ملتقى الأسئلة" subheading="حائط أسئلة ومتابعة بين الطلاب والإدارة مع صور ومرفقات صوتية وردود متسلسلة.">
    <section class="space-y-6">
        <section class="panel-tight">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm font-semibold text-[var(--color-brand-700)]">جدار الأسئلة</p>
                    <h2 class="mt-2 text-2xl font-bold lg:text-3xl">تابع كل الأسئلة أو ارجع فقط إلى أسئلتك.</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-8 text-[var(--color-ink-700)]">
                        يعرض هذا القسم الأسئلة العامة، الصور المرفقة، الردود الإدارية، والردود الصوتية في صياغة أقرب إلى جدار دعم أكاديمي حي.
                    </p>
                </div>

                <nav class="flex flex-wrap gap-2">
                    <a href="{{ route('student.forum.index') }}" @class(['btn-primary' => $mode === 'all', 'btn-secondary' => $mode !== 'all'])>الكل</a>
                    <a href="{{ route('student.forum.mine') }}" @class(['btn-primary' => $mode === 'mine', 'btn-secondary' => $mode !== 'mine'])>أسئلتي</a>
                </nav>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <x-student.summary-card label="إجمالي المواضيع" :value="$threads->total()" description="عدد الموضوعات المطابقة للفلاتر الحالية" />
                <x-student.summary-card label="الوضع الحالي" :value="$mode === 'mine' ? 'أسئلتي' : 'الكل'" description="التبويب النشط في الجدار" />
                <x-student.summary-card label="عدد الصفحة" :value="$threads->count()" description="عدد الموضوعات المعروضة الآن" />
                <x-student.summary-card label="الدعم" value="متاح" description="الردود الإدارية تظهر داخل نفس التسلسل" />
            </div>

            <form method="GET" class="mt-6 grid gap-3 lg:grid-cols-[1fr_auto]">
                @if ($mode === 'mine')
                    <input type="hidden" name="mine" value="1">
                @endif
                <input type="search" name="search" value="{{ request('search') }}" class="form-input" placeholder="ابحث في العنوان أو نص السؤال">
                <button class="btn-secondary">بحث</button>
            </form>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.25fr_0.75fr]">
            <div class="space-y-4">
                @forelse ($threads as $thread)
                    @php($firstMessage = $thread->firstMessage)
                    @php($staffReply = $thread->latestStaffReply)

                    <article class="panel-tight">
                        <div class="flex flex-wrap items-center gap-3">
                            <x-admin.status-badge :label="$thread->status->label()" :tone="$thread->status->tone()" />
                            <span class="text-xs text-[var(--color-ink-500)]">{{ $thread->student?->name }}</span>
                            <span class="text-xs text-[var(--color-ink-500)]">{{ optional($thread->last_activity_at)->diffForHumans() }}</span>
                        </div>

                        <div class="mt-4 flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <h2 class="text-xl font-bold">{{ $thread->title }}</h2>
                                <p class="mt-3 text-sm leading-8 text-[var(--color-ink-700)]">{{ \Illuminate\Support\Str::limit($firstMessage?->body, 220) }}</p>
                            </div>
                            <div class="shrink-0 text-left">
                                <p class="text-xs text-[var(--color-ink-500)]">الردود</p>
                                <p class="mt-2 text-2xl font-bold">{{ $thread->messages_count }}</p>
                            </div>
                        </div>

                        @if ($firstMessage?->attachments?->isNotEmpty())
                            <div class="forum-attachments mt-5">
                                @foreach ($firstMessage->attachments as $attachment)
                                    @php($attachmentUrl = asset('storage/'.$attachment->path))

                                    @if ($attachment->type->value === 'image')
                                        <a href="{{ $attachmentUrl }}" target="_blank" rel="noreferrer" class="forum-attachment forum-attachment--image">
                                            <img src="{{ $attachmentUrl }}" alt="{{ $attachment->original_name }}">
                                        </a>
                                    @else
                                        <div class="forum-attachment forum-attachment--audio">
                                            <p class="text-xs font-semibold text-[var(--color-brand-700)]">رد أو سؤال صوتي</p>
                                            <audio controls class="mt-3 w-full">
                                                <source src="{{ $attachmentUrl }}" type="{{ $attachment->mime_type }}">
                                            </audio>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif

                        @if ($staffReply)
                            <div class="mt-5 rounded-[1.8rem] bg-[color-mix(in_oklch,var(--color-success)_10%,white)] p-4">
                                <div class="flex flex-wrap items-center gap-2">
                                    <x-admin.status-badge label="آخر رد إداري" tone="success" />
                                    <span class="text-xs text-[var(--color-ink-500)]">{{ $staffReply->author?->name ?? 'الإدارة' }}</span>
                                    <span class="text-xs text-[var(--color-ink-500)]">{{ $staffReply->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="mt-3 text-sm leading-8 text-[var(--color-ink-700)]">{{ \Illuminate\Support\Str::limit($staffReply->body, 180) }}</p>
                            </div>
                        @endif

                        <div class="mt-5 flex flex-wrap items-center gap-3 text-xs text-[var(--color-ink-500)]">
                            <span>{{ $thread->messages_count }} رسالة</span>
                            <span>{{ $thread->staff_replies_count }} رد إداري</span>
                            <span>{{ $firstMessage?->attachments?->count() ?? 0 }} مرفق</span>
                        </div>

                        <div class="mt-5">
                            <a href="{{ route('student.forum.show', $thread) }}" class="btn-primary">فتح الموضوع</a>
                        </div>
                    </article>
                @empty
                    <x-student.empty-state title="لا توجد موضوعات بعد" description="ابدأ أول سؤال، أو جرّب التبديل بين الكل وأسئلتي." />
                @endforelse

                <div class="px-2">
                    {{ $threads->links() }}
                </div>
            </div>

            <aside class="panel-tight">
                <p class="text-sm font-semibold text-[var(--color-brand-700)]">إضافة سؤال جديد</p>
                <form method="POST" action="{{ route('student.forum.store') }}" enctype="multipart/form-data" class="mt-5 space-y-4">
                    @csrf

                    <div>
                        <label class="field-label" for="title">عنوان السؤال</label>
                        <input id="title" name="title" value="{{ old('title') }}" class="form-input" required>
                    </div>

                    <div>
                        <label class="field-label" for="body">نص السؤال</label>
                        <textarea id="body" name="body" class="form-textarea" required>{{ old('body') }}</textarea>
                    </div>

                    <div>
                        <label class="field-label" for="attachments">صور أو ملفات صوتية</label>
                        <input id="attachments" type="file" name="attachments[]" multiple class="form-input">
                        <p class="field-help">حتى 3 ملفات. الأنواع المدعومة: صور وملفات صوتية.</p>
                    </div>

                    <button class="btn-primary">نشر السؤال</button>
                </form>
            </aside>
        </section>
    </section>
</x-layouts.student>
