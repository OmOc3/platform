<?php

namespace App\Modules\Academic\Queries;

use App\Modules\Academic\Models\ExamAttempt;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ExamAttemptsIndexQuery
{
    public function builder(Request $request): Builder
    {
        $search = $request->string('search')->toString();
        $status = $request->string('status')->toString();
        $examId = $request->integer('exam_id');

        return ExamAttempt::query()
            ->with(['exam', 'student'])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $builder) use ($search): void {
                    $builder->whereHas('student', function (Builder $studentQuery) use ($search): void {
                        $studentQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('student_number', 'like', "%{$search}%");
                    })->orWhereHas('exam', function (Builder $examQuery) use ($search): void {
                        $examQuery
                            ->where('title', 'like', "%{$search}%")
                            ->orWhere('slug', 'like', "%{$search}%");
                    });
                });
            })
            ->when($status !== '', fn (Builder $query) => $query->where('status', $status))
            ->when($examId > 0, fn (Builder $query) => $query->where('exam_id', $examId))
            ->latest('started_at');
    }
}
