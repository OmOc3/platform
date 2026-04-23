<?php

namespace App\Shared\Services;

use App\Modules\Centers\Models\AttendanceRecord;
use App\Modules\Centers\Models\AttendanceSession;
use App\Shared\Contracts\AttendanceRecorder;
use App\Shared\Contracts\AuditLogger;
use App\Shared\Enums\AttendanceStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DatabaseAttendanceRecorder implements AttendanceRecorder
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function record(array $payload): void
    {
        /** @var AttendanceSession|null $session */
        $session = $payload['session'] ?? null;
        $rows = collect($payload['records'] ?? []);
        $actor = $payload['actor'] ?? null;

        if (! $session instanceof AttendanceSession) {
            throw ValidationException::withMessages([
                'session' => ['جلسة الحضور غير صالحة.'],
            ]);
        }

        if ($rows->isEmpty()) {
            throw ValidationException::withMessages([
                'records' => ['أضف سجل حضور واحدًا على الأقل قبل الحفظ.'],
            ]);
        }

        DB::transaction(function () use ($session, $rows, $actor): void {
            /** @var AttendanceSession $lockedSession */
            $lockedSession = AttendanceSession::query()
                ->with(['group.students:id,name,student_number,group_id', 'records'])
                ->lockForUpdate()
                ->findOrFail($session->id);

            $group = $lockedSession->group;
            $allowedStudentIds = $group?->students->pluck('id') ?? collect();

            if ($group === null || $allowedStudentIds->isEmpty()) {
                throw ValidationException::withMessages([
                    'records' => ['لا توجد مجموعة أو طلاب مرتبطون بهذه الجلسة حتى الآن.'],
                ]);
            }

            $existingRecords = $lockedSession->records->keyBy('student_id');
            $normalizedRows = $rows
                ->keyBy(fn (array $row): int => (int) ($row['student_id'] ?? 0))
                ->values();

            foreach ($normalizedRows as $row) {
                $studentId = (int) ($row['student_id'] ?? 0);

                if (! $allowedStudentIds->contains($studentId)) {
                    throw ValidationException::withMessages([
                        'records' => ['يوجد طالب غير مرتبط بمجموعة الجلسة الحالية.'],
                    ]);
                }

                $attendanceStatus = $row['attendance_status'] instanceof AttendanceStatus
                    ? $row['attendance_status']
                    : AttendanceStatus::from((string) $row['attendance_status']);

                $score = $this->normalizeDecimal($row['score'] ?? null);
                $maxScore = $this->normalizeDecimal($row['max_score'] ?? null);

                if (($score === null) xor ($maxScore === null)) {
                    throw ValidationException::withMessages([
                        'records' => ['يجب إدخال الدرجة والنهاية العظمى معًا أو تركهما فارغين.'],
                    ]);
                }

                if ($score !== null && $maxScore !== null && $score > $maxScore) {
                    throw ValidationException::withMessages([
                        'records' => ['لا يمكن أن تتجاوز الدرجة النهاية العظمى.'],
                    ]);
                }

                $record = $existingRecords->get($studentId) ?? new AttendanceRecord([
                    'attendance_session_id' => $lockedSession->id,
                    'student_id' => $studentId,
                ]);

                $oldValues = $record->exists ? $record->toArray() : [];
                $examStatusLabel = $lockedSession->session_type === 'exam'
                    ? $this->resolveExamStatusLabel($attendanceStatus, (string) ($row['exam_status_label'] ?? ''))
                    : null;

                $record->fill([
                    'attendance_status' => $attendanceStatus,
                    'exam_status_label' => $examStatusLabel,
                    'score' => $lockedSession->session_type === 'exam' ? $score : null,
                    'max_score' => $lockedSession->session_type === 'exam' ? $maxScore : null,
                    'notes' => filled($row['notes'] ?? null) ? trim((string) $row['notes']) : null,
                    'recorded_at' => now(),
                ]);

                if (! $record->exists || $record->isDirty()) {
                    $record->save();

                    $this->auditLogger->log(
                        event: 'centers.attendance.recorded',
                        actor: $actor,
                        subject: $lockedSession,
                        oldValues: $oldValues,
                        newValues: $record->fresh()->toArray(),
                        meta: [
                            'attendance_session_id' => $lockedSession->id,
                            'student_id' => $studentId,
                        ],
                    );
                }
            }
        });
    }

    private function normalizeDecimal(mixed $value): ?float
    {
        if (! filled($value)) {
            return null;
        }

        return round((float) $value, 2);
    }

    private function resolveExamStatusLabel(AttendanceStatus $attendanceStatus, string $customLabel): string
    {
        $label = trim($customLabel);

        if ($label !== '') {
            return $label;
        }

        return match ($attendanceStatus) {
            AttendanceStatus::Absent => 'لم يختبر',
            AttendanceStatus::Excused => 'بعذر',
            default => 'تم الاختبار',
        };
    }
}
