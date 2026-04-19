<?php

namespace App\Shared\Services;

use App\Shared\Contracts\ExamAttemptService;
use BadMethodCallException;

class DatabaseExamAttemptService implements ExamAttemptService
{
    public function start(array $payload): mixed
    {
        throw new BadMethodCallException('Exam attempts are not implemented in Milestone 1.');
    }

    public function submit(array $payload): mixed
    {
        throw new BadMethodCallException('Exam attempts are not implemented in Milestone 1.');
    }
}
