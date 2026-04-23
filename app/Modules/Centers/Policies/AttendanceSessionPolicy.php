<?php

namespace App\Modules\Centers\Policies;

use App\Modules\Centers\Models\AttendanceSession;
use App\Modules\Identity\Models\Admin;

class AttendanceSessionPolicy
{
    public function viewAny(Admin $admin): bool
    {
        return $admin->can('attendance.view');
    }

    public function view(Admin $admin, AttendanceSession $attendanceSession): bool
    {
        return $admin->can('attendance.view');
    }

    public function update(Admin $admin, AttendanceSession $attendanceSession): bool
    {
        return $admin->can('attendance.manage');
    }
}
