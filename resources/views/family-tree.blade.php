@extends('layouts.main-wide')
@section('title', 'Ургийн мод')

@php
    $relationMeta = [
        'me' => ['label' => 'Би', 'color' => '#f5f5f5', 'stroke' => '#8a8a8a'],
        'dad' => ['label' => 'Аав', 'color' => '#dbeafe', 'stroke' => '#7c95ad'],
        'mom' => ['label' => 'Ээж', 'color' => '#fce7f3', 'stroke' => '#9f7b8a'],
        'sib' => ['label' => 'Ах/Эгч/Дүү', 'color' => '#e2e8f0', 'stroke' => '#7b8794'],
        'child' => ['label' => 'Хүүхэд', 'color' => '#ecfccb', 'stroke' => '#8a9761'],
        'partner' => ['label' => 'Хань', 'color' => '#f3e8ff', 'stroke' => '#8e77a8'],
        'gpl' => ['label' => 'Өвөө', 'color' => '#f3f4f6', 'stroke' => '#8b8b8b'],
        'gml' => ['label' => 'Эмээ', 'color' => '#f3f4f6', 'stroke' => '#8b8b8b'],
        'gpr' => ['label' => 'Өвөө', 'color' => '#f3f4f6', 'stroke' => '#8b8b8b'],
        'gmr' => ['label' => 'Эмээ', 'color' => '#f3f4f6', 'stroke' => '#8b8b8b'],
        'ggplf' => ['label' => 'Өвөөгийн аав', 'color' => '#f3f4f6', 'stroke' => '#8b8b8b'],
        'ggplm' => ['label' => 'Өвөөгийн ээж', 'color' => '#f3f4f6', 'stroke' => '#8b8b8b'],
        'ggmlf' => ['label' => 'Эмээгийн аав', 'color' => '#f3f4f6', 'stroke' => '#8b8b8b'],
        'ggmlm' => ['label' => 'Эмээгийн ээж', 'color' => '#f3f4f6', 'stroke' => '#8b8b8b'],
        'ggprf' => ['label' => 'Өвөөгийн аав', 'color' => '#f3f4f6', 'stroke' => '#8b8b8b'],
        'ggprm' => ['label' => 'Өвөөгийн ээж', 'color' => '#f3f4f6', 'stroke' => '#8b8b8b'],
        'ggmrf' => ['label' => 'Эмээгийн аав', 'color' => '#f3f4f6', 'stroke' => '#8b8b8b'],
        'ggmrm' => ['label' => 'Эмээгийн ээж', 'color' => '#f3f4f6', 'stroke' => '#8b8b8b'],
        'uncle' => ['label' => 'Авга/Нагац ах', 'color' => '#fef3c7', 'stroke' => '#9c8a62'],
        'aunt' => ['label' => 'Авга/Нагац эгч', 'color' => '#fde68a', 'stroke' => '#9e8747'],
        'cousin' => ['label' => 'Үеэл', 'color' => '#e0f2fe', 'stroke' => '#6f90a8'],
    ];

    $relationOptions = collect($relationMeta)->mapWithKeys(fn ($meta, $key) => [$key => $meta['label']])->all();

    $defaultMembers = [[
        'id' => 1,
        'name' => 'Би',
        'rel' => 'me',
        'related_to_id' => null,
        'emoji' => 'image/jaal_huu.png',
        'photo' => null,
        'bio' => 'Үндсэн хүн',
    ]];

    $serverMembersData = auth()->check() && isset($members)
        ? $members->map(fn ($member) => [
            'id' => $member->id,
            'name' => $member->name,
            'rel' => $member->rel,
            'related_to_id' => $member->related_to_id,
            'emoji' => $member->emoji,
            'photo' => $member->photo ? asset('storage/' . $member->photo) : null,
            'bio' => $member->bio,
        ])->values()->toArray()
        : [];

    $initialMembersData = auth()->check()
        ? $serverMembersData
        : (count($serverMembersData) ? $serverMembersData : $defaultMembers);
@endphp

@section('content')
@if(session('success'))
    <div class="glass mb-3 rounded-2xl border border-green-300 px-4 py-3 text-center font-black text-green-800">
        {{ session('success') }}
    </div>
@endif

<div class="glass rounded-3xl p-4 shadow-2xl grid grid-cols-1 gap-4 xl:grid-cols-4">
    <div class="space-y-4 xl:col-span-1">
        <x-deer message="Ургийн модоо удирдаарай." msgId="deer-msg" size="sm" />

        <div class="rounded-2xl border border-teal-200 bg-white/90 p-4 space-y-3 shadow-sm">
            <div class="flex items-center gap-3">
                <div id="sel-av-area" class="h-14 w-14 overflow-hidden rounded-full border-4 border-white bg-gray-100 shadow"></div>
                <div>
                    <div id="sel-name" class="font-bubblegum text-xl text-green-800">Сонгогдоогүй</div>
                    <div id="sel-rel" class="text-sm font-black text-gray-500">-</div>
                </div>
            </div>

            <div class="grid grid-cols-[auto_1fr] gap-x-2 gap-y-1 text-sm">
                <div class="font-extrabold text-slate-500">Нэр</div><div id="sel-name-meta" class="text-right font-bold text-slate-700">-</div>
                <div class="font-extrabold text-slate-500">Харилцаа</div><div id="sel-rel-meta" class="text-right font-bold text-slate-700">-</div>
                <div class="font-extrabold text-slate-500">Тэмдэглэл</div><div id="sel-bio-meta" class="text-right font-bold text-slate-700">-</div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                <button type="button" class="rounded-xl bg-teal-700 py-2.5 font-black text-white transition hover:bg-teal-800" onclick="openEditModal()">Хүн засах</button>
                <button type="button" class="rounded-xl bg-teal-600 py-2.5 font-black text-white transition hover:bg-teal-700" onclick="addParentsAuto()">Эцэг эх нэмэх</button>
                <button type="button" class="rounded-xl bg-teal-600 py-2.5 font-black text-white transition hover:bg-teal-700" onclick="openAddByRelation('partner')">Хань нэмэх</button>
                <button type="button" class="rounded-xl bg-teal-600 py-2.5 font-black text-white transition hover:bg-teal-700" onclick="openAddByRelation('sib')">Ах/Эгч/Дүү нэмэх</button>
                <button type="button" class="rounded-xl bg-teal-600 py-2.5 font-black text-white transition hover:bg-teal-700 md:col-span-2" onclick="addChildWithPartnerCheck()">Хүүхэд нэмэх</button>
            </div>

            @auth
            <form method="POST" id="side-delete-form" action="">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full rounded-xl bg-rose-600 py-2.5 font-black text-white transition hover:bg-rose-700" onclick="return confirm('Энэ хүнийг устгах уу?')">Хүн устгах</button>
            </form>
            @else
            <button type="button" class="w-full rounded-xl bg-rose-600 py-2.5 font-black text-white transition hover:bg-rose-700" onclick="deleteLocal()">Хүн устгах</button>
            @endauth

            <button type="button" onclick="openAddModal()" class="w-full rounded-xl bg-emerald-600 py-3 font-black text-white transition hover:bg-emerald-700">Шинэ гишүүн нэмэх</button>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white/85 p-4 xl:col-span-3 shadow-sm">
        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
            <div class="text-sm font-bold text-slate-600">Мод харагдац</div>
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-bold text-slate-700" onclick="zoomOut()">-</button>
                <span id="zoom-label" class="w-14 text-center text-sm font-bold text-slate-600">100%</span>
                <button type="button" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-bold text-slate-700" onclick="zoomIn()">+</button>
                <button type="button" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-bold text-slate-700" onclick="zoomReset()">Сэргээх</button>
                <button type="button" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-bold text-slate-700" onclick="printTree()">Хэвлэх</button>
                <button type="button" class="rounded-lg bg-slate-800 px-3 py-1.5 text-sm font-bold text-white" onclick="downloadTreePng()">PNG татах</button>
            </div>
        </div>

        <div id="tree-scroll" class="h-[74vh] overflow-auto rounded-2xl border border-slate-200 bg-[#f8fbff]">
            <div id="tree-stage" class="origin-top-left">
                <svg id="tree-svg" xmlns="http://www.w3.org/2000/svg"></svg>
            </div>
        </div>
    </div>
</div>

<div id="add-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/45 p-4" onclick="closeAddModal(event)">
    <div class="max-h-[92vh] w-full max-w-lg overflow-y-auto rounded-3xl border border-green-200 bg-white p-5 shadow-2xl" onclick="event.stopPropagation()">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="font-bubblegum text-2xl text-green-800">Гишүүн нэмэх</h2>
            <button type="button" onclick="closeAddModal()" class="rounded-lg bg-gray-100 px-3 py-1 text-sm font-black text-gray-700 hover:bg-gray-200">Хаах</button>
        </div>

        @guest
        <div class="mb-3 rounded-2xl border border-yellow-300 bg-yellow-50 px-4 py-2.5 text-sm font-bold text-yellow-800">
            Зочин горимд зөвхөн энэ төхөөрөмж дээр хадгалагдана.
        </div>
        @endguest

        <form id="add-form" method="POST" action="{{ route('family-tree.store') }}" enctype="multipart/form-data" onsubmit="return handleAddSubmit(event)">
            @csrf

            @if($errors->any())
                <div class="mb-3 rounded-xl bg-red-100 px-4 py-2.5 font-black text-red-800">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <div class="mb-3">
                <label class="mb-1 block text-sm font-black text-gray-600">Нэр</label>
                <input name="name" type="text" placeholder="Жишээ: Болд" value="{{ old('name') }}" class="w-full rounded-2xl border-2 border-gray-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-green-400"/>
            </div>

            <div class="mb-3">
                <label class="mb-1 block text-sm font-black text-gray-600">Харилцаа</label>
                <select name="rel" class="w-full rounded-2xl border-2 border-gray-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-green-400">
                    <option value="">-- Сонгоно уу --</option>
                    @foreach($relationOptions as $relationKey => $relationLabel)
                        <option value="{{ $relationKey }}" {{ old('rel') === $relationKey ? 'selected' : '' }}>{{ $relationLabel }}</option>
                    @endforeach
                </select>
                <input type="hidden" name="related_to_id" id="fi-related-to-id" value="">
            </div>

            <div class="mb-3">
                <label class="mb-1 block text-sm font-black text-gray-600">Тэмдэглэл</label>
                <textarea name="bio" placeholder="Товч тэмдэглэл" class="min-h-[72px] w-full resize-y rounded-2xl border-2 border-gray-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-green-400">{{ old('bio') }}</textarea>
            </div>

            <div class="mb-3">
                <label class="mb-1 block text-sm font-black text-gray-600">Аватар</label>
                <div class="mt-1 flex flex-wrap gap-2" id="emoji-row">
                    @php
                        $avatars = [
                            'image/jaal_huu.png','image/huurhun_eme.png','image/er_hun.png',
                            'image/eej.png','image/huurhun_owoo.png','image/emee.png',
                            'image/nylh_huuhed.png','image/hogshin_aaw.png','image/ah.png','image/egch.png',
                        ];
                    @endphp
                    @foreach($avatars as $i => $av)
                        <button type="button" class="h-10 w-10 rounded-full border-2 {{ $i===0?'border-green-500 bg-green-100':'border-transparent bg-gray-100' }} flex items-center justify-center" data-e="{{ $av }}" onclick="pickEmoji(this)">
                            <img src="{{ asset($av) }}" class="h-7 w-7 rounded-full object-cover" alt=""/>
                        </button>
                    @endforeach
                </div>
                <input type="hidden" name="emoji" id="fi-emoji" value="{{ $avatars[0] }}"/>
            </div>

            <div class="mb-3">
                <label class="mb-1 block text-sm font-black text-gray-600">Зураг (заавал биш)</label>
                <label class="block cursor-pointer rounded-2xl border-2 border-dashed border-green-300 p-5 text-center transition-colors hover:border-green-500 hover:bg-green-50">
                    <input type="file" name="photo" accept="image/*" onchange="previewImg(this)" class="hidden"/>
                    <div id="upload-inner">
                        <div class="text-lg font-bold text-slate-600">Зураг сонгох</div>
                        <div class="mt-1 text-xs text-gray-400">Дарж оруулна уу</div>
                    </div>
                </label>
            </div>

            <button type="submit" class="w-full rounded-full bg-green-600 py-3 font-black text-white hover:bg-green-700">Хадгалах</button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
window.FAMILY_TREE_BOOT = {
    rels: {{ \Illuminate\Support\Js::from($relationMeta) }},
    initialMembers: {{ \Illuminate\Support\Js::from($initialMembersData) }},
    isAuth: {{ auth()->check() ? 'true' : 'false' }},
    hasErrors: {{ $errors->any() ? 'true' : 'false' }}
};
</script>
<script src="{{ asset('js/family-tree.js') }}?v={{ filemtime(public_path('js/family-tree.js')) }}"></script>
@endpush
