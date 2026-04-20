<?php

namespace App\Modules\Commerce\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Modules\Commerce\Actions\Payments\HandlePaymentWebhookAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentWebhookController extends Controller
{
    public function __construct(private readonly HandlePaymentWebhookAction $handlePaymentWebhookAction)
    {
    }

    public function __invoke(Request $request, string $provider): JsonResponse
    {
        $result = $this->handlePaymentWebhookAction->execute($provider, $request);

        return response()->json([
            'processed' => $result['processed'],
            'payment_id' => $result['payment']?->id,
        ], $result['processed'] ? 200 : 202);
    }
}
