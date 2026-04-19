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

        return view('student.history.payments', [
            'entitlements' => $this->studentEntitlementHistoryQuery->builder($student)->paginate(12),
        ]);
    }
}
