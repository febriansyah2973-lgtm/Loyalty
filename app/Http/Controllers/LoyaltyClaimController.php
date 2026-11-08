<?php

namespace App\Http\Controllers;

use App\Models\LoyaltyClaim;
use App\Models\Reward;
use App\Models\Voucher;
use App\Models\User;
use App\Notifications\LoyaltyActivityNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use App\Services\ReceiptValidationService;

class LoyaltyClaimController extends Controller
{
    protected ReceiptValidationService $receiptValidator;

    public function __construct(ReceiptValidationService $receiptValidator)
    {
        $this->receiptValidator = $receiptValidator;
    }

    /**
     * Menampilkan form klaim poin untuk user.
     */
    public function create()
    {
        return view('claims.create');
    }

    /**
     * Menyimpan data klaim baru dari user ke database dengan audit fraud struk otomatis.
     */
    public function store(Request $request)
    {
        $request->validate([
            'order_number' => 'required|unique:loyalty_claims,order_number',
            'amount'       => 'required|numeric|min:1',
            'proof_image'  => 'required|image|mimes:jpg,png,jpeg|max:2048',
        ]);

        $file = $request->file('proof_image');
        $path = $file->store('proofs', 'public');

        // Audit & Fraud Detection Struk Otomatis
        $fraudInspection = $this->receiptValidator->inspectReceipt(
            $file,
            $request->order_number,
            (float) $request->amount,
            Auth::id()
        );

        $claim = LoyaltyClaim::create([
            'user_id'          => Auth::id(),
            'order_number'     => $request->order_number,
            'amount'           => $request->amount,
            'proof_path'       => $path,
            'proof_hash'       => $fraudInspection['proof_hash'],
            'fraud_risk_score' => $fraudInspection['fraud_risk_score'],
            'fraud_flags'      => $fraudInspection['fraud_flags'],
            'status'           => 'pending',
        ]);

        // Notifikasi ke staf admin & manager
        User::whereHas('role', fn ($q) => $q->whereIn('name', ['admin', 'manager']))
            ->get()
            ->each(function (User $admin) use ($request, $fraudInspection) {
                $riskWarning = $fraudInspection['fraud_risk_score'] >= 60 ? ' [RISIKO TINGGI DETEKSI FRAUD]' : '';
                $admin->notify(new LoyaltyActivityNotification(
                    'Pengajuan Klaim Belanja Baru' . $riskWarning,
                    'Klaim baru untuk transaksi #' . $request->order_number . ' menunggu verifikasi (Skor Risiko: ' . $fraudInspection['fraud_risk_score'] . '%).',
                    'claim',
                    route('admin.claims.index')
                ));
            });

        return redirect()->route('dashboard')->with('success', 'Klaim berhasil dikirim! Admin akan segera memverifikasi.');
    }

    /**
     * Menampilkan daftar klaim untuk Admin dilengkapi dengan fitur Pencarian,
     * Deteksi Fraud Struk, dan Statistik Pengambilan Keputusan.
     */
    public function adminIndex(Request $request)
    {
        if (!Auth::user()->hasRole(['admin', 'manager'])) {
            abort(403, 'Akses ditolak. Khusus staf operasional.');
        }

        // Statistik Dashboard Admin
        $stats = [
            'total_pending'          => LoyaltyClaim::where('status', 'pending')->count(),
            'vip_pending'            => LoyaltyClaim::where('status', 'pending')
                                          ->whereHas('user', function($q) {
                                              $q->whereIn('tier', ['Silver', 'Gold']);
                                          })->count(),
            'high_risk_pending'      => LoyaltyClaim::where('status', 'pending')->where('fraud_risk_score', '>=', 60)->count(),
            'total_sales_verified'   => LoyaltyClaim::where('status', 'approved')->sum('amount'),
            'total_cashback_balance' => User::sum('cashback_balance'),
        ];

        // Query Utama dengan Eager Loading untuk performa
        $query = LoyaltyClaim::with('user');

        // Fitur Pencarian Cepat (Berdasarkan Order Number atau Nama User)
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $claims = $query->latest()->paginate(10);
        
        return view('claims.index', compact('claims', 'stats'));
    }

    /**
     * Proses persetujuan klaim oleh Admin:
     * Menghitung poin via Point Calculation Engine (Base Formula * Tier Multiplier),
     * menginjeksi points & lifetime_points, dan memperbarui tier member.
     */
    public function approve($id)
    {
        if (!Auth::user()->hasRole(['admin', 'manager'])) {
            abort(403);
        }

        $claim = LoyaltyClaim::findOrFail($id);
        
        if ($claim->status !== 'pending') {
            return back()->with('error', 'Klaim ini sudah diproses.');
        }

        $earnedPoints = 0;
        $cashback = 0.0;

        DB::transaction(function () use ($claim, &$earnedPoints, &$cashback) {
            // Point Calculation Engine (Earning Points):
            // Base Formula: (Transaction Amount / 100,000) * 10
            // Multiplier Logic (Match on Tier): Reguler 1.0, Silver 1.5, Gold 2.0
            $earnedPoints = $claim->user->calculateEarnedPoints($claim->amount);
            $cashback = $claim->user->creditCashback($claim->amount);

            // Database Injection: Update BOTH points & lifetime_points, and evaluate tier upgrade
            $claim->user->addPoints(
                $earnedPoints,
                'earn_claim',
                "Persetujuan klaim transaksi #{$claim->order_number} (Rp " . number_format($claim->amount, 0, ',', '.') . ")",
                $claim
            );

            // Tandai status klaim
            $claim->update(['status' => 'approved']);
        });

        $claim->user->notify(new LoyaltyActivityNotification(
            'Purchase claim approved',
            sprintf(
                'Claim #%s approved. You received %d points and Rp %s cashback.',
                $claim->order_number,
                $earnedPoints,
                number_format($cashback, 2, ',', '.')
            ),
            'success',
            route('dashboard')
        ));

        return back()->with('success', "Klaim disetujui! User menerima {$earnedPoints} poin (tercatat di Point Ledger) dan cashback Rp " . number_format($cashback, 2, ',', '.'));
    }

    /**
     * Proses penukaran poin (Redeem) oleh User menjadi Voucher
     * dengan pemotongan poin tercatat di Point Ledger.
     */
    public function redeem($id)
    {
        $reward = Reward::findOrFail($id);
        $user = Auth::user();

        // Cek kecukupan poin
        if ($user->points < $reward->points_required) {
            return back()->with('error', 'Poin Anda tidak mencukupi untuk item ini.');
        }

        if ($reward->stock <= 0) {
            return back()->with('error', 'Hadiah ini sedang habis.');
        }

        // Cek syarat tingkatan tier membership
        if ($reward->tier_required === 'Gold' && $user->tier !== 'Gold') {
            return back()->with('error', 'Item ini adalah hak istimewa eksklusif khusus untuk member Gold Tier.');
        }

        if ($reward->tier_required === 'Silver' && !in_array($user->tier, ['Silver', 'Gold'], true)) {
            return back()->with('error', 'Item ini adalah hak istimewa eksklusif untuk member Silver dan Gold Tier.');
        }

        DB::transaction(function () use ($user, $reward) {
            // Potong poin melalui Point Ledger (lifetime_points tetap utuh)
            $user->deductPoints(
                $reward->points_required,
                'redeem_voucher',
                "Penukaran hadiah katalog: {$reward->title}",
                $reward
            );

            $reward->decrement('stock');

            // Penerbitan Voucher ke Wallet User
            Voucher::create([
                'user_id'    => $user->id,
                'reward_id'  => $reward->id,
                'title'      => $reward->title,
                'category'   => $reward->category ?? 'general',
                'code'       => 'KHN-' . strtoupper(Str::random(8)),
                'expires_at' => now()->addDays(14), // Masa berlaku voucher
            ]);
        });

        $user->notify(new LoyaltyActivityNotification(
            'Reward redeemed',
            'Your reward has been converted into a voucher and added to your wallet.',
            'voucher',
            route('dashboard')
        ));

        return back()->with('success', 'Berhasil! Poin telah ditukar dan voucher eksklusif telah ditambahkan ke dompet Anda.');
    }
}