<?php

namespace App\Modules\Support\Http\Requests\Admin;

use App\Modules\Identity\Models\Admin;
use App\Modules\Support\Models\SupportTeam;
use Illuminate\Foundation\Http\FormRequest;

class AssignSupportTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'support_team_id' => ['nullable', 'exists:support_teams,id'],
            'assigned_admin_id' => ['nullable', 'exists:admins,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $teamId = $this->input('support_team_id');
            $adminId = $this->input('assigned_admin_id');

            if ($adminId === null || $adminId === '') {
                return;
            }

            if ($teamId === null || $teamId === '') {
                $validator->errors()->add('support_team_id', 'اختر فريق الدعم أولًا قبل تحديد المسؤول.');

                return;
            }

            $team = SupportTeam::query()->with('admins')->find($teamId);

            if (! $team?->admins->contains('id', (int) $adminId)) {
                $validator->errors()->add('assigned_admin_id', 'المسؤول المختار غير منضم إلى فريق الدعم المحدد.');

                return;
            }

            $admin = Admin::query()->find($adminId);

            if (! $admin?->is_active || ! $admin->can('tickets.manage')) {
                $validator->errors()->add('assigned_admin_id', 'المسؤول المختار غير مؤهل حاليًا لاستلام التذاكر.');
            }
        });
    }
}
