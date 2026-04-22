<?php

namespace App\Modules\Support\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Support\Actions\AssignSupportTicketAction;
use App\Modules\Support\Actions\ReplyToSupportTicketAction;
use App\Modules\Support\Actions\UpdateSupportTicketStatusAction;
use App\Modules\Support\Http\Requests\Admin\AssignSupportTicketRequest;
use App\Modules\Support\Http\Requests\Admin\UpdateSupportTicketStatusRequest;
use App\Modules\Support\Http\Requests\Student\StoreSupportTicketReplyRequest;
use App\Modules\Support\Models\SupportTeam;
use App\Modules\Support\Models\SupportTicket;
use App\Modules\Support\Models\SupportTicketType;
use App\Modules\Support\Queries\AdminSupportTicketsIndexQuery;
use App\Shared\Enums\TicketStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SupportTicketController extends Controller
{
    public function __construct(
        private readonly AdminSupportTicketsIndexQuery $adminSupportTicketsIndexQuery,
        private readonly UpdateSupportTicketStatusAction $updateSupportTicketStatusAction,
        private readonly AssignSupportTicketAction $assignSupportTicketAction,
        private readonly ReplyToSupportTicketAction $replyToSupportTicketAction,
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', SupportTicket::class);

        return view('admin.support.tickets.index', [
            'tickets' => $this->adminSupportTicketsIndexQuery->builder($request)->paginate(20)->withQueryString(),
            'statuses' => TicketStatus::cases(),
            'teams' => SupportTeam::query()->where('is_active', true)->orderBy('name')->get(),
            'types' => SupportTicketType::query()->where('is_active', true)->orderBy('name')->get(),
            'overview' => [
                'total' => SupportTicket::query()->count(),
                'active' => SupportTicket::query()->whereIn('status', TicketStatus::activeWorkloadValues())->count(),
                'waiting_customer' => SupportTicket::query()->where('status', TicketStatus::WaitingCustomer->value)->count(),
                'unassigned' => SupportTicket::query()->whereNull('assigned_admin_id')->count(),
            ],
        ]);
    }

    public function show(SupportTicket $supportTicket): View
    {
        $this->authorize('view', $supportTicket);

        return view('admin.support.tickets.show', [
            'ticket' => $supportTicket->load([
                'student.ownerAdmin',
                'student.center',
                'student.group',
                'type.defaultTeam',
                'team',
                'assignedAdmin.supportTeams',
                'replies.author',
            ]),
            'statuses' => TicketStatus::cases(),
            'teams' => SupportTeam::query()
                ->with(['admins' => fn ($query) => $query->where('is_active', true)->orderBy('name')])
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
            'admins' => \App\Modules\Identity\Models\Admin::query()
                ->with('supportTeams')
                ->where('is_active', true)
                ->orderBy('name')
                ->get()
                ->filter(fn (\App\Modules\Identity\Models\Admin $admin): bool => $admin->can('tickets.manage'))
                ->values(),
        ]);
    }

    public function updateStatus(UpdateSupportTicketStatusRequest $request, SupportTicket $supportTicket): RedirectResponse
    {
        $this->authorize('update', $supportTicket);

        $this->updateSupportTicketStatusAction->execute($supportTicket, $request->validated(), auth('admin')->user());

        return redirect()
            ->route('admin.tickets.show', $supportTicket)
            ->with('status', 'تم تحديث حالة التذكرة.');
    }

    public function assign(AssignSupportTicketRequest $request, SupportTicket $supportTicket): RedirectResponse
    {
        $this->authorize('assign', $supportTicket);

        $this->assignSupportTicketAction->execute($supportTicket, $request->validated(), auth('admin')->user());

        return redirect()
            ->route('admin.tickets.show', $supportTicket)
            ->with('status', 'تم تحديث إسناد التذكرة.');
    }

    public function autoAssign(SupportTicket $supportTicket): RedirectResponse
    {
        $this->authorize('assign', $supportTicket);

        $this->assignSupportTicketAction->autoAssign($supportTicket, auth('admin')->user());

        return redirect()
            ->route('admin.tickets.show', $supportTicket)
            ->with('status', 'تم تنفيذ الإسناد التلقائي للتذكرة.');
    }

    public function reply(StoreSupportTicketReplyRequest $request, SupportTicket $supportTicket): RedirectResponse
    {
        $this->authorize('reply', $supportTicket);

        $this->replyToSupportTicketAction->execute($supportTicket, auth('admin')->user(), $request->validated());

        return redirect()
            ->route('admin.tickets.show', $supportTicket)
            ->with('status', 'تم إرسال الرد الإداري على التذكرة.');
    }
}
