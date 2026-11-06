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
        Schema::table('vouchers', function (Blueprint $table) {
            $table->foreignId('reward_id')->nullable()->change();
            $table->string('title')->nullable()->after('reward_id');
            $table->decimal('discount_amount', 12, 2)->nullable()->after('title');
            $table->text('notes')->nullable()->after('discount_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->foreignId('reward_id')->nullable(false)->change();
            $table->dropColumn(['title', 'discount_amount', 'notes']);
        });
    }
};
