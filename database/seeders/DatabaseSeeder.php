<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            RewardSeeder::class,
        ]);

        $managerRole = Role::where('name', 'manager')->first();
        $customerRole = Role::where('name', 'customer')->first();

        // 1. Manager / Kasir
        User::updateOrCreate(
            ['email' => 'manager@example.com'],
            [
                'name' => 'Kasir / Store Manager',
                'password' => Hash::make('password'),
                'role_id' => $managerRole->id,
                'points' => 0,
                'lifetime_points' => 0,
                'tier' => 'Reguler',
                'points_expire_at' => now()->endOfYear()->toDateString(),
                'cashback_balance' => 0,
                'birth_date' => null,
                'birthday_rewarded_at' => null,
                'qr_token' => (string) Str::uuid(),
                'email_verified_at' => now(),
            ]
        );

        // 2. Demo Member (Silver Tier: 500 - 999 lifetime points)
        $member = User::updateOrCreate(
            ['email' => 'member@example.com'],
            [
                'name' => 'Demo Member',
                'password' => Hash::make('password'),
                'role_id' => $customerRole->id,
                'points' => 750,
                'lifetime_points' => 750,
                'tier' => 'Silver',
                'points_expire_at' => now()->endOfYear()->toDateString(),
                'cashback_balance' => 35.50,
                'birth_date' => now()->subYears(25)->format('Y-m-d'),
                'birthday_rewarded_at' => null,
                'qr_token' => (string) Str::uuid(),
                'email_verified_at' => now(),
            ]
        );

        // Populate initial point ledger for demo member if empty
        if ($member->pointLedgers()->count() === 0) {
            $member->pointLedgers()->create([
                'type' => 'earn_claim',
                'amount' => 750,
                'balance_before' => 0,
                'balance_after' => 750,
                'description' => 'Saldo awal perolehan poin transaksi',
                'created_at' => now()->subDays(5),
            ]);
        }

        // 3. Test User (Reguler Tier: 0 - 499 lifetime points)
        $testUser = User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'role_id' => $customerRole->id,
                'points' => 300,
                'lifetime_points' => 300,
                'tier' => 'Reguler',
                'points_expire_at' => now()->endOfYear()->toDateString(),
                'cashback_balance' => 5.00,
                'birth_date' => null,
                'birthday_rewarded_at' => null,
                'qr_token' => (string) Str::uuid(),
                'email_verified_at' => now(),
            ]
        );

        if ($testUser->pointLedgers()->count() === 0) {
            $testUser->pointLedgers()->create([
                'type' => 'earn_claim',
                'amount' => 300,
                'balance_before' => 0,
                'balance_after' => 300,
                'description' => 'Saldo awal perolehan poin belanja',
                'created_at' => now()->subDays(2),
            ]);
        }
    }
}
