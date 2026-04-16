@extends('layouts.main')
@section('title', 'Ургийн Мод')

@push('styles')
    <style>
        /* ── JS-toggled panels ── */
        .panel {
            display: none;
        }

        .panel.act {
            display: block;
        }

        .overlay {
            display: none;
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, .45);
            z-index: 50;
            align-items: flex-start;
            justify-content: center;
            padding-top: 60px;
            min-height: 400px;
        }

        .overlay.show {
            display: flex;
        }

        /* ── Tab button color states ── */
        .nb-tree {
            background: #66bb6a;
            color: #1b5e20;
        }

        .nb-tree.act {
            background: #2e7d32;
            color: #c8e6c9;
        }

        .nb-add {
            background: #f48fb1;
            color: #880e4f;
        }

        .nb-add.act {
            background: #c2185b;
            color: #fce4ec;
        }

        /* ── Tree node cards ── */
        .pcard {
            width: 96px;
            display: flex;
            flex-direction: column;
            align-items: center;
            cursor: pointer;
            transition: transform .18s;
        }

        .pcard:hover {
            transform: translateY(-4px) scale(1.05);
        }

        .avatar {
            width: 68px;
            height: 68px;
            border-radius: 50%;
            border: 4px solid #fff;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            box-shadow: 0 3px 10px rgba(0, 0, 0, .15);
        }

        .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .pname {
            font-size: .78rem;
            font-weight: 800;
            text-align: center;
            margin-top: 4px;
            color: #2d2d2d;
            line-height: 1.2;
        }

        .prelabel {
            font-size: .65rem;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 20px;
            margin-top: 3px;
            text-align: center;
        }

        /* ── Generation layout ── */
        .gen-section {
            text-align: center;
        }

        .gen-label {
            font-family: 'Bubblegum Sans', cursive;
            font-size: .82rem;
            color: #795548;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }

        .gen-row {
            display: flex;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        /* ── Tree connector lines ── */
        .tree-connector {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 36px;
            position: relative;
            margin: 2px 0;
        }

        .tree-connector::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            width: 3px;
            height: 100%;
            background: #a5d6a7;
            transform: translateX(-50%);
        }

        /* ── Couple bracket (connects two parents) ── */
        .couple-bracket {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            margin-bottom: 4px;
        }

        .couple-bracket .bline {
            flex: 1;
            height: 3px;
            background: #a5d6a7;
            max-width: 60px;
        }

        .couple-bracket .heart {
            font-size: .9rem;
        }

        /* ── Empty state ── */
        .empty-tree {
            text-align: center;
            padding: 36px 20px;
        }

        .empty-tree .big {
            font-size: 3.5rem;
            margin-bottom: 10px;
        }

        .empty-tree p {
            font-weight: 700;
            color: #666;
            font-size: .95rem;
        }

        /* ── Add form ── */
        .emo-btn {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            border: 3px solid transparent;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f5f5f5;
            transition: all .15s;
        }

        .emo-btn.sel {
            border-color: #66bb6a;
            background: #c8e6c9;
            transform: scale(1.1);
        }

        .prev-img {
            width: 76px;
            height: 76px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto;
            display: block;
            border: 3px solid #66bb6a;
        }

        .add-btn {
            width: 100%;
            padding: 13px;
            background: #66bb6a;
            color: #fff;
            border: none;
            border-radius: 50px;
            font-family: 'Nunito', sans-serif;
            font-weight: 900;
            font-size: 1.05rem;
            cursor: pointer;
            margin-top: 8px;
            transition: transform .15s, background .15s;
            box-shadow: 0 4px 0 #388e3c;
        }

        .add-btn:hover {
            transform: translateY(-2px);
            background: #4caf50;
        }

        .add-btn:active {
            transform: translateY(2px);
            box-shadow: 0 1px 0 #388e3c;
        }

        /* ── Popup ── */
        .pop-img {
            width: 88px;
            height: 88px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 8px;
            display: block;
            border: 4px solid #ffb74d;
        }

        .pop-av {
            font-size: 3.2rem;
            margin-bottom: 6px;
        }

        @media(max-width:480px) {
            .pcard {
                width: 76px;
            }

            .avatar {
                width: 56px;
                height: 56px;
            }
        }
    </style>
@endpush

@section('content')

    {{-- Flash messages --}}
    @if(session('success'))
        <div
            class="glass rounded-2xl px-4 py-3 mb-3 text-green-800 font-black text-center border border-green-300 flex items-center justify-center gap-2">
            <i data-lucide="party-popper" class="w-5 h-5"></i> {{ session('success') }}
        </div>
    @endif

    {{-- Guest save-to-account banner --}}
    @guest
        <div class="glass rounded-2xl px-4 py-3 mb-3 border border-yellow-300 flex items-center gap-3 flex-wrap">
            <span class="text-sm font-bold text-yellow-800 flex-1 flex items-center gap-1.5">
                <i data-lucide="save" class="w-4 h-4"></i>
                Нэвтэрснээр ургийн модоо дансандаа хадгалах боломжтой!
            </span>
            <div class="flex gap-2">
                <a href="{{ route('login') }}"
                    class="font-bubblegum text-sm px-4 py-1.5 bg-purple-400 text-white rounded-full no-underline hover:bg-purple-500 flex items-center gap-1.5">
                    <i data-lucide="key-round" class="w-3.5 h-3.5"></i> Нэвтрэх
                </a>
                <a href="{{ route('register') }}"
                    class="font-bubblegum text-sm px-4 py-1.5 bg-pink-400 text-white rounded-full no-underline hover:bg-pink-500 flex items-center gap-1.5">
                    <i data-lucide="pencil-line" class="w-3.5 h-3.5"></i> Бүртгэл
                </a>
            </div>
        </div>
    @endguest

    {{-- ── Header card ── --}}
    <div class="glass rounded-3xl shadow-2xl p-5 mb-4">
        <div class="flex items-end gap-4">
            <x-deer message="Ургийн модоо харцгаая!" msgId="deer-msg" size="sm" />
            <div class="flex-1">
                <h1 class="font-bubblegum text-3xl text-green-800 flex items-center gap-2">
                    <i data-lucide="tree-pine" class="w-7 h-7"></i> Ургийн Мод
                </h1>
                <p class="text-sm font-bold text-gray-500">Гэр бүлийнхнээ таниарай</p>
            </div>
        </div>
        <div class="block">
            {{-- ── Tab nav ── --}}
            <div class="flex gap-2 justify-center my-3 flex-wrap">
                <button id="nb-tree" onclick="goTab('tree')"
                    class="nb-tree act font-bubblegum text-base px-5 py-2.5 border-0 rounded-full cursor-pointer shadow transition-transform hover:-translate-y-0.5 flex items-center gap-1.5">
                    <i data-lucide="tree-pine" class="w-4 h-4"></i> Ургийн мод
                </button>
                <button id="nb-add" onclick="goTab('add')"
                    class="nb-add font-bubblegum text-base px-5 py-2.5 border-0 rounded-full cursor-pointer shadow transition-transform hover:-translate-y-0.5 flex items-center gap-1.5">
                    <i data-lucide="plus" class="w-4 h-4"></i> Гишүүн нэмэх
                </button>
            </div>

            {{-- ══ TREE PANEL ══ --}}
            <div class="panel act" id="p-tree">
                <div class="relative glass rounded-3xl p-5 shadow-xl">
                    <div id="tree-content"></div>
                    {{-- Popup overlay --}}
                    <div class="overlay" id="popup-overlay" onclick="closePopup(event)">
                        <div class="popup bg-yellow-50 rounded-3xl p-7 max-w-xs w-[90%] text-center shadow-2xl"
                            onclick="event.stopPropagation()">
                            <div id="pop-av-area"></div>
                            <div class="font-bubblegum text-2xl text-green-800" id="pop-name"></div>
                            <div class="text-sm font-bold text-gray-400 my-1" id="pop-rel"></div>
                            <div class="text-sm text-gray-600 leading-relaxed mb-4" id="pop-bio"></div>
                            @auth
                                <form method="POST" id="pop-delete-form" action="" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="bg-red-300 text-red-900 border-0 rounded-full px-4 py-1.5 font-nunito font-black text-sm cursor-pointer mr-2 inline-flex items-center gap-1"
                                        onclick="return confirm('Устгах уу?')">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Устгах
                                    </button>
                                </form>
                            @else
                                <button id="pop-delete-local"
                                    class="bg-red-300 text-red-900 border-0 rounded-full px-4 py-1.5 font-nunito font-black text-sm cursor-pointer mr-2 inline-flex items-center gap-1"
                                    onclick="deleteLocal()">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Устгах
                                </button>
                            @endauth
                            <button
                                class="bg-pink-400 text-white border-0 rounded-full px-6 py-2 font-nunito font-black text-base cursor-pointer inline-flex items-center gap-1"
                                onclick="closePopup()">
                                <i data-lucide="x" class="w-4 h-4"></i> Хаах
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ══ ADD PANEL ══ --}}
            <div class="panel" id="p-add">
                <div class="glass rounded-3xl p-5 shadow-xl">
                    <h2
                        class="font-bubblegum text-2xl text-green-800 text-center mb-4 flex items-center justify-center gap-2">
                        <i data-lucide="sparkles" class="w-6 h-6"></i> Шинэ гишүүн нэмэх
                    </h2>

                    {{-- Guest: local-save notice --}}
                    @guest
                        <div
                            class="flex items-center gap-2 bg-yellow-50 border border-yellow-300 rounded-2xl px-4 py-2.5 mb-3 text-sm font-bold text-yellow-800">
                            <i data-lucide="save" class="w-4 h-4 shrink-0"></i>
                            <span>Зочин горимд зөвхөн энэ хөтчид хадгалагдана.
                                <a href="{{ route('login') }}" class="text-purple-600 underline">Нэвтэрснээр</a>
                                дансандаа хадгалагдана.
                            </span>
                        </div>
                    @endguest

                    <form id="add-form" method="POST" action="{{ route('family-tree.store') }}"
                        enctype="multipart/form-data" onsubmit="return handleAddSubmit(event)">
                        @csrf

                        @if($errors->any())
                            <div class="bg-red-100 text-red-800 rounded-xl px-4 py-2.5 mb-3 font-black">
                                @foreach($errors->all() as $error)
                                    <div class="flex items-center gap-1.5">
                                        <i data-lucide="triangle-alert" class="w-3.5 h-3.5 shrink-0"></i> {{ $error }}
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- Name --}}
                        <div class="mb-3">
                            <label class="flex items-center gap-1.5 font-black text-sm text-gray-600 mb-1">
                                <i data-lucide="user" class="w-4 h-4"></i> Нэр
                            </label>
                            <input name="name" type="text" placeholder="Жишээ: Баяр өвөө..." value="{{ old('name') }}"
                                class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-2xl text-sm outline-none focus:border-green-400 bg-white transition-colors" />
                        </div>

                        {{-- Relation --}}
                        <div class="mb-3">
                            <label class="flex items-center gap-1.5 font-black text-sm text-gray-600 mb-1">
                                <i data-lucide="users" class="w-4 h-4"></i> Хэн бэ?
                            </label>
                            <select name="rel"
                                class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-2xl text-sm outline-none focus:border-green-400 bg-white transition-colors">
                                <option value="">-- Сонгоно уу --</option>
                                <option value="gpl" {{ old('rel') == 'gpl' ? 'selected' : '' }}>Өвөө (аавын тал)</option>
                                <option value="gml" {{ old('rel') == 'gml' ? 'selected' : '' }}>Эмээ (аавын тал)</option>
                                <option value="gpr" {{ old('rel') == 'gpr' ? 'selected' : '' }}>Өвөө (ээжийн тал)</option>
                                <option value="gmr" {{ old('rel') == 'gmr' ? 'selected' : '' }}>Эмээ (ээжийн тал)</option>
                                <option value="dad" {{ old('rel') == 'dad' ? 'selected' : '' }}>Аав</option>
                                <option value="mom" {{ old('rel') == 'mom' ? 'selected' : '' }}>Ээж</option>
                                <option value="uncle" {{ old('rel') == 'uncle' ? 'selected' : '' }}>Авга/нагац ах</option>
                                <option value="aunt" {{ old('rel') == 'aunt' ? 'selected' : '' }}>Авга/нагац эгч</option>
                                <option value="sib" {{ old('rel') == 'sib' ? 'selected' : '' }}>Ах / Эгч / Дүү</option>
                                <option value="me" {{ old('rel') == 'me' ? 'selected' : '' }}>Би</option>
                                <option value="cousin" {{ old('rel') == 'cousin' ? 'selected' : '' }}>Үеэл</option>
                            </select>
                        </div>

                        {{-- Bio --}}
                        <div class="mb-3">
                            <label class="flex items-center gap-1.5 font-black text-sm text-gray-600 mb-1">
                                <i data-lucide="message-circle" class="w-4 h-4"></i> Тэмдэглэл
                            </label>
                            <textarea name="bio" placeholder="Жишээ: Бялуу хийдэг, загас барьдаг..."
                                class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-2xl text-sm outline-none focus:border-green-400 bg-white resize-y min-h-[72px] transition-colors">{{ old('bio') }}</textarea>
                        </div>

                        {{-- Avatar picker --}}
                        <div class="mb-3">
                            <label class="flex items-center gap-1.5 font-black text-sm text-gray-600 mb-1">
                                <i data-lucide="image" class="w-4 h-4"></i> Аватар сонгох
                            </label>
                            <div class="flex flex-wrap gap-2 mt-1" id="emoji-row">
                                @php
                                    $avatars = [
                                        'image/jaal_huu.png',
                                        'image/huurhun_eme.png',
                                        'image/er_hun.png',
                                        'image/eej.png',
                                        'image/huurhun_owoo.png',
                                        'image/emee.png',
                                        'image/nylh_huuhed.png',
                                        'image/hogshin_aaw.png',
                                        'image/ah.png',
                                        'image/egch.png',
                                    ];
                                @endphp
                                @foreach($avatars as $i => $av)
                                    <div class="emo-btn {{ $i === 0 ? 'sel' : '' }}" data-e="{{ $av }}" onclick="pickEmoji(this)">
                                        <img src="{{ asset($av) }}"
                                            style="width:28px;height:28px;border-radius:50%;object-fit:cover" alt="" />
                                    </div>
                                @endforeach
                            </div>
                            <input type="hidden" name="emoji" id="fi-emoji" value="{{ $avatars[0] }}" />
                        </div>

                        {{-- Photo upload --}}
                        <div class="mb-3">
                            <label class="flex items-center gap-1.5 font-black text-sm text-gray-600 mb-1">
                                <i data-lucide="camera" class="w-4 h-4"></i> Зураг оруулах (заавал биш)
                            </label>
                            <label
                                class="border-2 border-dashed border-green-300 rounded-2xl p-5 text-center cursor-pointer hover:border-green-500 hover:bg-green-50 transition-colors block">
                                <input type="file" name="photo" accept="image/*" onchange="previewImg(this)"
                                    class="hidden" />
                                <div id="upload-inner">
                                    <div class="flex justify-center mb-1"><i data-lucide="camera"
                                            class="w-10 h-10 text-gray-300"></i></div>
                                    <div class="text-xs text-gray-400 mt-1">Дарж зураг оруулна уу</div>
                                </div>
                            </label>
                        </div>

                        <button type="submit" class="add-btn flex items-center justify-center gap-2">
                            <i data-lucide="sprout" class="w-5 h-5"></i> Нэмэх!
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>



@endsection

@push('scripts')
    <script>
        const RELS = {
            gpl: { lbl: 'Өвөө (аавын тал)', col: '#ce93d8', gen: 0 },
            gml: { lbl: 'Эмээ (аавын тал)', col: '#f48fb1', gen: 0 },
            gpr: { lbl: 'Өвөө (ээжийн тал)', col: '#ce93d8', gen: 0 },
            gmr: { lbl: 'Эмээ (ээжийн тал)', col: '#f48fb1', gen: 0 },
            dad: { lbl: 'Аав', col: '#90caf9', gen: 1 },
            mom: { lbl: 'Ээж', col: '#f48fb1', gen: 1 },
            uncle: { lbl: 'Авга/нагац ах', col: '#ffcc80', gen: 1 },
            aunt: { lbl: 'Авга/нагац эгч', col: '#f48fb1', gen: 1 },
            sib: { lbl: 'Ах/Эгч/Дүү', col: '#a5d6a7', gen: 2 },
            me: { lbl: 'Би', col: '#ffd600', gen: 2 },
            cousin: { lbl: 'Үеэл', col: '#ffb74d', gen: 2 },
        };

        const IS_AUTH = {{ auth()->check() ? 'true' : 'false' }};

        // Server members (logged-in) or localStorage fallback
        @if(auth()->check() && $members->count())
            const SERVER_MEMBERS = {!! $members->map(fn($m) => [
                'id' => $m->id,
                'name' => $m->name,
                'rel' => $m->rel,
                'emoji' => $m->emoji,
                'photo' => $m->photo ? asset('storage/' . $m->photo) : null,
                'bio' => $m->bio,
            ])->values()->toJson() !!};
        @else
            const SERVER_MEMBERS = null;
        @endif

        const DEFAULTS = [
            { id: 1, name: 'Баяр өвөө', rel: 'gpl', emoji: 'image/huurhun_owoo.png', bio: 'Загас барьдаг', photo: null },
            { id: 2, name: 'Сувд эмээ', rel: 'gml', emoji: 'image/emee.png', bio: 'Боов хийдэг гайхалтай', photo: null },
            { id: 3, name: 'Болд аав', rel: 'dad', emoji: 'image/aaw.png', bio: 'Машин жолоодно', photo: null },
            { id: 4, name: 'Номун ээж', rel: 'mom', emoji: 'image/eej.png', bio: 'Дуулдаг', photo: null },
            { id: 5, name: 'Би', rel: 'me', emoji: 'image/unaach.png', bio: 'Миний ургийн мод!', photo: null },
            { id: 6, name: 'Эрдэнэ дүү', rel: 'sib', emoji: 'image/eregtei_duu.png', bio: 'Хурдан гүйдэг', photo: null },
        ];

        let members, selEmoji = 'image/jaal_huu.png', photoData = null, currentPopupId = null;
        function isImgPath(e) { return e && (e.startsWith('image/') || e.startsWith('/') || e.startsWith('data:') || e.startsWith('http')); }

        function loadMembers() {
            if (SERVER_MEMBERS) {
                members = SERVER_MEMBERS;
            } else {
                try {
                    members = JSON.parse(localStorage.getItem('fm_members') || 'null') || JSON.parse(JSON.stringify(DEFAULTS));
                } catch (e) { members = JSON.parse(JSON.stringify(DEFAULTS)); }
            }
        }

        function darken(h) {
            let r = parseInt(h.slice(1, 3), 16), g = parseInt(h.slice(3, 5), 16), b = parseInt(h.slice(5, 7), 16);
            return `rgb(${Math.max(0, r - 70)},${Math.max(0, g - 70)},${Math.max(0, b - 70)})`;
        }

        // ── Tab navigation ──
        function goTab(t) {
            ['tree', 'add'].forEach(x => {
                const p = document.getElementById('p-' + x);
                const b = document.getElementById('nb-' + x);
                if (p) p.classList.toggle('act', x === t);
                if (b) b.classList.toggle('act', x === t);
            });
            const msg = document.getElementById('deer-msg');
            if (msg) {
                msg.textContent = t === 'add'
                    ? 'Шинэ гишүүн нэмцгээе!'
                    : 'Ургийн модоо харцгаая!';
            }
            if (t === 'tree') renderTree();
        }

        // ── Tree rendering ──
        function renderTree() {
            const tc = document.getElementById('tree-content');
            if (!members || !members.length) {
                tc.innerHTML = `<div class="empty-tree">
                    <div class="big flex justify-center"><i data-lucide="sprout" style="width:56px;height:56px;color:#66bb6a"></i></div>
                    <p>Гишүүн байхгүй байна.<br>"Гишүүн нэмэх" дарж нэмнэ үү!</p>
                </div>`;
                window.createLucideIcons();
                return;
            }
            const gens = [
                { lbl: 'Өвөө эмээ нар', keys: ['gpl', 'gml', 'gpr', 'gmr'] },
                { lbl: 'Эцэг эхчүүд', keys: ['dad', 'mom', 'uncle', 'aunt'] },
                { lbl: 'Бид нар', keys: ['me', 'sib', 'cousin'] },
            ];
            let h = '';
            let prevHad = false;
            gens.forEach((g, gi) => {
                const gm = members.filter(m => g.keys.includes(m.rel));
                if (!gm.length) return;
                if (prevHad) h += '<div class="tree-connector"></div>';
                h += `<div class="gen-section mb-2">
                    <div class="gen-label">${g.lbl}</div>
                    <div class="gen-row">`;
                gm.forEach(m => {
                    const r = RELS[m.rel] || {};
                    const isMe = m.rel === 'me';
                    const avatar = m.photo
                        ? `<img src="${m.photo}" alt="${m.name}"/>`
                        : isImgPath(m.emoji)
                            ? `<img src="${m.emoji}" alt="${m.name}" style="width:100%;height:100%;object-fit:cover"/>`
                            : `<span style="font-size:2rem">${m.emoji}</span>`;
                    h += `<div class="pcard" onclick="showPopup(${m.id})" style="${isMe ? 'transform:scale(1.08)' : ''}">
                        <div class="avatar" style="border-color:${r.col || '#ccc'}${isMe ? ';box-shadow:0 0 0 4px ' + r.col + '55' : ''}">
                            ${avatar}
                        </div>
                        <div class="pname">${m.name}</div>
                        <div class="prelabel" style="background:${r.col || '#eee'}22;color:${darken(r.col || '#888')}">${r.lbl || m.rel}</div>
                    </div>`;
                });
                h += '</div></div>';
                prevHad = true;
            });
            tc.innerHTML = h;
        }

        // ── Local delete (guest) ──
        function deleteLocal() {
            if (!confirm('Устгах уу?')) return;
            members = members.filter(m => m.id !== currentPopupId);
            try { localStorage.setItem('fm_members', JSON.stringify(members)); } catch (e) { }
            closePopup(); renderTree();
        }

        // ── Popup ──
        function showPopup(id) {
            currentPopupId = id;
            const m = members.find(x => x.id === id); if (!m) return;
            const r = RELS[m.rel] || {};
            document.getElementById('pop-name').textContent = m.name;
            document.getElementById('pop-rel').textContent = (r.lbl || m.rel);
            document.getElementById('pop-bio').textContent = m.bio || '';
            document.getElementById('pop-av-area').innerHTML = m.photo
                ? `<img class="pop-img" src="${m.photo}" alt="${m.name}"/>`
                : isImgPath(m.emoji)
                    ? `<img class="pop-img" src="${m.emoji}" alt="${m.name}"/>`
                    : `<div class="pop-av">${m.emoji}</div>`;
            const delForm = document.getElementById('pop-delete-form');
            if (delForm) delForm.action = `/family-tree/${id}`;
            document.getElementById('popup-overlay').classList.add('show');
            window.createLucideIcons();
        }
        function closePopup(e) {
            if (!e || e.target === document.getElementById('popup-overlay'))
                document.getElementById('popup-overlay').classList.remove('show');
        }

        // ── Avatar picker ──
        function pickEmoji(el) {
            document.querySelectorAll('.emo-btn').forEach(e => e.classList.remove('sel'));
            el.classList.add('sel');
            selEmoji = el.dataset.e;
            const input = document.getElementById('fi-emoji');
            if (input) input.value = el.dataset.e;
        }

        // ── Photo preview (stores base64 for guest use) ──
        function previewImg(inp) {
            const f = inp.files[0]; if (!f) return;
            if (!IS_AUTH) {
                const reader = new FileReader();
                reader.onload = ev => {
                    photoData = ev.target.result;
                    document.getElementById('upload-inner').innerHTML =
                        `<img class="prev-img" src="${photoData}"/>
                         <div class="text-xs text-green-600 mt-1 font-black text-center flex items-center justify-center gap-1">
                             <i data-lucide="check" style="width:12px;height:12px"></i> Бэлэн!
                         </div>`;
                    window.createLucideIcons();
                };
                reader.readAsDataURL(f);
            } else {
                document.getElementById('upload-inner').innerHTML =
                    `<img class="prev-img" src="${URL.createObjectURL(f)}"/>
                     <div class="text-xs text-green-600 mt-1 font-black text-center flex items-center justify-center gap-1">
                         <i data-lucide="check" style="width:12px;height:12px"></i> Бэлэн!
                     </div>`;
                window.createLucideIcons();
            }
        }

        // ── Guest: save to localStorage and re-render ──
        function handleAddSubmit(e) {
            if (IS_AUTH) return true;

            e.preventDefault();
            const name = document.querySelector('[name="name"]').value.trim();
            const rel = document.querySelector('[name="rel"]').value;
            const bio = document.querySelector('[name="bio"]').value.trim();
            const btn = document.querySelector('.add-btn');

            if (!name || !rel) {
                btn.innerHTML = '<i data-lucide="triangle-alert" style="width:18px;height:18px;display:inline-block;vertical-align:middle;margin-right:4px"></i>Нэр болон харилцааг оруулна уу!';
                btn.style.background = '#ef5350';
                window.createLucideIcons();
                setTimeout(() => {
                    btn.innerHTML = '<i data-lucide="sprout" style="width:18px;height:18px;display:inline-block;vertical-align:middle;margin-right:4px"></i>Нэмэх!';
                    btn.style.background = '';
                    window.createLucideIcons();
                }, 1800);
                return false;
            }

            const nxtId = Math.max(...members.map(m => m.id), 0) + 1;
            members.push({ id: nxtId, name, rel, emoji: selEmoji, bio: bio || 'Гэр бүлийн гишүүн', photo: photoData });

            try { localStorage.setItem('fm_members', JSON.stringify(members)); } catch (ex) { }

            // Reset form
            document.querySelector('[name="name"]').value = '';
            document.querySelector('[name="rel"]').value = '';
            document.querySelector('[name="bio"]').value = '';
            photoData = null;
            selEmoji = 'image/jaal_huu.png';
            document.querySelectorAll('.emo-btn').forEach((b, i) => b.classList.toggle('sel', i === 0));
            document.getElementById('fi-emoji').value = selEmoji;
            document.getElementById('upload-inner').innerHTML =
                `<div class="flex justify-center mb-1"><i data-lucide="camera" style="width:40px;height:40px;color:#d1d5db"></i></div>
                 <div class="text-xs text-gray-400 mt-1">Дарж зураг оруулна уу</div>`;

            btn.innerHTML = '<i data-lucide="party-popper" style="width:18px;height:18px;display:inline-block;vertical-align:middle;margin-right:4px"></i>Нэмэгдлээ!';
            btn.style.background = '#ff9800';
            window.createLucideIcons();
            setTimeout(() => {
                btn.innerHTML = '<i data-lucide="sprout" style="width:18px;height:18px;display:inline-block;vertical-align:middle;margin-right:4px"></i>Нэмэх!';
                btn.style.background = '';
                window.createLucideIcons();
            }, 1400);
            setTimeout(() => goTab('tree'), 600);
            return false;
        }

        // ── Init ──
        loadMembers();
        renderTree();

        // Handle ?tab=add from dashboard or direct link
        const urlTab = new URLSearchParams(window.location.search).get('tab');
        if (urlTab === 'add') goTab('add');

        // Handle localStorage redirect from other pages
        const gotoTab = localStorage.getItem('fm_goto');
        if (gotoTab === 'add') { localStorage.removeItem('fm_goto'); goTab('add'); }
    </script>
@endpush