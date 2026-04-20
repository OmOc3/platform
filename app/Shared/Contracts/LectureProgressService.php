<?php

namespace App\Shared\Contracts;

use App\Modules\Academic\Models\Lecture;
use App\Modules\Academic\Models\LectureCheckpoint;
use App\Modules\Academic\Models\LectureProgress;
use App\Modules\Students\Models\Student;

interface LectureProgressService
{
    public function touchOpen(Student $student, Lecture $lecture): LectureProgress;

    public function updateProgress(Student $student, Lecture $lecture, array $payload): LectureProgress;

    public function reachCheckpoint(Student $student, Lecture $lecture, LectureCheckpoint $checkpoint): LectureProgress;

    public function markCompleted(Student $student, Lecture $lecture): LectureProgress;
}
