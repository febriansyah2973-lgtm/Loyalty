<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role_id' => Role::firstOrCreate(
                ['name' => 'customer'],
                ['display_name' => 'Customer / Member', 'description' => 'Akses portal loyalitas pelanggan']
            )->id,
            'points' => 0,
            'lifetime_points' => 0,
            'tier' => 'Reguler',
            'points_expire_at' => now()->endOfYear()->toDateString(),
            'cashback_balance' => 0,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * State for admin user.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => Role::firstOrCreate(
                ['name' => 'admin'],
                ['display_name' => 'Administrator', 'description' => 'Akses operasional penuh']
            )->id,
        ]);
    }

    /**
     * State for manager / cashier user.
     */
    public function manager(): static
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => Role::firstOrCreate(
                ['name' => 'manager'],
                ['display_name' => 'Store Manager / Kasir', 'description' => 'Akses kasir & QR scan']
            )->id,
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
