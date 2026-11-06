<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->date('birth_date')->nullable()->after('points');
            $table->decimal('cashback_balance', 12, 2)->default(0)->after('birth_date');
            $table->timestamp('birthday_rewarded_at')->nullable()->after('cashback_balance');
            $table->string('qr_token', 64)->nullable()->unique()->after('birthday_rewarded_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['qr_token']);
            $table->dropColumn(['birth_date', 'cashback_balance', 'birthday_rewarded_at', 'qr_token']);
        });
    }
};