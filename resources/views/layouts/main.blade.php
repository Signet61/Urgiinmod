<!DOCTYPE html>
<html lang="mn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Миний Ургийн Мод')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;800;900&family=Bubblegum+Sans&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .font-bubblegum { font-family: 'Bubblegum Sans', cursive; }
        .font-nunito    { font-family: 'Nunito', sans-serif; }

        /* ── Shared animations ── */
        @keyframes deerwobble  { 0%,100%{transform:rotate(-2deg) scale(1)}   50%{transform:rotate(2deg) scale(1.03)} }
        @keyframes bubblefloat { 0%,100%{transform:translateY(0)}             50%{transform:translateY(-5px)} }
        @keyframes avbounce    { 0%,100%{transform:translateY(0)}             50%{transform:translateY(-8px)} }
        @keyframes popin       { from{transform:scale(.7);opacity:0}          to{transform:scale(1);opacity:1} }

        /* ── Deer mascot ── */
        .deer-img {
            animation: deerwobble 3s ease-in-out infinite;
            transform-origin: bottom center;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,.2));
        }
        .deer-bubble {
            animation: bubblefloat 2.8s ease-in-out infinite;
            position: relative;
        }
        .deer-bubble::after {
            content:''; position:absolute; bottom:-16px; left:26px;
            border-width:16px 10px 0; border-style:solid;
            border-color:#86efac transparent transparent;
        }
        .deer-bubble::before {
            content:''; position:absolute; bottom:-11px; left:28px; z-index:1;
            border-width:12px 8px 0; border-style:solid;
            border-color:#fff transparent transparent;
        }

        /* ── Avatar bounce row ── */
        .av-item { animation: avbounce 2.4s ease-in-out infinite; }
        .av-item:nth-child(2){animation-delay:.2s} .av-item:nth-child(3){animation-delay:.4s}
        .av-item:nth-child(4){animation-delay:.6s} .av-item:nth-child(5){animation-delay:.8s}
        .av-item:nth-child(6){animation-delay:1s}

        /* ── Glass card ── */
        .glass { background:rgba(255,255,255,.82); backdrop-filter:blur(12px); border:1px solid rgba(255,255,255,.5); }

        /* ── Popup animation ── */
        .popup { animation: popin .25s cubic-bezier(.34,1.56,.64,1); }
    </style>

    @stack('styles')
</head>
<body class="min-h-screen overflow-x-hidden font-nunito"
      style="background:url('/landscape.png') center/cover no-repeat fixed;">

{{-- ── Top navigation bar ── --}}
<nav class="sticky top-0 z-50 bg-white/75 backdrop-blur-md border-b border-white/40 shadow-sm">
    <div class="max-w-3xl mx-auto px-4 py-2 flex items-center justify-between gap-2 flex-wrap">

        {{-- Logo --}}
        <a href="{{ route('home') }}" class="flex items-center gap-1.5 no-underline">
            <img src="{{ asset('mongoliandeer.png') }}" class="w-8 h-8 object-contain" alt=""/>
            <span class="font-bubblegum text-lg text-green-800 leading-none">Ургийн Мод</span>
        </a>

        {{-- Nav links --}}
        <div class="flex gap-1.5 flex-wrap items-center">
            <a href="{{ route('family-tree') }}"
               class="font-bubblegum text-sm px-3 py-1.5 bg-green-400 hover:bg-green-500 text-white rounded-full no-underline transition-colors flex items-center gap-1.5 {{ request()->routeIs('family-tree') ? 'ring-2 ring-green-700' : '' }}">
                <i data-lucide="tree-pine" class="w-4 h-4"></i> Мод
            </a>
            <a href="{{ route('game') }}"
               class="font-bubblegum text-sm px-3 py-1.5 bg-orange-400 hover:bg-orange-500 text-white rounded-full no-underline transition-colors flex items-center gap-1.5 {{ request()->routeIs('game') ? 'ring-2 ring-orange-700' : '' }}">
                <i data-lucide="gamepad-2" class="w-4 h-4"></i> Тоглоом
            </a>
            @auth
                <a href="{{ route('dashboard') }}"
                   class="font-bubblegum text-sm px-3 py-1.5 bg-blue-400 hover:bg-blue-500 text-white rounded-full no-underline transition-colors flex items-center gap-1.5 {{ request()->routeIs('dashboard') ? 'ring-2 ring-blue-700' : '' }}">
                    <i data-lucide="house" class="w-4 h-4"></i> Самбар
                </a>
                <a href="{{ route('profile.edit') }}"
                   class="flex items-center gap-1.5 px-2 py-1 rounded-full no-underline transition-colors hover:bg-white/50 {{ request()->routeIs('profile.*') ? 'ring-2 ring-purple-400 bg-white/40' : '' }}">
                    <img src="{{ asset(auth()->user()->avatar ?? 'image/unaach.png') }}"
                         class="w-7 h-7 rounded-full border-2 border-white shadow object-cover" alt=""/>
                    <span class="font-bubblegum text-sm text-purple-700 hidden sm:inline">{{ auth()->user()->name }}</span>
                </a>
                <form method="POST" action="{{ route('logout') }}" class="inline m-0">
                    @csrf
                    <button type="submit"
                            class="font-bubblegum text-sm px-3 py-1.5 bg-red-300 hover:bg-red-400 text-red-900 rounded-full border-0 cursor-pointer transition-colors">
                        Гарах
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}"
                   class="font-bubblegum text-sm px-3 py-1.5 bg-purple-400 hover:bg-purple-500 text-white rounded-full no-underline transition-colors flex items-center gap-1.5">
                    <i data-lucide="key-round" class="w-4 h-4"></i> Нэвтрэх
                </a>
                <a href="{{ route('register') }}"
                   class="font-bubblegum text-sm px-3 py-1.5 bg-pink-400 hover:bg-pink-500 text-white rounded-full no-underline transition-colors flex items-center gap-1.5">
                    <i data-lucide="pencil-line" class="w-4 h-4"></i> Бүртгэл
                </a>
            @endauth
        </div>

    </div>
</nav>

{{-- ── Page content ── --}}
<div class="max-w-3xl mx-auto px-3 py-6 pb-16">
    @yield('content')
</div>

@stack('scripts')
</body>
</html>
