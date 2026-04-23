<?php

namespace Tests\Feature;

use App\Modules\Students\Models\Student;
use App\Modules\Support\Models\SupportTicket;
use App\Modules\Support\Models\SupportTicketType;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentSupportTicketsTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_create_and_reply_to_own_ticket(): void
    {
        $this->seed(DatabaseSeeder::class);

        $student = Student::query()->firstWhere('email', 'student@example.edu');
        $type = SupportTicketType::query()->firstOrFail();

        $this->actingAs($student, 'student');

        $this->get(route('student.tickets.index'))
            ->assertOk()
            ->assertSeeText('تذاكر الدعم');

        $this->post(route('student.tickets.store'), [
            'support_ticket_type_id' => $type->id,
            'subject' => 'أحتاج مراجعة تفعيل الحصة التجريبية',
            'body' => 'أواجه مشكلة في الوصول إلى الحصة التجريبية رغم ظهورها ضمن المحتوى المتاح في الصفحة الرئيسية.',
        ])->assertRedirect();

        $ticket = SupportTicket::query()->firstWhere('subject', 'أحتاج مراجعة تفعيل الحصة التجريبية');

        $this->assertNotNull($ticket);
        $this->assertDatabaseHas('support_tickets', [
            'id' => $ticket->id,
            'student_id' => $student->id,
            'support_ticket_type_id' => $type->id,
        ]);
        $this->assertDatabaseHas('support_ticket_replies', [
            'support_ticket_id' => $ticket->id,
            'author_id' => $student->id,
            'author_type' => $student->getMorphClass(),
            'is_staff_reply' => false,
        ]);

        $this->post(route('student.tickets.reply.store', $ticket), [
            'body' => 'تحديث إضافي: جرّبت من الهاتف أيضًا وما زالت المشكلة موجودة.',
        ])->assertRedirect(route('student.tickets.show', $ticket));

        $this->assertDatabaseHas('support_ticket_replies', [
            'support_ticket_id' => $ticket->id,
            'body' => 'تحديث إضافي: جرّبت من الهاتف أيضًا وما زالت المشكلة موجودة.',
            'author_id' => $student->id,
            'author_type' => $student->getMorphClass(),
        ]);
    }
}
