<?php

namespace App\Services;

use App\Models\LoyaltyClaim;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;

class ReceiptValidationService
{
    /**
     * Inspect receipt and calculate fraud risk score and flags.
     *
     * @return array{proof_hash: string, fraud_risk_score: int, fraud_flags: array<string>}
     */
    public function inspectReceipt(UploadedFile $file, string $orderNumber, float $amount, ?int $userId = null): array
    {
        $flags = [];
        $riskScore = 0;

        // 1. Calculate file hash (SHA-256)
        $hash = hash_file('sha256', $file->getRealPath());

        // 2. Check for identical proof image uploaded previously
        $duplicateClaim = LoyaltyClaim::where('proof_hash', $hash)->first();
        if ($duplicateClaim) {
            $riskScore += 70;
            $flags[] = "DUPLICATE_IMAGE: Foto struk ini terdeteksi 100% identik dengan klaim #{$duplicateClaim->order_number} yang diunggah sebelumnya.";
        }

        // 3. Check for order number format & pattern
        $cleanOrder = trim($orderNumber);
        if (strlen($cleanOrder) < 4) {
            $riskScore += 20;
            $flags[] = 'SHORT_ORDER_NUMBER: Nomor struk terlalu pendek / tidak sesuai format POS.';
        }

        // 4. Unusual Amount Checks (> Rp 25.000.000)
        if ($amount > 25000000) {
            $riskScore += 30;
            $flags[] = 'HIGH_VALUE_TRANSACTION: Nilai transaksi sangat tinggi (> Rp 25 Juta), memerlukan verifikasi ganda kasir / supervisor.';
        } elseif ($amount < 10000) {
            $riskScore += 10;
            $flags[] = 'LOW_VALUE_TRANSACTION: Nilai transaksi di bawah batas minimum kewajaran.';
        }

        // 5. Rapid Multi-Claim Submission Check (Spamming fraud check)
        if ($userId) {
            $recentClaimsCount = LoyaltyClaim::where('user_id', $userId)
                ->where('created_at', '>=', now()->subMinutes(10))
                ->count();

            if ($recentClaimsCount >= 3) {
                $riskScore += 35;
                $flags[] = "RAPID_SUBMISSION: Member mengajukan {$recentClaimsCount} klaim dalam waktu 10 menit terakhir.";
            }
        }

        // 6. POS Simulated Text / Format Check (Mock OCR Engine)
        $simulatedOcr = $this->simulateOcrAnalysis($file, $orderNumber, $amount);
        if (!empty($simulatedOcr['flags'])) {
            $flags = array_merge($flags, $simulatedOcr['flags']);
            $riskScore += $simulatedOcr['risk_addition'];
        }

        // Cap risk score at 100
        $riskScore = min(100, $riskScore);

        return [
            'proof_hash' => $hash,
            'fraud_risk_score' => $riskScore,
            'fraud_flags' => $flags,
        ];
    }

    /**
     * Simulated OCR Engine to emulate AI / POS receipt parsing.
     *
     * @return array{flags: array<string>, risk_addition: int}
     */
    private function simulateOcrAnalysis(UploadedFile $file, string $orderNumber, float $amount): array
    {
        $flags = [];
        $riskAddition = 0;

        $mime = $file->getMimeType();
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'])) {
            $riskAddition += 40;
            $flags[] = "SUSPICIOUS_MIME: Format file struk bukan citra standar ({$mime}).";
        }

        // Check image file size (< 20 KB often indicates a blank or fake screenshot)
        if ($file->getSize() < 20480) {
            $riskAddition += 25;
            $flags[] = 'LOW_RESOLUTION_PROOF: Ukuran gambar sangat kecil (< 20 KB), indikasi gambar thumbnail atau palsu.';
        }

        return [
            'flags' => $flags,
            'risk_addition' => $riskAddition,
        ];
    }
}
