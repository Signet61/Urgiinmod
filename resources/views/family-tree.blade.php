<!DOCTYPE html>
<html lang="mn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Миний Ургийн Мод' }}</title>

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;800;900&family=Bubblegum+Sans&display=swap" rel="stylesheet">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;800;900&family=Bubblegum+Sans&display=swap');
        *{box-sizing:border-box;margin:0;padding:0}
        :root{
            --sky1:#a8d8f0;--sky2:#c8ecb8;--leaf:#4caf50;--leaf2:#2e7d32;
            --trunk:#795548;--sun:#ffd600;--pink:#f48fb1;--purple:#ce93d8;
            --orange:#ffb74d;--teal:#4db6ac;--red:#ef5350;--blue:#42a5f5;
            --cream:#fffde7;--white:#ffffff;
            --card-w:120px;--card-h:140px;
        }
        body{font-family:'Nunito',sans-serif;background:linear-gradient(180deg,#b3e0f7 0%,#c8ecb8 55%,#81c784 100%);min-height:100vh;overflow-x:hidden}

        /* ── Decorative sky ── */
        .sky-deco{position:relative;height:70px;overflow:hidden}
        .sun-el{position:absolute;right:30px;top:8px;width:52px;height:52px;background:var(--sun);border-radius:50%;animation:sunpulse 4s ease-in-out infinite}
        @keyframes sunpulse{0%,100%{box-shadow:0 0 0 0 #ffd60066}50%{box-shadow:0 0 0 14px #ffd60022}}
        .cloud{position:absolute;background:#fff;border-radius:40px;opacity:.88}
        .cloud::before,.cloud::after{content:'';position:absolute;background:#fff;border-radius:50%}
        .cl1{width:80px;height:28px;top:12px;left:18px}
        .cl1::before{width:36px;height:36px;top:-16px;left:10px}
        .cl1::after{width:26px;height:26px;top:-10px;left:36px}
        .cl2{width:60px;height:22px;top:20px;left:160px}
        .cl2::before{width:28px;height:28px;top:-12px;left:8px}
        .cl2::after{width:20px;height:20px;top:-7px;left:28px}
        .bird{position:absolute;font-size:16px;top:6px;left:260px;animation:bfly 14s linear infinite}
        @keyframes bfly{from{transform:translateX(0)}to{transform:translateX(520px)}}

        /* ── Main layout ── */
        .app{max-width:860px;margin:0 auto;padding:0 12px 60px}

        /* ── Nav tabs ── */
        .nav{display:flex;gap:8px;justify-content:center;margin:10px 0 16px;flex-wrap:wrap}
        .nav-btn{font-family:'Bubblegum Sans',cursive;font-size:1rem;padding:10px 22px;border:none;border-radius:50px;cursor:pointer;transition:transform .15s,box-shadow .15s;box-shadow:0 3px 0 rgba(0,0,0,.18)}
        .nav-btn:hover{transform:translateY(-2px);box-shadow:0 5px 0 rgba(0,0,0,.16)}
        .nav-btn:active{transform:translateY(1px);box-shadow:0 1px 0 rgba(0,0,0,.18)}
        .nb-tree{background:#66bb6a;color:#1b5e20}
        .nb-tree.act{background:#2e7d32;color:#c8e6c9}
        .nb-add{background:#f48fb1;color:#880e4f}
        .nb-add.act{background:#c2185b;color:#fce4ec}
        .nb-game{background:#ffb74d;color:#e65100}
        .nb-game.act{background:#e65100;color:#fff3e0}

        /* ── Panel ── */
        .panel{display:none}
        .panel.act{display:block}

        /* ── Tree panel ── */
        .tree-box{background:rgba(255,253,230,.93);border-radius:24px;padding:20px 16px;box-shadow:0 6px 24px rgba(0,0,0,.1)}
        .gen-label{text-align:center;font-family:'Bubblegum Sans',cursive;font-size:.82rem;color:#795548;letter-spacing:1px;margin-bottom:6px}
        .gen-row{display:flex;justify-content:center;gap:10px;flex-wrap:wrap;margin-bottom:6px}
        .arr{text-align:center;font-size:1.3rem;color:#66bb6a;margin:2px 0}

        /* ── Person card ── */
        .pcard{width:96px;display:flex;flex-direction:column;align-items:center;cursor:pointer;transition:transform .18s}
        .pcard:hover{transform:translateY(-4px) scale(1.05)}
        .avatar{width:68px;height:68px;border-radius:50%;border:4px solid #fff;overflow:hidden;display:flex;align-items:center;justify-content:center;font-size:2rem;position:relative;box-shadow:0 3px 10px rgba(0,0,0,.15)}
        .avatar img{width:100%;height:100%;object-fit:cover}
        .pname{font-size:.78rem;font-weight:800;text-align:center;margin-top:4px;color:#2d2d2d;line-height:1.2}
        .prelabel{font-size:.68rem;font-weight:700;padding:2px 8px;border-radius:20px;margin-top:3px;text-align:center}

        /* ── Popup ── */
        .overlay{display:none;position:absolute;inset:0;background:rgba(0,0,0,.45);z-index:50;align-items:flex-start;justify-content:center;padding-top:60px;min-height:400px}
        .overlay.show{display:flex}
        .popup{background:#fffde7;border-radius:24px;padding:28px 22px;max-width:300px;width:90%;text-align:center;animation:popin .25s cubic-bezier(.34,1.56,.64,1)}
        @keyframes popin{from{transform:scale(.7);opacity:0}to{transform:scale(1);opacity:1}}
        .pop-av{font-size:3.2rem;margin-bottom:6px}
        .pop-img{width:88px;height:88px;border-radius:50%;object-fit:cover;margin:0 auto 8px;display:block;border:4px solid var(--orange)}
        .pop-name{font-family:'Bubblegum Sans',cursive;font-size:1.5rem;color:#2e7d32}
        .pop-rel{font-size:.85rem;font-weight:700;color:#888;margin:3px 0 10px}
        .pop-bio{font-size:.9rem;color:#444;line-height:1.5;margin-bottom:14px}
        .pop-close{background:#f48fb1;color:#fff;border:none;border-radius:50px;padding:9px 26px;font-family:'Nunito',sans-serif;font-weight:900;font-size:.95rem;cursor:pointer}
        .empty-tree{text-align:center;padding:36px 20px}
        .empty-tree .big{font-size:3.5rem;margin-bottom:10px}
        .empty-tree p{font-weight:700;color:#666;font-size:.95rem}

        /* ── Add form ── */
        .form-box{background:rgba(255,253,230,.95);border-radius:24px;padding:22px 18px;box-shadow:0 6px 24px rgba(0,0,0,.1)}
        .form-title{font-family:'Bubblegum Sans',cursive;font-size:1.55rem;color:#2e7d32;text-align:center;margin-bottom:18px}
        .fg{margin-bottom:14px}
        .fg label{display:block;font-weight:800;font-size:.88rem;color:#444;margin-bottom:5px}
        .fg input,.fg select,.fg textarea{width:100%;padding:11px 15px;border:2px solid #ddd;border-radius:14px;font-family:'Nunito',sans-serif;font-size:.92rem;outline:none;transition:border .2s;background:#fff}
        .fg input:focus,.fg select:focus,.fg textarea:focus{border-color:#66bb6a}
        .fg textarea{resize:vertical;min-height:72px}
        .emoji-row{display:flex;flex-wrap:wrap;gap:7px;margin-top:7px}
        .emo-btn{width:42px;height:42px;border-radius:50%;border:3px solid transparent;cursor:pointer;font-size:1.5rem;display:flex;align-items:center;justify-content:center;background:#f5f5f5;transition:all .15s}
        .emo-btn.sel{border-color:#66bb6a;background:#c8e6c9;transform:scale(1.1)}
        .upload-zone{border:2.5px dashed #a5d6a7;border-radius:14px;padding:18px;text-align:center;cursor:pointer;transition:all .2s;margin-top:7px}
        .upload-zone:hover{border-color:#66bb6a;background:#f1f8e9}
        .upload-zone input{display:none}
        .prev-img{width:76px;height:76px;border-radius:50%;object-fit:cover;margin:0 auto;display:block;border:3px solid #66bb6a}
        .add-btn{width:100%;padding:13px;background:#66bb6a;color:#fff;border:none;border-radius:50px;font-family:'Nunito',sans-serif;font-weight:900;font-size:1.05rem;cursor:pointer;margin-top:8px;transition:transform .15s,background .15s;box-shadow:0 4px 0 #388e3c}
        .add-btn:hover{transform:translateY(-2px);background:#4caf50}
        .add-btn:active{transform:translateY(2px);box-shadow:0 1px 0 #388e3c}

        /* ── Game panel ── */
        .game-box{background:rgba(255,253,230,.95);border-radius:24px;padding:20px 16px;box-shadow:0 6px 24px rgba(0,0,0,.1)}
        .game-title{font-family:'Bubblegum Sans',cursive;font-size:1.6rem;color:#e65100;text-align:center;margin-bottom:4px}
        .game-sub{text-align:center;font-size:.88rem;color:#777;font-weight:700;margin-bottom:14px}

        /* score bar */
        .sbar-wrap{background:#ffe0b2;border-radius:50px;height:14px;margin-bottom:6px;overflow:hidden}
        .sbar-fill{height:100%;border-radius:50px;background:#ff9800;transition:width .4s ease}
        .sbar-txt{text-align:center;font-size:.82rem;font-weight:800;color:#e65100;margin-bottom:16px}

        /* matching grid */
        .match-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-bottom:16px}
        .mcard{border-radius:14px;cursor:pointer;transition:transform .15s;aspect-ratio:1;display:flex;flex-direction:column;align-items:center;justify-content:center;border:3px solid transparent;position:relative;overflow:hidden;min-height:80px}
        .mcard:hover:not(.matched):not(.disabled){transform:scale(1.04)}
        .mcard .mcard-inner{display:flex;flex-direction:column;align-items:center;justify-content:center;width:100%;height:100%}
        .mcard.face-down{background:#a5d6a7;border-color:#66bb6a;cursor:pointer}
        .mcard.face-down .mcard-inner{display:none}
        .mcard.face-down::after{content:'?';font-family:'Bubblegum Sans',cursive;font-size:1.8rem;color:#2e7d32;display:flex;align-items:center;justify-content:center;position:absolute;inset:0}
        .mcard.flipped,.mcard.matched{background:#fff;border-color:#ffa726}
        .mcard.matched{border-color:#66bb6a;background:#c8e6c9;animation:matchpop .35s cubic-bezier(.34,1.56,.64,1)}
        @keyframes matchpop{0%{transform:scale(1)}50%{transform:scale(1.12)}100%{transform:scale(1)}}
        .mcard.wrong-flash{animation:wrongflash .4s}
        @keyframes wrongflash{0%,100%{background:#fff}25%,75%{background:#ffcdd2}}
        .mc-emoji{font-size:1.6rem;margin-bottom:2px}
        .mc-img{width:44px;height:44px;border-radius:50%;object-fit:cover;border:2px solid #ffa726}
        .mc-label{font-size:.65rem;font-weight:800;color:#444;text-align:center;line-height:1.2;padding:0 4px}
        .mc-badge{font-size:.58rem;font-weight:700;padding:2px 6px;border-radius:10px;margin-top:2px;text-align:center}

        /* timer */
        .timer-row{display:flex;align-items:center;justify-content:center;gap:10px;margin-bottom:10px}
        .timer-icon{font-size:1.2rem}
        .timer-val{font-family:'Bubblegum Sans',cursive;font-size:1.4rem;color:#e65100;min-width:40px;text-align:center}
        .moves-val{font-size:.88rem;font-weight:800;color:#888}

        /* game screens */
        .gscreen{text-align:center;padding:20px 10px}
        .gs-big{font-size:3.5rem;margin-bottom:10px}
        .gs-title{font-family:'Bubblegum Sans',cursive;font-size:1.6rem;color:#2e7d32;margin-bottom:8px}
        .gs-msg{font-size:.95rem;color:#555;font-weight:700;margin-bottom:20px}
        .stars-row{font-size:2rem;letter-spacing:6px;margin-bottom:14px}
        .gs-btn{font-family:'Bubblegum Sans',cursive;font-size:1.1rem;padding:12px 36px;border:none;border-radius:50px;cursor:pointer;transition:transform .15s;box-shadow:0 4px 0 rgba(0,0,0,.18)}
        .gs-btn:hover{transform:translateY(-2px)}
        .gs-btn-start{background:#ffb74d;color:#e65100}
        .gs-btn-play{background:#66bb6a;color:#1b5e20;margin:0 6px}
        .gs-btn-tree{background:#ce93d8;color:#4a148c;margin:0 6px}

        /* confetti layer */
        .confetti-layer{position:absolute;top:0;left:0;right:0;height:1px;pointer-events:none;z-index:60}
        .conf-piece{position:absolute;width:9px;height:9px;border-radius:2px;animation:cfall linear forwards;opacity:1}
        @keyframes cfall{to{transform:translateY(500px) rotate(720deg);opacity:0}}

        /* responsive */
        @media(max-width:480px){
            .match-grid{grid-template-columns:repeat(3,1fr)}
            .mcard{min-height:70px}
            .mc-emoji{font-size:1.3rem}
            .mc-img{width:36px;height:36px}
            .nav-btn{font-size:.88rem;padding:8px 16px}
            .avatar{width:56px;height:56px}
            .pcard{width:76px}
        }
    </style>
</head>
<body>

{{-- ── Decorative sky ── --}}
<div class="sky-deco">
    <div class="sun-el"></div>
    <div class="cloud cl1"></div>
    <div class="cloud cl2"></div>
    <span class="bird">🐦</span>
</div>

<div class="app">

    <div style="text-align:center;margin-bottom:12px">
        <div style="font-family:'Bubblegum Sans',cursive;font-size:2rem;color:#1b5e20;text-shadow:2px 2px 0 rgba(255,255,255,.5)">
            🌳 {{ $pageTitle ?? 'Миний Ургийн Мод' }} 🌳
        </div>
        <div style="font-size:.9rem;font-weight:700;color:#388e3c;margin-top:2px">
            {{ $pageSubtitle ?? 'Гэр бүлийнхнээ таниарай!' }}
        </div>
    </div>

    {{-- ── Navigation tabs ── --}}
    <div class="nav">
        <button class="nav-btn nb-tree act" id="nb-tree" onclick="goTab('tree')">🌳 Ургийн мод</button>
        <button class="nav-btn nb-add" id="nb-add" onclick="goTab('add')">➕ Гишүүн нэмэх</button>
        <button class="nav-btn nb-game" id="nb-game" onclick="goTab('game')">🎮 Тоглоом</button>
    </div>

    {{-- ── TREE PANEL ── --}}
    <div class="panel act" id="p-tree">
        <div class="tree-box" style="position:relative">
            <div id="tree-content"></div>

            {{-- Member detail popup --}}
            <div class="overlay" id="popup-overlay" onclick="closePopup(event)">
                <div class="popup" onclick="event.stopPropagation()">
                    <div id="pop-av-area"></div>
                    <div class="pop-name" id="pop-name"></div>
                    <div class="pop-rel" id="pop-rel"></div>
                    <div class="pop-bio" id="pop-bio"></div>
                    <button class="pop-close" onclick="closePopup()">💛 Хаах</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ── ADD MEMBER PANEL ── --}}
    <div class="panel" id="p-add">
        <div class="form-box">
            <div class="form-title">✨ Шинэ гишүүн нэмэх</div>

            @if(session('success'))
                <div style="background:#c8e6c9;color:#2e7d32;border-radius:12px;padding:10px 16px;margin-bottom:14px;font-weight:800;text-align:center;">
                    🎉 {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div style="background:#ffcdd2;color:#c62828;border-radius:12px;padding:10px 16px;margin-bottom:14px;font-weight:800;">
                    @foreach($errors->all() as $error)
                        <div>⚠️ {{ $error }}</div>
                    @endforeach
                </div>
            @endif

            {{-- Optional: use a Laravel form POST instead of JS-only --}}
            {{-- <form method="POST" action="{{ route('family.store') }}" enctype="multipart/form-data"> --}}
            {{-- @csrf --}}

            <div class="fg">
                <label>👤 Нэр</label>
                <input id="fi-name" type="text" placeholder="Жишээ: Баяр өвөө..."
                       value="{{ old('name') }}"/>
            </div>

            <div class="fg">
                <label>👨‍👩‍👧 Хэн бэ?</label>
                <select id="fi-rel">
                    <option value="">-- Сонгоно уу --</option>
                    <option value="gpl" {{ old('rel') == 'gpl' ? 'selected' : '' }}>Өвөө (аавын тал)</option>
                    <option value="gml" {{ old('rel') == 'gml' ? 'selected' : '' }}>Эмээ (аавын тал)</option>
                    <option value="gpr" {{ old('rel') == 'gpr' ? 'selected' : '' }}>Өвөө (ээжийн тал)</option>
                    <option value="gmr" {{ old('rel') == 'gmr' ? 'selected' : '' }}>Эмээ (ээжийн тал)</option>
                    <option value="dad" {{ old('rel') == 'dad' ? 'selected' : '' }}>Аав</option>
                    <option value="mom" {{ old('rel') == 'mom' ? 'selected' : '' }}>Ээж</option>
                    <option value="uncle" {{ old('rel') == 'uncle' ? 'selected' : '' }}>Авга эсвэл нагац ах</option>
                    <option value="aunt" {{ old('rel') == 'aunt' ? 'selected' : '' }}>Авга эгч эсвэл нагац эгч</option>
                    <option value="sib" {{ old('rel') == 'sib' ? 'selected' : '' }}>Ах / Эгч / Дүү</option>
                    <option value="me" {{ old('rel') == 'me' ? 'selected' : '' }}>Би</option>
                    <option value="cousin" {{ old('rel') == 'cousin' ? 'selected' : '' }}>Үеэл</option>
                </select>
            </div>

            <div class="fg">
                <label>💬 Тэмдэглэл (дуртай зүйл, онцлог)</label>
                <textarea id="fi-bio" placeholder="Жишээ: Бялуу хийдэг, загас барьдаг...">{{ old('bio') }}</textarea>
            </div>

            <div class="fg">
                <label>😊 Emoji сонгох</label>
                <div class="emoji-row" id="emoji-row">
                    @php
                        $emojis = ['👦','👧','👨','👩','👴','👵','🧒','👶','🧓','⭐'];
                    @endphp
                    @foreach($emojis as $index => $emoji)
                        <div class="emo-btn {{ $index === 0 ? 'sel' : '' }}"
                             data-e="{{ $emoji }}"
                             onclick="pickEmoji(this)">{{ $emoji }}</div>
                    @endforeach
                </div>
            </div>

            <div class="fg">
                <label>📷 Зураг (заавал биш)</label>
                <div class="upload-zone" onclick="document.getElementById('fi-photo').click()">
                    <input type="file" id="fi-photo" accept="image/*" onchange="previewImg(this)"/>
                    <div id="upload-inner">
                        <div style="font-size:2rem">📸</div>
                        <div style="font-size:.82rem;color:#888;margin-top:4px">Дарж зураг оруулна уу</div>
                    </div>
                </div>
            </div>

            <button class="add-btn" onclick="addMember()">🌱 Нэмэх!</button>

            {{-- </form> --}}
        </div>
    </div>

    {{-- ── GAME PANEL ── --}}
    <div class="panel" id="p-game">
        <div class="game-box" style="position:relative">
            <div class="confetti-layer" id="conf-layer"></div>

            {{-- Start screen --}}
            <div id="gs-start" class="gscreen">
                <div class="gs-big">🃏</div>
                <div class="gs-title">Matching Pairs тоглоом!</div>
                <div class="gs-msg">
                    Гэр бүлийн гишүүдийн хос картыг олоорой.<br>
                    Нэр болон зургийг хослуулаарай!
                </div>
                <div style="font-size:.85rem;color:#999;margin-bottom:18px">
                    Тоглохын тулд дор хаяж 2 гишүүн хэрэгтэй!
                </div>
                <button class="gs-btn gs-btn-start" onclick="startGame()">🚀 Тоглоом эхлүүлэх!</button>
            </div>

            {{-- Game board --}}
            <div id="gs-board" style="display:none">
                <div class="game-title">🃏 Хосыг ол!</div>
                <div class="game-sub">Нэр болон нүүрийг хослуулаарай</div>
                <div class="sbar-wrap">
                    <div class="sbar-fill" id="sbar" style="width:0%"></div>
                </div>
                <div class="sbar-txt" id="sbar-txt">0 / 0 хос олдсон</div>
                <div class="timer-row">
                    <span class="timer-icon">⏱️</span>
                    <span class="timer-val" id="timer-val">0</span>
                    <span class="moves-val" id="moves-val">0 нүүлт</span>
                </div>
                <div class="match-grid" id="match-grid"></div>
                <div style="text-align:center">
                    <button class="gs-btn gs-btn-start" onclick="startGame()">🔄 Дахин эхлэх</button>
                </div>
            </div>

            {{-- Result screen --}}
            <div id="gs-result" style="display:none" class="gscreen">
                <div class="gs-big" id="res-emoji">🏆</div>
                <div class="stars-row" id="res-stars">⭐⭐⭐</div>
                <div class="gs-title" id="res-title">Гайхалтай!</div>
                <div class="gs-msg" id="res-msg"></div>
                <button class="gs-btn gs-btn-play" onclick="startGame()">🔄 Дахин тоглох</button>
                <button class="gs-btn gs-btn-tree" onclick="goTab('tree')">🌳 Ургийн мод харах</button>
            </div>
        </div>
    </div>

</div>{{-- /.app --}}

<script>
    {{-- ── Relationship config ── --}}
    const RELS = {
        gpl:  { lbl:'Өвөө (аавын тал)',   col:'#ce93d8', gen:0 },
        gml:  { lbl:'Эмээ (аавын тал)',   col:'#f48fb1', gen:0 },
        gpr:  { lbl:'Өвөө (ээжийн тал)',  col:'#ce93d8', gen:0 },
        gmr:  { lbl:'Эмээ (ээжийн тал)',  col:'#f48fb1', gen:0 },
        dad:  { lbl:'Аав',                 col:'#90caf9', gen:1 },
        mom:  { lbl:'Ээж',                 col:'#f48fb1', gen:1 },
        uncle:{ lbl:'Авга/нагац ах',       col:'#ffcc80', gen:1 },
        aunt: { lbl:'Авга/нагац эгч',      col:'#f48fb1', gen:1 },
        sib:  { lbl:'Ах/Эгч/Дүү',         col:'#a5d6a7', gen:2 },
        me:   { lbl:'Би',                  col:'#ffd600', gen:2 },
        cousin:{ lbl:'Үеэл',              col:'#ffb74d', gen:2 }
    };

    {{-- ── Seed data (server-side via Blade or fallback defaults) ── --}}
    @if(isset($members) && count($members))
        const SERVER_MEMBERS = {!! json_encode($members) !!};
    @else
        const SERVER_MEMBERS = null;
    @endif

    const DEFAULTS = [
        { id:1, name:'Баяр өвөө',   rel:'gpl', emoji:'👴', bio:'Загас барьдаг, шатар тоглодог',         photo:null },
        { id:2, name:'Сувд эмээ',   rel:'gml', emoji:'👵', bio:'Цагааны боов хийдэг гайхалтай',          photo:null },
        { id:3, name:'Болд аав',    rel:'dad', emoji:'👨', bio:'Машин жолоодно, хөгжим тоглодог',        photo:null },
        { id:4, name:'Номун ээж',   rel:'mom', emoji:'👩', bio:'Дуулдаг, цэцэг тарьдаг',                 photo:null },
        { id:5, name:'Би',          rel:'me',  emoji:'⭐', bio:'Энэ бол МИНИЙ ургийн мод!',               photo:null },
        { id:6, name:'Эрдэнэ дүү',  rel:'sib', emoji:'🧒', bio:'Хамгийн хурдан гүйдэг',                  photo:null },
    ];

    let members, nxtId, selEmoji = '👦', photoData = null;
    let gameState = { cards:[], flipped:[], matched:[], moves:0, timer:0, timerInt:null };

    function load() {
        try {
            members = SERVER_MEMBERS
                || JSON.parse(localStorage.getItem('fm_members') || 'null')
                || JSON.parse(JSON.stringify(DEFAULTS));
        } catch(e) {
            members = JSON.parse(JSON.stringify(DEFAULTS));
        }
        nxtId = Math.max(...members.map(m => m.id), 0) + 1;
    }

    function save() {
        try { localStorage.setItem('fm_members', JSON.stringify(members)); } catch(e) {}
    }

    load();

    {{-- ── Tab navigation ── --}}
    function goTab(t) {
        ['tree','add','game'].forEach(x => {
            document.getElementById('p-' + x).classList.toggle('act', x === t);
            document.getElementById('nb-' + x).classList.toggle('act', x === t);
        });
        if (t === 'tree') renderTree();
    }

    {{-- ── TREE ── --}}
    function renderTree() {
        const tc = document.getElementById('tree-content');
        if (!members.length) {
            tc.innerHTML = '<div class="empty-tree"><div class="big">🌱</div><p>Гишүүн байхгүй байна.<br>"Гишүүн нэмэх" таб руу очоорой!</p></div>';
            return;
        }
        const gens = [
            { lbl:'👴👵 Өвөө эмээ нар',    keys:['gpl','gml','gpr','gmr'] },
            { lbl:'👨👩 Эцэг эхчүүд',      keys:['dad','mom','uncle','aunt'] },
            { lbl:'👦👧 Бид нар',           keys:['me','sib','cousin'] },
        ];
        let h = '';
        gens.forEach((g, gi) => {
            const gm = members.filter(m => g.keys.includes(m.rel));
            if (!gm.length) return;
            h += `<div class="gen-label">${g.lbl}</div><div class="gen-row">`;
            gm.forEach(m => {
                const r = RELS[m.rel] || {};
                const isMe = m.rel === 'me';
                h += `<div class="pcard" onclick="showPopup(${m.id})" style="${isMe ? 'transform:scale(1.08)' : ''}">
                    <div class="avatar" style="border-color:${r.col || '#ccc'}${isMe ? ';box-shadow:0 0 0 4px ' + r.col + '55' : ''}">
                        ${m.photo ? `<img src="${m.photo}" alt="${m.name}"/>` : `<span style="font-size:2rem">${m.emoji}</span>`}
                    </div>
                    <div class="pname">${m.name}</div>
                    <div class="prelabel" style="background:${r.col || '#eee'}22;color:${darken(r.col || '#888')}">${r.lbl || m.rel}</div>
                </div>`;
            });
            h += '</div>';
            if (gi < gens.length - 1) h += '<div class="arr">↕️</div>';
        });
        tc.innerHTML = h;
    }

    function darken(h) {
        let r = parseInt(h.slice(1,3),16),
            g = parseInt(h.slice(3,5),16),
            b = parseInt(h.slice(5,7),16);
        return `rgb(${Math.max(0,r-70)},${Math.max(0,g-70)},${Math.max(0,b-70)})`;
    }

    function showPopup(id) {
        const m = members.find(x => x.id === id);
        if (!m) return;
        const r = RELS[m.rel] || {};
        document.getElementById('pop-name').innerHTML =
            `<span style="font-family:'Bubblegum Sans',cursive;font-size:1.5rem;color:#2e7d32">${m.name}</span>`;
        document.getElementById('pop-rel').textContent = (r.lbl || m.rel) + (m.rel === 'me' ? ' ⭐' : '');
        document.getElementById('pop-bio').textContent = m.bio || '';
        const aa = document.getElementById('pop-av-area');
        aa.innerHTML = m.photo
            ? `<img class="pop-img" src="${m.photo}" alt="${m.name}"/>`
            : `<div class="pop-av" style="font-size:3.2rem">${m.emoji}</div>`;
        document.getElementById('popup-overlay').classList.add('show');
    }

    function closePopup(e) {
        if (!e || e.target === document.getElementById('popup-overlay'))
            document.getElementById('popup-overlay').classList.remove('show');
    }

    {{-- ── ADD ── --}}
    function pickEmoji(el) {
        document.querySelectorAll('.emo-btn').forEach(e => e.classList.remove('sel'));
        el.classList.add('sel');
        selEmoji = el.dataset.e;
    }

    function previewImg(inp) {
        const f = inp.files[0];
        if (!f) return;
        const r = new FileReader();
        r.onload = e => {
            photoData = e.target.result;
            document.getElementById('upload-inner').innerHTML =
                `<img class="prev-img" src="${photoData}"/><div style="font-size:.8rem;color:#66bb6a;margin-top:5px;font-weight:800">✓ Бэлэн!</div>`;
        };
        r.readAsDataURL(f);
    }

    function addMember() {
        const name = document.getElementById('fi-name').value.trim();
        const rel  = document.getElementById('fi-rel').value;
        const bio  = document.getElementById('fi-bio').value.trim();
        const btn  = document.querySelector('.add-btn');

        if (!name || !rel) {
            btn.textContent = '⚠️ Нэр болон харилцааг оруулна уу!';
            btn.style.background = '#ef5350';
            setTimeout(() => { btn.textContent = '🌱 Нэмэх!'; btn.style.background = ''; }, 1800);
            return;
        }

        members.push({ id: nxtId++, name, rel, emoji: selEmoji, bio: bio || 'Гэр бүлийн гишүүн', photo: photoData });
        save();

        btn.textContent = '🎉 Нэмэгдлээ!';
        btn.style.background = '#ff9800';
        setTimeout(() => { btn.textContent = '🌱 Нэмэх!'; btn.style.background = ''; }, 1400);

        document.getElementById('fi-name').value = '';
        document.getElementById('fi-bio').value  = '';
        document.getElementById('fi-rel').value  = '';
        photoData = null;
        document.getElementById('upload-inner').innerHTML =
            `<div style="font-size:2rem">📸</div><div style="font-size:.82rem;color:#888;margin-top:4px">Дарж зураг оруулна уу</div>`;

        setTimeout(() => goTab('tree'), 600);
    }

    {{-- ── GAME ── --}}
    function startGame() {
        if (members.length < 2) { alert('Дор хаяж 2 гишүүн хэрэгтэй!'); return; }
        clearInterval(gameState.timerInt);

        const pool = members.slice(0, Math.min(8, members.length));
        const pairs = [];
        pool.forEach(m => {
            const r = RELS[m.rel] || {};
            pairs.push({ id:m.id,      type:'face', emoji:m.emoji, photo:m.photo, name:m.name, relLbl:r.lbl||m.rel, relCol:r.col||'#ccc', pairKey:m.id });
            pairs.push({ id:m.id+'n',  type:'name',                               name:m.name, relLbl:r.lbl||m.rel, relCol:r.col||'#ccc', pairKey:m.id });
        });

        const shuffled = pairs.sort(() => Math.random() - .5);
        gameState = {
            cards:    shuffled.map((c,i) => ({...c, idx:i, flipped:false, matched:false})),
            flipped:  [],
            matched:  [],
            moves:    0,
            timer:    0,
            timerInt: null
        };
        gameState.timerInt = setInterval(() => {
            gameState.timer++;
            const tv = document.getElementById('timer-val');
            if (tv) tv.textContent = gameState.timer;
        }, 1000);

        document.getElementById('gs-start').style.display  = 'none';
        document.getElementById('gs-result').style.display = 'none';
        document.getElementById('gs-board').style.display  = 'block';
        updateScore();
        renderBoard();
    }

    function renderBoard() {
        const grid = document.getElementById('match-grid');
        grid.style.gridTemplateColumns = `repeat(4,1fr)`;
        grid.innerHTML = '';
        gameState.cards.forEach(c => {
            const div = document.createElement('div');
            div.className = 'mcard' + (c.matched ? ' matched' : c.flipped ? ' flipped' : ' face-down');
            div.id = 'mc-' + c.idx;
            if (c.flipped || c.matched) {
                let inner = '';
                if (c.type === 'face') {
                    inner += c.photo
                        ? `<img class="mc-img" src="${c.photo}" alt="${c.name}"/>`
                        : `<div class="mc-emoji">${c.emoji}</div>`;
                }
                inner += `<div class="mc-label">${c.name}</div>`;
                inner += `<div class="mc-badge" style="background:${c.relCol}22;color:${darken(c.relCol)}">${c.relLbl}</div>`;
                if (c.type === 'name') {
                    inner = `<div style="font-size:.82rem;font-weight:900;color:#444;padding:4px;text-align:center;line-height:1.3">${c.name}</div>`
                          + `<div class="mc-badge" style="background:${c.relCol}22;color:${darken(c.relCol)}">${c.relLbl}</div>`;
                }
                div.innerHTML = `<div class="mcard-inner">${inner}</div>`;
            }
            if (!c.matched) div.onclick = () => flipCard(c.idx);
            grid.appendChild(div);
        });
    }

    function flipCard(idx) {
        const gs = gameState;
        if (gs.flipped.length >= 2) return;
        const card = gs.cards[idx];
        if (card.flipped || card.matched) return;
        card.flipped = true;
        gs.flipped.push(idx);
        renderBoard();
        if (gs.flipped.length === 2) {
            gs.moves++;
            document.getElementById('moves-val').textContent = gs.moves + ' нүүлт';
            const [a, b] = gs.flipped.map(i => gs.cards[i]);
            if (a.pairKey === b.pairKey && a.type !== b.type) {
                gs.cards[gs.flipped[0]].matched = true;
                gs.cards[gs.flipped[1]].matched = true;
                gs.matched.push(a.pairKey);
                gs.flipped = [];
                updateScore();
                renderBoard();
                celebrate();
                if (gs.matched.length === gs.cards.length / 2) {
                    clearInterval(gs.timerInt);
                    setTimeout(showResult, 600);
                }
            } else {
                setTimeout(() => {
                    const els = gs.flipped.map(i => document.getElementById('mc-' + i));
                    els.forEach(el => { if (el) el.classList.add('wrong-flash'); });
                    setTimeout(() => {
                        gs.flipped.forEach(i => gs.cards[i].flipped = false);
                        gs.flipped = [];
                        renderBoard();
                    }, 500);
                }, 700);
            }
        }
    }

    function updateScore() {
        const total = gameState.cards.length / 2;
        const found = gameState.matched.length;
        const pct   = total ? Math.round(found / total * 100) : 0;
        const sb = document.getElementById('sbar');    if (sb) sb.style.width = pct + '%';
        const st = document.getElementById('sbar-txt'); if (st) st.textContent = found + ' / ' + total + ' хос олдсон';
    }

    function showResult() {
        document.getElementById('gs-board').style.display  = 'none';
        document.getElementById('gs-result').style.display = 'block';
        const mv = gameState.moves;
        const t  = gameState.timer;
        const total = gameState.cards.length / 2;
        const ratio = mv / total;
        let stars, emoji, title, msg;
        if (ratio <= 1.8)     { stars='⭐⭐⭐'; emoji='🏆'; title='Аварга байна!';    msg=`${mv} нүүлт, ${t} секундэд бүгдийг олов!`; }
        else if (ratio <= 3)  { stars='⭐⭐';   emoji='😊'; title='Маш сайн!';        msg=`${mv} нүүлт, ${t} секундэд дуусгалаа!`; }
        else                  { stars='⭐';     emoji='💪'; title='Баяр хүргэе!';     msg=`${mv} нүүлт хийлээ. Дараа илүү хурдан болно!`; }
        document.getElementById('res-stars').textContent = stars;
        document.getElementById('res-emoji').textContent = emoji;
        document.getElementById('res-title').textContent = title;
        document.getElementById('res-msg').textContent   = msg;
        bigCelebrate();
    }

    function celebrate() {
        const layer  = document.getElementById('conf-layer');
        const colors = ['#f48fb1','#ffb74d','#a5d6a7','#90caf9','#ce93d8','#ffd600'];
        for (let i = 0; i < 8; i++) {
            const c = document.createElement('div');
            c.className = 'conf-piece';
            c.style.cssText = `left:${10 + Math.random() * 80}%;background:${colors[Math.floor(Math.random() * colors.length)]};width:${6 + Math.random() * 6}px;height:${6 + Math.random() * 6}px;border-radius:${Math.random() > .5 ? '50%' : '2px'};animation-duration:${1 + Math.random()}s;animation-delay:${Math.random() * .3}s`;
            layer.appendChild(c);
            setTimeout(() => c.remove(), 1500);
        }
    }

    function bigCelebrate() {
        for (let i = 0; i < 5; i++) setTimeout(celebrate, i * 200);
    }

    renderTree();
</script>

</body>
</html>