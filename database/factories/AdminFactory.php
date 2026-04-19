<?php

namespace Database\Factories;

use App\Modules\Identity\Models\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<Admin>
 */
class AdminFactory extends Factory
{
    protected $model = Admin::class;

    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'name' => fake('ar_EG')->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'job_title' => fake('ar_EG')->jobTitle(),
            'locale' => 'ar',
            'is_active' => true,
            'last_login_at' => null,
            'password' => static::$password ??= Hash::make('password'),
        ];
    }
}
