<?php

namespace Tests\Feature\Admin;

use App\Modules\Centers\Models\AttendanceRecord;
use App\Modules\Centers\Models\AttendanceSession;
use App\Modules\Students\Models\Student;
use App\Shared\Enums\AttendanceStatus;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\InteractsWithAdminAuth;
use Tests\TestCase;

class AttendanceRecordingTest extends TestCase
{
    use InteractsWithAdminAuth;
    use RefreshDatabase;

    public function test_admin_can_open_attendance_session_and_update_exam_roster_idempotently(): void
    {
        $this->seed(DatabaseSeeder::class);

        $attendanceSession = AttendanceSession::query()->where('session_type', 'exam')->firstOrFail();
        $student = Student::query()->where('group_id', $attendanceSession->group_id)->firstOrFail();

        $this->signInAdmin(['attendance.view', 'attendance.manage']);

        $this->get(route('admin.attendance.show', $attendanceSession))
            ->assertOk()
            ->assertSeeText($attendanceSession->title)
            ->assertSeeText($student->name);

        $this->put(route('admin.attendance.update', $attendanceSession), [
            'records' => [[
                'student_id' => $student->id,
                'attendance_status' => AttendanceStatus::Present->value,
                'exam_status_label' => 'تم الاختبار',
                'score' => 18,
                'max_score' => 20,
                'notes' => 'أداء جيد في الاختبار.',
            ]],
        ])->assertRedirect(route('admin.attendance.show', $attendanceSession));

        $this->put(route('admin.attendance.update', $attendanceSession), [
            'records' => [[
                'student_id' => $student->id,
                'attendance_status' => AttendanceStatus::Late->value,
                'exam_status_label' => 'إعادة تسجيل بعد المراجعة',
                'score' => 19,
                'max_score' => 20,
                'notes' => 'تم تحديث الدرجة بعد المراجعة.',
            ]],
        ])->assertRedirect(route('admin.attendance.show', $attendanceSession));

        $this->assertSame(1, AttendanceRecord::query()
            ->where('attendance_session_id', $attendanceSession->id)
            ->where('student_id', $student->id)
            ->count());

        $record = AttendanceRecord::query()
            ->where('attendance_session_id', $attendanceSession->id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        $this->assertSame(AttendanceStatus::Late, $record->attendance_status);
        $this->assertSame(19.0, (float) $record->score);
        $this->assertSame(20.0, (float) $record->max_score);
        $this->assertSame('تم تحديث الدرجة بعد المراجعة.', $record->notes);
        $this->assertNotNull($record->recorded_at);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'centers.attendance.recorded',
            'auditable_id' => $attendanceSession->id,
        ]);

        $this->actingAs($student, 'student');

        $this->get(route('student.attendance.index'))
            ->assertOk()
            ->assertSeeText($attendanceSession->title)
            ->assertSeeText('19/20');
    }

    public function test_attendance_update_rejects_student_outside_session_group(): void
    {
        $this->seed(DatabaseSeeder::class);

        $attendanceSession = AttendanceSession::query()->firstOrFail();
        $outsider = Student::factory()->create();

        $this->signInAdmin(['attendance.view', 'attendance.manage']);

        $this->from(route('admin.attendance.show', $attendanceSession))
            ->put(route('admin.attendance.update', $attendanceSession), [
                'records' => [[
                    'student_id' => $outsider->id,
                    'attendance_status' => AttendanceStatus::Present->value,
                ]],
            ])
            ->assertRedirect(route('admin.attendance.show', $attendanceSession))
            ->assertSessionHasErrors('records');

        $this->assertDatabaseMissing('attendance_records', [
            'attendance_session_id' => $attendanceSession->id,
            'student_id' => $outsider->id,
        ]);
    }

    public function test_attendance_show_and_update_routes_are_permission_protected(): void
    {
        $this->seed(DatabaseSeeder::class);

        $attendanceSession = AttendanceSession::query()->firstOrFail();
        $student = Student::query()->where('group_id', $attendanceSession->group_id)->firstOrFail();

        $this->signInAdmin(['dashboard.view']);

        $this->get(route('admin.attendance.show', $attendanceSession))->assertForbidden();

        $this->signInAdmin(['attendance.view']);

        $this->get(route('admin.attendance.show', $attendanceSession))
            ->assertOk()
            ->assertSeeText($attendanceSession->title);

        $this->put(route('admin.attendance.update', $attendanceSession), [
            'records' => [[
                'student_id' => $student->id,
                'attendance_status' => AttendanceStatus::Present->value,
            ]],
        ])->assertForbidden();
    }
}
