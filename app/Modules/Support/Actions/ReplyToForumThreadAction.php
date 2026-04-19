<?php

namespace App\Modules\Support\Actions;

use App\Modules\Identity\Models\Admin;
use App\Modules\Students\Models\Student;
use App\Modules\Support\Enums\ForumAttachmentType;
use App\Modules\Support\Enums\ForumThreadStatus;
use App\Modules\Support\Models\ForumThread;
use App\Shared\Contracts\AuditLogger;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ReplyToForumThreadAction
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function execute(ForumThread $thread, Student|Admin $author, array $data): void
    {
        DB::transaction(function () use ($thread, $author, $data): void {
            $message = $thread->messages()->create([
                'author_type' => $author->getMorphClass(),
                'author_id' => $author->getKey(),
                'body' => $data['body'],
                'is_staff_reply' => $author instanceof Admin,
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

            $thread->update([
                'last_activity_at' => now(),
                'status' => $author instanceof Admin ? ForumThreadStatus::Answered : ForumThreadStatus::Open,
                'answered_at' => $author instanceof Admin ? now() : $thread->answered_at,
            ]);

            $this->auditLogger->log(
                event: $author instanceof Admin ? 'support.forum-thread.staff-replied' : 'support.forum-thread.student-replied',
                actor: $author,
                subject: $thread,
                newValues: $thread->fresh('messages.attachments')->toArray(),
            );
        });
    }
}
