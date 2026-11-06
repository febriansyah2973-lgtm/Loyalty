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

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'points',
        'tier',
        'points_expire_at',
        'birth_date',
        'cashback_balance',
        'birthday_rewarded_at',
        'qr_token',
    ];

    protected $casts = [
        'birth_date' => 'date',
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
                $user->tier = 'Classic';
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
            'Gold' => 15,
            'Silver' => 5,
            default => 0,
        };
    }

    /**
     * Memperbarui kolom level/tier pelanggan di database secara permanen
     * sesuai Activity Diagram "Update Level/Tier Pelanggan".
     */
    public function updateTier(): string
    {
        $oldTier = $this->tier ?? 'Classic';
        $newTier = 'Classic';

        if ($this->points >= 5000) {
            $newTier = 'Gold';
        } elseif ($this->points >= 1000) {
            $newTier = 'Silver';
        }

        if ($oldTier !== $newTier || blank($this->tier)) {
            $this->forceFill(['tier' => $newTier])->save();

            // Notifikasi jika naik tier
            if ($oldTier !== $newTier && in_array($newTier, ['Silver', 'Gold'])) {
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
     */
    public function addPoints(int $amount, string $type, string $description, mixed $reference = null): PointLedger
    {
        return DB::transaction(function () use ($amount, $type, $description, $reference) {
            $balanceBefore = (int) $this->points;
            $balanceAfter = $balanceBefore + $amount;

            $this->points = $balanceAfter;
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

            $this->updateTier();

            return $ledger;
        });
    }

    /**
     * Memotong poin dan mencatat mutasi keluar ke tabel Point Ledger.
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

            $this->updateTier();
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