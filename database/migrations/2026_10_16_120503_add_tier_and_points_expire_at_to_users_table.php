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
        Schema::table('users', function (Blueprint $table) {
            $table->string('tier', 20)->default('Classic')->after('points');
            $table->date('points_expire_at')->nullable()->after('tier');
        });

        // Sync existing users tier & expiry
        $endOfYear = now()->endOfYear()->toDateString();
        DB::table('users')->where('points', '>=', 5000)->update([
            'tier' => 'Gold',
            'points_expire_at' => $endOfYear,
        ]);
        DB::table('users')->where('points', '>=', 1000)->where('points', '<', 5000)->update([
            'tier' => 'Silver',
            'points_expire_at' => $endOfYear,
        ]);
        DB::table('users')->where('points', '<', 1000)->update([
            'tier' => 'Classic',
            'points_expire_at' => $endOfYear,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['tier', 'points_expire_at']);
        });
    }
};
