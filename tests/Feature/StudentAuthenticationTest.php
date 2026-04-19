<?php

namespace Tests\Feature;

use App\Modules\Students\Models\Student;
use App\Modules\Students\Notifications\StudentResetPasswordNotification;
use App\Shared\Enums\StudentStatus;
use Database\Seeders\AcademicSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class StudentAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_register_and_is_created_as_pending(): void
    {
        $this->seed(AcademicSeeder::class);

        $grade = \App\Modules\Academic\Models\Grade::query()->first();
        $track = \App\Modules\Academic\Models\Track::query()->where('grade_id', $grade->id)->first();

        $response = $this->post(route('student.register.store'), [
            'name' => 'طالب جديد',
            'email' => 'new.student@example.edu',
            'phone' => '01012345699',
            'parent_phone' => '01012345698',
            'governorate' => 'القاهرة',
            'grade_id' => $grade->id,
            'track_id' => $track->id,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $student = Student::query()->firstWhere('email', 'new.student@example.edu');

        $response->assertRedirect(route('student.dashboard'));
        $this->assertAuthenticatedAs($student, 'student');
        $this->assertDatabaseHas('students', [
            'email' => 'new.student@example.edu',
            'status' => StudentStatus::Pending->value,
        ]);
        $this->assertDatabaseHas('student_status_histories', [
            'student_id' => $student->id,
            'new_status' => StudentStatus::Pending->value,
        ]);
    }

    public function test_pending_student_can_login_and_blocked_student_is_denied_portal_access(): void
    {
        $pendingStudent = Student::factory()->create([
            'email' => 'pending.login@example.edu',
            'password' => 'password',
            'status' => StudentStatus::Pending,
        ]);

        $this->post(route('student.login.store'), [
            'email' => $pendingStudent->email,
            'password' => 'password',
        ])->assertRedirect(route('student.dashboard'));

        $this->assertAuthenticatedAs($pendingStudent, 'student');

        auth('student')->logout();

        $blockedStudent = Student::factory()->create([
            'status' => StudentStatus::Blocked,
        ]);

        $this->actingAs($blockedStudent, 'student');

        $response = $this->get(route('student.dashboard'));

        $response->assertRedirect(route('student.login'));
        $this->assertGuest('student');
    }

    public function test_student_can_request_and_complete_password_reset(): void
    {
        Notification::fake();

        $student = Student::factory()->create([
            'email' => 'reset.student@example.edu',
        ]);

        $this->post(route('student.password.email'), [
            'email' => $student->email,
        ])->assertSessionHas('status');

        Notification::assertSentTo($student, StudentResetPasswordNotification::class);

        $token = Password::broker('students')->createToken($student);

        $this->post(route('student.password.store'), [
            'token' => $token,
            'email' => $student->email,
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
        ])->assertRedirect(route('student.login'));

        $student->refresh();

        $this->assertTrue(Hash::check('new-password123', $student->password));
    }
}
