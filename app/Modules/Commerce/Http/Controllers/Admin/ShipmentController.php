<?php

namespace App\Modules\Commerce\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Commerce\Actions\Shipments\UpdateShipmentStatusAction;
use App\Modules\Commerce\Http\Requests\Admin\Shipments\UpdateShipmentStatusRequest;
use App\Modules\Commerce\Models\Shipment;
use App\Modules\Commerce\Queries\ShipmentsIndexQuery;
use App\Shared\Enums\ShipmentStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ShipmentController extends Controller
{
    public function __construct(
        private readonly ShipmentsIndexQuery $shipmentsIndexQuery,
        private readonly UpdateShipmentStatusAction $updateShipmentStatusAction,
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Shipment::class);

        return view('admin.commerce.shipments.index', [
            'shipments' => $this->shipmentsIndexQuery->builder($request)->paginate(15)->withQueryString(),
            'statuses' => ShipmentStatus::cases(),
            'overview' => [
                'total' => Shipment::query()->count(),
                'active' => Shipment::query()->whereIn('status', [
                    ShipmentStatus::Pending->value,
                    ShipmentStatus::Prepared->value,
                    ShipmentStatus::HandedToCarrier->value,
                    ShipmentStatus::InTransit->value,
                ])->count(),
                'delivered' => Shipment::query()->where('status', ShipmentStatus::Delivered->value)->count(),
            ],
        ]);
    }

    public function show(Shipment $shipment): View
    {
        $this->authorize('view', $shipment);

        return view('admin.commerce.shipments.show', [
            'shipment' => $shipment->load(['order.student', 'order.items.product']),
            'availableTransitions' => $this->updateShipmentStatusAction->availableTransitions($shipment),
        ]);
    }

    public function update(UpdateShipmentStatusRequest $request, Shipment $shipment): RedirectResponse
    {
        $this->authorize('update', $shipment);

        $result = $this->updateShipmentStatusAction->execute(
            $shipment,
            ShipmentStatus::from($request->validated('status')),
            auth('admin')->user(),
            $request->validated(),
        );

        return redirect()
            ->route('admin.shipments.show', $result['shipment'])
            ->with('status', 'تم تحديث حالة الشحنة.');
    }
}
