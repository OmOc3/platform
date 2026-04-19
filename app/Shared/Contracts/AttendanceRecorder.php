<?php

namespace App\Shared\Contracts;

interface AttendanceRecorder
{
    public function record(array $payload): void;
}
