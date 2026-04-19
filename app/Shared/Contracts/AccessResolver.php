<?php

namespace App\Shared\Contracts;

use App\Modules\Students\Models\Student;

interface AccessResolver
{
    public function hasAccess(Student $student, string $resourceType, int|string $resourceId): bool;

    /**
     * @return array<string, mixed>
     */
    public function resolveState(Student $student, object $resource): array;
}
