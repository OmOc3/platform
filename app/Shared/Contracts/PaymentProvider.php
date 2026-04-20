<?php

namespace App\Shared\Contracts;

use App\Modules\Commerce\Models\Order;
use App\Modules\Commerce\Models\Payment;
use Illuminate\Http\Request;

interface PaymentProvider
{
    /**
     * @return array{provider_reference:string, checkout_url:?string, expires_at:mixed, meta:array<string,mixed>}
     */
    public function initiate(Payment $payment, Order $order, array $context = []): array;

    /**
     * @return array{
     *     event_key:string,
     *     provider_reference:string,
     *     provider_transaction_reference:?string,
     *     status:string,
     *     meta:array<string,mixed>,
     *     payload:array<string,mixed>
     * }
     */
    public function normalizeWebhook(Request $request): array;
}
