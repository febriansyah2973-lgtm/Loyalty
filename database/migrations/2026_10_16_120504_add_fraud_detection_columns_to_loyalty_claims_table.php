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
        Schema::table('loyalty_claims', function (Blueprint $table) {
            $table->string('proof_hash', 64)->nullable()->after('proof_path')->index();
            $table->unsignedSmallInteger('fraud_risk_score')->default(0)->after('proof_hash');
            $table->text('fraud_flags')->nullable()->after('fraud_risk_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loyalty_claims', function (Blueprint $table) {
            $table->dropIndex(['proof_hash']);
            $table->dropColumn(['proof_hash', 'fraud_risk_score', 'fraud_flags']);
        });
    }
};
