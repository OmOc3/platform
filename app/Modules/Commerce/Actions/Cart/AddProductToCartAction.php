<?php

namespace App\Modules\Commerce\Actions\Cart;

use App\Modules\Commerce\Actions\Packages\EvaluatePackageEligibilityAction;
use App\Modules\Commerce\Models\Cart;
use App\Modules\Commerce\Models\CartItem;
use App\Modules\Commerce\Models\Product;
use App\Modules\Students\Models\Student;
use App\Shared\Enums\ProductKind;
use Illuminate\Validation\ValidationException;

class AddProductToCartAction
{
    public function __construct(
        private readonly EvaluatePackageEligibilityAction $evaluatePackageEligibilityAction,
    ) {
    }

    public function execute(Student $student, Product $product, int $quantity = 1): CartItem
    {
        $this->ensureProductCanBeAdded($student, $product);

        $cart = Cart::query()->firstOrCreate(
            ['student_id' => $student->id],
            ['currency' => $product->currency],
        );

        $normalizedQuantity = $product->kind === ProductKind::Book
            ? max(1, min($quantity, 10))
            : 1;

        $item = $cart->items()->firstOrNew([
            'product_id' => $product->id,
        ]);

        $item->quantity = $product->kind === ProductKind::Book
            ? ($item->exists ? $item->quantity + $normalizedQuantity : $normalizedQuantity)
            : 1;
        $item->unit_price_amount = $product->price_amount;
        $item->total_price_amount = $item->quantity * $product->price_amount;
        $item->meta = [
            'product_kind' => $product->kind->value,
        ];
        $item->save();

        return $item->fresh('product');
    }

    private function ensureProductCanBeAdded(Student $student, Product $product): void
    {
        if ($product->kind === ProductKind::Package) {
            $package = $product->package()->first();

            if ($package) {
                $eligibility = $this->evaluatePackageEligibilityAction->execute($student, $package);

                if (! $eligibility['eligible']) {
                    throw ValidationException::withMessages([
                        'product_id' => $eligibility['message'],
                    ]);
                }
            }

            return;
        }

        if ($product->kind === ProductKind::Book) {
            return;
        }

        $alreadyOwned = $product->entitlements()
            ->where('student_id', $student->id)
            ->where('status', 'active')
            ->where(function ($query): void {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query): void {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->exists();

        if ($alreadyOwned) {
            throw ValidationException::withMessages([
                'product_id' => 'هذا العنصر مفعّل بالفعل على حسابك.',
            ]);
        }
    }
}
