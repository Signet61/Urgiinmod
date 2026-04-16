@extends('layouts.main')
@section('title', 'Миний Ургийн Мод – Тавтай морил')

@section('content')

{{-- ══ Hero card ══ --}}
<div class="glass rounded-3xl shadow-2xl p-7 mb-4">

    {{-- Deer + title --}}
    <div class="flex items-end gap-4 mb-5">
        <x-deer message="Тавтай морилно уу!" size="lg" />
        <div class="flex-1 text-center pb-2">
            <h1 class="font-bubblegum text-4xl md:text-5xl text-green-800 leading-tight"
                style="text-shadow:2px 2px 0 rgba(255,255,255,.7)">
                <i data-lucide="tree-pine" class="w-10 h-10 inline-block align-middle"></i> Миний<br>Ургийн Мод
            </h1>
            <p class="text-base font-bold text-green-700 mt-2">Гэр бүлийнхнээ таниарай!</p>
        </div>
    </div>

    {{-- Bouncing avatar preview --}}
    <div class="flex justify-center gap-2 flex-wrap my-4">
        @foreach(['image/huurhun_owoo.png','image/emee.png','image/aaw.png','image/eej.png','image/jaal_huu.png','image/huurhun_eme.png'] as $av)
            <div class="av-item w-14 h-14 rounded-full overflow-hidden border-4 border-white shadow-lg">
                <img src="{{ asset($av) }}" class="w-full h-full object-cover" alt=""/>
            </div>
        @endforeach
    </div>

    {{-- Main CTAs --}}
    <div class="flex gap-3 justify-center flex-wrap mt-5">
        <a href="{{ route('family-tree') }}"
           class="font-bubblegum text-xl px-10 py-3 bg-green-500 hover:bg-green-400 text-white rounded-full no-underline transition-transform hover:-translate-y-1 active:translate-y-0.5 flex items-center gap-2"
           style="box-shadow:0 5px 0 #388e3c">
            <i data-lucide="tree-pine" class="w-5 h-5"></i> Ургийн мод харах
        </a>
        <a href="{{ route('game') }}"
           class="font-bubblegum text-xl px-10 py-3 bg-orange-400 hover:bg-orange-300 text-white rounded-full no-underline transition-transform hover:-translate-y-1 active:translate-y-0.5 flex items-center gap-2"
           style="box-shadow:0 5px 0 #e65100">
            <i data-lucide="gamepad-2" class="w-5 h-5"></i> Тоглоом тоглох
        </a>
    </div>
</div>

{{-- ══ Auth section ══ --}}
@auth
<div class="glass rounded-3xl shadow-xl p-6 text-center">
    <p class="font-bubblegum text-xl text-green-800 mb-3">
        Сайн уу, {{ auth()->user()->name }}!
    </p>
    <a href="{{ route('dashboard') }}"
       class="font-bubblegum text-lg px-8 py-2.5 bg-blue-400 hover:bg-blue-500 text-white rounded-full no-underline transition-transform hover:-translate-y-0.5 inline-flex items-center gap-2"
       style="box-shadow:0 4px 0 #1565c0">
        <i data-lucide="house" class="w-5 h-5"></i> Хянах самбар руу орох
    </a>
</div>
@else
<div class="glass rounded-3xl shadow-xl p-6">
    <p class="text-center text-sm font-bold text-gray-600 mb-4 flex items-center justify-center gap-1.5">
        <i data-lucide="lightbulb" class="w-4 h-4 text-yellow-500"></i>
        Нэвтэрснээр өөрийн дансанд гэр бүлийн мэдээллийг хадгалах боломжтой!
    </p>
    <div class="flex gap-3 justify-center flex-wrap">
        <a href="{{ route('login') }}"
           class="font-bubblegum text-lg px-8 py-2.5 bg-purple-400 hover:bg-purple-500 text-white rounded-full no-underline transition-transform hover:-translate-y-0.5 flex items-center gap-2"
           style="box-shadow:0 4px 0 #6a1b9a">
            <i data-lucide="key-round" class="w-5 h-5"></i> Нэвтрэх
        </a>
        <a href="{{ route('register') }}"
           class="font-bubblegum text-lg px-8 py-2.5 bg-pink-400 hover:bg-pink-500 text-white rounded-full no-underline transition-transform hover:-translate-y-0.5 flex items-center gap-2"
           style="box-shadow:0 4px 0 #880e4f">
            <i data-lucide="pencil-line" class="w-5 h-5"></i> Бүртгүүлэх
        </a>
    </div>
</div>
@endauth

@endsection
