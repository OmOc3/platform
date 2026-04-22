<?php

namespace App\Modules\Support\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Modules\Support\Actions\CreateSupportTicketAction;
use App\Modules\Support\Actions\ReplyToSupportTicketAction;
use App\Modules\Support\Http\Requests\Student\StoreSupportTicketReplyRequest;
use App\Modules\Support\Http\Requests\Student\StoreSupportTicketRequest;
use App\Modules\Support\Models\SupportTicket;
use App\Modules\Support\Models\SupportTicketType;
use App\Modules\Support\Queries\StudentSupportTicketsQuery;
use App\Shared\Enums\TicketStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class SupportTicketController extends Controller
{
    public function __construct(
        private readonly StudentSupportTicketsQuery $studentSupportTicketsQuery,
        private readonly CreateSupportTicketAction $createSupportTicketAction,
        private readonly ReplyToSupportTicketAction $replyToSupportTicketAction,
    ) {
    }

    public function index(): View
    {
        $student = auth('student')->user();

        $this->authorize('viewAny', SupportTicket::class);

        return view('student.support.tickets.index', [
            'tickets' => $this->studentSupportTicketsQuery->builder($student)->paginate(12),
            'ticketTypes' => SupportTicketType::query()
                ->with('defaultTeam')
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
            'overview' => [
                'total' => SupportTicket::query()->where('student_id', $student->id)->count(),
                'active' => SupportTicket::query()
                    ->where('student_id', $student->id)
                    ->whereIn('status', TicketStatus::activeWorkloadValues())
                    ->count(),
                'waiting' => SupportTicket::query()
                    ->where('student_id', $student->id)
                    ->whereIn('status', [TicketStatus::WaitingCustomer->value, TicketStatus::WaitingInternal->value])
                    ->count(),
                'resolved' => SupportTicket::query()
                    ->where('student_id', $student->id)
                    ->where('status', TicketStatus::Resolved->value)
                    ->count(),
            ],
        ]);
    }

    public function show(SupportTicket $supportTicket): View
    {
        $this->authorize('view', $supportTicket);

        return view('student.support.tickets.show', [
            'ticket' => $supportTicket->load(['type.defaultTeam', 'team', 'assignedAdmin', 'replies.author']),
        ]);
    }

    public function store(StoreSupportTicketRequest $request): RedirectResponse
    {
        $student = auth('student')->user();

        $this->authorize('create', SupportTicket::class);

        $ticket = $this->createSupportTicketAction->execute($student, $request->validated());

        return redirect()
            ->route('student.tickets.show', $ticket)
            ->with('status', 'تم إنشاء التذكرة وإرسالها إلى فريق الدعم.');
    }

    public function reply(StoreSupportTicketReplyRequest $request, SupportTicket $supportTicket): RedirectResponse
    {
        $this->authorize('reply', $supportTicket);

        $this->replyToSupportTicketAction->execute($supportTicket, auth('student')->user(), $request->validated());

        return redirect()
            ->route('student.tickets.show', $supportTicket)
            ->with('status', 'تمت إضافة ردك إلى التذكرة.');
    }
}
