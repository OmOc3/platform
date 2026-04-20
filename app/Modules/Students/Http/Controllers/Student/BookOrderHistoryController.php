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
        $builder = $this->studentBookOrdersQuery->builder($student);
        $summary = [
            'count' => (clone $builder)->count(),
            'fulfilled_count' => (clone $builder)->whereIn('status', ['completed', 'fulfilled'])->count(),
            'pending_count' => (clone $builder)->whereIn('status', ['draft', 'pending_payment', 'paid', 'ready_for_shipping', 'shipped'])->count(),
            'total_amount' => (clone $builder)->sum('total_amount'),
        ];

        return view('student.history.book-orders', [
            'orders' => $builder->paginate(12),
            'summary' => $summary,
        ]);
    }
}
