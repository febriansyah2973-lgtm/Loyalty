<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Voucher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MemberQrController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $user->ensureQrToken();

        return view('qr-scan');
    }

    public function scan(Request $request): JsonResponse
    {
        $data = $request->validate([
            'payload' => ['required', 'string', 'max:1000'],
        ]);

        $token = $this->extractToken($data['payload']);

        if (blank($token)) {
            return response()->json([
                'message' => 'QR code tidak valid.',
            ], 422);
        }

        $member = User::where('qr_token', $token)->first();

        if (! $member) {
            return response()->json([
                'message' => 'Member tidak ditemukan.',
            ], 404);
        }

        $activeVouchers = $member->vouchers()
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->get()
            ->map(fn ($v) => [
                'id' => $v->id,
                'code' => $v->code,
                'title' => $v->title,
                'category' => $v->resolvedCategory(),
                'category_label' => $v->categoryLabel(),
                'category_badge' => $v->categoryBadgeClass(),
                'discount_amount' => (float) ($v->discount_amount ?? 0),
                'expires_at' => $v->expires_at->format('d M Y'),
                'expires_human' => $v->expires_at->diffForHumans(),
            ]);

        return response()->json([
            'message' => 'QR code berhasil dipindai.',
            'member' => [
                'id' => $member->id,
                'name' => $member->name,
                'email' => $member->email,
                'tier' => $member->tier,
                'points' => $member->points,
                'lifetime_points' => $member->lifetime_points,
                'points_expire_at' => $member->points_expire_at ? $member->points_expire_at->format('d M Y') : '31 Des ' . now()->format('Y'),
                'cashback_balance' => (float) $member->cashback_balance,
                'qr_token' => $member->qr_token,
                'vouchers' => $activeVouchers,
            ],
        ]);
    }

    /**
     * Alur Kasir: Menandai voucher digunakan secara langsung di kasir
     * saat pelanggan menukarkan voucher di toko.
     */
    public function redeemVoucher(Request $request, Voucher $voucher): JsonResponse
    {
        if (!Auth::user()->hasRole(['admin', 'manager'])) {
            return response()->json(['message' => 'Akses ditolak. Khusus staf kasir / manager.'], 403);
        }

        if ($voucher->used_at) {
            return response()->json(['message' => 'Voucher ini sudah pernah digunakan sebelumnya.'], 422);
        }

        if ($voucher->isExpired()) {
            return response()->json(['message' => 'Voucher ini telah kedaluwarsa.'], 422);
        }

        $voucher->update(['used_at' => now()]);

        // Kirim notifikasi ke pelanggan
        $voucher->user->notify(new \App\Notifications\LoyaltyActivityNotification(
            'Voucher Telah Digunakan di Kasir',
            "Voucher {$voucher->code} ({$voucher->title}) berhasil digunakan untuk transaksi belanja di toko/boutique.",
            'voucher_used',
            route('dashboard')
        ));

        return response()->json([
            'message' => "Voucher {$voucher->code} berhasil digunakan di kasir!",
            'voucher' => [
                'id' => $voucher->id,
                'code' => $voucher->code,
                'title' => $voucher->title,
                'used_at' => $voucher->used_at->format('d M Y, H:i'),
            ],
        ]);
    }

    private function extractToken(string $payload): ?string
    {
        $payload = trim($payload);

        if (Str::contains($payload, 'token=')) {
            parse_str(parse_url($payload, PHP_URL_QUERY) ?? '', $query);

            if (! empty($query['token'])) {
                return $query['token'];
            }
        }

        if (Str::contains($payload, ':')) {
            $parts = explode(':', $payload);

            return trim((string) end($parts));
        }

        return $payload;
    }
}