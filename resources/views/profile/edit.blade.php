@extends('layouts.main')
@section('title', 'Профайл засах')

@section('content')

@php
    $avatars = [
        'image/unaach.png','image/jaal_huu.png','image/aaw.png','image/eej.png',
        'image/emee.png','image/huurhun_owoo.png','image/ah.png','image/egch.png',
        'image/eregtei_duu.png','image/emegtei_duu.png','image/er_hun.png',
        'image/huurhun_eme.png','image/nohor.png','image/sevger.png',
        'image/nylh_huuhed.png','image/ajiima.png','image/hogshin_aaw.png',
        'image/kazak_aaw.png','image/kazak_eej.png','image/kazak_ah.png',
        'image/buriad_emee.png','image/buriad_owoo.png','image/Halh_ah.png',
    ];
    $current = $user->avatar ?? 'image/unaach.png';
@endphp

{{-- Header card --}}
<div class="glass rounded-3xl shadow-2xl p-6 mb-4 flex items-center gap-4">
    <div class="relative">
        <img id="profile-preview" src="{{ asset($current) }}"
             class="w-20 h-20 rounded-full border-4 border-white shadow-lg object-cover"
             alt="{{ $user->name }}"/>
        <div class="absolute -bottom-1 -right-1 bg-green-400 rounded-full w-6 h-6 flex items-center justify-center text-white text-xs shadow">✎</div>
    </div>
    <div>
        <h1 class="font-bubblegum text-2xl text-green-800">{{ $user->name }}</h1>
        <p class="text-sm font-bold text-gray-500">{{ $user->email }}</p>
    </div>
</div>

{{-- ── Avatar picker ── --}}
<div class="glass rounded-3xl shadow-xl p-5 mb-4">
    <h2 class="font-bubblegum text-lg text-green-800 mb-3">🎨 Аватар сонгох</h2>
    <div class="grid grid-cols-6 gap-2 sm:grid-cols-8">
        @foreach($avatars as $av)
        <button type="button" onclick="pickAvatar('{{ $av }}')"
                class="av-pick rounded-xl border-2 p-0.5 transition-all hover:scale-110 focus:outline-none
                       {{ $av === $current ? 'border-green-500 scale-110 ring-2 ring-green-400' : 'border-transparent' }}"
                data-av="{{ $av }}">
            <img src="{{ asset($av) }}" class="w-full aspect-square rounded-lg object-cover"/>
        </button>
        @endforeach
    </div>
</div>

{{-- ── Profile info form ── --}}
<div class="glass rounded-3xl shadow-xl p-5 mb-4">
    <h2 class="font-bubblegum text-lg text-green-800 mb-4">👤 Мэдээлэл засах</h2>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">@csrf</form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-4">
        @csrf
        @method('patch')
        <input type="hidden" name="avatar" id="avatar-input" value="{{ $current }}"/>

        <div>
            <label class="font-bubblegum text-sm text-gray-700 block mb-1">Нэр</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                   class="w-full rounded-xl border-2 border-green-200 focus:border-green-400 focus:outline-none px-3 py-2 font-nunito text-sm bg-white/80"/>
            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="font-bubblegum text-sm text-gray-700 block mb-1">И-мэйл</label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                   class="w-full rounded-xl border-2 border-green-200 focus:border-green-400 focus:outline-none px-3 py-2 font-nunito text-sm bg-white/80"/>
            @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center gap-3 pt-1">
            <button type="submit"
                    class="font-bubblegum px-6 py-2 bg-green-400 hover:bg-green-500 text-white rounded-full transition-colors shadow">
                💾 Хадгалах
            </button>
            @if(session('status') === 'profile-updated')
                <span class="font-bubblegum text-green-600 text-sm">✓ Хадгалагдлаа!</span>
            @endif
        </div>
    </form>
</div>

{{-- ── Password form ── --}}
<div class="glass rounded-3xl shadow-xl p-5 mb-4">
    <h2 class="font-bubblegum text-lg text-green-800 mb-4">🔒 Нууц үг солих</h2>
    <form method="post" action="{{ route('password.update') }}" class="space-y-4">
        @csrf
        @method('put')

        <div>
            <label class="font-bubblegum text-sm text-gray-700 block mb-1">Одоогийн нууц үг</label>
            <input type="password" name="current_password" autocomplete="current-password"
                   class="w-full rounded-xl border-2 border-blue-200 focus:border-blue-400 focus:outline-none px-3 py-2 font-nunito text-sm bg-white/80"/>
            @error('current_password', 'updatePassword')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="font-bubblegum text-sm text-gray-700 block mb-1">Шинэ нууц үг</label>
            <input type="password" name="password" autocomplete="new-password"
                   class="w-full rounded-xl border-2 border-blue-200 focus:border-blue-400 focus:outline-none px-3 py-2 font-nunito text-sm bg-white/80"/>
            @error('password', 'updatePassword')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="font-bubblegum text-sm text-gray-700 block mb-1">Нууц үг давтах</label>
            <input type="password" name="password_confirmation" autocomplete="new-password"
                   class="w-full rounded-xl border-2 border-blue-200 focus:border-blue-400 focus:outline-none px-3 py-2 font-nunito text-sm bg-white/80"/>
        </div>

        <div class="flex items-center gap-3 pt-1">
            <button type="submit"
                    class="font-bubblegum px-6 py-2 bg-blue-400 hover:bg-blue-500 text-white rounded-full transition-colors shadow">
                🔑 Солих
            </button>
            @if(session('status') === 'password-updated')
                <span class="font-bubblegum text-blue-600 text-sm">✓ Солигдлоо!</span>
            @endif
        </div>
    </form>
</div>

{{-- ── Delete account ── --}}
<div class="glass rounded-3xl shadow-xl p-5 mb-4">
    <h2 class="font-bubblegum text-lg text-red-600 mb-2">⚠️ Бүртгэл устгах</h2>
    <p class="text-xs font-bold text-gray-500 mb-4">Бүртгэлээ устгавал бүх өгөгдөл устна. Буцааж сэргээх боломжгүй.</p>

    <button onclick="document.getElementById('delete-modal').style.display='flex'"
            class="font-bubblegum px-5 py-2 bg-red-400 hover:bg-red-500 text-white rounded-full transition-colors shadow text-sm">
        🗑 Устгах
    </button>
</div>

{{-- Delete modal --}}
<div id="delete-modal" style="display:none" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="glass rounded-3xl shadow-2xl p-6 w-full max-w-sm mx-4 popup">
        <h3 class="font-bubblegum text-xl text-red-600 mb-2">Устгах уу?</h3>
        <p class="text-sm font-bold text-gray-600 mb-4">Нууц үгээ оруулж баталгаажуулна уу.</p>
        <form method="post" action="{{ route('profile.destroy') }}">
            @csrf
            @method('delete')
            <input type="password" name="password" placeholder="Нууц үг"
                   class="w-full rounded-xl border-2 border-red-200 focus:border-red-400 focus:outline-none px-3 py-2 font-nunito text-sm bg-white mb-3"/>
            @error('password', 'userDeletion')
                <p class="text-red-500 text-xs mb-2">{{ $message }}</p>
            @enderror
            <div class="flex gap-2">
                <button type="button" onclick="document.getElementById('delete-modal').style.display='none'"
                        class="flex-1 font-bubblegum py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-full transition-colors">
                    Болих
                </button>
                <button type="submit"
                        class="flex-1 font-bubblegum py-2 bg-red-400 hover:bg-red-500 text-white rounded-full transition-colors">
                    Устгах
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
function pickAvatar(av) {
    document.getElementById('avatar-input').value = av;
    document.getElementById('profile-preview').src = '/' + av;
    document.querySelectorAll('.av-pick').forEach(b => {
        const selected = b.dataset.av === av;
        b.classList.toggle('border-green-500', selected);
        b.classList.toggle('scale-110', selected);
        b.classList.toggle('ring-2', selected);
        b.classList.toggle('ring-green-400', selected);
        b.classList.toggle('border-transparent', !selected);
    });
}
</script>
@endpush