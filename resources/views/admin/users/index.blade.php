<x-app-layout>
    <div class="py-12 bg-[#FCFCFC] min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" x-data="{ 
            showCreateModal: false,
            showEditModal: false,
            editUser: {
                id: null,
                name: '',
                email: '',
                role_id: '',
                points: 0,
                lifetime_points: 0,
                tier: 'Reguler'
            },
            openEdit(u) {
                this.editUser = { ...u };
                this.showEditModal = true;
            }
        }">
            
            <!-- HEADER -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-10 gap-6">
                <div>
                    <h2 class="khanaan-font-serif text-4xl text-black italic">Staff & User Management</h2>
                    <p class="text-[10px] tracking-[0.3em] text-gray-400 uppercase mt-2">Kelola Akun Administrator, Kasir Boutique, & Member Loyalty</p>
                </div>
                
                <div class="flex flex-wrap items-center gap-3">
                    <button @click="showCreateModal = !showCreateModal" 
                            class="bg-black text-white text-[10px] tracking-[0.2em] uppercase font-bold px-6 py-3 hover:bg-amber-600 transition flex items-center gap-2 shadow-sm">
                        <span class="text-amber-400 text-sm font-bold">+</span> Tambah Staff / Admin Baru
                    </button>
                    <a href="{{ route('admin.claims.index') }}" class="text-[9px] tracking-[0.2em] uppercase font-bold border border-gray-300 text-gray-700 px-5 py-3 hover:border-black hover:text-black transition">
                        Verifikasi Klaim
                    </a>
                </div>
            </div>

            <!-- FLASH NOTIFICATIONS -->
            @if(session('success'))
            <div class="mb-8 p-4 bg-black text-white text-xs tracking-wider flex items-center justify-between border-l-4 border-amber-400 shadow-md">
                <div class="flex items-center gap-3">
                    <span class="text-amber-400 text-base">✦</span>
                    <span>{{ session('success') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-gray-400 hover:text-white text-sm font-bold px-2">&times;</button>
            </div>
            @endif

            @if(session('error'))
            <div class="mb-8 p-4 bg-red-50 border-l-4 border-red-500 text-red-800 text-xs shadow-sm flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="text-red-500 font-bold">✕</span>
                    <span>{{ session('error') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-red-400 hover:text-red-700 text-sm font-bold px-2">&times;</button>
            </div>
            @endif

            @if($errors->any())
            <div class="mb-8 p-4 bg-red-50 border border-red-200 text-red-700 text-xs shadow-sm">
                <p class="font-bold uppercase tracking-wider mb-2">Terdapat Kesalahan Pengisian:</p>
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- STATS CARDS -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 mb-10">
                <div class="bg-black p-6 text-white border border-gray-800 shadow-lg">
                    <p class="text-[9px] tracking-[0.3em] uppercase opacity-50 mb-1">Total Admin</p>
                    <h4 class="khanaan-font-serif text-3xl text-amber-400">{{ $totalAdmins }}</h4>
                    <p class="text-[10px] text-gray-400 mt-2 tracking-widest uppercase">Hak Akses Penuh Sistem</p>
                </div>

                <div class="bg-white p-6 border border-gray-100 shadow-sm">
                    <p class="text-[9px] tracking-[0.3em] text-blue-600 font-bold uppercase mb-1">Kasir / Store Manager</p>
                    <h4 class="khanaan-font-serif text-3xl text-black">{{ $totalManagers }}</h4>
                    <p class="text-[10px] text-gray-400 mt-2 tracking-widest uppercase font-semibold">Operasional Scan QR</p>
                </div>

                <div class="bg-white p-6 border border-gray-100 shadow-sm">
                    <p class="text-[9px] tracking-[0.3em] text-stone-500 uppercase mb-1">Member / Customer</p>
                    <h4 class="khanaan-font-serif text-3xl text-black">{{ $totalCustomers }}</h4>
                    <p class="text-[10px] text-gray-400 mt-2 tracking-widest uppercase font-semibold">Pelanggan Terdaftar</p>
                </div>

                <div class="bg-white p-6 border border-gray-100 shadow-sm">
                    <p class="text-[9px] tracking-[0.3em] text-stone-400 uppercase mb-1">Total Pengguna</p>
                    <h4 class="khanaan-font-serif text-3xl text-black">{{ $users->total() }}</h4>
                    <p class="text-[10px] text-gray-400 mt-2 tracking-widest uppercase font-semibold">Semua Kategori</p>
                </div>
            </div>

            <!-- FORM TAMBAH PENGGUNA (TOGGLEABLE) -->
            <div x-show="showCreateModal" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="mb-12 bg-white border border-stone-200 shadow-xl p-8"
                 style="display: none;">
                <div class="flex justify-between items-center pb-4 mb-6 border-b border-stone-100">
                    <div>
                        <h3 class="khanaan-font-serif text-2xl text-black">Tambah Staff / Admin Baru</h3>
                        <p class="text-xs text-stone-400">Buat akun untuk Admin kedua, Kasir cabang, atau staff operasional lainnya</p>
                    </div>
                    <button @click="showCreateModal = false" class="text-stone-400 hover:text-black text-xl font-bold px-2">&times;</button>
                </div>

                <form method="POST" action="{{ route('admin.users.store') }}">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div>
                            <label class="block text-[10px] uppercase tracking-wider text-stone-500 font-bold mb-2">Nama Lengkap</label>
                            <input type="text" name="name" required placeholder="Contoh: Sarah Boutique Admin" 
                                   value="{{ old('name') }}"
                                   class="w-full bg-[#FAFAFA] border border-stone-200 px-4 py-2.5 text-xs text-black focus:outline-none focus:border-black focus:ring-0">
                        </div>

                        <div>
                            <label class="block text-[10px] uppercase tracking-wider text-stone-500 font-bold mb-2">Email Address</label>
                            <input type="email" name="email" required placeholder="admin2@khanaan.com" 
                                   value="{{ old('email') }}"
                                   class="w-full bg-[#FAFAFA] border border-stone-200 px-4 py-2.5 text-xs text-black focus:outline-none focus:border-black focus:ring-0">
                        </div>

                        <div>
                            <label class="block text-[10px] uppercase tracking-wider text-stone-500 font-bold mb-2">Password Awal</label>
                            <input type="password" name="password" required minlength="8" placeholder="Minimal 8 karakter" 
                                   class="w-full bg-[#FAFAFA] border border-stone-200 px-4 py-2.5 text-xs text-black focus:outline-none focus:border-black focus:ring-0">
                        </div>

                        <div>
                            <label class="block text-[10px] uppercase tracking-wider text-stone-500 font-bold mb-2">Peran (Role)</label>
                            <select name="role_id" required class="w-full bg-[#FAFAFA] border border-stone-200 px-4 py-2.5 text-xs text-black focus:outline-none focus:border-black focus:ring-0">
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" {{ old('role_id', $roles->firstWhere('name', 'admin')?->id) == $role->id ? 'selected' : '' }}>
                                        {{ $role->display_name }} ({{ strtoupper($role->name) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-stone-100">
                        <button type="button" @click="showCreateModal = false" class="px-5 py-2.5 border border-stone-300 text-[10px] uppercase tracking-wider text-stone-600 hover:text-black">
                            Batal
                        </button>
                        <button type="submit" class="bg-black text-white text-[10px] uppercase tracking-widest font-bold px-8 py-2.5 hover:bg-amber-600 transition shadow">
                            Simpan & Terbitkan Akun
                        </button>
                    </div>
                </form>
            </div>

            <!-- MODAL EDIT PENGGUNA & GANTI PASSWORD (TOGGLEABLE) -->
            <div x-show="showEditModal" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="mb-12 bg-white border-2 border-amber-400 shadow-2xl p-8"
                 style="display: none;">
                <div class="flex justify-between items-center pb-4 mb-6 border-b border-stone-100">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-amber-500 font-bold">✦</span>
                            <h3 class="khanaan-font-serif text-2xl text-black">Edit Data Pengguna & Ganti Password</h3>
                        </div>
                        <p class="text-xs text-stone-400 mt-1">Perbarui profil pengguna, ganti password akun, ubah role, dan kelola saldo poin</p>
                    </div>
                    <button @click="showEditModal = false" class="text-stone-400 hover:text-black text-xl font-bold px-2">&times;</button>
                </div>

                <form method="POST" :action="'{{ url('admin/users') }}/' + editUser.id">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div>
                            <label class="block text-[10px] uppercase tracking-wider text-stone-500 font-bold mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input type="text" name="name" x-model="editUser.name" required
                                   class="w-full bg-[#FAFAFA] border border-stone-200 px-4 py-2.5 text-xs text-black focus:outline-none focus:border-black focus:ring-0">
                        </div>

                        <div>
                            <label class="block text-[10px] uppercase tracking-wider text-stone-500 font-bold mb-2">Email Address <span class="text-red-500">*</span></label>
                            <input type="email" name="email" x-model="editUser.email" required
                                   class="w-full bg-[#FAFAFA] border border-stone-200 px-4 py-2.5 text-xs text-black focus:outline-none focus:border-black focus:ring-0">
                        </div>

                        <div>
                            <label class="block text-[10px] uppercase tracking-wider text-stone-500 font-bold mb-2">
                                Ganti Password Baru
                                <span class="text-[9px] text-amber-600 font-normal lowercase">(kosongkan jika tidak diubah)</span>
                            </label>
                            <input type="password" name="password" minlength="8" placeholder="Ketik password baru (min 8 char)" 
                                   class="w-full bg-[#FAFAFA] border border-amber-200 px-4 py-2.5 text-xs text-black focus:outline-none focus:border-black focus:ring-0">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 pt-4 border-t border-stone-100">
                        <div>
                            <label class="block text-[10px] uppercase tracking-wider text-stone-500 font-bold mb-2">Peran (Role) <span class="text-red-500">*</span></label>
                            <select name="role_id" x-model="editUser.role_id" required class="w-full bg-[#FAFAFA] border border-stone-200 px-4 py-2.5 text-xs text-black focus:outline-none focus:border-black focus:ring-0">
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}">
                                        {{ $role->display_name }} ({{ strtoupper($role->name) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-[10px] uppercase tracking-wider text-stone-500 font-bold mb-2">Membership Tier</label>
                            <select name="tier" x-model="editUser.tier" class="w-full bg-[#FAFAFA] border border-stone-200 px-4 py-2.5 text-xs text-black focus:outline-none focus:border-black focus:ring-0">
                                <option value="Reguler">Reguler (0 - 499 Pts)</option>
                                <option value="Silver">Silver (500 - 999 Pts)</option>
                                <option value="Gold">Gold (&ge; 1.000 Pts)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[10px] uppercase tracking-wider text-stone-500 font-bold mb-2">Saldo Poin Aktif</label>
                            <input type="number" name="points" x-model="editUser.points" min="0"
                                   class="w-full bg-[#FAFAFA] border border-stone-200 px-4 py-2.5 text-xs text-black focus:outline-none focus:border-black focus:ring-0">
                        </div>

                        <div>
                            <label class="block text-[10px] uppercase tracking-wider text-stone-500 font-bold mb-2">Lifetime Points (Akumulasi)</label>
                            <input type="number" name="lifetime_points" x-model="editUser.lifetime_points" min="0"
                                   class="w-full bg-[#FAFAFA] border border-stone-200 px-4 py-2.5 text-xs text-black focus:outline-none focus:border-black focus:ring-0">
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-stone-100">
                        <button type="button" @click="showEditModal = false" class="px-5 py-2.5 border border-stone-300 text-[10px] uppercase tracking-wider text-stone-600 hover:text-black">
                            Batal
                        </button>
                        <button type="submit" class="bg-black text-white text-[10px] uppercase tracking-widest font-bold px-8 py-2.5 hover:bg-amber-600 transition shadow">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>

            <!-- SEARCH & FILTER TOOLBAR -->
            <div class="bg-white border border-stone-100 p-6 mb-8 shadow-sm">
                <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="w-full md:w-1/2 flex items-center gap-3">
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Cari berdasarkan nama atau email..." 
                               class="w-full bg-[#FAFAFA] border border-stone-200 px-4 py-2.5 text-xs text-black focus:outline-none focus:border-black focus:ring-0">
                        <button type="submit" class="bg-black text-white text-[10px] uppercase tracking-wider font-bold px-5 py-2.5 hover:bg-stone-800 transition">
                            Cari
                        </button>
                    </div>

                    <div class="w-full md:w-auto flex items-center gap-3">
                        <label class="text-[10px] uppercase tracking-wider text-stone-400 font-bold">Filter Role:</label>
                        <select name="role_id" onchange="this.form.submit()" class="bg-[#FAFAFA] border border-stone-200 px-4 py-2.5 text-xs text-black focus:outline-none focus:border-black focus:ring-0">
                            <option value="">Semua Peran</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>
                                    {{ $role->display_name }}
                                </option>
                            @endforeach
                        </select>

                        @if(request('search') || request('role_id'))
                            <a href="{{ route('admin.users.index') }}" class="text-[10px] uppercase tracking-wider text-amber-600 hover:underline">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- TABLE DAFTAR PENGGUNA -->
            <div class="bg-white border border-stone-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-stone-100 flex justify-between items-center">
                    <h3 class="text-xs font-bold uppercase tracking-[0.2em] text-stone-700">Daftar Pengguna & Hak Akses</h3>
                    <span class="text-[10px] text-stone-400">Menampilkan {{ $users->count() }} dari {{ $users->total() }} akun</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-stone-50/60 border-b border-stone-100 text-[9px] uppercase tracking-[0.2em] text-stone-500">
                                <th class="py-4 px-6">Pengguna</th>
                                <th class="py-4 px-6">Peran / Role</th>
                                <th class="py-4 px-6">Ubah Hak Akses</th>
                                <th class="py-4 px-6">Membership & Poin</th>
                                <th class="py-4 px-6">Terdaftar</th>
                                <th class="py-4 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 text-xs">
                            @forelse($users as $user)
                            <tr class="hover:bg-stone-50/50 transition {{ $user->id === auth()->id() ? 'bg-amber-50/30' : '' }}">
                                <!-- User Info -->
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full flex items-center justify-center font-bold text-xs uppercase
                                            {{ $user->isAdmin() ? 'bg-amber-100 text-amber-800 border border-amber-300' : ($user->isManager() ? 'bg-blue-100 text-blue-800 border border-blue-300' : 'bg-stone-100 text-stone-700 border border-stone-200') }}">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <p class="font-bold text-stone-900">{{ $user->name }}</p>
                                                @if($user->id === auth()->id())
                                                    <span class="text-[8px] bg-amber-400 text-black px-1.5 py-0.5 rounded font-extrabold uppercase">Anda</span>
                                                @endif
                                            </div>
                                            <p class="text-[11px] text-stone-400">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Role Badge -->
                                <td class="py-4 px-6">
                                    @if($user->isAdmin())
                                        <span class="inline-flex items-center px-2.5 py-1 rounded text-[10px] font-bold uppercase tracking-wider bg-black text-amber-400 border border-black">
                                            Administrator
                                        </span>
                                    @elseif($user->isManager())
                                        <span class="inline-flex items-center px-2.5 py-1 rounded text-[10px] font-bold uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-200">
                                            Kasir / Store Manager
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded text-[10px] font-medium uppercase tracking-wider bg-stone-100 text-stone-600 border border-stone-200">
                                            Customer / Member
                                        </span>
                                    @endif
                                </td>

                                <!-- Form Ubah Role Cepat -->
                                <td class="py-4 px-6">
                                    @if($user->id === auth()->id())
                                        <span class="text-[10px] text-stone-400 italic">Akun aktif saat ini</span>
                                    @else
                                        <form method="POST" action="{{ route('admin.users.update-role', $user) }}" class="flex items-center gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <select name="role_id" class="bg-white border border-stone-200 text-[11px] py-1 px-2 focus:border-black focus:ring-0">
                                                @foreach($roles as $role)
                                                    <option value="{{ $role->id }}" {{ $user->role_id == $role->id ? 'selected' : '' }}>
                                                        {{ $role->display_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="text-[9px] uppercase tracking-wider bg-stone-200 hover:bg-black hover:text-white px-2.5 py-1 font-bold transition">
                                                Terapkan
                                            </button>
                                        </form>
                                    @endif
                                </td>

                                <!-- Membership Tier & Points -->
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-2">
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded
                                            {{ $user->tier === 'Gold' ? 'bg-amber-100 text-amber-800' : ($user->tier === 'Silver' ? 'bg-stone-200 text-stone-700' : 'bg-stone-100 text-stone-600') }}">
                                            {{ $user->tier ?? 'Reguler' }}
                                        </span>
                                        <span class="text-xs font-semibold text-stone-800">{{ number_format($user->points) }} Pts <span class="text-[10px] text-stone-400 font-normal">(LT: {{ number_format($user->lifetime_points) }})</span></span>
                                    </div>
                                </td>

                                <!-- Registered At -->
                                <td class="py-4 px-6 text-stone-500 text-[11px]">
                                    {{ $user->created_at ? $user->created_at->format('d M Y') : '-' }}
                                </td>

                                <!-- Action (Edit & Delete) -->
                                <td class="py-4 px-6 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-3">
                                        <button type="button" 
                                            @click="openEdit({
                                                id: {{ $user->id }},
                                                name: @js($user->name),
                                                email: @js($user->email),
                                                role_id: {{ $user->role_id ?? 'null' }},
                                                points: {{ (int) $user->points }},
                                                lifetime_points: {{ (int) $user->lifetime_points }},
                                                tier: @js($user->tier ?? 'Reguler')
                                            })"
                                            class="text-[10px] uppercase tracking-wider text-amber-700 hover:text-black font-bold hover:underline flex items-center gap-1 transition">
                                            <span>✏</span> Edit
                                        </button>

                                        @if($user->id !== auth()->id())
                                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun pengguna {{ $user->name }}? Tindakan ini tidak dapat dibatalkan.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-[10px] uppercase tracking-wider text-red-500 hover:text-red-700 font-bold hover:underline">
                                                    Hapus
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-stone-400">
                                    Tidak ada pengguna ditemukan dengan kriteria pencarian ini.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($users->hasPages())
                <div class="px-6 py-4 border-t border-stone-100">
                    {{ $users->links() }}
                </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
