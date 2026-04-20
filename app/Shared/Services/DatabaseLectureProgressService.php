<?php

namespace App\Shared\Services;

use App\Modules\Academic\Models\Lecture;
use App\Modules\Academic\Models\LectureCheckpoint;
use App\Modules\Academic\Models\LectureProgress;
use App\Modules\Students\Models\Student;
use App\Shared\Contracts\AccessResolver;
use App\Shared\Contracts\LectureProgressService;
use App\Shared\Enums\ContentAccessState;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DatabaseLectureProgressService implements LectureProgressService
{
    public function __construct(private readonly AccessResolver $accessResolver)
    {
    }

    public function touchOpen(Student $student, Lecture $lecture): LectureProgress
    {
        $this->assertCanTrackProgress($student, $lecture);

        return DB::transaction(function () use ($student, $lecture): LectureProgress {
            $progress = $this->lockProgressRecord($student, $lecture);
            $now = now();

            $progress->fill([
                'started_at' => $progress->started_at ?? $now,
                'first_opened_at' => $progress->first_opened_at ?? $now,
                'last_opened_at' => $now,
            ]);
            $progress->save();

            return $progress->fresh(['lastCheckpoint']);
        });
    }

    public function updateProgress(Student $student, Lecture $lecture, array $payload): LectureProgress
    {
        $this->assertCanTrackProgress($student, $lecture);

        return DB::transaction(function () use ($student, $lecture, $payload): LectureProgress {
            $progress = $this->lockProgressRecord($student, $lecture);
            $durationSeconds = $this->durationSeconds($lecture);
            $threshold = $this->completionThreshold($lecture);

            $positionSeconds = $this->clampSeconds(
                $payload['position_seconds'] ?? $progress->last_position_seconds ?? 0,
                $durationSeconds,
            );
            $consumedSeconds = max(
                (int) $progress->consumed_seconds,
                $this->clampSeconds($payload['consumed_seconds'] ?? $progress->consumed_seconds ?? 0, $durationSeconds),
            );

            $derivedPercent = $this->deriveCompletionPercent($lecture, $progress, $consumedSeconds);
            $manualPercent = (float) ($payload['completion_percent'] ?? 0);

            if ($durationSeconds === null && $lecture->checkpoints->isEmpty()) {
                $derivedPercent = max($derivedPercent, $this->clampPercent($manualPercent));
            }

            $completionPercent = max(
                (float) $progress->completion_percent,
                $this->clampPercent($derivedPercent),
            );

            $now = now();

            $progress->fill([
                'started_at' => $progress->started_at ?? $now,
                'first_opened_at' => $progress->first_opened_at ?? $now,
                'last_opened_at' => $now,
                'last_position_seconds' => $positionSeconds,
                'consumed_seconds' => $consumedSeconds,
                'completion_percent' => $completionPercent,
                'completed_at' => $progress->completed_at ?? ($completionPercent >= $threshold ? $now : null),
                'meta' => array_merge($progress->meta ?? [], [
                    'last_progress_sync_at' => $now->toIso8601String(),
                ]),
            ]);
            $progress->save();

            return $progress->fresh(['lastCheckpoint']);
        });
    }

    public function reachCheckpoint(Student $student, Lecture $lecture, LectureCheckpoint $checkpoint): LectureProgress
    {
        $this->assertCanTrackProgress($student, $lecture);

        if ((int) $checkpoint->lecture_id !== (int) $lecture->id) {
            throw ValidationException::withMessages([
                'checkpoint' => ['هذه النقطة المرحلية غير مرتبطة بهذه المحاضرة.'],
            ]);
        }

        return DB::transaction(function () use ($student, $lecture, $checkpoint): LectureProgress {
            $progress = $this->lockProgressRecord($student, $lecture);
            $now = now();

            $lecture->loadMissing('checkpoints');

            $currentCheckpoint = $progress->lastCheckpoint;
            $resolvedCheckpoint = $checkpoint;

            if ($currentCheckpoint instanceof LectureCheckpoint && $currentCheckpoint->sort_order > $checkpoint->sort_order) {
                $resolvedCheckpoint = $currentCheckpoint;
            }

            $durationSeconds = $this->durationSeconds($lecture);
            $positionSeconds = $resolvedCheckpoint->position_seconds ?? $progress->last_position_seconds ?? 0;
            $positionSeconds = max(
                (int) $progress->last_position_seconds,
                $this->clampSeconds($positionSeconds, $durationSeconds),
            );
            $consumedSeconds = max((int) $progress->consumed_seconds, $positionSeconds);
            $completionPercent = max(
                (float) $progress->completion_percent,
                $this->deriveCompletionPercent($lecture, $progress, $consumedSeconds, $resolvedCheckpoint),
            );

            $progress->fill([
                'started_at' => $progress->started_at ?? $now,
                'first_opened_at' => $progress->first_opened_at ?? $now,
                'last_opened_at' => $now,
                'last_position_seconds' => $positionSeconds,
                'consumed_seconds' => $consumedSeconds,
                'completion_percent' => $completionPercent,
                'last_checkpoint_id' => $resolvedCheckpoint->id,
                'completed_at' => $progress->completed_at ?? ($completionPercent >= $this->completionThreshold($lecture) ? $now : null),
                'meta' => array_merge($progress->meta ?? [], [
                    'last_checkpoint_reached_at' => $now->toIso8601String(),
                ]),
            ]);
            $progress->save();

            return $progress->fresh(['lastCheckpoint']);
        });
    }

    public function markCompleted(Student $student, Lecture $lecture): LectureProgress
    {
        $this->assertCanTrackProgress($student, $lecture);

        return DB::transaction(function () use ($student, $lecture): LectureProgress {
            $progress = $this->lockProgressRecord($student, $lecture);
            $now = now();
            $durationSeconds = $this->durationSeconds($lecture);

            $progress->fill([
                'started_at' => $progress->started_at ?? $now,
                'first_opened_at' => $progress->first_opened_at ?? $now,
                'last_opened_at' => $now,
                'last_position_seconds' => max((int) $progress->last_position_seconds, $durationSeconds ?? 0),
                'consumed_seconds' => max((int) $progress->consumed_seconds, $durationSeconds ?? (int) $progress->consumed_seconds),
                'completion_percent' => 100,
                'completed_at' => $progress->completed_at ?? $now,
                'meta' => array_merge($progress->meta ?? [], [
                    'completed_manually' => true,
                ]),
            ]);
            $progress->save();

            return $progress->fresh(['lastCheckpoint']);
        });
    }

    private function assertCanTrackProgress(Student $student, Lecture $lecture): void
    {
        if (! $lecture->is_active || ($lecture->published_at && $lecture->published_at->isFuture())) {
            throw ValidationException::withMessages([
                'lecture' => ['هذه المحاضرة غير متاحة الآن.'],
            ]);
        }

        $access = $this->accessResolver->resolveState($student, $lecture);

        if (! in_array($access['state'], [
            ContentAccessState::Open,
            ContentAccessState::Free,
            ContentAccessState::OwnedViaEntitlement,
        ], true)) {
            throw ValidationException::withMessages([
                'lecture' => ['لا تملك صلاحية متابعة هذه المحاضرة الآن.'],
            ]);
        }
    }

    private function lockProgressRecord(Student $student, Lecture $lecture): LectureProgress
    {
        $lecture->loadMissing('checkpoints');

        $progress = LectureProgress::query()->firstOrCreate(
            [
                'student_id' => $student->id,
                'lecture_id' => $lecture->id,
            ],
            [
                'started_at' => now(),
                'first_opened_at' => now(),
                'last_opened_at' => now(),
                'last_position_seconds' => 0,
                'consumed_seconds' => 0,
                'completion_percent' => 0,
                'completed_at' => null,
                'last_checkpoint_id' => null,
                'meta' => null,
            ],
        );

        return LectureProgress::query()
            ->whereKey($progress->id)
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function durationSeconds(Lecture $lecture): ?int
    {
        return $lecture->duration_minutes ? max(60, (int) $lecture->duration_minutes * 60) : null;
    }

    private function completionThreshold(Lecture $lecture): float
    {
        return max(1, min(100, (float) data_get($lecture->metadata, 'completion_threshold_percent', 90)));
    }

    private function clampSeconds(mixed $value, ?int $durationSeconds): int
    {
        $seconds = max(0, (int) $value);
        $upperBound = $durationSeconds ?? 43200;

        return min($seconds, $upperBound);
    }

    private function clampPercent(mixed $value): float
    {
        return (float) min(100, max(0, round((float) $value, 2)));
    }

    private function deriveCompletionPercent(
        Lecture $lecture,
        LectureProgress $progress,
        int $consumedSeconds,
        ?LectureCheckpoint $checkpoint = null,
    ): float {
        $lecture->loadMissing('checkpoints');

        $durationPercent = 0;
        $durationSeconds = $this->durationSeconds($lecture);

        if ($durationSeconds !== null && $durationSeconds > 0) {
            $durationPercent = ($consumedSeconds / $durationSeconds) * 100;
        }

        $checkpointPercent = 0;
        $resolvedCheckpoint = $checkpoint ?? $progress->lastCheckpoint;

        if ($resolvedCheckpoint instanceof LectureCheckpoint && $lecture->checkpoints->isNotEmpty()) {
            $orderedIds = $lecture->checkpoints->sortBy('sort_order')->values()->pluck('id')->all();
            $position = array_search($resolvedCheckpoint->id, $orderedIds, true);

            if ($position !== false) {
                $checkpointPercent = (($position + 1) / max(1, count($orderedIds))) * 100;
            }
        }

        return max($durationPercent, $checkpointPercent);
    }
}
