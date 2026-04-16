@extends('layouts.main')
@section('title', 'Тоглоом – Ургийн Мод')

@push('styles')
<style>
    @keyframes matchpop  { 0%{transform:scale(1)} 50%{transform:scale(1.12)} 100%{transform:scale(1)} }
    @keyframes wrongflash{ 0%,100%{background:#fff} 25%,75%{background:#ffcdd2} }
    @keyframes cfall     { to{transform:translateY(500px) rotate(720deg);opacity:0} }

    .mcard         { border-radius:14px; cursor:pointer; transition:transform .15s;
                     aspect-ratio:1; display:flex; flex-direction:column;
                     align-items:center; justify-content:center;
                     border:3px solid transparent; position:relative;
                     overflow:hidden; min-height:80px; }
    .mcard:hover:not(.matched):not(.disabled) { transform:scale(1.04); }
    .mcard .mcard-inner { display:flex; flex-direction:column; align-items:center;
                          justify-content:center; width:100%; height:100%; }
    .mcard.face-down   { background:#a5d6a7; border-color:#66bb6a; }
    .mcard.face-down .mcard-inner { display:none; }
    .mcard.face-down::after { content:'?'; font-family:'Bubblegum Sans',cursive;
                               font-size:1.8rem; color:#2e7d32;
                               display:flex; align-items:center; justify-content:center;
                               position:absolute; inset:0; }
    .mcard.flipped,
    .mcard.matched     { background:#fff; border-color:#ffa726; }
    .mcard.matched     { border-color:#66bb6a; background:#c8e6c9; animation:matchpop .35s cubic-bezier(.34,1.56,.64,1); }
    .mcard.wrong-flash { animation:wrongflash .4s; }
    .mc-img   { width:44px; height:44px; border-radius:50%; object-fit:cover; border:2px solid #ffa726; }
    .mc-label { font-size:.65rem; font-weight:800; color:#444; text-align:center; line-height:1.2; padding:0 4px; }
    .mc-badge { font-size:.58rem; font-weight:700; padding:2px 6px; border-radius:10px; margin-top:2px; text-align:center; }
    .conf-piece { position:absolute; width:9px; height:9px; border-radius:2px;
                  animation:cfall linear forwards; opacity:1; }
    @media(max-width:480px) { .mc-img { width:36px; height:36px; } }
</style>
@endpush

@section('content')

{{-- Header card --}}
<div class="glass rounded-3xl shadow-2xl p-5 mb-4">
    <div class="flex items-end gap-4">
        <x-deer message="Тоглоом тоглоцгооё!" size="sm" />
        <div class="flex-1">
            <h1 class="font-bubblegum text-3xl text-orange-700 flex items-center gap-2">
                <i data-lucide="gamepad-2" class="w-7 h-7"></i> Тоглоом
            </h1>
            <p class="text-sm font-bold text-gray-500">Хосыг олоорой!</p>
        </div>
    </div>
</div>

{{-- ── Game area ── --}}
<div class="relative glass rounded-3xl p-5 shadow-xl">
    <div class="absolute top-0 left-0 right-0 h-px pointer-events-none z-10" id="conf-layer"></div>

    {{-- Start screen --}}
    <div id="gs-start" class="text-center py-5 px-2.5">
        <div class="mb-2.5 flex justify-center">
            <i data-lucide="layers" class="w-16 h-16 text-orange-400"></i>
        </div>
        <div class="font-bubblegum text-2xl text-green-800 mb-2">Matching Pairs тоглоом!</div>
        <div class="text-sm text-gray-500 font-bold mb-5">
            Гэр бүлийн гишүүдийн хос картыг олоорой.<br>
            Нэр болон зургийг хослуулаарай!
        </div>
        <div class="text-xs text-gray-400 mb-4">Тоглохын тулд дор хаяж 2 гишүүн хэрэгтэй!</div>
        <button class="font-bubblegum text-lg px-9 py-3 bg-orange-300 text-orange-800 border-0 rounded-full cursor-pointer shadow transition-transform hover:-translate-y-0.5 inline-flex items-center gap-2"
                onclick="startGame()">
            <i data-lucide="rocket" class="w-5 h-5"></i> Тоглоом эхлүүлэх!
        </button>
    </div>

    {{-- Game board --}}
    <div id="gs-board" style="display:none">
        <div class="font-bubblegum text-2xl text-orange-700 text-center mb-1 flex items-center justify-center gap-2">
            <i data-lucide="layers" class="w-6 h-6"></i> Хосыг ол!
        </div>
        <div class="text-center text-sm text-gray-500 font-bold mb-3">Нэр болон нүүрийг хослуулаарай</div>
        <div class="bg-orange-100 rounded-full h-3 mb-1.5 overflow-hidden">
            <div class="h-full rounded-full bg-orange-400 transition-all duration-300" id="sbar" style="width:0%"></div>
        </div>
        <div class="text-center text-xs font-black text-orange-700 mb-4" id="sbar-txt">0 / 0 хос олдсон</div>
        <div class="flex items-center justify-center gap-2.5 mb-2.5">
            <i data-lucide="timer" class="w-5 h-5 text-orange-500"></i>
            <span class="font-bubblegum text-xl text-orange-700 min-w-[40px] text-center" id="timer-val">0</span>
            <span class="text-sm font-black text-gray-400" id="moves-val">0 нүүлт</span>
        </div>
        <div class="grid grid-cols-4 gap-2 mb-4 sm:grid-cols-3" id="match-grid"></div>
        <div class="text-center">
            <button class="font-bubblegum text-lg px-9 py-3 bg-orange-300 text-orange-800 border-0 rounded-full cursor-pointer shadow inline-flex items-center gap-2"
                    onclick="startGame()">
                <i data-lucide="refresh-cw" class="w-5 h-5"></i> Дахин эхлэх
            </button>
        </div>
    </div>

    {{-- Result screen --}}
    <div id="gs-result" style="display:none" class="text-center py-5 px-2.5">
        <div class="mb-2.5 flex justify-center" id="res-emoji">
            <i data-lucide="trophy" class="w-16 h-16 text-yellow-500"></i>
        </div>
        <div class="flex justify-center gap-1 mb-3" id="res-stars">
            <i data-lucide="star" class="w-8 h-8 text-yellow-400 fill-yellow-400"></i>
            <i data-lucide="star" class="w-8 h-8 text-yellow-400 fill-yellow-400"></i>
            <i data-lucide="star" class="w-8 h-8 text-yellow-400 fill-yellow-400"></i>
        </div>
        <div class="font-bubblegum text-2xl text-green-800 mb-2" id="res-title">Гайхалтай!</div>
        <div class="text-sm text-gray-500 font-bold mb-5" id="res-msg"></div>
        <button class="font-bubblegum text-lg px-9 py-3 bg-green-400 text-green-900 border-0 rounded-full cursor-pointer shadow mx-1.5 transition-transform hover:-translate-y-0.5 inline-flex items-center gap-2"
                onclick="startGame()">
            <i data-lucide="refresh-cw" class="w-5 h-5"></i> Дахин тоглох
        </button>
        <a href="{{ route('family-tree') }}"
           class="font-bubblegum text-lg px-9 py-3 bg-purple-300 text-purple-900 rounded-full no-underline shadow mx-1.5 transition-transform hover:-translate-y-0.5 inline-flex items-center gap-2">
            <i data-lucide="tree-pine" class="w-5 h-5"></i> Ургийн мод харах
        </a>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const RELS = {
        gpl:  { lbl:'Өвөө (аавын тал)',  col:'#ce93d8' },
        gml:  { lbl:'Эмээ (аавын тал)',  col:'#f48fb1' },
        gpr:  { lbl:'Өвөө (ээжийн тал)', col:'#ce93d8' },
        gmr:  { lbl:'Эмээ (ээжийн тал)', col:'#f48fb1' },
        dad:  { lbl:'Аав',               col:'#90caf9' },
        mom:  { lbl:'Ээж',               col:'#f48fb1' },
        uncle:{ lbl:'Авга/нагац ах',     col:'#ffcc80' },
        aunt: { lbl:'Авга/нагац эгч',    col:'#f48fb1' },
        sib:  { lbl:'Ах/Эгч/Дүү',       col:'#a5d6a7' },
        me:   { lbl:'Би',                col:'#ffd600' },
        cousin:{lbl:'Үеэл',              col:'#ffb74d' },
    };

    // Server members (logged-in) or localStorage fallback
    @if(auth()->check() && $members->count())
        const SERVER_MEMBERS = {!! $members->map(fn($m) => [
            'id'    => $m->id,
            'name'  => $m->name,
            'rel'   => $m->rel,
            'emoji' => $m->emoji,
            'photo' => $m->photo ? asset('storage/' . $m->photo) : null,
            'bio'   => $m->bio,
        ])->values()->toJson() !!};
    @else
        const SERVER_MEMBERS = null;
    @endif

    const DEFAULTS = [
        { id:1, name:'Баяр өвөө',  rel:'gpl', emoji:'image/huurhun_owoo.png', photo:null },
        { id:2, name:'Сувд эмээ',  rel:'gml', emoji:'image/emee.png',          photo:null },
        { id:3, name:'Болд аав',   rel:'dad', emoji:'image/aaw.png',           photo:null },
        { id:4, name:'Номун ээж',  rel:'mom', emoji:'image/eej.png',           photo:null },
        { id:5, name:'Би',         rel:'me',  emoji:'image/unaach.png',        photo:null },
        { id:6, name:'Эрдэнэ дүү', rel:'sib', emoji:'image/eregtei_duu.png',   photo:null },
    ];

    let members;
    function isImgPath(e) { return e && (e.startsWith('image/') || e.startsWith('/') || e.startsWith('data:') || e.startsWith('http')); }

    (function loadMembers() {
        if (SERVER_MEMBERS) { members = SERVER_MEMBERS; return; }
        try {
            members = JSON.parse(localStorage.getItem('fm_members') || 'null') || JSON.parse(JSON.stringify(DEFAULTS));
        } catch(e) { members = JSON.parse(JSON.stringify(DEFAULTS)); }
    })();

    function darken(h) {
        let r=parseInt(h.slice(1,3),16), g=parseInt(h.slice(3,5),16), b=parseInt(h.slice(5,7),16);
        return `rgb(${Math.max(0,r-70)},${Math.max(0,g-70)},${Math.max(0,b-70)})`;
    }

    let gameState = { cards:[], flipped:[], matched:[], moves:0, timer:0, timerInt:null };

    function startGame() {
        if (members.length < 2) { alert('Дор хаяж 2 гишүүн хэрэгтэй!'); return; }
        clearInterval(gameState.timerInt);
        const pool = members.slice(0, Math.min(8, members.length));
        const pairs = [];
        pool.forEach(m => {
            const r = RELS[m.rel] || {};
            pairs.push({ id:m.id,     type:'face', emoji:m.emoji, photo:m.photo, name:m.name, relLbl:r.lbl||m.rel, relCol:r.col||'#ccc', pairKey:m.id });
            pairs.push({ id:m.id+'n', type:'name',                               name:m.name, relLbl:r.lbl||m.rel, relCol:r.col||'#ccc', pairKey:m.id });
        });
        const shuffled = pairs.sort(() => Math.random() - .5);
        gameState = {
            cards: shuffled.map((c,i) => ({...c, idx:i, flipped:false, matched:false})),
            flipped:[], matched:[], moves:0, timer:0, timerInt:null
        };
        gameState.timerInt = setInterval(() => {
            gameState.timer++;
            const tv = document.getElementById('timer-val');
            if (tv) tv.textContent = gameState.timer;
        }, 1000);
        document.getElementById('gs-start').style.display  = 'none';
        document.getElementById('gs-result').style.display = 'none';
        document.getElementById('gs-board').style.display  = 'block';
        updateScore(); renderBoard();
        window.createLucideIcons();
    }

    function renderBoard() {
        const grid = document.getElementById('match-grid');
        grid.innerHTML = '';
        gameState.cards.forEach(c => {
            const div = document.createElement('div');
            div.className = 'mcard' + (c.matched?' matched':c.flipped?' flipped':' face-down');
            div.id = 'mc-' + c.idx;
            if (c.flipped || c.matched) {
                let inner = '';
                if (c.type === 'face') {
                    inner += c.photo
                        ? `<img class="mc-img" src="${c.photo}" alt="${c.name}"/>`
                        : isImgPath(c.emoji)
                            ? `<img class="mc-img" src="${c.emoji}" alt="${c.name}"/>`
                            : `<div style="font-size:1.6rem;margin-bottom:2px">${c.emoji}</div>`;
                    inner += `<div class="mc-label">${c.name}</div>`;
                } else {
                    inner = `<div style="font-size:.82rem;font-weight:900;color:#444;padding:4px;text-align:center;line-height:1.3">${c.name}</div>`;
                }
                inner += `<div class="mc-badge" style="background:${c.relCol}22;color:${darken(c.relCol)}">${c.relLbl}</div>`;
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
        card.flipped = true; gs.flipped.push(idx); renderBoard();
        if (gs.flipped.length === 2) {
            gs.moves++;
            document.getElementById('moves-val').textContent = gs.moves + ' нүүлт';
            const [a, b] = gs.flipped.map(i => gs.cards[i]);
            if (a.pairKey === b.pairKey && a.type !== b.type) {
                gs.cards[gs.flipped[0]].matched = true;
                gs.cards[gs.flipped[1]].matched = true;
                gs.matched.push(a.pairKey); gs.flipped = [];
                updateScore(); renderBoard(); celebrate();
                if (gs.matched.length === gs.cards.length / 2) {
                    clearInterval(gs.timerInt); setTimeout(showResult, 600);
                }
            } else {
                setTimeout(() => {
                    gs.flipped.map(i => document.getElementById('mc-'+i))
                              .forEach(el => el && el.classList.add('wrong-flash'));
                    setTimeout(() => {
                        gs.flipped.forEach(i => gs.cards[i].flipped = false);
                        gs.flipped = []; renderBoard();
                    }, 500);
                }, 700);
            }
        }
    }

    function updateScore() {
        const total = gameState.cards.length / 2, found = gameState.matched.length;
        const pct = total ? Math.round(found / total * 100) : 0;
        const sb = document.getElementById('sbar'); if (sb) sb.style.width = pct + '%';
        const st = document.getElementById('sbar-txt'); if (st) st.textContent = found + ' / ' + total + ' хос олдсон';
    }

    function showResult() {
        document.getElementById('gs-board').style.display  = 'none';
        document.getElementById('gs-result').style.display = 'block';
        const mv = gameState.moves, t = gameState.timer, total = gameState.cards.length / 2;
        const ratio = mv / total;

        const starFull  = '<i data-lucide="star" class="w-8 h-8 text-yellow-400 fill-yellow-400"></i>';
        const starEmpty = '<i data-lucide="star" class="w-8 h-8 text-gray-300"></i>';
        let emojiIcon, starsHtml, title, msg;

        if (ratio <= 1.8) {
            emojiIcon = '<i data-lucide="trophy" class="w-16 h-16 text-yellow-500"></i>';
            starsHtml = starFull.repeat(3);
            title = 'Аварга байна!';
            msg   = `${mv} нүүлт, ${t} секундэд бүгдийг олов!`;
        } else if (ratio <= 3) {
            emojiIcon = '<i data-lucide="smile" class="w-16 h-16 text-green-500"></i>';
            starsHtml = starFull.repeat(2) + starEmpty;
            title = 'Маш сайн!';
            msg   = `${mv} нүүлт, ${t} секундэд дуусгалаа!`;
        } else {
            emojiIcon = '<i data-lucide="dumbbell" class="w-16 h-16 text-blue-500"></i>';
            starsHtml = starFull + starEmpty.repeat(2);
            title = 'Баяр хүргэе!';
            msg   = `${mv} нүүлт хийлээ. Дараа илүү хурдан болно!`;
        }

        document.getElementById('res-emoji').innerHTML = emojiIcon;
        document.getElementById('res-stars').innerHTML = starsHtml;
        document.getElementById('res-title').textContent = title;
        document.getElementById('res-msg').textContent   = msg;
        window.createLucideIcons();
        bigCelebrate();
    }

    function celebrate() {
        const layer = document.getElementById('conf-layer');
        const colors = ['#f48fb1','#ffb74d','#a5d6a7','#90caf9','#ce93d8','#ffd600'];
        for (let i = 0; i < 8; i++) {
            const c = document.createElement('div'); c.className = 'conf-piece';
            c.style.cssText = `left:${10+Math.random()*80}%;background:${colors[Math.floor(Math.random()*colors.length)]};width:${6+Math.random()*6}px;height:${6+Math.random()*6}px;border-radius:${Math.random()>.5?'50%':'2px'};animation-duration:${1+Math.random()}s;animation-delay:${Math.random()*.3}s;top:0;`;
            layer.appendChild(c); setTimeout(() => c.remove(), 1500);
        }
    }
    function bigCelebrate() { for (let i = 0; i < 5; i++) setTimeout(celebrate, i * 200); }
</script>
@endpush
