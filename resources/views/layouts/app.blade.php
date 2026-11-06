<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Khanaan Loyalty') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">

        <!-- Tailwind CDN ensures all utility classes work regardless of build state -->
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        fontFamily: {
                            display: ['Cormorant Garamond', 'serif'],
                            sans: ['DM Sans', 'sans-serif'],
                        }
                    }
                }
            }
        </script>

        <style>
            :root {
                --gold: #B8964E;
                --gold-light: #D4AF6A;
                --cream: #FAF8F5;
                --ink: #1A1A1A;
            }
            * { box-sizing: border-box; }
            ::-webkit-scrollbar { width: 4px; }
            ::-webkit-scrollbar-track { background: var(--cream); }
            ::-webkit-scrollbar-thumb { background: var(--gold); border-radius: 2px; }
            
            body { 
                font-family: 'DM Sans', sans-serif;
                background: var(--cream);
                color: var(--ink);
                -webkit-font-smoothing: antialiased;
            }
            .font-display { font-family: 'Cormorant Garamond', serif; }

            /* Page entrance animation */
            .page-enter {
                animation: pageEnter 0.5s cubic-bezier(0.22, 1, 0.36, 1) forwards;
            }
            @keyframes pageEnter {
                from { opacity: 0; transform: translateY(16px); }
                to   { opacity: 1; transform: translateY(0); }
            }

            /* Staggered — opacity starts at 1, only translates */
            .stagger > * {
                animation: staggerSlide 0.45s ease both;
            }
            @keyframes staggerSlide {
                from { transform: translateY(14px); }
                to   { transform: translateY(0); }
            }
            .stagger > *:nth-child(1) { animation-delay: 0.04s; }
            .stagger > *:nth-child(2) { animation-delay: 0.10s; }
            .stagger > *:nth-child(3) { animation-delay: 0.16s; }
            .stagger > *:nth-child(4) { animation-delay: 0.22s; }
            .stagger > *:nth-child(5) { animation-delay: 0.28s; }
            .stagger > *:nth-child(6) { animation-delay: 0.34s; }

            /* Gold underline link effect */
            .gold-link {
                position: relative;
                display: inline-block;
            }
            .gold-link::after {
                content: '';
                position: absolute;
                bottom: -2px; left: 0;
                width: 0; height: 1px;
                background: var(--gold);
                transition: width 0.3s ease;
            }
            .gold-link:hover::after { width: 100%; }

            /* Gold divider */
            .gold-divider {
                width: 40px; height: 1px;
                background: linear-gradient(90deg, var(--gold), transparent);
                margin: 12px 0;
            }

            /* Backward compat alias */
            .khanaan-font-serif, .font-serif-display {
                font-family: 'Cormorant Garamond', serif !important;
            }

            input:focus, textarea:focus, select:focus {
                outline: none;
                border-color: var(--gold) !important;
            }
        </style>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <div class="min-h-screen flex flex-col">
            @include('layouts.navigation')

            <main class="flex-grow page-enter">
                {{ $slot }}
            </main>

            <footer class="border-t border-stone-200 bg-white py-10 mt-16">
                <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row justify-between items-center gap-4">
                    <span class="font-display text-lg tracking-[0.3em] text-stone-800">KHANAAN</span>
                    <p class="text-[10px] tracking-[0.3em] text-stone-400 uppercase">&copy; {{ date('Y') }} Khanaan. All rights reserved.</p>
                    <div class="flex gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                        <span class="w-1.5 h-1.5 rounded-full bg-stone-300"></span>
                        <span class="w-1.5 h-1.5 rounded-full bg-stone-200"></span>
                    </div>
                </div>
            </footer>
        </div>
    </body>
</html>