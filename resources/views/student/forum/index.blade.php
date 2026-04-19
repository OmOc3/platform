<x-layouts.student title="ملتقى الأسئلة" heading="ملتقى الأسئلة" subheading="اسأل، تابع الردود، واحتفظ بكل المرفقات في مسار منظم يشبه المنتدى الدراسي أكثر من كونه نموذج تواصل عابر.">
    <section class="space-y-6">
        <section class="panel-tight">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="text-sm font-semibold text-[var(--color-brand-700)]">الموضوعات</p>
                    <p class="mt-2 text-sm leading-8 text-[var(--color-ink-700)]">اعرض كل الأسئلة العامة أو ارجع فقط إلى الأسئلة التي أنشأتها بنفسك.</p>
                </div>
                <nav class="flex flex-wrap gap-2">
                    <a href="{{ route('student.forum.index') }}" @class(['btn-primary' => $mode === 'all', 'btn-secondary' => $mode !== 'all'])>كل الأسئلة</a>
                    <a href="{{ route('student.forum.mine') }}" @class(['btn-primary' => $mode === 'mine', 'btn-secondary' => $mode !== 'mine'])>أسئلتي</a>
                </nav>
            </div>

            <form method="GET" class="mt-5 grid gap-3 lg:grid-cols-[1fr_auto]">
                <input type="search" name="search" value="{{ request('search') }}" class="form-input" placeholder="ابحث في العناوين أو نص السؤال">
                <button class="btn-secondary">بحث</button>
            </form>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
            <div class="space-y-4">
                @forelse ($threads as $thread)
                    <article class="panel-tight">
                        <div class="flex flex-wrap items-center gap-3">
                            <x-admin.status-badge :label="$thread->status->value" :tone="$thread->status->value === 'answered' ? 'success' : ($thread->status->value === 'closed' ? 'warning' : 'neutral')" />
                            <span class="text-xs text-[var(--color-ink-500)]">{{ $thread->student?->name }}</span>
                            <span class="text-xs text-[var(--color-ink-500)]">{{ optional($thread->last_activity_at)->diffForHumans() }}</span>
                        </div>

                        <h2 class="mt-4 text-lg font-bold">{{ $thread->title }}</h2>
                        <p class="mt-3 text-sm leading-8 text-[var(--color-ink-700)]">{{ \Illuminate\Support\Str::limit($thread->firstMessage?->body, 180) }}</p>

                        <div class="mt-5 flex flex-wrap items-center gap-3 text-xs text-[var(--color-ink-500)]">
                            <span>{{ $thread->messages->count() }} رسالة</span>
                            <span>{{ $thread->firstMessage?->attachments?->count() ?? 0 }} مرفق</span>
                        </div>

                        <div class="mt-5">
                            <a href="{{ route('student.forum.show', $thread) }}" class="btn-primary">فتح الموضوع</a>
                        </div>
                    </article>
                @empty
                    <x-student.empty-state title="لا توجد موضوعات بعد" description="ابدأ أول سؤال، أو جرّب التبديل بين كل الأسئلة وأسئلتي." />
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
                        <label class="field-label" for="attachments">مرفقات صوتية أو صور</label>
                        <input id="attachments" type="file" name="attachments[]" multiple class="form-input">
                        <p class="field-help">حتى 3 ملفات. الامتدادات المدعومة: صور وصوتيات.</p>
                    </div>
                    <button class="btn-primary">نشر السؤال</button>
                </form>
            </aside>
        </section>
    </section>
</x-layouts.student>
