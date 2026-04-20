<?php

namespace App\Modules\Commerce\Queries;

use App\Modules\Commerce\Models\Shipment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ShipmentsIndexQuery
{
    public function builder(Request $request): Builder
    {
        $search = $request->string('search')->toString();
        $status = $request->string('status')->toString();
        $governorate = $request->string('governorate')->toString();

        return Shipment::query()
            ->with(['order.student'])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $builder) use ($search): void {
                    $builder
                        ->where('recipient_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('carrier_reference', 'like', "%{$search}%")
                        ->orWhereHas('order', function (Builder $orderQuery) use ($search): void {
                            $orderQuery
                                ->where('uuid', 'like', "%{$search}%")
                                ->orWhereHas('student', function (Builder $studentQuery) use ($search): void {
                                    $studentQuery
                                        ->where('name', 'like', "%{$search}%")
                                        ->orWhere('student_number', 'like', "%{$search}%");
                                });
                        });
                });
            })
            ->when($status !== '', fn (Builder $query) => $query->where('status', $status))
            ->when($governorate !== '', fn (Builder $query) => $query->where('governorate', $governorate))
            ->latest('created_at');
    }
}
