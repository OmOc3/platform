<?php

namespace App\Modules\Students\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Modules\Commerce\Queries\StudentBookOrdersQuery;
use Illuminate\Contracts\View\View;

class BookOrderHistoryController extends Controller
{
    public function __construct(private readonly StudentBookOrdersQuery $studentBookOrdersQuery)
    {
    }

    public function __invoke(): View
    {
        $student = auth('student')->user();

        return view('student.history.book-orders', [
            'orders' => $this->studentBookOrdersQuery->builder($student)->paginate(12),
        ]);
    }
}
