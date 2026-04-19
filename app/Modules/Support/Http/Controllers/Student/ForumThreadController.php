<?php

namespace App\Modules\Support\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Modules\Support\Actions\CreateForumThreadAction;
use App\Modules\Support\Actions\ReplyToForumThreadAction;
use App\Modules\Support\Http\Requests\Student\StoreForumReplyRequest;
use App\Modules\Support\Http\Requests\Student\StoreForumThreadRequest;
use App\Modules\Support\Models\ForumThread;
use App\Modules\Support\Queries\ForumThreadsQuery;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ForumThreadController extends Controller
{
    public function __construct(
        private readonly ForumThreadsQuery $forumThreadsQuery,
        private readonly CreateForumThreadAction $createForumThreadAction,
        private readonly ReplyToForumThreadAction $replyToForumThreadAction,
    ) {
    }

    public function index(Request $request): View
    {
        $student = auth('student')->user();

        return view('student.forum.index', [
            'threads' => $this->forumThreadsQuery->paginate($request, $student),
            'mode' => 'all',
        ]);
    }

    public function mine(Request $request): View
    {
        $student = auth('student')->user();

        return view('student.forum.index', [
            'threads' => $this->forumThreadsQuery->paginate($request, $student, true),
            'mode' => 'mine',
        ]);
    }

    public function show(ForumThread $forumThread): View
    {
        $this->authorize('view', $forumThread);

        return view('student.forum.show', [
            'thread' => $forumThread->load(['student', 'messages.author', 'messages.attachments']),
        ]);
    }

    public function store(StoreForumThreadRequest $request): RedirectResponse
    {
        $this->authorize('create', ForumThread::class);

        $thread = $this->createForumThreadAction->execute(auth('student')->user(), $request->validated());

        return redirect()
            ->route('student.forum.show', $thread)
            ->with('status', 'تم نشر سؤالك.');
    }

    public function reply(StoreForumReplyRequest $request, ForumThread $forumThread): RedirectResponse
    {
        $this->authorize('reply', $forumThread);

        $this->replyToForumThreadAction->execute($forumThread, auth('student')->user(), $request->validated());

        return redirect()
            ->route('student.forum.show', $forumThread)
            ->with('status', 'تمت إضافة الرد.');
    }
}
