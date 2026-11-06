<x-app-layout>
    <div class="py-12 bg-[#FCFCFC] min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- HEADER & SEARCH -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-12 gap-6">
                <div>
                    <h2 class="khanaan-font-serif text-4xl text-black italic">Verification Center</h2>
                    <p class="text-[10px] tracking-[0.3em] text-gray-400 uppercase mt-2">PT ASA GROUP UTAMA - Operational & Anti-Fraud Center</p>
                </div>
                
                <form action="{{ route('admin.claims.index') }}" method="GET" class="relative w-full md:w-auto">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search Order / Name..." 
                        class="border-0 border-b border-gray-200 focus:border-black focus:ring-0 text-xs tracking-widest uppercase w-full md:w-64 bg-transparent pb-2">
                </form>
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

            <!-- STATS CARDS (Decision Support & Fraud Detection) -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
                <!-- VIP Alert -->
                <div class="bg-black p-8 text-white border border-gray-800 shadow-xl">
                    <p class="text-[9px] tracking-[0.3em] uppercase opacity-50 mb-2">VIP Priority</p>
                    <h4 class="khanaan-font-serif text-4xl">{{ $stats['vip_pending'] }}</h4>
                    <p class="text-[10px] text-yellow-500 mt-3 font-bold tracking-widest uppercase flex items-center">
                        <span class="w-2 h-2 bg-yellow-500 rounded-full mr-2 animate-pulse"></span>
                        Pending VIP Claims
                    </p>
                </div>

                <!-- High Risk Fraud Alert -->
                <div class="bg-white p-8 border border-red-200 shadow-sm">
                    <p class="text-[9px] tracking-[0.3em] text-red-500 uppercase mb-2 font-bold">Fraud Inspector</p>
                    <h4 class="khanaan-font-serif text-4xl text-red-600">{{ $stats['high_risk_pending'] ?? 0 }}</h4>
                    <p class="text-[10px] text-red-600 mt-3 tracking-widest uppercase font-semibold flex items-center">
                        <span class="w-2 h-2 bg-red-600 rounded-full mr-2 animate-ping"></span>
                        High Risk Claims
                    </p>
                </div>

                <!-- Total Operational Load -->
                <div class="bg-white p-8 border border-gray-100 shadow-sm">
                    <p class="text-[9px] tracking-[0.3em] text-gray-400 uppercase mb-2">Operational Load</p>
                    <h4 class="khanaan-font-serif text-4xl text-black">{{ $stats['total_pending'] }}</h4>
                    <p class="text-[10px] text-gray-400 mt-3 tracking-widest uppercase font-semibold">Waiting Approval</p>
                </div>

                <!-- Verified Sales -->
                <div class="bg-white p-8 border border-gray-100 shadow-sm">
                    <p class="text-[9px] tracking-[0.3em] text-gray-400 uppercase mb-2">Total Verified Revenue</p>
                    <h4 class="khanaan-font-serif text-4xl text-black">IDR {{ number_format($stats['total_sales_verified'] / 1000000, 1) }}M</h4>
                    <p class="text-[10px] text-green-600 mt-3 font-bold tracking-widest uppercase">Growth Contribution</p>
                </div>

                <!-- Cashback Liability -->
                <div class="bg-white p-8 border border-gray-100 shadow-sm md:col-span-4 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <p class="text-[9px] tracking-[0.3em] text-gray-400 uppercase mb-1">Total Cashback Balance Across Members</p>
                        <h4 class="khanaan-font-serif text-3xl text-black">Rp {{ number_format($stats['total_cashback_balance'], 2, ',', '.') }}</h4>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('admin.vouchers.index') }}" class="text-[9px] tracking-[0.2em] uppercase font-bold border border-black px-4 py-2 hover:bg-black hover:text-white transition">
                            Kelola Voucher Langsung →
                        </a>
                    </div>
                </div>
            </div>

            <!-- DATA TABLE -->
            <div class="bg-white border border-gray-100 shadow-sm overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead class="text-[10px] tracking-[0.2em] uppercase font-bold text-gray-400 bg-gray-50/50">
                        <tr>
                            <th class="px-6 py-5">Customer Info</th>
                            <th class="px-6 py-5">Order Detail</th>
                            <th class="px-6 py-5">Fraud & AI Audit</th>
                            <th class="px-6 py-5">Proof</th>
                            <th class="px-6 py-5 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 text-black">
                        @foreach($claims as $claim)
                        <tr class="hover:bg-gray-50/50 transition-colors group">
                            <!-- Customer Info -->
                            <td class="px-6 py-6">
                                <div class="flex items-center">
                                    @if($claim->user->tier !== 'Classic')
                                        <span class="w-2 h-2 rounded-full bg-yellow-500 mr-3 animate-pulse" title="{{ $claim->user->tier }} Member"></span>
                                    @endif
                                    <div>
                                        <p class="text-xs font-bold uppercase tracking-widest">{{ $claim->user->name }}</p>
                                        <p class="text-[9px] text-gray-400 uppercase tracking-tighter">{{ $claim->user->tier }} Member ({{ number_format($claim->user->points) }} Pts)</p>
                                        <span class="text-[8px] text-stone-400 font-mono">{{ $claim->user->email }}</span>
                                    </div>
                                </div>
                            </td>

                            <!-- Order Detail & Lifetime Value -->
                            <td class="px-6 py-6">
                                <p class="text-[10px] font-mono text-gray-400 mb-1">#{{ $claim->order_number }}</p>
                                <p class="text-xs font-bold mb-2">IDR {{ number_format($claim->amount) }}</p>
                                
                                @php
                                    $totalUserSpend = $claim->user->loyaltyClaims()->where('status', 'approved')->sum('amount');
                                @endphp
                                <div class="text-[9px] tracking-widest uppercase font-medium text-gray-400 bg-gray-50 inline-block px-2 py-1">
                                    LTV: <span class="text-black font-bold">IDR {{ number_format($totalUserSpend) }}</span>
                                </div>
                            </td>

                            <!-- Fraud & AI Audit -->
                            <td class="px-6 py-6">
                                <div class="space-y-1.5">
                                    <div class="flex items-center gap-2">
                                        <span class="text-[9px] px-2 py-0.5 border font-bold uppercase tracking-wider {{ $claim->getRiskBadgeClass() }}">
                                            {{ $claim->getRiskLevelName() }}
                                        </span>
                                        <span class="text-[9px] font-mono text-gray-400 font-bold">
                                            Score: {{ $claim->fraud_risk_score }}%
                                        </span>
                                    </div>

                                    @if(!empty($claim->fraud_flags))
                                        <div class="mt-1 p-2 bg-red-50/70 border border-red-100 text-[9px] text-red-700 max-w-xs space-y-1">
                                            @foreach($claim->fraud_flags as $flag)
                                                <p class="leading-tight">• {{ $flag }}</p>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-[9px] text-gray-400 italic">✓ Struk unik & format valid</p>
                                    @endif

                                    @if($claim->proof_hash)
                                        <p class="text-[8px] font-mono text-gray-400 truncate max-w-[160px]" title="SHA-256 Hash: {{ $claim->proof_hash }}">
                                            Hash: {{ substr($claim->proof_hash, 0, 16) }}...
                                        </p>
                                    @endif
                                </div>
                            </td>

                            <!-- Proof of Purchase -->
                            <td class="px-6 py-6">
                                <a href="{{ asset('storage/' . $claim->proof_path) }}" target="_blank" 
                                   class="text-[9px] tracking-[0.2em] uppercase font-bold border-b border-black pb-0.5 hover:opacity-50 transition inline-flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    View Image
                                </a>
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-6 text-right">
                                @if($claim->status == 'pending')
                                    <form action="{{ route('admin.claims.approve', $claim->id) }}" method="POST" class="inline"
                                        @if($claim->isHighRisk()) onsubmit="return confirm('PERINGATAN: Klaim ini terindikasi RISIKO TINGGI FRAUD (Skor: {{ $claim->fraud_risk_score }}%). Apakah Anda yakin tetap ingin menyetujui klaim ini?')" @endif>
                                        @csrf
                                        <button class="text-[9px] px-6 py-2.5 uppercase tracking-widest font-bold transition shadow-lg shadow-black/10 {{ $claim->isHighRisk() ? 'bg-red-700 hover:bg-red-800 text-white' : 'bg-black hover:bg-gray-800 text-white' }}">
                                            Approve
                                        </button>
                                    </form>
                                @else
                                    <span class="text-[9px] tracking-[0.2em] uppercase font-bold {{ $claim->status == 'approved' ? 'text-green-600' : 'text-red-500' }}">
                                        {{ $claim->status }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- PAGINATION -->
            <div class="mt-10 khanaan-font-sans">
                {{ $claims->links() }}
            </div>
        </div>
    </div>
</x-app-layout>