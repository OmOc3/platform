<?php

namespace App\Modules\Commerce\Policies;

use App\Modules\Commerce\Models\Order;
use App\Modules\Identity\Models\Admin;

class OrderPolicy
{
    public function viewAny(Admin $admin): bool
    {
        return $admin->can('orders.view');
    }

    public function view(Admin $admin, Order $order): bool
    {
        return $admin->can('orders.view');
    }

    public function update(Admin $admin, Order $order): bool
    {
        return $admin->can('orders.manage');
    }
}
