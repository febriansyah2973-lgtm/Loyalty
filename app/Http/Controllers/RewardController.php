<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reward;
use App\Models\Redemption;
use Illuminate\Support\Str;

public function redeem($rewardId)
{
    $user = auth()->user();
    $reward = Reward::findOrFail($rewardId);

    // Cek apakah poin cukup
    if ($user->points < $reward->points_required) {
        return back()->with('error', 'Poin Anda tidak cukup untuk menukar hadiah ini.');
    }

    // 1. Kurangi poin user
    $user->decrement('points', $reward->points_required);

    // 2. Buat kode voucher unik
    $voucherCode = 'KHN-' . strtoupper(Str::random(8));

    // 3. Simpan riwayat penukaran
    Redemption::create([
        'user_id' => $user->id,
        'reward_id' => $reward->id,
        'unique_code' => $voucherCode,
    ]);

    return back()->with('success', "Sukses! Gunakan kode: {$voucherCode} di khanaan.com");
}
