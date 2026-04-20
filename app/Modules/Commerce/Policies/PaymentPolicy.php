<?php

namespace App\Modules\Commerce\Policies;

use App\Modules\Commerce\Models\Payment;
use App\Modules\Identity\Models\Admin;

class PaymentPolicy
{
    public function viewAny(Admin $admin): bool
    {
        return $admin->can('transactions.view');
    }

    public function view(Admin $admin, Payment $payment): bool
    {
        return $admin->can('transactions.view');
    }

    public function update(Admin $admin, Payment $payment): bool
    {
        return $admin->can('transactions.manage');
    }
}
