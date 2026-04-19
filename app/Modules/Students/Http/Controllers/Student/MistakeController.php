<?php

namespace App\Modules\Students\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Models\Lecture;
use App\Modules\Students\Models\MistakeItem;
use App\Modules\Students\Queries\MistakeGroupsQuery;
use Illuminate\Contracts\View\View;

class MistakeController extends Controller
{
    public function __construct(private readonly MistakeGroupsQuery $mistakeGroupsQuery)
    {
    }

    public function index(): View
    {
        return view('student.mistakes.index', [
            'groups' => $this->mistakeGroupsQuery->forStudent(auth('student')->user()),
        ]);
    }

    public function show(Lecture $lecture): View
    {
        $items = MistakeItem::query()
            ->with(['lecture', 'exam'])
            ->where('student_id', auth('student')->id())
            ->where('lecture_id', $lecture->id)
            ->latest('created_at')
            ->get();

        abort_if($items->isEmpty(), 404);

        return view('student.mistakes.show', [
            'lecture' => $lecture,
            'items' => $items,
        ]);
    }
}
