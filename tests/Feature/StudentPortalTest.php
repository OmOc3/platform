<?php

namespace Tests\Feature;

use App\Modules\Students\Models\Student;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\InteractsWithStudentAuth;
use Tests\TestCase;

class StudentPortalTest extends TestCase
{
    use InteractsWithStudentAuth;
    use RefreshDatabase;

    public function test_student_can_view_portal_pages_and_submit_complaint(): void
    {
        $this->seed(DatabaseSeeder::class);

        $student = Student::query()->firstWhere('email', 'student@example.edu');
        $this->actingAs($student, 'student');

        $this->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('أحدث محتوى متاح لك');

        $this->get(route('student.payments.index'))
            ->assertOk()
            ->assertSee('باقة الفيزياء الشهرية');

        $this->get(route('student.book-orders.index'))
            ->assertOk()
            ->assertSee('book-order-demo-100002');

        $this->get(route('student.attendance.index'))
            ->assertOk()
            ->assertSee('محاضرة قوانين نيوتن');

        $this->post(route('student.complaints.store'), [
            'type' => 'suggestion',
            'content' => 'أقترح إضافة ملخص أسبوعي داخل لوحة الطالب.',
        ])->assertRedirect(route('student.complaints.index'));

        $this->assertDatabaseHas('complaints', [
            'student_id' => $student->id,
            'type' => 'suggestion',
        ]);
    }

    public function test_student_can_update_own_profile_without_affecting_other_students(): void
    {
        $student = $this->signInStudent([
            'email' => 'profile.owner@example.edu',
            'phone' => '01011111111',
        ]);

        $other = Student::factory()->create([
            'email' => 'other.student@example.edu',
            'phone' => '01022222222',
        ]);

        $this->put(route('student.profile.update'), [
            'name' => 'الاسم المحدث',
            'email' => 'profile.owner@example.edu',
            'phone' => '01011111111',
            'parent_phone' => '01033333333',
            'governorate' => 'الجيزة',
        ])->assertRedirect(route('student.profile.show'));

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'name' => 'الاسم المحدث',
            'governorate' => 'الجيزة',
        ]);

        $this->assertDatabaseHas('students', [
            'id' => $other->id,
            'email' => 'other.student@example.edu',
            'phone' => '01022222222',
        ]);
    }
}
