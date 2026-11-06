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
		Schema::create('vouchers', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->constrained()->onDelete('cascade');
			$table->foreignId('reward_id')->constrained()->onDelete('cascade');
			$table->string('code')->unique(); // Kode unik seperti KHN-XXXX
			$table->timestamp('expires_at'); // Untuk fitur Urgency
			$table->timestamp('used_at')->nullable(); // Untuk melacak penggunaan
			$table->timestamps();
		});
	}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
