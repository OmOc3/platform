<?php

namespace Database\Factories;

use App\Modules\Students\Models\Student;
use App\Shared\Enums\StudentStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    protected $model = Student::class;

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
            'student_number' => fake()->unique()->numerify('STU####'),
            'name' => fake('ar_EG')->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->unique()->numerify('010########'),
            'parent_phone' => fake()->numerify('010########'),
            'governorate' => fake('ar_EG')->city(),
            'language' => 'ar',
            'status' => StudentStatus::Pending,
            'last_login_at' => null,
            'password' => static::$password ??= Hash::make('password'),
        ];
    }
}
