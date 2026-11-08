<x-app-layout>
    <style>
        .khanaan-font-serif { font-family: 'Cormorant Garamond', serif; }
        @keyframes marquee {
            0% { transform: translateX(100vw); }
            100% { transform: translateX(-100%); }
        }
        .animate-marquee { display: inline-block; animation: marquee 25s linear infinite; }

        .card-hover {
            transition: transform 0.35s cubic-bezier(0.22,1,0.36,1), box-shadow 0.35s ease;
        }
        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px -12px rgba(184,150,78,0.15);
        }

        .tier-badge-gold     { background: linear-gradient(135deg, #B8964E, #E8C97A, #B8964E); }
        .tier-badge-silver   { background: linear-gradient(135deg, #9CA3AF, #D1D5DB, #9CA3AF); }
        .tier-badge-reguler  { background: linear-gradient(135deg, #D6D3D1, #E7E5E4, #D6D3D1); }
        .tier-badge-classic  { background: linear-gradient(135deg, #D6D3D1, #E7E5E4, #D6D3D1); }

        .stat-card {
            background: white;
            border: 1px solid #E7E5E4;
            padding: 28px 32px;
            transition: border-color 0.25s ease;
        }
        .stat-card:hover { border-color: #B8964E; }
    </style>

    <div class="py-10 min-h-screen" style="background: #FAF8F5;">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            @php $qrPayload = 'KHANAAN:' . (Auth::user()->qr_token ?? ''); @endphp

            <!-- FLASH ALERTS -->
            @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-800 text-xs tracking-wider flex items-center justify-between shadow-sm">
                <span>{{ session('error') }}</span>
                <button onclick="this.parentElement.remove()" class="text-red-400 hover:text-red-900 font-bold ml-4">&times;</button>
            </div>
            @endif

            @if(session('success'))
            <div class="mb-6 p-4 bg-stone-900 text-white text-xs tracking-wider flex items-center justify-between border-l-4 border-amber-400 shadow-md">
                <div class="flex items-center gap-2">
                    <span class="text-amber-400">✦</span>
                    <span>{{ session('success') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-stone-400 hover:text-white font-bold ml-4">&times;</button>
            </div>
            @endif

            {{-- Tier Banner --}}
            @if(!in_array(Auth::user()->tier, ['Reguler', 'Classic']))
            <div class="mb-10 overflow-hidden border-y border-amber-100 bg-gradient-to-r from-amber-50 to-white py-2.5">
                <div class="animate-marquee whitespace-nowrap">
                    <span class="text-[9px] tracking-[0.5em] uppercase font-semibold text-amber-700">
                        ✦ &nbsp; {{ Auth::user()->tier }} Privilege — {{ Auth::user()->discount_percentage }}% exclusive discount active &bull; {{ number_format(Auth::user()->getTierMultiplier(), 1) }}x Point Earning Multiplier &nbsp; ✦ &nbsp; Khanaan Private Member &nbsp; ✦ &nbsp; {{ Auth::user()->tier }} Privilege — {{ Auth::user()->discount_percentage }}% exclusive discount active
                    </span>
                </div>
            </div>
            @endif

            {{-- Page Header --}}
            <div class="text-center mb-12" style="animation: pageEnter 0.6s cubic-bezier(0.22,1,0.36,1) both;">
                <p style="font-size:9px; letter-spacing:0.5em; text-transform:uppercase; color:#B8964E; font-weight:600; margin-bottom:12px;">Member Portal</p>
                <h1 style="font-family:'Cormorant Garamond',serif; font-size:3.5rem; font-weight:300; color:#1C1916; line-height:1.1;">
                    Welcome back, <em>{{ explode(' ', Auth::user()->name)[0] }}</em>
                </h1>
                <div class="gold-divider" style="margin: 16px auto;"></div>
                <p style="font-size:10px; letter-spacing:0.4em; text-transform:uppercase; color:#A8A29E; margin-top:8px;">
                    Exclusive Member of Khanaan &bull; Role: {{ Auth::user()->role?->display_name ?? 'Member' }}
                </p>
            </div>

            {{-- Member Card --}}
            <div class="card-hover mb-10 rounded-none overflow-hidden"
                 style="background: linear-gradient(135deg, #1C1916 0%, #2D2820 50%, #1C1916 100%); border: 1px solid rgba(184,150,78,0.3);">
                <div class="p-10 md:p-14 flex flex-col md:flex-row justify-between items-center gap-8">
                    <div class="text-center md:text-left">
                        <p class="text-[9px] tracking-[0.5em] text-amber-400/60 uppercase mb-4 font-semibold">Available Points (Spendable)</p>
                        <div class="flex items-baseline gap-3 justify-center md:justify-start">
                            <span class="font-display text-8xl font-light text-white" style="font-family:'Cormorant Garamond',serif;">{{ number_format(Auth::user()->points) }}</span>
                            <span class="text-[10px] tracking-[0.4em] text-amber-400/50 uppercase">pts</span>
                        </div>
                        <div class="mt-3 flex flex-wrap items-center gap-4 justify-center md:justify-start">
                            <span class="text-[10px] text-amber-400/80 tracking-wider uppercase bg-white/5 border border-white/10 px-3 py-1">
                                Lifetime Points: <strong>{{ number_format(Auth::user()->lifetime_points) }} pts</strong>
                            </span>
                            <span class="text-[9px] text-white/50 tracking-widest uppercase">
                                Kedaluwarsa: {{ Auth::user()->points_expire_at ? Auth::user()->points_expire_at->format('d M Y') : '31 Des ' . now()->format('Y') }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="w-px h-20 bg-white/10 hidden md:block"></div>
                    
                    <div class="text-center md:text-right">
                        <p class="text-[9px] tracking-[0.5em] text-amber-400/60 uppercase mb-3 font-semibold">Membership Level</p>
                        <div class="inline-flex items-center gap-2 mb-3">
                            <span class="w-2.5 h-2.5 rounded-full {{ Auth::user()->tier == 'Gold' ? 'tier-badge-gold' : (Auth::user()->tier == 'Silver' ? 'tier-badge-silver' : 'tier-badge-reguler') }}"></span>
                            <span class="font-display text-2xl font-light text-white italic" style="font-family:'Cormorant Garamond',serif;">{{ Auth::user()->tier }} Class</span>
                        </div>
                        <div class="text-[9px] text-amber-400/90 tracking-wider uppercase mb-2 font-medium">
                            Multiplier: <strong>{{ number_format(Auth::user()->getTierMultiplier(), 1) }}x Poin</strong>
                            @if(Auth::user()->discount_percentage > 0)
                                &bull; {{ Auth::user()->discount_percentage }}% Diskon
                            @endif
                        </div>
                        <p class="text-[9px] text-stone-300/80 tracking-wide mb-3">
                            @if(Auth::user()->lifetime_points < 500)
                                {{ 500 - Auth::user()->lifetime_points }} pts lagi menuju Silver (500 pts)
                            @elseif(Auth::user()->lifetime_points < 1000)
                                {{ 1000 - Auth::user()->lifetime_points }} pts lagi menuju Gold (1.000 pts)
                            @else
                                Level Kehormatan Tertinggi (Tier Gold)
                            @endif
                        </p>
                        <p class="text-[9px] text-white/40 tracking-[0.4em] uppercase border border-white/10 px-5 py-2 inline-block">
                            KHN-{{ str_pad(Auth::id(), 4, '0', STR_PAD_LEFT) }}
                        </p>
                    </div>
                </div>
                {{-- Gold shimmer line --}}
                <div class="h-[2px]" style="background: linear-gradient(90deg, transparent, #B8964E, #E8C97A, #B8964E, transparent);"></div>
            </div>

            @if($birthdayReward)
            <div class="mb-8 bg-amber-50 border border-amber-200 px-6 py-4 flex items-center gap-3">
                <span class="text-amber-500">🎂</span>
                <p class="text-sm text-amber-800">Birthday reward credited: <strong>{{ $birthdayReward['points'] }} points</strong> and <strong>Rp {{ number_format($birthdayReward['cashback'], 2, ',', '.') }}</strong> cashback.</p>
            </div>
            @endif

            {{-- Stats Row --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-10">
                <div class="stat-card">
                    <p class="text-[9px] tracking-[0.4em] uppercase text-amber-600 font-semibold mb-3">Cashback Wallet</p>
                    <p class="font-display text-3xl text-stone-800" style="font-family:'Cormorant Garamond',serif;">Rp {{ number_format(Auth::user()->cashback_balance ?? 0, 0, ',', '.') }}</p>
                    <p class="text-xs text-stone-400 mt-2">Credited from approved purchases</p>
                </div>
                <div class="stat-card">
                    <p class="text-[9px] tracking-[0.4em] uppercase text-amber-600 font-semibold mb-3">Birthday Reward</p>
                    <p class="font-display text-3xl text-stone-800" style="font-family:'Cormorant Garamond',serif;">{{ Auth::user()->birth_date ? Auth::user()->birth_date->format('d MMM') : '—' }}</p>
                    <p class="text-xs text-stone-400 mt-2">{{ Auth::user()->birth_date ? 'Annual bonus active' : 'Set in profile settings' }}</p>
                </div>
                <div class="stat-card">
                    <p class="text-[9px] tracking-[0.4em] uppercase text-amber-600 font-semibold mb-3">Active Vouchers</p>
                    <p class="font-display text-3xl text-stone-800" style="font-family:'Cormorant Garamond',serif;">{{ Auth::user()->vouchers()->whereNull('used_at')->where('expires_at', '>', now())->count() }}</p>
                    <p class="text-xs text-stone-400 mt-2">Available in your wallet</p>
                </div>
            </div>

            {{-- Voucher Wallet --}}
            <div class="mb-12">
                <div class="flex items-end justify-between mb-6 pb-4 border-b border-stone-100">
                    <div>
                        <h2 class="font-display text-2xl text-stone-800 italic" style="font-family:'Cormorant Garamond',serif;">Voucher Wallet</h2>
                        <p class="text-[9px] tracking-[0.3em] text-stone-400 uppercase mt-1">Daftar kode voucher aktif Anda</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @forelse(Auth::user()->vouchers()->whereNull('used_at')->where('expires_at', '>', now())->with('reward')->latest()->get() as $voucher)
                    <div class="card-hover bg-white border border-stone-200 p-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 shadow-sm">
                        <div>
                            <div class="flex items-center gap-2 mb-1.5">
                                <span class="inline-block text-[8px] uppercase tracking-wider font-bold px-2 py-0.5 border {{ $voucher->categoryBadgeClass() }}">
                                    {{ $voucher->categoryLabel() }}
                                </span>
                            </div>
                            <p class="text-xs font-bold text-stone-900 mb-1">{{ $voucher->title }}</p>
                            <p class="font-display text-2xl text-stone-900 tracking-widest font-bold font-mono" style="font-family:'Cormorant Garamond',serif;">{{ $voucher->code }}</p>
                            @if($voucher->discount_amount)
                                <p class="text-xs text-stone-600 font-semibold mt-1">Diskon: Rp {{ number_format($voucher->discount_amount, 0, ',', '.') }}</p>
                            @endif
                            @if($voucher->notes)
                                <p class="text-[10px] text-stone-500 mt-1 italic">{{ $voucher->notes }}</p>
                            @endif
                            <p class="text-[10px] text-red-500 mt-2 tracking-wide font-medium">Berlaku s/d {{ $voucher->expires_at->format('d M Y') }} ({{ $voucher->expires_at->diffForHumans() }})</p>
                        </div>
                        <button onclick="navigator.clipboard.writeText('{{ $voucher->code }}'); this.textContent='Copied!'; setTimeout(()=>this.textContent='Copy',2000)"
                            class="bg-black text-white hover:bg-amber-600 text-[9px] px-5 py-2.5 uppercase tracking-widest font-semibold transition-all duration-200 shadow whitespace-nowrap">
                            Copy
                        </button>
                    </div>
                    @empty
                    <div class="col-span-2 py-10 text-center border border-dashed border-stone-200 bg-white">
                        <p class="font-display italic text-stone-400 text-base" style="font-family:'Cormorant Garamond',serif;">Belum ada voucher aktif di dompet Anda.</p>
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- Actions --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-12">
                <a href="{{ route('claims.create') }}" class="card-hover group block bg-white border border-stone-100 p-8 relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-1 h-full bg-amber-400 group-hover:w-2 transition-all duration-300"></div>
                    <p class="text-[9px] tracking-[0.4em] uppercase text-amber-600 font-semibold mb-3 pl-4">Step 1</p>
                    <h3 class="font-display text-2xl text-stone-800 italic pl-4" style="font-family:'Cormorant Garamond',serif;">Register Purchase</h3>
                    <p class="text-xs text-stone-400 mt-2 leading-relaxed pl-4">Submit your receipt to earn exclusive loyalty points ((Rp 100.000 = 10 Pts) &times; {{ number_format(Auth::user()->getTierMultiplier(), 1) }}x Multiplier).</p>
                    <p class="mt-4 pl-4 text-[9px] tracking-[0.3em] uppercase font-semibold text-amber-600 group-hover:underline">Submit Claim →</p>
                </a>
                <a href="#katalog-hadiah" class="card-hover group block bg-white border border-stone-100 p-8 relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-1 h-full bg-stone-300 group-hover:bg-amber-400 group-hover:w-2 transition-all duration-300"></div>
                    <p class="text-[9px] tracking-[0.4em] uppercase text-stone-400 font-semibold mb-3 pl-4">Step 2</p>
                    <h3 class="font-display text-2xl text-stone-800 italic pl-4" style="font-family:'Cormorant Garamond',serif;">Curated Privileges</h3>
                    <p class="text-xs text-stone-400 mt-2 leading-relaxed pl-4">Tukarkan poin Anda dengan layanan vermak gratis, undangan privat VIP, hampers mewah, dan produk eksklusif.</p>
                    <p class="mt-4 pl-4 text-[9px] tracking-[0.3em] uppercase font-semibold text-stone-400 group-hover:text-amber-600 group-hover:underline transition">Browse Collection →</p>
                </a>
            </div>

            {{-- MEMBERSHIP TIER PRIVILEGE GUIDE --}}
            <div class="mb-12 bg-white border border-stone-200 p-8 shadow-sm">
                <div class="mb-6 border-b border-stone-100 pb-4">
                    <p class="text-[9px] tracking-[0.4em] uppercase text-amber-600 font-semibold mb-1">Membership Privileges</p>
                    <h3 class="font-display text-2xl text-stone-900 italic" style="font-family:'Cormorant Garamond',serif;">Panduan Hak Istimewa Member Khanaan</h3>
                    <p class="text-xs text-stone-400 mt-1">Struktur level keanggotaan berdasarkan akumulasi total poin (Lifetime Points)</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- REGULER TIER -->
                    <div class="p-6 border {{ Auth::user()->tier === 'Reguler' ? 'border-amber-400 bg-amber-50/20 shadow-md' : 'border-stone-100 bg-[#FAFAFA]' }} flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-[9px] uppercase tracking-widest font-bold px-2 py-0.5 bg-stone-200 text-stone-700">0 - 499 Pts</span>
                                @if(Auth::user()->tier === 'Reguler')
                                    <span class="text-[8px] bg-amber-400 text-black px-1.5 py-0.5 font-extrabold uppercase">Tier Anda</span>
                                @endif
                            </div>
                            <h4 class="font-display text-xl text-stone-900 italic font-semibold mb-3" style="font-family:'Cormorant Garamond',serif;">Reguler (Classic)</h4>
                            <ul class="text-xs text-stone-600 space-y-2 leading-relaxed">
                                <li class="flex items-start gap-2">
                                    <span class="text-amber-600 font-bold">✓</span>
                                    <span><strong>Free Charge:</strong> Gratis layanan custom / vermak khusus untuk barang pada personal list setiap transaksi.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-amber-600 font-bold">✓</span>
                                    <span><strong>Base Earning:</strong> 1.0x kelipatan poin (Rp 100.000 = 10 Pts).</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- SILVER TIER -->
                    <div class="p-6 border {{ Auth::user()->tier === 'Silver' ? 'border-amber-400 bg-amber-50/20 shadow-md' : 'border-stone-100 bg-[#FAFAFA]' }} flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-[9px] uppercase tracking-widest font-bold px-2 py-0.5 bg-gray-200 text-gray-800">500 - 999 Pts</span>
                                @if(Auth::user()->tier === 'Silver')
                                    <span class="text-[8px] bg-amber-400 text-black px-1.5 py-0.5 font-extrabold uppercase">Tier Anda</span>
                                @endif
                            </div>
                            <h4 class="font-display text-xl text-stone-900 italic font-semibold mb-3" style="font-family:'Cormorant Garamond',serif;">Silver Class</h4>
                            <ul class="text-xs text-stone-600 space-y-2 leading-relaxed">
                                <li class="flex items-start gap-2">
                                    <span class="text-amber-600 font-bold">✓</span>
                                    <span>Seluruh benefit Member Reguler (Free Vermak).</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-amber-600 font-bold">✓</span>
                                    <span><strong>Private Invitation:</strong> Undangan privat ke acara eksklusif (runway & trunk show) Khanaan.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-amber-600 font-bold">✓</span>
                                    <span><strong>Privilege Discount:</strong> Akses diskon khusus private sale yang tidak dibuka untuk umum.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-amber-600 font-bold">✓</span>
                                    <span><strong>Multiplier:</strong> 1.5x kelipatan poin setiap belanja.</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- GOLD TIER -->
                    <div class="p-6 border {{ Auth::user()->tier === 'Gold' ? 'border-amber-400 bg-amber-50/30 shadow-md' : 'border-stone-100 bg-[#FAFAFA]' }} flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-[9px] uppercase tracking-widest font-bold px-2 py-0.5 bg-amber-200 text-amber-900">&ge; 1.000 Pts</span>
                                @if(Auth::user()->tier === 'Gold')
                                    <span class="text-[8px] bg-amber-400 text-black px-1.5 py-0.5 font-extrabold uppercase">Tier Anda</span>
                                @endif
                            </div>
                            <h4 class="font-display text-xl text-amber-900 italic font-semibold mb-3" style="font-family:'Cormorant Garamond',serif;">Gold Class (VIP)</h4>
                            <ul class="text-xs text-stone-600 space-y-2 leading-relaxed">
                                <li class="flex items-start gap-2">
                                    <span class="text-amber-600 font-bold">✓</span>
                                    <span>Seluruh benefit Member Silver & Reguler.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-amber-600 font-bold">✓</span>
                                    <span><strong>High-Rate Accumulation:</strong> 2.0x kelipatan poin perolehan belanja terbesar.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-amber-600 font-bold">✓</span>
                                    <span><strong>Exclusive Hampers & Gift:</strong> Bingkisan hampers mewah saat hari raya dan hadiah spesial.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-amber-600 font-bold">✓</span>
                                    <span><strong>Redeem Point (Akses Penuh):</strong> Hak penuh menukarkan poin dengan produk adibusana eksklusif Khanaan.</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Reward Catalogue --}}
            <div id="katalog-hadiah" class="mb-12">
                <div class="text-center mb-10">
                    <p class="text-[9px] tracking-[0.5em] uppercase text-amber-600 font-semibold mb-2">Privilege & Rewards</p>
                    <h2 class="font-display text-4xl font-light text-stone-900 italic" style="font-family:'Cormorant Garamond',serif;">Redeemable Collection</h2>
                    <p class="text-xs text-stone-400 mt-2 max-w-xl mx-auto">Tukarkan saldo poin aktif Anda dengan layanan custom, tiket undangan privat, hampers hari raya, dan produk adibusana eksklusif.</p>
                    <div class="gold-divider mx-auto mt-3"></div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    @forelse(\App\Models\Reward::orderBy('points_required')->get() as $reward)
                    @php
                        $isTierEligible = ($reward->tier_required === 'Gold' && Auth::user()->tier === 'Gold') 
                            || ($reward->tier_required === 'Silver' && in_array(Auth::user()->tier, ['Silver', 'Gold'])) 
                            || ($reward->tier_required === 'Reguler');
                        $hasEnoughPoints = Auth::user()->points >= $reward->points_required;
                    @endphp
                    <div class="card-hover group bg-white border border-stone-200 overflow-hidden flex flex-col justify-between shadow-sm">
                        <div class="p-8 text-center bg-stone-50/50 border-b border-stone-100 flex-1 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between mb-4">
                                    <span class="inline-block text-[8px] uppercase tracking-wider font-bold px-2 py-0.5 border {{ $reward->categoryBadgeClass() }}">
                                        {{ $reward->categoryLabel() }}
                                    </span>
                                    <span class="text-[9px] uppercase tracking-wider font-bold {{ $reward->tier_required === 'Gold' ? 'text-amber-700' : ($reward->tier_required === 'Silver' ? 'text-stone-600' : 'text-stone-400') }}">
                                        Tier {{ $reward->tier_required }}+
                                    </span>
                                </div>
                                <span class="inline-block px-3 py-1 bg-black text-amber-400 text-[10px] tracking-widest uppercase font-bold mb-4">
                                    {{ number_format($reward->points_required) }} Poin
                                </span>
                                <h4 class="font-display text-xl text-stone-900 italic mb-2 font-semibold" style="font-family:'Cormorant Garamond',serif;">{{ $reward->title }}</h4>
                                <p class="text-xs text-stone-500 leading-relaxed">{{ $reward->description }}</p>
                            </div>

                            @if($reward->stock <= 5 && $reward->stock > 0)
                                <p class="text-[9px] text-amber-600 font-bold tracking-wider uppercase mt-4">Sisa Kuota: {{ $reward->stock }}</p>
                            @endif
                        </div>
                        <div class="p-6 bg-white">
                            @if(!$isTierEligible)
                                <button disabled class="w-full bg-stone-100 text-stone-400 text-[9px] py-3 uppercase tracking-[0.2em] font-bold cursor-not-allowed border border-stone-200">
                                    Khusus Member {{ $reward->tier_required }}
                                </button>
                            @elseif(!$hasEnoughPoints)
                                <button disabled class="w-full bg-stone-100 text-stone-400 text-[9px] py-3 uppercase tracking-[0.2em] font-bold cursor-not-allowed border border-stone-200">
                                    Poin Kurang ({{ number_format($reward->points_required - Auth::user()->points) }} Pts Lagi)
                                </button>
                            @else
                                <form action="{{ route('rewards.redeem', $reward->id) }}" method="POST">
                                    @csrf
                                    <button class="w-full bg-black text-white text-[9px] py-3 uppercase tracking-[0.3em] font-bold hover:bg-amber-600 transition-colors duration-200 shadow">
                                        Klaim / Tukar Sekarang
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                    @empty
                    <p class="col-span-3 text-center text-stone-400 italic py-10">Katalog hadiah sedang diperbarui.</p>
                    @endforelse
                </div>
            </div>

            {{-- QR & Notifications Section --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-12">
                <!-- QR CODE -->
                <div class="bg-white border border-stone-100 p-8 flex flex-col items-center text-center justify-center shadow-sm">
                    <p class="text-[9px] tracking-[0.4em] uppercase text-amber-600 font-semibold mb-3">Membership QR</p>
                    <div id="member-qr" class="p-3 border border-stone-200 bg-[#FAF8F5] inline-block mb-4"></div>
                    <p class="text-xs text-stone-500">Scan QR token saat bertransaksi di boutique untuk verifikasi member.</p>
                </div>

                <!-- NOTIFICATIONS PREVIEW -->
                <div class="bg-white border border-stone-100 p-8 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <p class="text-[9px] tracking-[0.4em] uppercase text-amber-600 font-semibold">Recent Notifications</p>
                            <a href="{{ route('notifications.index') }}" class="text-[9px] tracking-widest uppercase font-bold text-stone-700 hover:text-black">Lihat Semua →</a>
                        </div>
                        <div class="space-y-3">
                            @forelse($notifications as $notification)
                                <div class="border-b border-stone-100 pb-3">
                                    <p class="text-xs font-bold text-stone-800">{{ $notification->data['title'] ?? 'Notifikasi' }}</p>
                                    <p class="text-xs text-stone-500 mt-0.5">{{ $notification->data['message'] ?? '' }}</p>
                                    <span class="text-[9px] text-stone-400 font-mono">{{ $notification->created_at->diffForHumans() }}</span>
                                </div>
                            @empty
                                <p class="text-xs text-stone-400 italic py-4">Tidak ada notifikasi baru.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            {{-- POINT LEDGER: RIWAYAT MUTASI POIN --}}
            <div class="bg-white border border-stone-100 overflow-hidden shadow-sm mb-12">
                <div class="px-8 py-6 border-b border-stone-100 flex items-end justify-between">
                    <div>
                        <h2 class="font-display text-2xl text-stone-800 italic" style="font-family:'Cormorant Garamond',serif;">Point Ledger</h2>
                        <p class="text-[9px] tracking-[0.3em] text-stone-400 uppercase mt-1">Buku mutasi transaksi poin masuk dan keluar</p>
                    </div>
                    <span class="text-[9px] tracking-widest uppercase font-bold text-amber-600 bg-amber-50 border border-amber-200 px-3 py-1">
                        Saldo: {{ number_format(Auth::user()->points) }} Pts
                    </span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-stone-100 text-stone-400 bg-stone-50/50">
                                <th class="px-6 py-4 text-left text-[9px] tracking-[0.3em] uppercase font-semibold">Waktu & Tanggal</th>
                                <th class="px-6 py-4 text-left text-[9px] tracking-[0.3em] uppercase font-semibold">Aktivitas</th>
                                <th class="px-6 py-4 text-left text-[9px] tracking-[0.3em] uppercase font-semibold">Keterangan</th>
                                <th class="px-4 py-4 text-right text-[9px] tracking-[0.3em] uppercase font-semibold">Sebelum</th>
                                <th class="px-4 py-4 text-right text-[9px] tracking-[0.3em] uppercase font-semibold">Mutasi</th>
                                <th class="px-6 py-4 text-right text-[9px] tracking-[0.3em] uppercase font-semibold">Saldo Akhir</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 text-xs">
                            @forelse(Auth::user()->pointLedgers()->take(15)->get() as $ledger)
                            <tr class="hover:bg-amber-50/20 transition-colors">
                                <td class="px-6 py-4 font-mono text-[10px] text-stone-400 whitespace-nowrap">
                                    {{ $ledger->created_at->format('d M Y, H:i') }}
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $typeBadge = match($ledger->type) {
                                            'earn_claim' => ['label' => 'Klaim Belanja', 'class' => 'bg-green-50 text-green-700 border-green-200'],
                                            'redeem_voucher' => ['label' => 'Tukar Voucher', 'class' => 'bg-amber-50 text-amber-700 border-amber-200'],
                                            'birthday_bonus' => ['label' => 'Bonus Ultah', 'class' => 'bg-purple-50 text-purple-700 border-purple-200'],
                                            'expired_reset' => ['label' => 'Kedaluwarsa', 'class' => 'bg-red-50 text-red-600 border-red-200'],
                                            default => ['label' => 'Penyesuaian', 'class' => 'bg-stone-50 text-stone-600 border-stone-200'],
                                        };
                                    @endphp
                                    <span class="inline-block text-[8px] uppercase tracking-wider font-bold px-2 py-0.5 border {{ $typeBadge['class'] }}">
                                        {{ $typeBadge['label'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-stone-700 text-xs font-medium">
                                    {{ $ledger->description }}
                                </td>
                                <td class="px-4 py-4 text-right font-mono text-stone-400">
                                    {{ number_format($ledger->balance_before) }}
                                </td>
                                <td class="px-4 py-4 text-right font-bold font-mono {{ $ledger->amount > 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $ledger->amount > 0 ? '+' . number_format($ledger->amount) : number_format($ledger->amount) }}
                                </td>
                                <td class="px-6 py-4 text-right font-bold font-mono text-black">
                                    {{ number_format($ledger->balance_after) }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-xs text-stone-400 italic">
                                    Belum ada catatan mutasi poin.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Claim History --}}
            <div class="bg-white border border-stone-100 overflow-hidden shadow-sm">
                <div class="px-8 py-6 border-b border-stone-100 flex items-end justify-between">
                    <div>
                        <h2 class="font-display text-2xl text-stone-800 italic" style="font-family:'Cormorant Garamond',serif;">Claim History</h2>
                        <p class="text-[9px] tracking-[0.3em] text-stone-400 uppercase mt-1">Transaction journal</p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-stone-100 text-stone-400 bg-stone-50/50">
                                <th class="px-8 py-4 text-left text-[9px] tracking-[0.3em] uppercase font-semibold">Reference</th>
                                <th class="px-4 py-4 text-left text-[9px] tracking-[0.3em] uppercase font-semibold">Date</th>
                                <th class="px-4 py-4 text-left text-[9px] tracking-[0.3em] uppercase font-semibold">Value</th>
                                <th class="px-8 py-4 text-right text-[9px] tracking-[0.3em] uppercase font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($claims as $claim)
                            <tr class="border-b border-stone-50 hover:bg-amber-50/30 transition-colors">
                                <td class="px-8 py-5 font-mono text-[10px] tracking-widest text-stone-400">#{{ $claim->order_number }}</td>
                                <td class="px-4 py-5 text-xs text-stone-500">{{ $claim->created_at->format('d M Y') }}</td>
                                <td class="px-4 py-5 text-sm font-semibold text-stone-800">Rp {{ number_format($claim->amount) }}</td>
                                <td class="px-8 py-5 text-right">
                                    <span class="inline-block px-3 py-1 text-[9px] tracking-[0.2em] uppercase font-bold rounded-full
                                        {{ $claim->status == 'approved' ? 'bg-green-50 text-green-700' : ($claim->status == 'rejected' ? 'bg-red-50 text-red-600' : 'bg-amber-50 text-amber-700') }}">
                                        {{ $claim->status }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="px-8 py-10 text-center text-sm text-stone-400 italic">Belum ada klaim yang dikirimkan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const target = document.getElementById('member-qr');
            if (target && typeof QRCode !== 'undefined') {
                new QRCode(target, { 
                    text: @json($qrPayload), 
                    width: 160, 
                    height: 160, 
                    colorDark: '#1C1916', 
                    colorLight: '#FAF8F5',
                    correctLevel: QRCode.CorrectLevel.M
                });
            }
        });
    </script>
</x-app-layout>