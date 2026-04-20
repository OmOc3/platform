<?php

namespace App\Modules\Commerce\Policies;

use App\Modules\Commerce\Models\Shipment;
use App\Modules\Identity\Models\Admin;

class ShipmentPolicy
{
    public function viewAny(Admin $admin): bool
    {
        return $admin->can('shipping.view');
    }

    public function view(Admin $admin, Shipment $shipment): bool
    {
        return $admin->can('shipping.view');
    }

    public function update(Admin $admin, Shipment $shipment): bool
    {
        return $admin->can('shipping.manage');
    }
}
