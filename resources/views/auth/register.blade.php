<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join the Club — {{ config('app.name', 'Khanaan') }}</title>
    
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

    <style>
        .fade-in {
            animation: fadeIn 1s ease-in-out forwards;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-neutral-950 text-white font-sans antialiased min-h-screen bg-cover bg-center bg-no-repeat bg-fixed relative flex flex-col justify-between selection:bg-khanaan selection:text-black" 
      style="background-image: url('{{ asset('images/bg-loyalty.jpg') }}');">

    <!-- Overlay Gelap Sinematik Semitransparan + Blur -->
    <div class="absolute inset-0 bg-gradient-to-b from-black/90 via-black/80 to-neutral-950/95 backdrop-blur-[4px] z-0"></div>

    <!-- Layout Wrapper -->
    <div class="relative z-10 min-h-screen flex flex-col justify-between p-6">
        
        <!-- TOP LOGO / BACK TO HOME -->
        <header class="flex justify-center md:justify-start items-center md:px-6 py-4 fade-in">
            <a href="/" class="group text-xl font-serif tracking-[0.25em] uppercase font-bold text-white transition-colors duration-300">
                Khanaan <span class="italic font-normal text-stone-400 group-hover:text-khanaan transition-colors">
            </a>
        </header>

        <!-- CENTER REGISTER CARD -->
        <main class="flex flex-col items-center justify-center py-6 w-full max-w-md mx-auto fade-in" style="animation-delay: 0.15s;">
            <div class="w-full backdrop-blur-xl bg-black/40 border border-white/15 p-8 md:p-10 rounded-2xl shadow-2xl shadow-black/80">
                
                <!-- Header Di Dalam Card -->
                <div class="text-center mb-6">
                    <div class="flex justify-center items-center space-x-2 mb-2">
                        <span class="h-[1px] w-4 bg-khanaan/40"></span>
                        <span class="text-[9px] uppercase tracking-[0.3em] text-khanaan font-bold">Privilege Membership</span>
                        <span class="h-[1px] w-4 bg-khanaan/40"></span>
                    </div>
                    <h1 class="text-2xl md:text-3xl font-serif tracking-wide text-white">Create Account</h1>
                </div>

                <!-- Form Register -->
                <form method="POST" action="{{ route('register') }}" class="space-y-4">
                    @csrf

                    <!-- Name Field -->
                    <div>
                        <label for="name" class="block text-[10px] uppercase tracking-[0.2em] text-stone-300 font-semibold mb-1.5">Full Name</label>
                        <input id="name" 
                               type="text" 
                               name="name" 
                               value="{{ old('name') }}" 
                               required 
                               autofocus 
                               autocomplete="name" 
                               placeholder="e.g., Jane Doe"
                               class="w-full bg-neutral-900/60 border border-stone-800 rounded-lg px-4 py-2.5 text-sm text-white placeholder-stone-600 focus:outline-none focus:border-khanaan focus:ring-1 focus:ring-khanaan transition-all duration-300">
                        @if($errors->has('name'))
                            <p class="mt-1.5 text-xs text-red-400 font-light">{{ $errors->first('name') }}</p>
                        @endif
                    </div>

                    <!-- Email Field -->
                    <div>
                        <label for="email" class="block text-[10px] uppercase tracking-[0.2em] text-stone-300 font-semibold mb-1.5">Email Address</label>
                        <input id="email" 
                               type="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               required 
                               autocomplete="username" 
                               placeholder="name@domain.com"
                               class="w-full bg-neutral-900/60 border border-stone-800 rounded-lg px-4 py-2.5 text-sm text-white placeholder-stone-600 focus:outline-none focus:border-khanaan focus:ring-1 focus:ring-khanaan transition-all duration-300">
                        @if($errors->has('email'))
                            <p class="mt-1.5 text-xs text-red-400 font-light">{{ $errors->first('email') }}</p>
                        @endif
                    </div>

                    <!-- Password Field -->
                    <div>
                        <label for="password" class="block text-[10px] uppercase tracking-[0.2em] text-stone-300 font-semibold mb-1.5">Password</label>
                        <input id="password" 
                               type="password" 
                               name="password" 
                               required 
                               autocomplete="new-password" 
                               placeholder="Minimum 8 characters"
                               class="w-full bg-neutral-900/60 border border-stone-800 rounded-lg px-4 py-2.5 text-sm text-white placeholder-stone-600 focus:outline-none focus:border-khanaan focus:ring-1 focus:ring-khanaan transition-all duration-300">
                        @if($errors->has('password'))
                            <p class="mt-1.5 text-xs text-red-400 font-light">{{ $errors->first('password') }}</p>
                        @endif
                    </div>

                    <!-- Confirm Password Field -->
                    <div>
                        <label for="password_confirmation" class="block text-[10px] uppercase tracking-[0.2em] text-stone-300 font-semibold mb-1.5">Confirm Password</label>
                        <input id="password_confirmation" 
                               type="password" 
                               name="password_confirmation" 
                               required 
                               autocomplete="new-password" 
                               placeholder="Repeat your password"
                               class="w-full bg-neutral-900/60 border border-stone-800 rounded-lg px-4 py-2.5 text-sm text-white placeholder-stone-600 focus:outline-none focus:border-khanaan focus:ring-1 focus:ring-khanaan transition-all duration-300">
                        @if($errors->has('password_confirmation'))
                            <p class="mt-1.5 text-xs text-red-400 font-light">{{ $errors->first('password_confirmation') }}</p>
                        @endif
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-3">
                        <button type="submit" class="w-full bg-khanaan text-black font-bold tracking-widest uppercase text-xs py-3.5 rounded-lg hover:bg-[#b8952e] active:scale-[0.99] focus:outline-none focus:ring-2 focus:ring-khanaan focus:ring-offset-2 focus:ring-offset-neutral-950 transition-all duration-150 shadow-xl shadow-khanaan/10">
                            Register & Join Club
                        </button>
                    </div>
                </form>

                <!-- Link Kembali ke Login -->
                <div class="mt-6 text-center border-t border-white/5 pt-4">
                    <p class="text-xs text-stone-400">
                        Already have an account? 
                        <a href="{{ route('login') }}" class="text-khanaan hover:underline font-medium ms-1">Sign In instead</a>
                    </p>
                </div>

            </div>
        </main>

        <!-- FOOTER -->
        <footer class="py-4 text-center">
            <p class="text-[9px] text-stone-600 tracking-[0.2em] uppercase">
                &copy; {{ date('Y') }} Khanaan.
            </p>
        </footer>

    </div>

</body>
</html>