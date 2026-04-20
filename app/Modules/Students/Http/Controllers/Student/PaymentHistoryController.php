<?php

namespace App\Modules\Students\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Modules\Commerce\Queries\StudentEntitlementHistoryQuery;
use Illuminate\Contracts\View\View;

class PaymentHistoryController extends Controller
{
    public function __construct(private readonly StudentEntitlementHistoryQuery $studentEntitlementHistoryQuery)
    {
    }

    public function __invoke(): View
    {
        $student = auth('student')->user();
        $builder = $this->studentEntitlementHistoryQuery->builder($student);
        $summary = [
            'count' => (clone $builder)->count(),
            'active_count' => (clone $builder)->where('status', 'active')->count(),
            'paid_count' => (clone $builder)->where('price_amount', '>', 0)->count(),
            'spent_total' => (clone $builder)->sum('price_amount'),
        ];

        return view('student.history.payments', [
            'entitlements' => $builder->paginate(12),
            'summary' => $summary,
        ]);
    }
}
