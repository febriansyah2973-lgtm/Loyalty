<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email — {{ config('app.name', 'Khanaan') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght=0,400;0,600;0,700;1,400&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        serif: ['Playfair Display', 'serif'],
                    },
                    colors: { khanaan: '#d4af37' }
                }
            }
        }
    </script>
    <style>
        .fade-in { animation: fadeIn 1s ease-in-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="bg-neutral-950 text-white font-sans antialiased min-h-screen bg-cover bg-center bg-no-repeat bg-fixed relative flex flex-col justify-between selection:bg-khanaan selection:text-black" 
      style="background-image: url('{{ asset('images/bg-loyalty.jpg') }}');">
    <div class="absolute inset-0 bg-gradient-to-b from-black/90 via-black/80 to-neutral-950/95 backdrop-blur-[4px] z-0"></div>
    <div class="relative z-10 min-h-screen flex flex-col justify-between p-6">
        <header class="flex justify-center md:justify-start items-center md:px-6 py-4 fade-in">
            <a href="/" class="text-xl font-serif tracking-[0.25em] uppercase font-bold text-white hover:text-khanaan transition-colors">Khanaan</a>
        </header>
        <main class="flex flex-col items-center justify-center py-8 w-full max-w-md mx-auto fade-in" style="animation-delay: 0.15s;">
            <div class="w-full backdrop-blur-xl bg-black/40 border border-white/15 p-8 md:p-10 rounded-2xl shadow-2xl shadow-black/80">
                <div class="text-center mb-8">
                    <div class="flex justify-center items-center space-x-2 mb-2">
                        <span class="h-[1px] w-4 bg-khanaan/40"></span>
                        <span class="text-[9px] uppercase tracking-[0.3em] text-khanaan font-bold">Verification</span>
                        <span class="h-[1px] w-4 bg-khanaan/40"></span>
                    </div>
                    <h1 class="text-2xl md:text-3xl font-serif tracking-wide text-white">Verify Your Email</h1>
                    <p class="text-xs text-stone-400 mt-3 leading-relaxed">Check your inbox for verification link. If you didn't receive it, we can resend.</p>
                </div>
                @if (session('status') == 'verification-link-sent')
                    <div class="mb-4 text-xs bg-green-900/30 border border-green-600/30 text-green-300 p-3 rounded-lg">A verification link has been sent to your email.</div>
                @endif
                <div class="space-y-3">
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit" class="w-full bg-khanaan hover:bg-yellow-600 text-black font-bold py-3 px-4 rounded-lg text-sm uppercase tracking-widest transition-all hover:shadow-lg hover:shadow-khanaan/40">Resend Verification Email</button>
                    </form>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full bg-white/10 hover:bg-white/20 border border-white/20 text-white font-bold py-3 px-4 rounded-lg text-sm uppercase tracking-widest transition-all">Log Out</button>
                    </form>
                </div>
            </div>
        </main>
        <footer class="text-center text-xs text-stone-600 fade-in" style="animation-delay: 0.3s;">&copy; {{ date('Y') }} Khanaan. All rights reserved.</footer>
    </div>
</body>
</html>
