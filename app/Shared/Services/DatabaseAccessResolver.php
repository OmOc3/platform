<?php

namespace App\Shared\Services;

use App\Modules\Students\Models\Student;
use App\Shared\Contracts\AccessResolver;

class DatabaseAccessResolver implements AccessResolver
{
    public function hasAccess(Student $student, string $resourceType, int|string $resourceId): bool
    {
        return false;
    }
}
