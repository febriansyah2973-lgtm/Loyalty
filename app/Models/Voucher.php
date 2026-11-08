<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Voucher extends Model
{
    protected $fillable = [
        'user_id',
        'reward_id',
        'title',
        'category',
        'discount_amount',
        'notes',
        'code',
        'expires_at',
        'used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
        'discount_amount' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reward(): BelongsTo
    {
        return $this->belongsTo(Reward::class);
    }

    public function getTitleAttribute($value): string
    {
        return $value ?: ($this->reward?->title ?? 'Exclusive Privilege');
    }

    public function resolvedCategory(): string
    {
        return $this->category && $this->category !== 'general'
            ? $this->category
            : ($this->reward?->category ?? 'general');
    }

    public function categoryLabel(): string
    {
        return match ($this->resolvedCategory()) {
            'service' => 'Layanan & Vermak',
            'invitation' => 'Undangan VIP',
            'privilege' => 'Privilege Access',
            'hampers' => 'Hampers & Gift',
            'product' => 'Produk Eksklusif',
            'discount' => 'Voucher Diskon',
            default => 'Benefit Keanggotaan',
        };
    }

    public function categoryBadgeClass(): string
    {
        return match ($this->resolvedCategory()) {
            'service' => 'bg-emerald-50 text-emerald-800 border-emerald-300',
            'invitation' => 'bg-purple-50 text-purple-800 border-purple-300',
            'privilege' => 'bg-blue-50 text-blue-800 border-blue-300',
            'hampers' => 'bg-amber-50 text-amber-900 border-amber-300',
            'product' => 'bg-stone-900 text-amber-400 border-black',
            'discount' => 'bg-stone-100 text-stone-800 border-stone-300',
            default => 'bg-stone-50 text-stone-700 border-stone-200',
        };
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isActive(): bool
    {
        return is_null($this->used_at) && ! $this->isExpired();
    }
}
