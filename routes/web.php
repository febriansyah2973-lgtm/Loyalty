<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LoyaltyClaimController;
use App\Http\Controllers\MemberQrController;
use App\Notifications\LoyaltyActivityNotification;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();
        if ($user->isAdmin()) {
            return redirect()->route('admin.claims.index');
        }
        if ($user->isManager()) {
            return redirect()->route('qr.scan');
        }
        return redirect()->route('dashboard');
    }

    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Authenticated Customer Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    
    // Dashboard - Mengambil data klaim
    Route::get('/dashboard', function () {
        $user = auth()->user();
        $user->ensureQrToken();
        $birthdayReward = $user->grantBirthdayReward();

        if ($birthdayReward) {
            $user->notify(new LoyaltyActivityNotification(
                'Birthday reward unlocked',
                'Happy birthday. You received ' . $birthdayReward['points'] . ' points and Rp ' . number_format($birthdayReward['cashback'], 2, ',', '.') . ' cashback.',
                'birthday',
                route('dashboard')
            ));
        }

        $claims = $user->loyaltyClaims()->latest()->get();
        $notifications = $user->unreadNotifications()->latest()->take(5)->get();

        return view('dashboard', compact('claims', 'notifications', 'birthdayReward'));
    })->name('dashboard');

    // Fitur Klaim Poin
    Route::get('/claim', [LoyaltyClaimController::class, 'create'])->name('claims.create');
    Route::post('/claim', [LoyaltyClaimController::class, 'store'])->name('claims.store');
    
    // Fitur Tukar Point (Redeem)
    Route::post('/redeem/{id}', [LoyaltyClaimController::class, 'redeem'])->name('rewards.redeem');

    // QR Scan (Staff Tool)
    Route::get('/qr-scan', [MemberQrController::class, 'index'])->name('qr.scan');
    Route::post('/qr-scan', [MemberQrController::class, 'scan'])->name('qr.scan.submit');
    Route::post('/qr-scan/redeem-voucher/{voucher}', [MemberQrController::class, 'redeemVoucher'])
        ->name('qr.redeem-voucher')
        ->middleware('role:admin,manager');

    // Notifications
    Route::get('/notifications', function () {
        $notifications = auth()->user()->notifications()->latest()->paginate(12);
        return view('notifications.index', compact('notifications'));
    })->name('notifications.index');

    Route::post('/notifications/read-all', function () {
        auth()->user()->unreadNotifications->markAsRead();
        return response()->json(['message' => 'Notifications marked as read.']);
    })->name('notifications.read-all');

    Route::post('/notifications/{notification}', function (string $notification) {
        $item = auth()->user()->notifications()->where('id', $notification)->firstOrFail();
        $item->markAsRead();
        return back()->with('success', 'Notification marked as read.');
    })->name('notifications.read');

    // Profile Settings
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

use App\Http\Controllers\AdminVoucherController;
use App\Http\Controllers\AdminUserController;

/*
|--------------------------------------------------------------------------
| Admin & Operational Staff Routes (Protected by Role Middleware)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin,manager'])->prefix('admin')->name('admin.')->group(function () {
    // Claims verification
    Route::get('/claims', [LoyaltyClaimController::class, 'adminIndex'])->name('claims.index');
    Route::post('/claims/{id}/approve', [LoyaltyClaimController::class, 'approve'])->name('claims.approve');

    // Voucher management (issue specific vouchers to 1 user)
    Route::get('/vouchers', [AdminVoucherController::class, 'index'])->name('vouchers.index');
    Route::post('/vouchers', [AdminVoucherController::class, 'store'])->name('vouchers.store');
    Route::post('/vouchers/{id}/toggle-used', [AdminVoucherController::class, 'toggleUsed'])->name('vouchers.toggle-used');
    Route::delete('/vouchers/{id}', [AdminVoucherController::class, 'destroy'])->name('vouchers.destroy');
});

// Staff & User Management (Super Admin only)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::patch('/users/{user}/role', [AdminUserController::class, 'updateRole'])->name('users.update-role');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
});

require __DIR__.'/auth.php';