<?php

namespace App\Http\Controllers;

use App\Models\Redemption;
use App\Models\Reward;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RewardController extends Controller
{
    /**
     * Redemption Engine (Point Deduction):
     * Deduct from points (points = points - reward.points_required).
     * DO NOT touch lifetime_points.
     */
    public function redeem($rewardId)
    {
        $user = auth()->user();
        $reward = Reward::findOrFail($rewardId);

        // Cek apakah saldo poin aktif cukup
        if ($user->points < $reward->points_required) {
            return back()->with('error', 'Poin Anda tidak cukup untuk menukar hadiah ini.');
        }

        if ($reward->stock <= 0) {
            return back()->with('error', 'Hadiah ini sedang habis.');
        }

        $voucherCode = 'KHN-' . strtoupper(Str::random(8));

        DB::transaction(function () use ($user, $reward, $voucherCode) {
            // Deduct points via deductPoints (points only, lifetime_points preserved)
            $user->deductPoints(
                $reward->points_required,
                'redeem_voucher',
                "Penukaran hadiah katalog: {$reward->title}",
                $reward
            );

            $reward->decrement('stock');

            // Simpan riwayat penukaran
            Redemption::create([
                'user_id' => $user->id,
                'reward_id' => $reward->id,
                'unique_code' => $voucherCode,
            ]);

            // Terbitkan Voucher ke Dompet Member
            Voucher::create([
                'user_id' => $user->id,
                'reward_id' => $reward->id,
                'title' => $reward->title,
                'code' => $voucherCode,
                'expires_at' => now()->addDays(7),
            ]);
        });

        return back()->with('success', "Sukses! Voucher {$voucherCode} telah ditambahkan ke dompet Anda.");
    }
}
