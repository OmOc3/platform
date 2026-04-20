<?php

namespace App\Modules\Students\Queries;

use App\Modules\Students\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class StudentsIndexQuery
{
    public function builder(Request $request): Builder
    {
        $search = $request->string('search')->toString();

        return Student::query()
            ->with(['ownerAdmin', 'grade', 'track', 'center', 'group'])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $builder) use ($search): void {
                    $builder->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('student_number', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->string('status')->toString()))
            ->when($request->filled('source_type'), fn (Builder $query) => $query->where('source_type', $request->string('source_type')->toString()))
            ->when($request->filled('is_azhar'), fn (Builder $query) => $query->where('is_azhar', $request->boolean('is_azhar')))
            ->when($request->filled('grade_id'), fn (Builder $query) => $query->where('grade_id', $request->integer('grade_id')))
            ->when($request->filled('track_id'), fn (Builder $query) => $query->where('track_id', $request->integer('track_id')))
            ->when($request->filled('center_id'), fn (Builder $query) => $query->where('center_id', $request->integer('center_id')))
            ->when($request->filled('group_id'), fn (Builder $query) => $query->where('group_id', $request->integer('group_id')))
            ->when($request->filled('owner_admin_id'), fn (Builder $query) => $query->where('owner_admin_id', $request->integer('owner_admin_id')))
            ->orderByDesc('created_at');
    }
}
