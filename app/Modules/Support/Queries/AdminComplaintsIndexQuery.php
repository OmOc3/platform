<?php

namespace App\Modules\Support\Queries;

use App\Modules\Support\Models\Complaint;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AdminComplaintsIndexQuery
{
    public function builder(Request $request): Builder
    {
        $search = $request->string('search')->toString();
        $status = $request->string('status')->toString();
        $type = $request->string('type')->toString();

        return Complaint::query()
            ->with(['student.ownerAdmin', 'student.center', 'student.group'])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $builder) use ($search): void {
                    $builder->where('content', 'like', "%{$search}%")
                        ->orWhere('admin_notes', 'like', "%{$search}%")
                        ->orWhereHas('student', function (Builder $studentQuery) use ($search): void {
                            $studentQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('student_number', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                });
            })
            ->when($status !== '', fn (Builder $query) => $query->where('status', $status))
            ->when($type !== '', fn (Builder $query) => $query->where('type', $type))
            ->orderByRaw("case when status in ('open', 'under_review') then 0 else 1 end")
            ->latest('created_at');
    }
}
