<?php

namespace App\Modules\Centers\Queries;

use App\Modules\Centers\Models\AttendanceSession;
use App\Shared\Enums\AttendanceStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AdminAttendanceSessionsQuery
{
    public function builder(Request $request): Builder
    {
        $search = $request->string('search')->toString();
        $centerId = $request->integer('center_id');
        $groupId = $request->integer('group_id');
        $sessionType = $request->string('session_type')->toString();

        return AttendanceSession::query()
            ->with(['group.center'])
            ->withCount([
                'records',
                'records as present_records_count' => fn (Builder $query) => $query->where('attendance_status', AttendanceStatus::Present->value),
                'records as late_records_count' => fn (Builder $query) => $query->where('attendance_status', AttendanceStatus::Late->value),
                'records as absent_records_count' => fn (Builder $query) => $query->where('attendance_status', AttendanceStatus::Absent->value),
            ])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $builder) use ($search): void {
                    $builder->where('title', 'like', "%{$search}%")
                        ->orWhereHas('group', fn (Builder $groupQuery) => $groupQuery->where('name_ar', 'like', "%{$search}%"));
                });
            })
            ->when($centerId > 0, fn (Builder $query) => $query->whereHas('group', fn (Builder $groupQuery) => $groupQuery->where('center_id', $centerId)))
            ->when($groupId > 0, fn (Builder $query) => $query->where('group_id', $groupId))
            ->when($sessionType !== '', fn (Builder $query) => $query->where('session_type', $sessionType))
            ->latest('starts_at');
    }
}
