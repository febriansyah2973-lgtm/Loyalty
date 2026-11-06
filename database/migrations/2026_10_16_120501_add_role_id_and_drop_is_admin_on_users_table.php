<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add role_id column as nullable temporarily for migration
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('id');
        });

        // 2. Ensure initial roles exist
        $roles = [
            ['name' => 'admin', 'display_name' => 'Administrator', 'description' => 'Akses penuh operasional & manajemen loyalty', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'manager', 'display_name' => 'Store Manager / Kasir', 'description' => 'Akses verifikasi QR scan & penukaran voucher di kasir', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'customer', 'display_name' => 'Customer / Member', 'description' => 'Akses portal loyalitas pelanggan', 'created_at' => now(), 'updated_at' => now()],
        ];

        foreach ($roles as $roleData) {
            DB::table('roles')->updateOrInsert(
                ['name' => $roleData['name']],
                $roleData
            );
        }

        $adminId = DB::table('roles')->where('name', 'admin')->value('id');
        $customerId = DB::table('roles')->where('name', 'customer')->value('id');

        // 3. Migrate existing is_admin values
        if (Schema::hasColumn('users', 'is_admin')) {
            DB::table('users')->where('is_admin', true)->update(['role_id' => $adminId]);
            DB::table('users')->where('is_admin', false)->update(['role_id' => $customerId]);
        } else {
            DB::table('users')->whereNull('role_id')->update(['role_id' => $customerId]);
        }

        // 4. Set foreign key constraint and drop is_admin column
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
            if (Schema::hasColumn('users', 'is_admin')) {
                $table->dropColumn('is_admin');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->after('points');
        });

        $adminId = DB::table('roles')->where('name', 'admin')->value('id');
        if ($adminId) {
            DB::table('users')->where('role_id', $adminId)->update(['is_admin' => true]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
        });
    }
};
