<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const TIER_REGULER = 'Reguler';
    public const TIER_SILVER = 'Silver';
    public const TIER_GOLD = 'Gold';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'points',
        'lifetime_points',
        'tier',
        'points_expire_at',
        'birth_date',
        'cashback_balance',
        'birthday_rewarded_at',
        'qr_token',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'points' => 'integer',
        'lifetime_points' => 'integer',
        'points_expire_at' => 'date',
        'cashback_balance' => 'decimal:2',
        'birthday_rewarded_at' => 'datetime',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (blank($user->qr_token)) {
                $user->qr_token = (string) Str::uuid();
            }

            if (blank($user->tier)) {
                $user->tier = self::TIER_REGULER;
            }

            if ($user->lifetime_points === null) {
                $user->lifetime_points = 0;
            }

            if (blank($user->points_expire_at)) {
                $user->points_expire_at = now()->endOfYear()->toDateString();
            }

            if (blank($user->role_id)) {
                $customerRole = Role::where('name', 'customer')->first();
                if ($customerRole) {
                    $user->role_id = $customerRole->id;
                }
            }
        });
    }

    /**
     * Relasi ke Role (Master Peran Pengguna)
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Relasi ke Point Ledgers (Tabel Riwayat Mutasi Poin)
     */
    public function pointLedgers(): HasMany
    {
        return $this->hasMany(PointLedger::class)->latest();
    }

    /**
     * Relasi ke Loyalty Claims (Riwayat Klaim Belanja)
     */
    public function loyaltyClaims(): HasMany
    {
        return $this->hasMany(LoyaltyClaim::class);
    }

    /**
     * Relasi ke Vouchers (Dompet Voucher User)
     */
    public function vouchers(): HasMany
    {
        return $this->hasMany(Voucher::class);
    }

    /**
     * Pengecekan Hak Akses / Role
     */
    public function hasRole(string|array $roles): bool
    {
        $roleName = $this->role?->name;
        if (! $roleName) {
            return false;
        }

        if (is_array($roles)) {
            return in_array($roleName, $roles, true);
        }

        return $roleName === $roles;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isManager(): bool
    {
        return $this->hasRole('manager');
    }

    public function isCustomer(): bool
    {
        return $this->hasRole('customer');
    }

    /**
     * Accessor is_admin untuk backward compatibility dengan kode lama.
     */
    public function getIsAdminAttribute(): bool
    {
        return $this->hasRole(['admin', 'manager']);
    }

    /**
     * Accessor: Menentukan persentase diskon dinamis berdasarkan Tier.
     */
    public function getDiscountPercentageAttribute()
    {
        return match($this->tier) {
            self::TIER_GOLD => 15,
            self::TIER_SILVER => 5,
            default => 0,
        };
    }

    /**
     * Multiplier Logic berdasarkan Tier:
     * Reguler -> 1.0
     * Silver  -> 1.5
     * Gold    -> 2.0
     */
    public function getTierMultiplier(): float
    {
        return match ($this->tier) {
            self::TIER_GOLD => 2.0,
            self::TIER_SILVER => 1.5,
            default => 1.0, // Reguler
        };
    }

    /**
     * Point Calculation Engine:
     * Base Formula: (Transaction Amount / 100,000) * 10
     * Multiplier: Match on Tier (1.0, 1.5, 2.0)
     */
    public function calculateEarnedPoints(float|int $amount): int
    {
        $baseFormula = ($amount / 100000) * 10;
        $multiplier = $this->getTierMultiplier();

        return (int) floor($baseFormula * $multiplier);
    }

    /**
     * Tier Calculation Logic (Thresholds):
     * Dijalankan setiap kali user memperoleh poin baru berdasarkan lifetime_points:
     * >= 1000           -> Gold
     * >= 500 AND <= 999 -> Silver
     * >= 0 AND <= 499   -> Reguler
     */
    public function updateTier(): string
    {
        $oldTier = $this->tier ?? self::TIER_REGULER;
        $newTier = self::TIER_REGULER;

        $lp = (int) ($this->lifetime_points ?? 0);

        if ($lp >= 1000) {
            $newTier = self::TIER_GOLD;
        } elseif ($lp >= 500) {
            $newTier = self::TIER_SILVER;
        } else {
            $newTier = self::TIER_REGULER;
        }

        if ($oldTier !== $newTier || blank($this->tier)) {
            $this->forceFill(['tier' => $newTier])->save();

            // Notifikasi jika naik tier
            if ($oldTier !== $newTier && in_array($newTier, [self::TIER_SILVER, self::TIER_GOLD], true)) {
                $this->notify(new \App\Notifications\LoyaltyActivityNotification(
                    'Membership Tier Upgrade',
                    "Selamat! Status keanggotaan Anda telah naik ke level {$newTier} Class dengan hak istimewa diskon {$this->discount_percentage}%.",
                    'tier_upgrade',
                    route('dashboard')
                ));
            }
        }

        return $newTier;
    }

    /**
     * Menambahkan poin dan mencatat mutasi ke tabel Point Ledger.
     * Database Injection: Update BOTH columns:
     * points = points + calculated_points
     * lifetime_points = lifetime_points + calculated_points
     */
    public function addPoints(int $amount, string $type, string $description, mixed $reference = null): PointLedger
    {
        return DB::transaction(function () use ($amount, $type, $description, $reference) {
            $balanceBefore = (int) $this->points;
            $balanceAfter = $balanceBefore + $amount;

            $this->points = $balanceAfter;
            $this->lifetime_points = (int) ($this->lifetime_points ?? 0) + $amount;

            if (! $this->points_expire_at || $this->points_expire_at->isPast()) {
                $this->points_expire_at = now()->endOfYear()->toDateString();
            }
            $this->save();

            $ledger = PointLedger::create([
                'user_id' => $this->id,
                'type' => $type,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_id' => $reference?->id ?? (is_numeric($reference) ? $reference : null),
                'reference_type' => is_object($reference) ? get_class($reference) : null,
                'description' => $description,
            ]);

            // Jalankan pengecekan tier setiap kali user memperoleh poin baru
            $this->updateTier();

            return $ledger;
        });
    }

    /**
     * Redemption Engine (Point Deduction):
     * Memotong saldo poin belanja aktif (points) dan mencatat mutasi ke tabel Point Ledger.
     * PENTING: JANGAN PERNAH menyentuh lifetime_points agar tier member tetap aman
     * meskipun saldo poin aktifnya turun menjadi 0.
     */
    public function deductPoints(int $amount, string $type, string $description, mixed $reference = null): bool
    {
        if ($this->points < $amount) {
            return false;
        }

        DB::transaction(function () use ($amount, $type, $description, $reference) {
            $balanceBefore = (int) $this->points;
            $balanceAfter = $balanceBefore - $amount;

            $this->points = $balanceAfter;
            // DO NOT touch lifetime_points!
            $this->save();

            PointLedger::create([
                'user_id' => $this->id,
                'type' => $type,
                'amount' => -$amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_id' => $reference?->id ?? (is_numeric($reference) ? $reference : null),
                'reference_type' => is_object($reference) ? get_class($reference) : null,
                'description' => $description,
            ]);

            // DO NOT touch tier on point deduction
        });

        return true;
    }

    public function ensureQrToken(): void
    {
        if (blank($this->qr_token)) {
            $this->forceFill(['qr_token' => (string) Str::uuid()])->save();
            $this->refresh();
        }
    }

    public function cashbackRate(): float
    {
        return match ($this->tier) {
            'Gold' => 0.03,
            'Silver' => 0.02,
            default => 0.01,
        };
    }

    public function creditCashback(float|int $amount): float
    {
        $cashback = round($amount * $this->cashbackRate(), 2);

        if ($cashback > 0) {
            $this->increment('cashback_balance', $cashback);
        }

        return $cashback;
    }

    public function isBirthdayToday(): bool
    {
        return $this->birth_date instanceof Carbon
            ? $this->birth_date->format('m-d') === now()->format('m-d')
            : false;
    }

    public function canReceiveBirthdayReward(): bool
    {
        if (! $this->birth_date) {
            return false;
        }

        if (! $this->isBirthdayToday()) {
            return false;
        }

        return ! $this->birthday_rewarded_at || ! $this->birthday_rewarded_at->isSameYear(now());
    }

    public function grantBirthdayReward(): ?array
    {
        if (! $this->canReceiveBirthdayReward()) {
            return null;
        }

        $bonusPoints = 250;
        $bonusCashback = 25.00;

        $this->addPoints($bonusPoints, 'birthday_bonus', 'Bonus Poin Ulang Tahun Member Tahunan');
        $this->increment('cashback_balance', $bonusCashback);
        $this->forceFill(['birthday_rewarded_at' => now()])->save();
        $this->refresh();

        return [
            'points' => $bonusPoints,
            'cashback' => $bonusCashback,
        ];
    }
}