<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Khanaan Privé') }}</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght=0,400;0,600;0,700;1,400&family=Inter:wght=300;400;500;600&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        serif: ['Playfair Display', 'serif'],
                    },
                    colors: {
                        khanaan: '#d4af37',
                    }
                }
            }
        }
    </script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .fade-in {
            animation: fadeIn 1.2s ease-in-out forwards;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<!-- Menggunakan asset() untuk memanggil gambar dari folder public/images/ -->
<body class="bg-neutral-950 text-white font-sans antialiased relative min-h-screen bg-cover bg-center bg-no-repeat bg-fixed selection:bg-khanaan selection:text-black" 
      style="background-image: url('{{ asset('image/bg-loyalty.jpeg') }}');">

    <!-- Overlay Gelap Sinematik -->
    <div class="absolute inset-0 bg-gradient-to-b from-black/85 via-black/80 to-neutral-950/95 backdrop-blur-[3px] z-0"></div>

    <!-- Konten Utama Pembungkus -->
    <div class="relative z-10 min-h-screen flex flex-col justify-between">
        
        <!-- 1. NAVIGATION BAR -->
        <nav class="flex justify-between items-center px-6 md:px-12 py-6 fade-in">
            <div class="text-xl md:text-2xl font-serif tracking-[0.25em] uppercase font-bold text-white">
                Khanaan <span class="italic font-normal text-stone-400"></span>
            </div>
            
            @if (Route::has('login'))
                <div class="flex items-center space-x-6 text-[10px] tracking-widest uppercase font-semibold">
                    @auth
                        @if(Auth::user()->hasRole(['admin', 'manager']))
                            <a href="{{ route('admin.claims.index') }}" class="text-khanaan border border-khanaan/30 px-4 py-2 hover:bg-khanaan hover:text-black transition duration-300 rounded-sm">Admin Center</a>
                        @else
                            <a href="{{ url('/dashboard') }}" class="text-khanaan border border-khanaan/30 px-4 py-2 hover:bg-khanaan hover:text-black transition duration-300 rounded-sm">Dashboard</a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="hover:text-khanaan transition duration-300">Log in</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="hidden sm:block text-khanaan border border-khanaan/30 px-4 py-2 hover:bg-khanaan hover:text-black transition duration-300 rounded-sm">Join Now</a>
                        @endif
                    @endauth
                </div>
            @endif
        </nav>

        <!-- 2. HERO SECTION (WELCOME CARD) -->
        <div class="flex flex-col items-center justify-center px-4 py-12 text-center fade-in" style="animation-delay: 0.2s;">
            <div class="backdrop-blur-md bg-black/40 border border-white/10 p-8 md:p-14 rounded-2xl max-w-2xl shadow-2xl">
                
                <div class="flex justify-center items-center space-x-2 mb-4">
                    <span class="h-[1px] w-6 bg-khanaan/40"></span>
                    <span class="text-[9px] uppercase tracking-[0.3em] text-khanaan font-semibold block">Exclusively For Our Patrons</span>
                    <span class="h-[1px] w-6 bg-khanaan/40"></span>
                </div>
                
                <h1 class="text-3xl md:text-5xl font-serif mb-5 leading-tight tracking-wide">
                    Elegance Rewarded
                </h1>
                
                <p class="text-stone-300 text-xs md:text-sm font-light tracking-wide mb-8 leading-relaxed max-w-md mx-auto">
                    Welcome to the Khanaan Privé Loyalty Platform. Every purchase brings you closer to private rewards, tailored privileges, and bespoke collections.
                </p>
            </div>
        </div>

        <!-- 3. PRIVILEGE GUIDE SECTION -->
        <div class="max-w-6xl mx-auto px-6 md:px-12 py-16 w-full fade-in" style="animation-delay: 0.4s;">
            <div class="text-center mb-12">
                <p class="text-[9px] tracking-[0.5em] text-khanaan uppercase mb-2 font-bold">Privilege Guide</p>
                <h3 class="font-serif text-2xl md:text-3xl text-white italic">How to Claim Your Rewards</h3>
            </div>

            <!-- Grid Alur Langkah Klaim Nota Proyek -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Step 1 -->
                <div class="backdrop-blur-sm bg-black/30 border border-white/5 p-8 rounded-xl relative group hover:border-khanaan/20 transition-all duration-300">
                    <div class="absolute -top-5 left-8 w-10 h-10 bg-neutral-900 border border-white/10 rounded-lg flex items-center justify-center font-serif text-khanaan font-bold text-lg shadow-xl">1</div>
                    <h4 class="font-serif text-lg text-white mb-3 mt-2 font-medium">Belanja & Simpan Nota</h4>
                    <p class="text-stone-400 text-xs font-light leading-relaxed">
                        Lakukan transaksi pembelian produk di butik resmi Khanaan secara *offline* maupun melalui platform *online store*. Pastikan Anda menyimpan bukti cetak atau foto nota belanja tersebut dengan jelas.
                    </p>
                    <div class="text-[9px] text-stone-500 tracking-wider uppercase border-t border-white/5 pt-4 mt-6">Langkah Pertama</div>
                </div>

                <!-- Step 2 -->
                <div class="backdrop-blur-sm bg-black/30 border border-white/5 p-8 rounded-xl relative group hover:border-khanaan/20 transition-all duration-300">
                    <div class="absolute -top-5 left-8 w-10 h-10 bg-neutral-900 border border-white/10 rounded-lg flex items-center justify-center font-serif text-khanaan font-bold text-lg shadow-xl">2</div>
                    <h4 class="font-serif text-lg text-white mb-3 mt-2 font-medium">Registrasi Transaksi</h4>
                    <p class="text-stone-400 text-xs font-light leading-relaxed">
                        Masuk ke menu klaim di dashboard Anda, ketik nomor nota beserta nominal transaksi belanja, lalu unggah foto nota tersebut sebagai bukti otentik. Tim kami akan segera melakukan verifikasi data.
                    </p>
                    <div class="mt-6">
                        @auth
                            <a href="{{ route('claims.create') }}" class="inline-block text-[9px] text-khanaan font-bold tracking-widest uppercase border-b border-khanaan/30 pb-0.5 hover:text-white hover:border-white transition-all duration-300">Submit Claim Baru &rarr;</a>
                        @else
                            <button onclick="handleLogin()" class="inline-block text-[9px] text-khanaan font-bold tracking-widest uppercase border-b border-khanaan/30 pb-0.5 hover:text-white hover:border-white transition-all duration-300">Login Untuk Klaim &rarr;</button>
                        @endauth
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="backdrop-blur-sm bg-black/30 border border-white/5 p-8 rounded-xl relative group hover:border-khanaan/20 transition-all duration-300">
                    <div class="absolute -top-5 left-8 w-10 h-10 bg-neutral-900 border border-white/10 rounded-lg flex items-center justify-center font-serif text-khanaan font-bold text-lg shadow-xl">3</div>
                    <h4 class="font-serif text-lg text-white mb-3 mt-2 font-medium">Tukarkan Poin</h4>
                    <p class="text-stone-400 text-xs font-light leading-relaxed">
                        Setelah status klaim disetujui (*approved*), poin loyalitas akun Anda otomatis bertambah. Gunakan poin tersebut untuk menukarkan kupon diskon langsung atau akses ke katalog *Curated Rewards*.
                    </p>
                    <div class="text-[9px] text-stone-500 tracking-wider uppercase border-t border-white/5 pt-4 mt-6">Langkah Terakhir</div>
                </div>
            </div>
        </div>

        <!-- 4. FOOTER -->
        <footer class="py-6 text-center border-t border-white/5 bg-black/20 backdrop-blur-sm">
            <p class="text-[9px] text-stone-500 tracking-[0.2em] uppercase font-semibold">
                &copy; {{ date('Y') }} Khanaan Privé Loyalty Platform. All rights reserved.
            </p>
        </footer>

    </div>

    <!-- Script Interaktif Notifikasi Alur -->
    <script>
        const Toast = Swal.mixin({
            background: '#111111',
            color: '#ffffff',
            confirmButtonColor: '#d4af37',
            cancelButtonColor: '#262626',
            customClass: {
                popup: 'border border-stone-800 rounded-xl shadow-2xl',
                title: 'font-serif text-xl tracking-wide text-khanaan',
                htmlContainer: 'font-sans text-xs text-stone-300 leading-relaxed font-light',
                confirmButton: 'font-sans tracking-widest uppercase text-[10px] font-bold px-6 py-2.5 rounded-sm'
            }
        });

        function handleLogin() {
            Toast.fire({
                title: 'Welcome Back',
                text: 'Silakan masuk terlebih dahulu untuk mendaftarkan nota belanja baru atau menukarkan poin hadiah Anda.',
                icon: 'info',
                confirmButtonText: 'Lanjut ke Login'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "{{ route('login') }}";
                }
            });
        }

        function handleRegister() {
            Toast.fire({
                title: 'Join The Club',
                text: 'Buat akun keanggotaan Khanaan Privé Anda sekarang untuk mengumpulkan poin belanja dan membuka penawaran eksklusif.',
                icon: 'success',
                confirmButtonText: 'Daftar Sekarang'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "{{ route('register') }}";
                }
            });
        }
    </script>
</body>
</html>