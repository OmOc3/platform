<?php

namespace App\Shared\Contracts;

interface ExamAttemptService
{
    public function start(array $payload): mixed;

    public function submit(array $payload): mixed;
}
