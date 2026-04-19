<?php

namespace App\Shared\Services;

use App\Shared\Contracts\AttendanceRecorder;
use BadMethodCallException;

class DatabaseAttendanceRecorder implements AttendanceRecorder
{
    public function record(array $payload): void
    {
        throw new BadMethodCallException('Attendance recording is not implemented in Milestone 1.');
    }
}
