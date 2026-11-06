<nav x-data="{ open: false }" class="bg-white border-b border-stone-100 sticky top-0 z-50" style="backdrop-filter: blur(12px);">
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex justify-between items-center h-16">
            
            <!-- LEFT: Nav Links -->
            <div class="hidden sm:flex items-center flex-1" style="gap: 2rem;">
                @php
                    $unreadNotificationCount = 0;
                    try { $unreadNotificationCount = Auth::user()->unreadNotifications()->count(); } catch (\Exception $e) {}
                @endphp

                <a href="{{ route('dashboard') }}" class="text-[10px] tracking-[0.18em] uppercase font-medium gold-link transition {{ request()->routeIs('dashboard') ? 'text-amber-600' : 'text-stone-500 hover:text-stone-900' }}">
                    Dashboard
                </a>
                <a href="{{ route('claims.create') }}" class="text-[10px] tracking-[0.18em] uppercase font-medium gold-link transition {{ request()->routeIs('claims.create') ? 'text-amber-600' : 'text-stone-500 hover:text-stone-900' }}">
                    Claim Point
                </a>
                <a href="{{ route('qr.scan') }}" class="text-[10px] tracking-[0.18em] uppercase font-medium gold-link transition {{ request()->routeIs('qr.scan') ? 'text-amber-600' : 'text-stone-500 hover:text-stone-900' }}">
                    QR Scan
                </a>
                <a href="{{ route('notifications.index') }}" class="relative text-[10px] tracking-[0.18em] uppercase font-medium gold-link transition {{ request()->routeIs('notifications.index') ? 'text-amber-600' : 'text-stone-500 hover:text-stone-900' }}">
                    Notifications
                    @if($unreadNotificationCount > 0)
                        <span class="absolute -top-1.5 -right-3 w-4 h-4 bg-amber-500 text-white text-[8px] rounded-full flex items-center justify-center font-bold">{{ $unreadNotificationCount }}</span>
                    @endif
                </a>
                @if(Auth::user()->hasRole(['admin', 'manager']))
                <a href="{{ route('admin.claims.index') }}" class="text-[10px] tracking-[0.18em] uppercase font-bold {{ request()->routeIs('admin.claims.*') ? 'text-amber-600' : 'text-stone-700 hover:text-black' }} transition">
                    Verifikasi
                </a>
                <a href="{{ route('admin.vouchers.index') }}" class="text-[10px] tracking-[0.18em] uppercase font-bold {{ request()->routeIs('admin.vouchers.*') ? 'text-amber-600' : 'text-stone-700 hover:text-black' }} transition">
                    Kelola Voucher
                </a>
                @if(Auth::user()->isAdmin())
                <a href="{{ route('admin.users.index') }}" class="text-[10px] tracking-[0.18em] uppercase font-bold {{ request()->routeIs('admin.users.*') ? 'text-amber-600' : 'text-stone-700 hover:text-black' }} transition">
                    Kelola Staff
                </a>
                @endif
                @endif
            </div>

            <!-- CENTER: Logo -->
            <div class="flex items-center justify-center">
                <a href="{{ Auth::user()->isAdmin() ? route('admin.claims.index') : (Auth::user()->isManager() ? route('qr.scan') : route('dashboard')) }}">
                    <img src="{{ asset('image/logo.jpeg') }}" alt="Khanaan" class="h-9 w-auto object-contain hover:opacity-70 transition-opacity duration-300">
                </a>
            </div>

            <!-- RIGHT: User Dropdown -->
            <div class="hidden sm:flex items-center justify-end flex-1">
                <div x-data="{ dropOpen: false }" class="relative">
                    <button @click="dropOpen = !dropOpen" class="flex items-center gap-2 text-[10px] tracking-[0.18em] uppercase font-medium text-stone-500 hover:text-stone-900 transition">
                        <div class="w-7 h-7 rounded-full bg-amber-100 border border-amber-300 flex items-center justify-center text-amber-700 font-bold text-[10px]">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <div class="text-left">
                            <p class="font-bold text-black leading-tight">{{ Auth::user()->name }}</p>
                            <span class="text-[8px] tracking-wider uppercase text-amber-700 block">{{ Auth::user()->role?->display_name ?? 'Member' }}</span>
                        </div>
                        <svg class="w-3 h-3 transition-transform duration-200" :class="dropOpen ? 'rotate-180' : ''" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
                    </button>
                    <div x-show="dropOpen" @click.outside="dropOpen = false"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         class="absolute right-0 mt-3 w-52 bg-white border border-stone-100 shadow-xl shadow-stone-200/50 py-1" style="display:none">
                        <div class="px-5 py-2.5 border-b border-stone-100 bg-stone-50/50">
                            <p class="text-[9px] uppercase tracking-wider text-gray-400">Level Member</p>
                            <p class="text-xs font-bold text-black">{{ Auth::user()->tier }} Class</p>
                        </div>
                        <a href="{{ route('profile.edit') }}" class="block px-5 py-3 text-[10px] tracking-[0.2em] uppercase text-stone-600 hover:bg-stone-50 hover:text-amber-600 transition">
                            Profile Settings
                        </a>
                        <div class="border-t border-stone-100 my-1"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button onclick="this.closest('form').submit()" class="w-full text-left px-5 py-3 text-[10px] tracking-[0.2em] uppercase text-red-500 hover:bg-red-50 transition">
                                Sign Out
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Mobile Hamburger -->
            <div class="sm:hidden">
                <button @click="open = !open" class="p-2 text-stone-400 hover:text-stone-900 transition">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path :class="{'hidden': open}" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 8h16M4 16h16"/>
                        <path :class="{'hidden': !open}" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div :class="{'block': open, 'hidden': !open}" class="hidden sm:hidden bg-white border-t border-stone-100 px-6 py-4 space-y-3">
        <a href="{{ route('dashboard') }}" class="block py-2 text-[10px] tracking-[0.2em] uppercase font-medium text-stone-600">Dashboard</a>
        <a href="{{ route('claims.create') }}" class="block py-2 text-[10px] tracking-[0.2em] uppercase font-medium text-stone-600">Claim Point</a>
        <a href="{{ route('qr.scan') }}" class="block py-2 text-[10px] tracking-[0.2em] uppercase font-medium text-stone-600">QR Scan</a>
        <a href="{{ route('notifications.index') }}" class="block py-2 text-[10px] tracking-[0.2em] uppercase font-medium text-stone-600">Notifications</a>
        @if(Auth::user()->hasRole(['admin', 'manager']))
            <a href="{{ route('admin.claims.index') }}" class="block py-2 text-[10px] tracking-[0.2em] uppercase font-bold text-amber-600">Admin Verifikasi</a>
            <a href="{{ route('admin.vouchers.index') }}" class="block py-2 text-[10px] tracking-[0.2em] uppercase font-bold text-amber-600">Admin Kelola Voucher</a>
            @if(Auth::user()->isAdmin())
            <a href="{{ route('admin.users.index') }}" class="block py-2 text-[10px] tracking-[0.2em] uppercase font-bold text-amber-600">Admin Kelola Staff</a>
            @endif
        @endif
        <div class="border-t border-stone-100 pt-3 mt-2">
            <p class="text-[9px] tracking-[0.2em] uppercase text-stone-400 mb-1">{{ Auth::user()->name }} ({{ Auth::user()->role?->display_name ?? 'Member' }})</p>
            <a href="{{ route('profile.edit') }}" class="block py-2 text-[10px] tracking-[0.2em] uppercase font-medium text-stone-600">Profile</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button onclick="this.closest('form').submit()" class="py-2 text-[10px] tracking-[0.2em] uppercase font-medium text-red-500">Sign Out</button>
            </form>
        </div>
    </div>
</nav>