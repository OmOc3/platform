<?php

namespace App\Shared\Contracts;

interface AuditLogger
{
    public function log(
        string $event,
        mixed $actor = null,
        mixed $subject = null,
        array $oldValues = [],
        array $newValues = [],
        array $meta = [],
    ): void;
}
