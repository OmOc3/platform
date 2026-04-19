<?php

namespace Database\Factories;

use App\Modules\Commerce\Models\Cart;
use App\Modules\Students\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cart>
 */
class CartFactory extends Factory
{
    protected $model = Cart::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'currency' => 'EGP',
        ];
    }
}
