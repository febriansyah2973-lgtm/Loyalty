<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyClaim extends Model
{
    protected $fillable = [
        'user_id',
        'order_number',
        'amount',
        'proof_path',
        'proof_hash',
        'fraud_risk_score',
        'fraud_flags',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fraud_risk_score' => 'integer',
        'fraud_flags' => 'array',
    ];

    /**
     * Relasi ke User (Satu klaim dimiliki oleh satu user)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reward(): BelongsTo
    {
        return $this->belongsTo(Reward::class);
    }

    public function isHighRisk(): bool
    {
        return $this->fraud_risk_score >= 60;
    }

    public function isMediumRisk(): bool
    {
        return $this->fraud_risk_score >= 30 && $this->fraud_risk_score < 60;
    }

    public function getRiskLevelName(): string
    {
        if ($this->isHighRisk()) {
            return 'High Risk (Waspada)';
        }
        if ($this->isMediumRisk()) {
            return 'Medium Risk (Review)';
        }
        return 'Low Risk (Aman)';
    }

    public function getRiskBadgeClass(): string
    {
        if ($this->isHighRisk()) {
            return 'bg-red-50 text-red-700 border-red-200';
        }
        if ($this->isMediumRisk()) {
            return 'bg-amber-50 text-amber-700 border-amber-200';
        }
        return 'bg-green-50 text-green-700 border-green-200';
    }
}
