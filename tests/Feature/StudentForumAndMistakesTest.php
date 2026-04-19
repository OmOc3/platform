<?php

namespace Tests\Feature;

use App\Modules\Academic\Models\Lecture;
use App\Modules\Students\Models\MistakeItem;
use App\Modules\Students\Models\Student;
use App\Modules\Support\Models\ForumThread;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\Support\InteractsWithAdminAuth;
use Tests\TestCase;

class StudentForumAndMistakesTest extends TestCase
{
    use InteractsWithAdminAuth;
    use RefreshDatabase;

    public function test_student_can_create_threads_reply_and_filter_my_questions(): void
    {
        $this->seed(DatabaseSeeder::class);

        $student = Student::query()->firstWhere('email', 'student@example.edu');
        $otherThreadTitle = 'هل توجد مراجعة مجانية هذا الأسبوع؟';
        $ownThreadTitle = 'سؤال عن قوانين نيوتن';

        $this->actingAs($student, 'student');

        $this->get(route('student.forum.index'))
            ->assertOk()
            ->assertSeeText($ownThreadTitle)
            ->assertSeeText($otherThreadTitle);

        $this->get(route('student.forum.mine'))
            ->assertOk()
            ->assertSeeText($ownThreadTitle)
            ->assertDontSeeText($otherThreadTitle);

        $this->post(route('student.forum.store'), [
            'title' => 'سؤال جديد عن الحركة المتسارعة',
            'body' => 'أحتاج توضيحًا لخطوات الحل عندما تتغير السرعة والعجلة في نفس المسألة.',
        ])->assertRedirect();

        $thread = ForumThread::query()->firstWhere('title', 'سؤال جديد عن الحركة المتسارعة');

        $this->assertNotNull($thread);
        $this->assertDatabaseHas('forum_messages', [
            'forum_thread_id' => $thread->id,
            'author_id' => $student->id,
            'author_type' => $student->getMorphClass(),
            'is_staff_reply' => false,
        ]);

        $this->post(route('student.forum.reply.store', $thread), [
            'body' => 'هذا توضيح إضافي بعد مراجعة السؤال من جديد.',
        ])->assertRedirect(route('student.forum.show', $thread));

        $this->assertDatabaseHas('forum_messages', [
            'forum_thread_id' => $thread->id,
            'body' => 'هذا توضيح إضافي بعد مراجعة السؤال من جديد.',
            'author_id' => $student->id,
            'author_type' => $student->getMorphClass(),
        ]);
    }

    public function test_forum_rejects_invalid_attachment_types_and_admin_reply_is_permission_gated(): void
    {
        $this->seed(DatabaseSeeder::class);

        $student = Student::query()->firstWhere('email', 'student@example.edu');
        $thread = ForumThread::query()->firstOrFail();

        $this->actingAs($student, 'student');

        $this->post(route('student.forum.store'), [
            'title' => 'ملف غير مدعوم',
            'body' => 'هذا النص صالح لكن المرفق يجب أن يُرفض لأنه ليس صورة أو ملفًا صوتيًا.',
            'attachments' => [
                UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf'),
            ],
        ])->assertSessionHasErrors('attachments.0');

        auth('admin')->logout();
        auth('student')->logout();

        $admin = $this->signInAdmin(['forum.view']);
        $this->post(route('admin.forum-threads.reply', $thread), [
            'body' => 'رد إداري بلا صلاحية الرد.',
        ])->assertForbidden();

        auth('admin')->logout();

        $admin = $this->signInAdmin(['forum.view', 'forum.reply']);
        $this->post(route('admin.forum-threads.reply', $thread), [
            'body' => 'تمت مراجعة السؤال وسنضيف توضيحًا داخل الشرح القادم.',
        ])->assertRedirect(route('admin.forum-threads.show', $thread));

        $this->assertDatabaseHas('forum_messages', [
            'forum_thread_id' => $thread->id,
            'author_id' => $admin->id,
            'author_type' => $admin->getMorphClass(),
            'is_staff_reply' => true,
        ]);
    }

    public function test_student_can_view_grouped_mistakes_and_cannot_open_another_students_group(): void
    {
        $this->seed(DatabaseSeeder::class);

        $student = Student::query()->firstWhere('email', 'student@example.edu');
        $lecture = Lecture::query()->firstWhere('slug', 'newton-laws-core');
        $hiddenLecture = Lecture::query()->firstWhere('slug', 'accelerated-motion-problem-solving');
        $otherStudent = Student::factory()->create();

        MistakeItem::query()->create([
            'student_id' => $otherStudent->id,
            'lecture_id' => $hiddenLecture->id,
            'exam_id' => null,
            'question_reference' => 'OTHER-900',
            'question_text' => 'سؤال خاص بطالب آخر.',
            'correct_answer_snapshot' => 'إجابة صحيحة.',
            'model_answer_snapshot' => 'نموذج الإجابة.',
            'explanation' => 'تفصيل إضافي.',
            'image_path' => null,
            'score_lost' => 1,
            'score_meta' => ['max_score' => 5],
            'source' => 'manual',
            'meta' => null,
        ]);

        $this->actingAs($student, 'student');

        $this->get(route('student.mistakes.index'))
            ->assertOk()
            ->assertSeeText('قوانين نيوتن الأساسية');

        $this->get(route('student.mistakes.show', $lecture))
            ->assertOk()
            ->assertSeeText('جسم يتحرك بعجلة ثابتة');

        $this->get(route('student.mistakes.show', $hiddenLecture))
            ->assertNotFound();
    }
}
