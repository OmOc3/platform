<?php

namespace App\Modules\Academic\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Models\Lecture;
use App\Modules\Academic\Queries\LectureProgressIndexQuery;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class LectureProgressController extends Controller
{
    public function __construct(private readonly LectureProgressIndexQuery $lectureProgressIndexQuery)
    {
    }

    public function index(Request $request, Lecture $lecture): View
    {
        $this->authorize('view', $lecture);

        return view('admin.academic.lectures.progress.index', [
            'lecture' => $lecture,
            'progressRecords' => $this->lectureProgressIndexQuery
                ->builder($lecture, $request)
                ->paginate(20)
                ->withQueryString(),
        ]);
    }
}
