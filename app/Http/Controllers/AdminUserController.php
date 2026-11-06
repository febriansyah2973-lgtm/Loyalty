<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    /**
     * Tampilkan daftar seluruh pengguna dan staff
     */
    public function index(Request $request): View
    {
        $roles = Role::all();

        $query = User::with('role')->latest();

        // Search name / email
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter Role
        if ($roleFilter = $request->input('role_id')) {
            $query->where('role_id', $roleFilter);
        }

        $users = $query->paginate(15)->withQueryString();

        // Statistics
        $totalAdmins = User::whereHas('role', fn ($q) => $q->where('name', 'admin'))->count();
        $totalManagers = User::whereHas('role', fn ($q) => $q->where('name', 'manager'))->count();
        $totalCustomers = User::whereHas('role', fn ($q) => $q->where('name', 'customer'))->count();

        return view('admin.users.index', compact(
            'users',
            'roles',
            'totalAdmins',
            'totalManagers',
            'totalCustomers'
        ));
    }

    /**
     * Tambah akun staff/admin/member baru
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'min:12'],
            'role_id' => ['required', 'exists:roles,id'],
        ], [
            'name.required' => 'Nama pengguna wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.unique' => 'Email ini sudah terdaftar oleh pengguna lain.',
            'password.required' => 'Password wajib diisi minimal 12 karakter.',
            'password.min' => 'Password minimal 12 karakter.',
            'role_id.required' => 'Pilih peran / role pengguna.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $validated['role_id'],
            'email_verified_at' => now(),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', "Akun \"{$user->name}\" ({$user->role?->display_name}) berhasil dibuat.");
    }

    /**
     * Perbarui role pengguna
     */
    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        // Mencegah admin mendegradasi akunnya sendiri jika dia admin yang sedang login
        if ($user->id === auth()->id() && (int) $validated['role_id'] !== (int) $user->role_id) {
            return back()->with('error', 'Anda tidak dapat mengubah role akun Anda sendiri saat ini.');
        }

        if ($this->isProtectedSuperAdmin($user) && Role::find($validated['role_id'])?->name !== 'admin') {
            return back()->with('error', 'Role Super Administrator tidak dapat diturunkan.');
        }

        $user->role_id = $validated['role_id'];
        $user->save();

        return back()->with('success', "Role untuk {$user->name} berhasil diperbarui menjadi {$user->fresh()->role?->display_name}.");
    }

    /**
     * Hapus pengguna
     */
    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        if ($this->isProtectedSuperAdmin($user)) {
            return back()->with('error', 'Akun Super Administrator tidak dapat dihapus.');
        }

        $userName = $user->name;
        $user->delete();

        return back()->with('success', "Akun \"{$userName}\" berhasil dihapus.");
    }

    private function isProtectedSuperAdmin(User $user): bool
    {
        return filled(config('super-admin.email'))
            && strcasecmp($user->email, config('super-admin.email')) === 0;
    }
}
