<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('rewards', function (Blueprint $table) {
            $table->string('category', 50)->default('general')->after('title');
            $table->string('tier_required', 20)->default('Reguler')->after('points_required');
            $table->string('badge_text', 50)->nullable()->after('tier_required');
        });

        Schema::table('vouchers', function (Blueprint $table) {
            $table->string('category', 50)->default('general')->after('title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rewards', function (Blueprint $table) {
            $table->dropColumn(['category', 'tier_required', 'badge_text']);
        });

        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
};
