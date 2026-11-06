<x-app-layout>
    <div class="py-12 bg-[#FCFCFC] min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- HEADER -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-10 gap-6">
                <div>
                    <h2 class="khanaan-font-serif text-4xl text-black italic">Voucher Management</h2>
                    <p class="text-[10px] tracking-[0.3em] text-gray-400 uppercase mt-2">Terbitkan & Kelola Kode Voucher Spesifik ke 1 Member</p>
                </div>
                
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.claims.index') }}" class="text-[9px] tracking-[0.2em] uppercase font-bold border border-gray-300 text-gray-700 px-5 py-2.5 hover:border-black hover:text-black transition">
                        ← Verifikasi Klaim
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
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
                <div class="bg-black p-6 text-white border border-gray-800 shadow-lg">
                    <p class="text-[9px] tracking-[0.3em] uppercase opacity-50 mb-1">Total Voucher</p>
                    <h4 class="khanaan-font-serif text-3xl">{{ $stats['total_vouchers'] }}</h4>
                    <p class="text-[10px] text-gray-400 mt-2 tracking-widest uppercase">Seluruh Penerbitan</p>
                </div>

                <div class="bg-white p-6 border border-gray-100 shadow-sm">
                    <p class="text-[9px] tracking-[0.3em] text-green-600 font-bold uppercase mb-1">Voucher Aktif</p>
                    <h4 class="khanaan-font-serif text-3xl text-black">{{ $stats['active_vouchers'] }}</h4>
                    <p class="text-[10px] text-gray-400 mt-2 tracking-widest uppercase font-semibold">Siap Digunakan User</p>
                </div>

                <div class="bg-white p-6 border border-gray-100 shadow-sm">
                    <p class="text-[9px] tracking-[0.3em] text-gray-400 uppercase mb-1">Sudah Terpakai</p>
                    <h4 class="khanaan-font-serif text-3xl text-black">{{ $stats['used_vouchers'] }}</h4>
                    <p class="text-[10px] text-gray-400 mt-2 tracking-widest uppercase font-semibold">Telah Digunakan</p>
                </div>

                <div class="bg-white p-6 border border-gray-100 shadow-sm">
                    <p class="text-[9px] tracking-[0.3em] text-red-500 uppercase mb-1">Kedaluwarsa</p>
                    <h4 class="khanaan-font-serif text-3xl text-black">{{ $stats['expired_vouchers'] }}</h4>
                    <p class="text-[10px] text-gray-400 mt-2 tracking-widest uppercase font-semibold">Melewati Batas Waktu</p>
                </div>
            </div>

            <!-- FORM TERBITKAN VOUCHER SPESIFIK KE 1 ORANG -->
            <div x-data="{
                voucherType: 'template',
                selectedRewardId: '{{ $rewards->first()?->id }}',
                generateCode() {
                    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                    let result = 'KHN-';
                    for (let i = 0; i < 8; i++) {
                        result += chars.charAt(Math.floor(Math.random() * chars.length));
                    }
                    document.getElementById('voucher_code_input').value = result;
                }
            }" class="bg-white border border-gray-200 shadow-sm p-8 md:p-10 mb-12">
                <div class="mb-8 border-b border-gray-100 pb-4 flex items-center justify-between">
                    <div>
                        <p class="text-[9px] tracking-[0.3em] uppercase text-amber-600 font-bold mb-1">Direct Assignment</p>
                        <h3 class="khanaan-font-serif text-2xl text-black italic">Terbitkan Kode Voucher Spesifik ke 1 Orang Member</h3>
                        <p class="text-xs text-gray-500 mt-1">Voucher ini akan langsung masuk ke dompet voucher member yang dipilih tanpa memotong poinnya.</p>
                    </div>
                    <span class="hidden md:inline-block px-3 py-1 bg-amber-50 border border-amber-200 text-amber-800 text-[9px] tracking-widest uppercase font-bold">
                        Special Privilege
                    </span>
                </div>

                <form action="{{ route('admin.vouchers.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        
                        <!-- TARGET USER (1 ORANG SPESIFIK) -->
                        <div>
                            <label for="user_id" class="block text-[10px] tracking-[0.2em] uppercase font-bold text-gray-700 mb-2">
                                Pilih Member Penerima <span class="text-red-500">*</span>
                            </label>
                            <select name="user_id" id="user_id" required
                                class="w-full border-gray-300 focus:border-black focus:ring-0 text-xs py-3 px-3 bg-[#FAF8F5]">
                                <option value="">-- Pilih 1 Orang Member --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->email }}) — {{ $user->tier }} Tier ({{ number_format($user->points) }} Pts)
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-[10px] text-gray-400 mt-1.5">Pilih member secara spesifik, tidak terbatas pada tier membership tertentu.</p>
                        </div>

                        <!-- KODE VOUCHER & AUTO GENERATE -->
                        <div>
                            <label for="voucher_code_input" class="block text-[10px] tracking-[0.2em] uppercase font-bold text-gray-700 mb-2">
                                Kode Voucher Spesifik <span class="text-red-500">*</span>
                            </label>
                            <div class="flex gap-2">
                                <input type="text" name="code" id="voucher_code_input" required
                                    value="{{ old('code', 'KHN-' . strtoupper(Str::random(8))) }}"
                                    placeholder="Contoh: KHN-VIP-SPECIAL"
                                    class="flex-1 border-gray-300 focus:border-black focus:ring-0 text-xs font-mono uppercase tracking-widest py-3 px-3">
                                <button type="button" @click="generateCode()"
                                    class="bg-gray-100 hover:bg-gray-200 border border-gray-300 text-stone-800 text-[9px] px-4 uppercase tracking-widest font-bold transition">
                                    Generate
                                </button>
                            </div>
                            <p class="text-[10px] text-gray-400 mt-1.5">Bisa dimasukkan kode khusus sesuai keinginan atau generate acak.</p>
                        </div>

                    </div>

                    <!-- TIPE VOUCHER: DARI REWARD ATAU KUSTOM -->
                    <div class="pt-4 border-t border-gray-100">
                        <label class="block text-[10px] tracking-[0.2em] uppercase font-bold text-gray-700 mb-3">
                            Jenis Hadiah / Diskon Voucher <span class="text-red-500">*</span>
                        </label>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <label class="border p-4 flex items-center gap-3 cursor-pointer transition"
                                :class="voucherType === 'template' ? 'border-black bg-stone-50' : 'border-gray-200 hover:border-gray-300'">
                                <input type="radio" name="voucher_type" value="template" x-model="voucherType" class="text-black focus:ring-0">
                                <div>
                                    <p class="text-xs font-bold text-black uppercase tracking-wider">Pilih dari Katalog Reward</p>
                                    <p class="text-[10px] text-gray-500">Gunakan template reward diskon yang sudah tersedia</p>
                                </div>
                            </label>

                            <label class="border p-4 flex items-center gap-3 cursor-pointer transition"
                                :class="voucherType === 'custom' ? 'border-black bg-stone-50' : 'border-gray-200 hover:border-gray-300'">
                                <input type="radio" name="voucher_type" value="custom" x-model="voucherType" class="text-black focus:ring-0">
                                <div>
                                    <p class="text-xs font-bold text-black uppercase tracking-wider">Voucher Kustom Khusus</p>
                                    <p class="text-[10px] text-gray-500">Tentukan judul & nominal diskon kustom secara bebas</p>
                                </div>
                            </label>
                        </div>

                        <!-- OPSI TEMPLATE REWARD -->
                        <div x-show="voucherType === 'template'" class="space-y-4">
                            <div>
                                <label for="reward_id" class="block text-[10px] tracking-[0.2em] uppercase font-semibold text-gray-600 mb-1">
                                    Pilih Reward
                                </label>
                                <select name="reward_id" id="reward_id"
                                    class="w-full border-gray-300 focus:border-black focus:ring-0 text-xs py-2.5 px-3 bg-white">
                                    @foreach($rewards as $reward)
                                        <option value="{{ $reward->id }}">
                                            {{ $reward->title }} (Standar: {{ $reward->points_required }} Poin)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- OPSI CUSTOM VOUCHER -->
                        <div x-show="voucherType === 'custom'" class="grid grid-cols-1 md:grid-cols-2 gap-6" style="display:none;">
                            <div>
                                <label for="custom_title" class="block text-[10px] tracking-[0.2em] uppercase font-semibold text-gray-600 mb-1">
                                    Judul Voucher Kustom
                                </label>
                                <input type="text" name="custom_title" id="custom_title"
                                    value="{{ old('custom_title') }}"
                                    placeholder="Misal: Voucher Diskon Rp 350.000 (VIP Loyalty)"
                                    class="w-full border-gray-300 focus:border-black focus:ring-0 text-xs py-2.5 px-3">
                            </div>
                            <div>
                                <label for="discount_amount" class="block text-[10px] tracking-[0.2em] uppercase font-semibold text-gray-600 mb-1">
                                    Nominal Diskon (Rp) (Opsional)
                                </label>
                                <input type="number" name="discount_amount" id="discount_amount"
                                    value="{{ old('discount_amount') }}"
                                    placeholder="Contoh: 350000"
                                    class="w-full border-gray-300 focus:border-black focus:ring-0 text-xs py-2.5 px-3">
                            </div>
                        </div>
                    </div>

                    <!-- MASA BERLAKU & CATATAN -->
                    <div class="pt-4 border-t border-gray-100 grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="expiry_days" class="block text-[10px] tracking-[0.2em] uppercase font-bold text-gray-700 mb-2">
                                Durasi Masa Berlaku
                            </label>
                            <select name="expiry_days" id="expiry_days"
                                class="w-full border-gray-300 focus:border-black focus:ring-0 text-xs py-2.5 px-3 bg-white">
                                <option value="7">7 Hari (1 Minggu)</option>
                                <option value="14" selected>14 Hari (2 Minggu)</option>
                                <option value="30">30 Hari (1 Bulan)</option>
                                <option value="60">60 Hari (2 Bulan)</option>
                                <option value="90">90 Hari (3 Bulan)</option>
                            </select>
                        </div>

                        <div>
                            <label for="custom_expires_at" class="block text-[10px] tracking-[0.2em] uppercase font-bold text-gray-700 mb-2">
                                Atau Pilih Tanggal Spesifik (Opsional)
                            </label>
                            <input type="date" name="custom_expires_at" id="custom_expires_at"
                                value="{{ old('custom_expires_at') }}"
                                class="w-full border-gray-300 focus:border-black focus:ring-0 text-xs py-2.5 px-3 bg-white">
                        </div>

                        <div>
                            <label for="notes" class="block text-[10px] tracking-[0.2em] uppercase font-bold text-gray-700 mb-2">
                                Catatan Admin (Opsional)
                            </label>
                            <input type="text" name="notes" id="notes"
                                value="{{ old('notes') }}"
                                placeholder="Misal: Hadiah ulang tahun / Kompensasi belanja"
                                class="w-full border-gray-300 focus:border-black focus:ring-0 text-xs py-2.5 px-3">
                        </div>
                    </div>

                    <!-- SUBMIT BUTTON -->
                    <div class="pt-4 flex justify-end">
                        <button type="submit"
                            class="bg-black text-white text-[10px] px-8 py-4 uppercase tracking-[0.3em] font-bold hover:bg-stone-800 transition shadow-lg shadow-black/10 flex items-center gap-2">
                            <span>✦ Terbitkan & Kirim ke User</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- SEARCH & FILTER VOUCHERS -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.vouchers.index') }}" 
                        class="text-[9px] tracking-widest uppercase px-4 py-2 border font-bold {{ !request('status') ? 'bg-black text-white border-black' : 'bg-white text-gray-600 border-gray-200 hover:border-gray-400' }}">
                        Semua ({{ $stats['total_vouchers'] }})
                    </a>
                    <a href="{{ route('admin.vouchers.index', ['status' => 'active']) }}" 
                        class="text-[9px] tracking-widest uppercase px-4 py-2 border font-bold {{ request('status') == 'active' ? 'bg-black text-white border-black' : 'bg-white text-green-700 border-gray-200 hover:border-gray-400' }}">
                        Aktif ({{ $stats['active_vouchers'] }})
                    </a>
                    <a href="{{ route('admin.vouchers.index', ['status' => 'used']) }}" 
                        class="text-[9px] tracking-widest uppercase px-4 py-2 border font-bold {{ request('status') == 'used' ? 'bg-black text-white border-black' : 'bg-white text-gray-600 border-gray-200 hover:border-gray-400' }}">
                        Terpakai ({{ $stats['used_vouchers'] }})
                    </a>
                    <a href="{{ route('admin.vouchers.index', ['status' => 'expired']) }}" 
                        class="text-[9px] tracking-widest uppercase px-4 py-2 border font-bold {{ request('status') == 'expired' ? 'bg-black text-white border-black' : 'bg-white text-red-600 border-gray-200 hover:border-gray-400' }}">
                        Kedaluwarsa ({{ $stats['expired_vouchers'] }})
                    </a>
                </div>

                <form action="{{ route('admin.vouchers.index') }}" method="GET" class="w-full md:w-72">
                    @if(request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif
                    <input type="text" name="search" value="{{ request('search') }}" 
                        placeholder="Cari Kode / Member / Judul..."
                        class="w-full border-0 border-b border-gray-300 focus:border-black focus:ring-0 text-xs tracking-widest uppercase bg-transparent pb-2">
                </form>
            </div>

            <!-- TABEL DAFTAR VOUCHER -->
            <div class="bg-white border border-gray-100 shadow-sm overflow-hidden mb-8">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="text-[10px] tracking-[0.2em] uppercase font-bold text-gray-400 bg-gray-50/70 border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-4">Penerima (Member)</th>
                                <th class="px-6 py-4">Kode Voucher</th>
                                <th class="px-6 py-4">Hadiah / Diskon</th>
                                <th class="px-6 py-4">Masa Berlaku</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 text-black text-xs">
                            @forelse($vouchers as $voucher)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <!-- Member Penerima -->
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-stone-100 border border-stone-300 flex items-center justify-center font-bold text-[10px] text-stone-700">
                                            {{ strtoupper(substr($voucher->user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="font-bold uppercase tracking-wider text-black">{{ $voucher->user->name }}</p>
                                            <p class="text-[10px] text-gray-400 font-mono">{{ $voucher->user->email }}</p>
                                            <span class="inline-block text-[8px] tracking-widest uppercase px-1.5 py-0.5 bg-gray-100 text-gray-600 mt-1 font-semibold">
                                                {{ $voucher->user->tier }} Tier
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Kode Voucher -->
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono font-bold tracking-widest text-black bg-stone-100 px-2.5 py-1 border border-stone-200">
                                            {{ $voucher->code }}
                                        </span>
                                        <button onclick="navigator.clipboard.writeText('{{ $voucher->code }}'); this.innerText='✓'; setTimeout(()=>this.innerText='Copy', 1500)"
                                            class="text-[9px] uppercase tracking-wider text-stone-500 hover:text-black border border-gray-200 px-2 py-0.5 transition">
                                            Copy
                                        </button>
                                    </div>
                                    @if($voucher->notes)
                                        <p class="text-[9px] text-gray-400 mt-1 italic">Note: {{ $voucher->notes }}</p>
                                    @endif
                                </td>

                                <!-- Hadiah / Diskon -->
                                <td class="px-6 py-5">
                                    <p class="font-bold text-stone-900">{{ $voucher->title }}</p>
                                    @if($voucher->discount_amount)
                                        <p class="text-[10px] text-amber-600 font-semibold mt-0.5">Diskon: Rp {{ number_format($voucher->discount_amount, 0, ',', '.') }}</p>
                                    @endif
                                </td>

                                <!-- Masa Berlaku -->
                                <td class="px-6 py-5">
                                    <p class="text-stone-800">{{ $voucher->expires_at->format('d M Y, H:i') }}</p>
                                    <p class="text-[10px] {{ $voucher->isExpired() ? 'text-red-500 font-bold' : 'text-gray-400' }}">
                                        {{ $voucher->expires_at->diffForHumans() }}
                                    </p>
                                </td>

                                <!-- Status -->
                                <td class="px-6 py-5">
                                    @if($voucher->used_at)
                                        <span class="inline-flex items-center px-2.5 py-1 text-[9px] tracking-wider uppercase font-bold bg-gray-100 text-gray-600 border border-gray-200">
                                            ✓ Terpakai
                                        </span>
                                    @elseif($voucher->isExpired())
                                        <span class="inline-flex items-center px-2.5 py-1 text-[9px] tracking-wider uppercase font-bold bg-red-50 text-red-600 border border-red-200">
                                            Kedaluwarsa
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 text-[9px] tracking-wider uppercase font-bold bg-green-50 text-green-700 border border-green-200">
                                            ● Aktif
                                        </span>
                                    @endif
                                </td>

                                <!-- Action Buttons -->
                                <td class="px-6 py-5 text-right space-x-2 whitespace-nowrap">
                                    <!-- Toggle Used -->
                                    <form action="{{ route('admin.vouchers.toggle-used', $voucher->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" 
                                            class="text-[9px] tracking-widest uppercase font-bold px-3 py-1.5 border transition {{ $voucher->used_at ? 'border-amber-400 text-amber-700 hover:bg-amber-50' : 'border-gray-300 text-gray-700 hover:bg-gray-100' }}"
                                            title="{{ $voucher->used_at ? 'Ubah status ke Belum Terpakai' : 'Tandai voucher ini sudah dipakai' }}">
                                            {{ $voucher->used_at ? 'Batal Pakai' : 'Tandai Pakai' }}
                                        </button>
                                    </form>

                                    <!-- Delete / Revoke -->
                                    <form action="{{ route('admin.vouchers.destroy', $voucher->id) }}" method="POST" class="inline"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus voucher {{ $voucher->code }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                            class="text-[9px] tracking-widest uppercase font-bold px-3 py-1.5 border border-red-200 text-red-600 hover:bg-red-50 transition">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-400 italic">
                                    Belum ada voucher yang diterbitkan atau data tidak ditemukan.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- PAGINATION -->
            <div class="mt-6">
                {{ $vouchers->links() }}
            </div>

        </div>
    </div>
</x-app-layout>
