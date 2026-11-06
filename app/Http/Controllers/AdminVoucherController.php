<?php

namespace App\Http\Controllers;

use App\Models\Reward;
use App\Models\User;
use App\Models\Voucher;
use App\Notifications\LoyaltyActivityNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AdminVoucherController extends Controller
{
    /**
     * Display a listing of all vouchers with search, filter & stats.
     */
    public function index(Request $request)
    {
        if (!Auth::user()->hasRole(['admin', 'manager'])) {
            abort(403, 'Akses ditolak. Hanya untuk Administrator.');
        }

        // Stats
        $stats = [
            'total_vouchers' => Voucher::count(),
            'active_vouchers' => Voucher::whereNull('used_at')->where('expires_at', '>', now())->count(),
            'used_vouchers' => Voucher::whereNotNull('used_at')->count(),
            'expired_vouchers' => Voucher::whereNull('used_at')->where('expires_at', '<=', now())->count(),
        ];

        $query = Voucher::with(['user', 'reward']);

        // Search by voucher code or customer name / email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->whereNull('used_at')->where('expires_at', '>', now());
            } elseif ($request->status === 'used') {
                $query->whereNotNull('used_at');
            } elseif ($request->status === 'expired') {
                $query->whereNull('used_at')->where('expires_at', '<=', now());
            }
        }

        $vouchers = $query->latest()->paginate(15)->withQueryString();

        // Customer list for selection dropdown
        $users = User::whereHas('role', fn ($q) => $q->where('name', 'customer'))->orderBy('name')->get();

        // Active reward templates
        $rewards = Reward::orderBy('points_required')->get();

        return view('admin.vouchers.index', compact('vouchers', 'users', 'rewards', 'stats'));
    }

    /**
     * Store a specifically assigned voucher for 1 targeted user.
     */
    public function store(Request $request)
    {
        if (!Auth::user()->hasRole(['admin', 'manager'])) {
            abort(403);
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'code' => 'required|string|max:50|unique:vouchers,code',
            'voucher_type' => 'required|in:template,custom',
            'reward_id' => 'required_if:voucher_type,template|nullable|exists:rewards,id',
            'custom_title' => 'required_if:voucher_type,custom|nullable|string|max:255',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
            'expiry_days' => 'nullable|integer|min:1|max:365',
            'custom_expires_at' => 'nullable|date|after:now',
        ]);

        $user = User::findOrFail($request->user_id);

        // Determine expiration timestamp
        if ($request->filled('custom_expires_at')) {
            $expiresAt = Carbon::parse($request->custom_expires_at)->endOfDay();
        } else {
            $days = (int) ($request->expiry_days ?: 14);
            $expiresAt = now()->addDays($days)->endOfDay();
        }

        $code = strtoupper(trim($request->code));

        if ($request->voucher_type === 'template') {
            $reward = Reward::findOrFail($request->reward_id);
            $voucher = Voucher::create([
                'user_id' => $user->id,
                'reward_id' => $reward->id,
                'title' => $reward->title,
                'discount_amount' => $request->filled('discount_amount') ? $request->discount_amount : null,
                'notes' => $request->notes,
                'code' => $code,
                'expires_at' => $expiresAt,
            ]);
            $voucherTitle = $reward->title;
        } else {
            $voucher = Voucher::create([
                'user_id' => $user->id,
                'reward_id' => null,
                'title' => $request->custom_title,
                'discount_amount' => $request->filled('discount_amount') ? $request->discount_amount : null,
                'notes' => $request->notes,
                'code' => $code,
                'expires_at' => $expiresAt,
            ]);
            $voucherTitle = $request->custom_title;
        }

        // Notify the specific target user
        $user->notify(new LoyaltyActivityNotification(
            'Special Voucher Received',
            sprintf('Anda menerima voucher khusus: "%s" dengan kode %s (Berlaku s/d %s). Cek dompet voucher Anda!', $voucherTitle, $voucher->code, $expiresAt->format('d M Y')),
            'voucher',
            route('dashboard')
        ));

        return redirect()->route('admin.vouchers.index')
            ->with('success', "Voucher {$voucher->code} berhasil diterbitkan khusus untuk {$user->name}!");
    }

    /**
     * Toggle voucher used / unused status.
     */
    public function toggleUsed($id)
    {
        if (!Auth::user()->hasRole(['admin', 'manager'])) {
            abort(403);
        }

        $voucher = Voucher::findOrFail($id);

        if ($voucher->used_at) {
            $voucher->update(['used_at' => null]);
            $msg = "Status voucher {$voucher->code} diubah menjadi Aktif / Belum Terpakai.";
        } else {
            $voucher->update(['used_at' => now()]);
            $msg = "Voucher {$voucher->code} berhasil ditandai sebagai Sudah Terpakai.";
        }

        return back()->with('success', $msg);
    }

    /**
     * Delete / revoke a voucher.
     */
    public function destroy($id)
    {
        if (!Auth::user()->hasRole(['admin', 'manager'])) {
            abort(403);
        }

        $voucher = Voucher::findOrFail($id);
        $code = $voucher->code;
        $voucher->delete();

        return back()->with('success', "Voucher {$code} berhasil dihapus.");
    }
}
