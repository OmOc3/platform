<?php

namespace App\Modules\Support\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Modules\Support\Actions\CreateComplaintAction;
use App\Modules\Support\Enums\ComplaintType;
use App\Modules\Support\Http\Requests\Student\StoreComplaintRequest;
use App\Modules\Support\Models\Complaint;
use App\Modules\Support\Queries\StudentComplaintsQuery;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ComplaintController extends Controller
{
    public function __construct(
        private readonly StudentComplaintsQuery $studentComplaintsQuery,
        private readonly CreateComplaintAction $createComplaintAction,
    ) {
    }

    public function index(): View
    {
        $student = auth('student')->user();

        $this->authorize('viewAny', Complaint::class);

        return view('student.support.complaints', [
            'complaints' => $this->studentComplaintsQuery->builder($student)->paginate(12),
            'types' => ComplaintType::cases(),
        ]);
    }

    public function store(StoreComplaintRequest $request): RedirectResponse
    {
        $student = auth('student')->user();

        $this->authorize('create', Complaint::class);

        $this->createComplaintAction->execute($student, $request->validated());

        return redirect()
            ->route('student.complaints.index')
            ->with('status', 'تم إرسال رسالتك بنجاح.');
    }
}
