<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\LoyaltyActivityNotification;
use Illuminate\Console\Command;

class ExpirePointsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'points:expire {--force : Paksa proses kedaluwarsa poin saat ini}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mereset poin loyalitas yang sudah melewati masa kedaluwarsa (misal per 31 Desember) dan mencatat mutasi ledger';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $force = (bool) $this->option('force');
        $today = now()->toDateString();

        $query = User::where('points', '>', 0);
        if (!$force) {
            $query->whereDate('points_expire_at', '<=', $today);
        }

        $users = $query->get();
        $this->info("Menemukan {$users->count()} member dengan poin yang jatuh tempo kedaluwarsa.");

        $processedCount = 0;
        $totalPointsExpired = 0;

        foreach ($users as $user) {
            $expiredPoints = (int) $user->points;
            $expirePeriod = $user->points_expire_at ? $user->points_expire_at->format('Y') : now()->format('Y');

            // Deduct points through ledger
            $user->deductPoints(
                $expiredPoints,
                'expired_reset',
                "Kedaluwarsa Poin Otomatis Periode {$expirePeriod}"
            );

            // Extend next expiry to end of following year
            $user->points_expire_at = now()->addYear()->endOfYear()->toDateString();
            $user->save();

            // Notify user
            $user->notify(new LoyaltyActivityNotification(
                'Poin Loyalty Telah Kedaluwarsa',
                "Poin sebesar {$expiredPoints} poin telah kedaluwarsa per akhir periode {$expirePeriod}. Mulai kumpulkan poin baru Anda!",
                'points_expired',
                route('dashboard')
            ));

            $this->line("- Member: {$user->name} ({$user->email}): {$expiredPoints} poin direset.");
            $processedCount++;
            $totalPointsExpired += $expiredPoints;
        }

        $this->info("Selesai! {$processedCount} member diproses, total {$totalPointsExpired} poin kedaluwarsa dicatat di ledger.");

        return Command::SUCCESS;
    }
}
