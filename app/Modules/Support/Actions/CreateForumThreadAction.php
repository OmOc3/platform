<?php

namespace App\Modules\Support\Actions;

use App\Modules\Students\Models\Student;
use App\Modules\Support\Enums\ForumAttachmentType;
use App\Modules\Support\Enums\ForumThreadStatus;
use App\Modules\Support\Enums\ForumVisibility;
use App\Modules\Support\Models\ForumThread;
use App\Shared\Contracts\AuditLogger;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CreateForumThreadAction
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function execute(Student $student, array $data): ForumThread
    {
        return DB::transaction(function () use ($student, $data): ForumThread {
            $thread = ForumThread::query()->create([
                'student_id' => $student->id,
                'title' => $data['title'],
                'status' => ForumThreadStatus::Open,
                'visibility' => ForumVisibility::Public,
                'last_activity_at' => now(),
            ]);

            $message = $thread->messages()->create([
                'author_type' => $student->getMorphClass(),
                'author_id' => $student->id,
                'body' => $data['body'],
                'is_staff_reply' => false,
            ]);

            foreach ($data['attachments'] ?? [] as $attachment) {
                if (! $attachment instanceof UploadedFile) {
                    continue;
                }

                $message->attachments()->create([
                    'type' => str_starts_with($attachment->getMimeType() ?? '', 'audio/') ? ForumAttachmentType::Audio : ForumAttachmentType::Image,
                    'disk' => 'public',
                    'path' => $attachment->store('forum', 'public'),
                    'original_name' => $attachment->getClientOriginalName(),
                    'mime_type' => $attachment->getMimeType() ?? 'application/octet-stream',
                    'size' => $attachment->getSize() ?: 0,
                ]);
            }

            $this->auditLogger->log(
                event: 'support.forum-thread.created',
                actor: $student,
                subject: $thread,
                newValues: $thread->fresh('messages.attachments')->toArray(),
            );

            return $thread->fresh(['student', 'firstMessage.attachments', 'latestMessage']);
        });
    }
}
