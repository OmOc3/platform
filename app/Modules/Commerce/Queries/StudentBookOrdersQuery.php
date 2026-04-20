<?php

namespace App\Modules\Commerce\Queries;

use App\Modules\Commerce\Models\Order;
use App\Modules\Students\Models\Student;
use App\Shared\Enums\OrderKind;
use Illuminate\Database\Eloquent\Builder;

class StudentBookOrdersQuery
{
    public function builder(Student $student): Builder
    {
        return Order::query()
            ->with(['items.product.book', 'shipment', 'payments'])
            ->where('student_id', $student->id)
            ->where('kind', OrderKind::Book->value)
            ->latest('placed_at');
    }
}
