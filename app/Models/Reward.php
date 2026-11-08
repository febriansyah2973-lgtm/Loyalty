<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reward extends Model
{
    protected $fillable = [
        'title',
        'category',
        'description',
        'points_required',
        'tier_required',
        'badge_text',
        'stock',
    ];

    public function vouchers(): HasMany
    {
        return $this->hasMany(Voucher::class);
    }

    public function categoryLabel(): string
    {
        return match ($this->category) {
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
        return match ($this->category) {
            'service' => 'bg-emerald-50 text-emerald-800 border-emerald-300',
            'invitation' => 'bg-purple-50 text-purple-800 border-purple-300',
            'privilege' => 'bg-blue-50 text-blue-800 border-blue-300',
            'hampers' => 'bg-amber-50 text-amber-900 border-amber-300',
            'product' => 'bg-stone-900 text-amber-400 border-black',
            'discount' => 'bg-stone-100 text-stone-800 border-stone-300',
            default => 'bg-stone-50 text-stone-700 border-stone-200',
        };
    }
}
