<?php

namespace App\Modules\Commerce\Queries;

use App\Modules\Commerce\Models\Payment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PaymentsIndexQuery
{
    public function builder(Request $request): Builder
    {
        $search = $request->string('search')->toString();
        $status = $request->string('status')->toString();
        $provider = $request->string('provider')->toString();

        return Payment::query()
            ->with(['order.student'])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $builder) use ($search): void {
                    $builder
                        ->where('provider_reference', 'like', "%{$search}%")
                        ->orWhere('provider_transaction_reference', 'like', "%{$search}%")
                        ->orWhereHas('order', function (Builder $orderQuery) use ($search): void {
                            $orderQuery
                                ->where('uuid', 'like', "%{$search}%")
                                ->orWhereHas('student', function (Builder $studentQuery) use ($search): void {
                                    $studentQuery
                                        ->where('name', 'like', "%{$search}%")
                                        ->orWhere('email', 'like', "%{$search}%")
                                        ->orWhere('student_number', 'like', "%{$search}%");
                                });
                        });
                });
            })
            ->when($status !== '', fn (Builder $query) => $query->where('status', $status))
            ->when($provider !== '', fn (Builder $query) => $query->where('provider', $provider))
            ->latest('created_at');
    }
}
