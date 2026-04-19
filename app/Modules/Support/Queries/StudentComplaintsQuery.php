<?php

namespace App\Modules\Support\Queries;

use App\Modules\Students\Models\Student;
use App\Modules\Support\Models\Complaint;
use Illuminate\Database\Eloquent\Builder;

class StudentComplaintsQuery
{
    public function builder(Student $student): Builder
    {
        return Complaint::query()
            ->where('student_id', $student->id)
            ->latest('created_at');
    }
}
