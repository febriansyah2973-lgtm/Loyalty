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
            $table->integer('lifetime_points')->default(0)->after('points');
        });

        // Initialize lifetime_points for existing users based on current points
        DB::table('users')->update([
            'lifetime_points' => DB::raw('points'),
        ]);

        // Migrate any 'Classic' tier to 'Reguler'
        DB::table('users')->where('tier', 'Classic')->orWhereNull('tier')->update([
            'tier' => 'Reguler',
        ]);

        // Synchronize existing users' tiers according to the new lifetime_points thresholds:
        // >= 1000       -> Gold
        // 500 - 999     -> Silver
        // 0 - 499       -> Reguler
        DB::table('users')->where('lifetime_points', '>=', 1000)->update([
            'tier' => 'Gold',
        ]);

        DB::table('users')->where('lifetime_points', '>=', 500)->where('lifetime_points', '<=', 999)->update([
            'tier' => 'Silver',
        ]);

        DB::table('users')->where('lifetime_points', '<', 500)->update([
            'tier' => 'Reguler',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('lifetime_points');
        });
    }
};
