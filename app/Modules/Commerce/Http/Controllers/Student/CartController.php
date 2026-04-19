<?php

namespace App\Modules\Commerce\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Modules\Commerce\Actions\Cart\AddProductToCartAction;
use App\Modules\Commerce\Actions\Cart\RemoveCartItemAction;
use App\Modules\Commerce\Actions\Cart\UpdateCartItemQuantityAction;
use App\Modules\Commerce\Http\Requests\Student\AddCartItemRequest;
use App\Modules\Commerce\Http\Requests\Student\UpdateCartItemRequest;
use App\Modules\Commerce\Models\CartItem;
use App\Modules\Commerce\Models\Product;
use App\Modules\Commerce\Queries\CartSummaryQuery;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class CartController extends Controller
{
    public function __construct(
        private readonly CartSummaryQuery $cartSummaryQuery,
        private readonly AddProductToCartAction $addProductToCartAction,
        private readonly UpdateCartItemQuantityAction $updateCartItemQuantityAction,
        private readonly RemoveCartItemAction $removeCartItemAction,
    ) {
    }

    public function index(): View
    {
        return view('student.cart.index', $this->cartSummaryQuery->dataFor(auth('student')->user()));
    }

    public function store(AddCartItemRequest $request): RedirectResponse
    {
        $student = auth('student')->user();
        $product = Product::query()->findOrFail($request->integer('product_id'));

        $this->addProductToCartAction->execute($student, $product, $request->integer('quantity'));

        return back()->with('status', 'تمت إضافة العنصر إلى السلة.');
    }

    public function update(UpdateCartItemRequest $request, CartItem $cartItem): RedirectResponse
    {
        abort_unless($cartItem->cart?->student_id === auth('student')->id(), 403);

        $cartItem->loadMissing('product');
        $this->updateCartItemQuantityAction->execute($cartItem, $request->integer('quantity'));

        return redirect()
            ->route('student.cart.index')
            ->with('status', 'تم تحديث الكمية.');
    }

    public function destroy(CartItem $cartItem): RedirectResponse
    {
        abort_unless($cartItem->cart?->student_id === auth('student')->id(), 403);

        $this->removeCartItemAction->execute($cartItem);

        return redirect()
            ->route('student.cart.index')
            ->with('status', 'تم حذف العنصر من السلة.');
    }
}
