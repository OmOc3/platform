<?php

namespace App\Modules\Commerce\Queries;

use App\Modules\Commerce\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class OrdersIndexQuery
{
    public function builder(Request $request): Builder
    {
        $search = $request->string('search')->toString();
        $status = $request->string('status')->toString();
        $kind = $request->string('kind')->toString();

        return Order::query()
            ->with(['student', 'items'])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $builder) use ($search): void {
                    $builder
                        ->where('uuid', 'like', "%{$search}%")
                        ->orWhereHas('student', function (Builder $studentQuery) use ($search): void {
                            $studentQuery
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('student_number', 'like', "%{$search}%");
                        });
                });
            })
            ->when($status !== '', fn (Builder $query) => $query->where('status', $status))
            ->when($kind !== '', fn (Builder $query) => $query->where('kind', $kind))
            ->latest('created_at');
    }
}
