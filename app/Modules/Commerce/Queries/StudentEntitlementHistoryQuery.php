<?php

namespace App\Modules\Commerce\Queries;

use App\Modules\Commerce\Models\Entitlement;
use App\Modules\Students\Models\Student;
use Illuminate\Database\Eloquent\Builder;

class StudentEntitlementHistoryQuery
{
    public function builder(Student $student): Builder
    {
        return Entitlement::query()
            ->with(['product', 'grantedByAdmin', 'orderItem.order'])
            ->where('student_id', $student->id)
            ->latest('granted_at');
    }
}
