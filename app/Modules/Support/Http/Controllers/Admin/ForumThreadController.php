<?php

namespace App\Modules\Support\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Support\Actions\ReplyToForumThreadAction;
use App\Modules\Support\Actions\UpdateForumThreadStatusAction;
use App\Modules\Support\Enums\ForumThreadStatus;
use App\Modules\Support\Enums\ForumVisibility;
use App\Modules\Support\Http\Requests\Admin\UpdateForumThreadRequest;
use App\Modules\Support\Http\Requests\Student\StoreForumReplyRequest;
use App\Modules\Support\Models\ForumThread;
use App\Modules\Support\Queries\AdminForumThreadsIndexQuery;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ForumThreadController extends Controller
{
    public function __construct(
        private readonly AdminForumThreadsIndexQuery $adminForumThreadsIndexQuery,
        private readonly UpdateForumThreadStatusAction $updateForumThreadStatusAction,
        private readonly ReplyToForumThreadAction $replyToForumThreadAction,
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', ForumThread::class);

        return view('admin.support.forum-threads.index', [
            'threads' => $this->adminForumThreadsIndexQuery->builder($request)->paginate(20)->withQueryString(),
            'statuses' => ForumThreadStatus::cases(),
            'visibilities' => ForumVisibility::cases(),
        ]);
    }

    public function show(ForumThread $forumThread): View
    {
        $this->authorize('view', $forumThread);

        return view('admin.support.forum-threads.show', [
            'thread' => $forumThread->load(['student', 'messages.author', 'messages.attachments']),
            'statuses' => ForumThreadStatus::cases(),
            'visibilities' => ForumVisibility::cases(),
        ]);
    }

    public function update(UpdateForumThreadRequest $request, ForumThread $forumThread): RedirectResponse
    {
        $this->authorize('update', $forumThread);

        $this->updateForumThreadStatusAction->execute($forumThread, $request->validated(), auth('admin')->user());

        return redirect()
            ->route('admin.forum-threads.show', $forumThread)
            ->with('status', 'تم تحديث حالة الموضوع.');
    }

    public function reply(StoreForumReplyRequest $request, ForumThread $forumThread): RedirectResponse
    {
        $this->authorize('reply', $forumThread);

        $this->replyToForumThreadAction->execute($forumThread, auth('admin')->user(), $request->validated());

        return redirect()
            ->route('admin.forum-threads.show', $forumThread)
            ->with('status', 'تم إرسال الرد الإداري.');
    }
}
