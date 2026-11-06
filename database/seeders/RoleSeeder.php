<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'Akses penuh operasional & manajemen loyalty',
            ],
            [
                'name' => 'manager',
                'display_name' => 'Store Manager / Kasir',
                'description' => 'Akses verifikasi QR scan & penukaran voucher di kasir',
            ],
            [
                'name' => 'customer',
                'display_name' => 'Customer / Member',
                'description' => 'Akses portal loyalitas pelanggan',
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['name' => $role['name']], $role);
        }
    }
}
