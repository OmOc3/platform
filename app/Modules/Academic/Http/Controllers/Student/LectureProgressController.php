<?php

namespace App\Modules\Academic\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Http\Requests\Student\Lectures\UpdateLectureProgressRequest;
use App\Modules\Academic\Models\Lecture;
use App\Modules\Academic\Models\LectureCheckpoint;
use App\Modules\Academic\Models\LectureProgress;
use App\Modules\Students\Models\Student;
use App\Shared\Contracts\LectureProgressService;
use Illuminate\Http\JsonResponse;

class LectureProgressController extends Controller
{
    public function __construct(private readonly LectureProgressService $lectureProgressService)
    {
    }

    public function touch(Lecture $lecture): JsonResponse
    {
        $progress = $this->lectureProgressService->touchOpen($this->student(), $lecture);

        return response()->json([
            'message' => 'تم تسجيل فتح المحاضرة.',
            'progress' => $this->payload($progress),
        ]);
    }

    public function update(UpdateLectureProgressRequest $request, Lecture $lecture): JsonResponse
    {
        $progress = $this->lectureProgressService->updateProgress(
            $this->student(),
            $lecture,
            $request->validated(),
        );

        return response()->json([
            'message' => 'تم تحديث التقدم.',
            'progress' => $this->payload($progress),
        ]);
    }

    public function complete(Lecture $lecture): JsonResponse
    {
        $progress = $this->lectureProgressService->markCompleted($this->student(), $lecture);

        return response()->json([
            'message' => 'تم تعليم المحاضرة كمكتملة.',
            'progress' => $this->payload($progress),
        ]);
    }

    public function reachCheckpoint(Lecture $lecture, LectureCheckpoint $lectureCheckpoint): JsonResponse
    {
        $progress = $this->lectureProgressService->reachCheckpoint($this->student(), $lecture, $lectureCheckpoint);

        return response()->json([
            'message' => 'تم تسجيل النقطة المرحلية.',
            'progress' => $this->payload($progress),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(LectureProgress $progress): array
    {
        return [
            'id' => $progress->id,
            'last_position_seconds' => (int) $progress->last_position_seconds,
            'consumed_seconds' => (int) $progress->consumed_seconds,
            'completion_percent' => (float) $progress->completion_percent,
            'completed_at' => $progress->completed_at?->toIso8601String(),
            'last_checkpoint_id' => $progress->last_checkpoint_id,
            'last_checkpoint_sort_order' => $progress->lastCheckpoint?->sort_order,
            'last_opened_at' => $progress->last_opened_at?->toIso8601String(),
        ];
    }

    private function student(): Student
    {
        /** @var Student $student */
        $student = auth('student')->user();

        return $student;
    }
}
