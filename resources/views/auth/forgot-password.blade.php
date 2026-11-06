<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password — {{ config('app.name', 'Khanaan') }}</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght=0,400;0,600;0,700;1,400&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    
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

    <!-- Overlay Gelap Sinematik -->
    <div class="absolute inset-0 bg-gradient-to-b from-black/90 via-black/80 to-neutral-950/95 backdrop-blur-[4px] z-0"></div>

    <!-- Layout Wrapper -->
    <div class="relative z-10 min-h-screen flex flex-col justify-between p-6">
        
        <!-- TOP LOGO -->
        <header class="flex justify-center md:justify-start items-center md:px-6 py-4 fade-in">
            <a href="/" class="text-xl font-serif tracking-[0.25em] uppercase font-bold text-white hover:text-khanaan transition-colors">
                Khanaan
            </a>
        </header>

        <!-- CENTER CARD -->
        <main class="flex flex-col items-center justify-center py-8 w-full max-w-md mx-auto fade-in" style="animation-delay: 0.15s;">
            <div class="w-full backdrop-blur-xl bg-black/40 border border-white/15 p-8 md:p-10 rounded-2xl shadow-2xl shadow-black/80">
                
                <!-- Header -->
                <div class="text-center mb-8">
                    <div class="flex justify-center items-center space-x-2 mb-2">
                        <span class="h-[1px] w-4 bg-khanaan/40"></span>
                        <span class="text-[9px] uppercase tracking-[0.3em] text-khanaan font-bold">Account Recovery</span>
                        <span class="h-[1px] w-4 bg-khanaan/40"></span>
                    </div>
                    <h1 class="text-2xl md:text-3xl font-serif tracking-wide text-white">Forgot Password?</h1>
                    <p class="text-xs text-stone-400 mt-3 leading-relaxed">
                        Enter your email address and we'll send you a link to reset your password.
                    </p>
                </div>

                <!-- Session Status -->
                @if (session('status'))
                    <div class="mb-4 text-xs bg-green-900/30 border border-green-600/30 text-green-300 p-3 rounded-lg">
                        {{ session('status') }}
                    </div>
                @endif

                <!-- Form -->
                <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
                    @csrf

                    <!-- Email Input -->
                    <div>
                        <label for="email" class="block text-xs uppercase tracking-[0.2em] text-stone-300 mb-3 font-semibold">Email Address</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white text-sm focus:border-khanaan/50 focus:bg-white/10 transition-all duration-300 placeholder-stone-500"
                            placeholder="you@example.com">
                        @if ($errors->has('email'))
                            <p class="text-xs text-red-400 mt-2">{{ $errors->first('email') }}</p>
                        @endif
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" 
                        class="w-full mt-6 bg-khanaan hover:bg-yellow-600 text-black font-bold py-3 px-4 rounded-lg text-sm uppercase tracking-widest transition-all duration-300 hover:shadow-lg hover:shadow-khanaan/40">
                        Send Reset Link
                    </button>
                </form>

                <!-- Divider -->
                <div class="relative my-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-white/10"></div>
                    </div>
                    <div class="relative flex justify-center text-xs">
                        <span class="px-2 bg-black/40 text-stone-500">Remember your password?</span>
                    </div>
                </div>

                <!-- Back to Login -->
                <a href="{{ route('login') }}" class="block text-center text-xs text-khanaan hover:text-yellow-400 transition-colors font-semibold uppercase tracking-wide">
                    Back to Sign In
                </a>
            </div>
        </main>

        <!-- Footer -->
        <footer class="text-center text-xs text-stone-600 fade-in" style="animation-delay: 0.3s;">
            &copy; {{ date('Y') }} Khanaan. All rights reserved.
        </footer>
    </div>
</body>
</html>
