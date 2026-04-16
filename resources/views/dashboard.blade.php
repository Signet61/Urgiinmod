@extends('layouts.main')
@section('title', 'Хянах самбар')

@section('content')

<div class="glass rounded-3xl shadow-2xl p-7 mb-4">

    {{-- Greeting + deer --}}
    <div class="flex items-end gap-4 mb-6">
        <x-deer message="Өнөөдөр ямар зүйл хийх вэ? 🌟" size="md" />
        <div class="flex-1 pb-2">
            <h1 class="font-bubblegum text-3xl text-green-800">
                Сайн уу, {{ auth()->user()->name }}! 👋
            </h1>
            <p class="text-sm font-bold text-gray-500 mt-1">Юу хийх вэ?</p>
        </div>
    </div>

    {{-- Navigation cards grid --}}
    <div class="grid grid-cols-2 gap-3">

        <a href="{{ route('family-tree') }}"
           class="flex flex-col items-center gap-2 p-5 rounded-2xl bg-white/70 border-2 border-green-200 hover:border-green-400 shadow no-underline transition-transform hover:-translate-y-1">
            <img src="{{ asset('image/huurhun_owoo.png') }}" class="w-16 h-16 rounded-full object-cover border-4 border-white shadow" alt=""/>
            <span class="font-bubblegum text-base text-green-800">🌳 Ургийн мод</span>
            <span class="text-xs font-bold text-gray-500 text-center leading-tight">Гэр бүлийн бүх гишүүдийг харах</span>
        </a>

        <a href="{{ route('family-tree') }}?tab=add"
           class="flex flex-col items-center gap-2 p-5 rounded-2xl bg-white/70 border-2 border-pink-200 hover:border-pink-400 shadow no-underline transition-transform hover:-translate-y-1">
            <img src="{{ asset('image/jaal_huu.png') }}" class="w-16 h-16 rounded-full object-cover border-4 border-white shadow" alt=""/>
            <span class="font-bubblegum text-base text-pink-700">➕ Гишүүн нэмэх</span>
            <span class="text-xs font-bold text-gray-500 text-center leading-tight">Шинэ гишүүн нэмж өргөтгөх</span>
        </a>

        <a href="{{ route('game') }}"
           class="flex flex-col items-center gap-2 p-5 rounded-2xl bg-white/70 border-2 border-orange-200 hover:border-orange-400 shadow no-underline transition-transform hover:-translate-y-1">
            <img src="{{ asset('image/nylh_huuhed.png') }}" class="w-16 h-16 rounded-full object-cover border-4 border-white shadow" alt=""/>
            <span class="font-bubblegum text-base text-orange-700">🎮 Тоглоом</span>
            <span class="text-xs font-bold text-gray-500 text-center leading-tight">Хосыг олох тоглоом тоглох</span>
        </a>

        <a href="{{ route('profile.edit') }}"
           class="flex flex-col items-center gap-2 p-5 rounded-2xl bg-white/70 border-2 border-blue-200 hover:border-blue-400 shadow no-underline transition-transform hover:-translate-y-1">
            <img src="{{ asset('image/unaach.png') }}" class="w-16 h-16 rounded-full object-cover border-4 border-white shadow" alt=""/>
            <span class="font-bubblegum text-base text-blue-700">👤 Профайл</span>
            <span class="text-xs font-bold text-gray-500 text-center leading-tight">Мэдээллээ засах</span>
        </a>

    </div>
</div>

{{-- Member count summary --}}
@php $count = auth()->user()->familyMembers()->count(); @endphp
@if($count > 0)
<div class="glass rounded-3xl shadow-xl p-5 text-center">
    <p class="font-bubblegum text-xl text-green-800">
        🌳 Таны ургийн модонд <span class="text-3xl text-orange-500">{{ $count }}</span> гишүүн байна!
    </p>
    <a href="{{ route('family-tree') }}"
       class="inline-block mt-3 font-bubblegum text-base px-7 py-2 bg-green-400 hover:bg-green-500 text-white rounded-full no-underline transition-transform hover:-translate-y-0.5">
        🌳 Харах
    </a>
</div>
@else
<div class="glass rounded-3xl shadow-xl p-5 text-center">
    <p class="font-bubblegum text-lg text-gray-600">
        🌱 Ургийн мод хоосон байна. Гишүүн нэмэж эхэлцгээе!
    </p>
    <a href="{{ route('family-tree') }}?tab=add"
       class="inline-block mt-3 font-bubblegum text-base px-7 py-2 bg-pink-400 hover:bg-pink-500 text-white rounded-full no-underline transition-transform hover:-translate-y-0.5">
        ➕ Гишүүн нэмэх
    </a>
</div>
@endif

@endsection
