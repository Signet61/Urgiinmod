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
        .font-nunito { font-family: 'Nunito', sans-serif; }

        @keyframes deerwobble { 0%,100%{transform:rotate(-2deg) scale(1)} 50%{transform:rotate(2deg) scale(1.03)} }
        @keyframes bubblefloat { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-5px)} }
        @keyframes avbounce { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-8px)} }
        @keyframes popin { from{transform:scale(.7);opacity:0} to{transform:scale(1);opacity:1} }

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
            content: '';
            position: absolute;
            bottom: -16px;
            left: 26px;
            border-width: 16px 10px 0;
            border-style: solid;
            border-color: #86efac transparent transparent;
        }

        .deer-bubble::before {
            content: '';
            position: absolute;
            bottom: -11px;
            left: 28px;
            z-index: 1;
            border-width: 12px 8px 0;
            border-style: solid;
            border-color: #fff transparent transparent;
        }

        .av-item { animation: avbounce 2.4s ease-in-out infinite; }
        .av-item:nth-child(2){animation-delay:.2s}
        .av-item:nth-child(3){animation-delay:.4s}
        .av-item:nth-child(4){animation-delay:.6s}
        .av-item:nth-child(5){animation-delay:.8s}
        .av-item:nth-child(6){animation-delay:1s}

        .glass {
            background: rgba(255,255,255,.82);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,.5);
        }

        .popup { animation: popin .25s cubic-bezier(.34,1.56,.64,1); }
    </style>

    @stack('styles')
</head>
<body class="min-h-screen overflow-x-hidden font-nunito" style="background:url('/landscape.png') center/cover no-repeat fixed;">

<nav class="sticky top-0 z-50 border-b border-white/40 bg-white/75 shadow-sm backdrop-blur-md">
    <div class="mx-auto flex max-w-[1850px] flex-wrap items-center justify-between gap-2 px-4 py-2">
        <a href="{{ route('home') }}" class="flex items-center gap-1.5 no-underline">
            <img src="{{ asset('mongoliandeer.png') }}" class="h-8 w-8 object-contain" alt=""/>
            <span class="font-bubblegum text-lg leading-none text-green-800">Ургийн Мод</span>
        </a>

        <div class="flex flex-wrap items-center gap-1.5">
            <a href="{{ route('family-tree') }}" class="flex items-center gap-1.5 rounded-full bg-green-400 px-3 py-1.5 font-bubblegum text-sm text-white no-underline transition-colors hover:bg-green-500 {{ request()->routeIs('family-tree') ? 'ring-2 ring-green-700' : '' }}">
                <i data-lucide="tree-pine" class="h-4 w-4"></i> Мод
            </a>
            <a href="{{ route('game') }}" class="flex items-center gap-1.5 rounded-full bg-orange-400 px-3 py-1.5 font-bubblegum text-sm text-white no-underline transition-colors hover:bg-orange-500 {{ request()->routeIs('game') ? 'ring-2 ring-orange-700' : '' }}">
                <i data-lucide="gamepad-2" class="h-4 w-4"></i> Тоглоом
            </a>

            @auth
                <a href="{{ route('dashboard') }}" class="flex items-center gap-1.5 rounded-full bg-blue-400 px-3 py-1.5 font-bubblegum text-sm text-white no-underline transition-colors hover:bg-blue-500 {{ request()->routeIs('dashboard') ? 'ring-2 ring-blue-700' : '' }}">
                    <i data-lucide="house" class="h-4 w-4"></i> Самбар
                </a>
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-1.5 rounded-full px-2 py-1 no-underline transition-colors hover:bg-white/50 {{ request()->routeIs('profile.*') ? 'ring-2 ring-purple-400 bg-white/40' : '' }}">
                    <img src="{{ asset(auth()->user()->avatar ?? 'image/unaach.png') }}" class="h-7 w-7 rounded-full border-2 border-white object-cover shadow" alt=""/>
                    <span class="hidden font-bubblegum text-sm text-purple-700 sm:inline">{{ auth()->user()->name }}</span>
                </a>
                <form method="POST" action="{{ route('logout') }}" class="m-0 inline">
                    @csrf
                    <button type="submit" class="cursor-pointer rounded-full border-0 bg-red-300 px-3 py-1.5 font-bubblegum text-sm text-red-900 transition-colors hover:bg-red-400">Гарах</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="flex items-center gap-1.5 rounded-full bg-purple-400 px-3 py-1.5 font-bubblegum text-sm text-white no-underline transition-colors hover:bg-purple-500">
                    <i data-lucide="key-round" class="h-4 w-4"></i> Нэвтрэх
                </a>
                <a href="{{ route('register') }}" class="flex items-center gap-1.5 rounded-full bg-pink-400 px-3 py-1.5 font-bubblegum text-sm text-white no-underline transition-colors hover:bg-pink-500">
                    <i data-lucide="pencil-line" class="h-4 w-4"></i> Бүртгэл
                </a>
            @endauth
        </div>
    </div>
</nav>

<div class="mx-auto max-w-[1850px] px-3 py-6 pb-16">
    @yield('content')
</div>

@stack('scripts')
</body>
</html>
