@extends('layouts.main')
@section('title', 'Тоглоом - Ургийн Мод')

@push('styles')
<style>
    @keyframes quizPulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.03); }
    }

    @keyframes timerGlow {
        0%, 100% { box-shadow: 0 0 0 0 rgba(249, 115, 22, 0.2); }
        50% { box-shadow: 0 0 0 12px rgba(249, 115, 22, 0); }
    }

    @keyframes wrongflash {
        0%, 100% { background: #fff; }
        25%, 75% { background: #fecaca; }
    }

    @keyframes confettiDrop {
        to {
            transform: translateY(320px) rotate(540deg);
            opacity: 0;
        }
    }

    .game-shell {
        background:
            radial-gradient(circle at top left, rgba(255, 255, 255, 0.55), transparent 35%),
            linear-gradient(135deg, rgba(255, 247, 237, 0.95), rgba(255, 255, 255, 0.88));
    }

    .game-mode-card {
        transition: transform .18s ease, box-shadow .18s ease;
    }

    .game-mode-card:hover {
        transform: translateY(-4px);
    }

    .quiz-answer {
        position: relative;
        overflow: hidden;
        transition: transform .18s ease, box-shadow .18s ease, opacity .18s ease;
    }

    .quiz-answer:hover:not(:disabled) {
        transform: translateY(-3px) scale(1.01);
    }

    .quiz-answer.correct {
        animation: quizPulse .35s ease;
        box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.25);
    }

    .quiz-answer.wrong {
        box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.22);
        opacity: 0.7;
    }

    .quiz-answer.dimmed {
        opacity: 0.45;
    }

    .quiz-timer-ring {
        animation: timerGlow 1.3s ease-in-out infinite;
    }

    .memory-card {
        border-radius: 1.25rem;
        cursor: pointer;
        transition: transform .15s ease;
        aspect-ratio: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        border: 3px solid transparent;
        position: relative;
        overflow: hidden;
        min-height: 92px;
    }

    .memory-card:hover:not(.matched):not(.disabled) {
        transform: scale(1.04);
    }

    .memory-card.face-down {
        background: #bbf7d0;
        border-color: #4ade80;
    }

    .memory-card.face-down .memory-card-inner {
        display: none;
    }

    .memory-card.face-down::after {
        content: '?';
        font-family: 'Bubblegum Sans', cursive;
        font-size: 1.9rem;
        color: #166534;
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .memory-card.flipped,
    .memory-card.matched {
        background: #fff;
        border-color: #fb923c;
    }

    .memory-card.matched {
        border-color: #4ade80;
        background: #dcfce7;
        animation: quizPulse .35s ease;
    }

    .memory-card.wrong-flash {
        animation: wrongflash .4s;
    }

    .memory-avatar {
        width: 48px;
        height: 48px;
        border-radius: 9999px;
        object-fit: cover;
        border: 3px solid #fdba74;
    }

    .memory-label {
        font-size: .68rem;
        font-weight: 800;
        color: #444;
        text-align: center;
        line-height: 1.2;
        padding: 0 4px;
    }

    .memory-badge {
        font-size: .58rem;
        font-weight: 700;
        padding: 2px 6px;
        border-radius: 9999px;
        margin-top: 4px;
        text-align: center;
    }

    .confetti-piece {
        position: absolute;
        top: 0;
        width: 10px;
        height: 14px;
        border-radius: 3px;
        animation: confettiDrop linear forwards;
    }
</style>
@endpush

@section('content')

<div class="relative game-shell rounded-[2rem] border border-white/60 shadow-2xl p-4 sm:p-6 overflow-hidden">
    

   
<div id="confetti-layer" class="pointer-events-none absolute inset-x-0 top-0 h-0 z-20"></div>

    <div id="game-selector" class="space-y-5">
        <div class="text-center">
            <div class="inline-flex items-center gap-2 rounded-full text-sm font-black ml-16">
               <x-deer message="Тоглоом тоглоцгооё" size="sm" />
            </div>
            <h2 class="mt-3 font-bubblegum text-4xl text-orange-800">Ямар тоглоом тоглох вэ?</h2>
            <p class="mt-2 text-sm sm:text-base font-bold text-gray-600">
                Ургийн модны гишүүдээрээ quiz тоглож болно, эсвэл matching pairs-ээр нэр ба зургийг тааруулж болно.
            </p>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <div class="game-mode-card rounded-[1.75rem] bg-white/85 p-5 shadow-lg">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="text-xs font-black uppercase tracking-[0.2em] text-orange-500">Тоглоом 1</div>
                        <div class="mt-2 font-bubblegum text-3xl text-orange-800">Асуулт</div>
                    </div>
                    <div class="rounded-2xl bg-orange-100 p-3 text-orange-600">
                        <i data-lucide="badge-help" class="w-8 h-8"></i>
                    </div>
                </div>
                <p class="mt-4 text-sm font-bold text-gray-600">
                    Асуултуудад  хурдан зөв хариулах тусам илүү өндөр оноо авна шүү
                </p>
              
                <button type="button" onclick="openMode('quiz')" class="mt-5 w-full rounded-full bg-orange-400 px-6 py-3 font-bubblegum text-lg text-white shadow-[0_6px_0_#c2410c] transition hover:-translate-y-0.5 hover:bg-orange-300">
                    Quiz тоглох
                </button>
            </div>

            <div class="game-mode-card rounded-[1.75rem] bg-white/85 p-5 shadow-lg">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="text-xs font-black uppercase tracking-[0.2em] text-green-500">Тоглоом 2</div>
                        <div class="mt-2 font-bubblegum text-3xl text-green-800">Matching Pairs</div>
                    </div>
                    <div class="rounded-2xl bg-green-100 p-3 text-green-600">
                        <i data-lucide="layers" class="w-8 h-8"></i>
                    </div>
                </div>
                <p class="mt-4 text-sm font-bold text-gray-600">
                    Картуудыг эргүүлээд нэр болон зургийг зөв тааруулж бүх хосыг олж нээгээрэй!
                </p>
            
                <button type="button" onclick="openMode('match')" class="mt-5 w-full rounded-full bg-green-400 px-6 py-3 font-bubblegum text-lg text-green-950 shadow-[0_6px_0_#15803d] transition hover:-translate-y-0.5 hover:bg-green-300">
                    Matching тоглох
                </button>
            </div>
        </div>
    </div>

    <div id="quiz-panel" class="hidden space-y-4">
        <div class="flex items-center justify-between gap-3">
            <div>
                <div class="text-xs font-black uppercase tracking-[0.2em] text-orange-500">Асуулт</div>
                <div class="font-bubblegum text-3xl text-orange-800">Гэр бүлээ танин мэдье</div>
            </div>
            <button type="button" onclick="showSelector()" class="rounded-full bg-white px-5 py-2 font-bubblegum text-base text-gray-700 shadow-sm transition hover:-translate-y-0.5">
                Бусад тоглоом
            </button>
        </div>

        <div id="quiz-start" class="rounded-[1.75rem] bg-white/80 p-5 shadow-sm">
            <div class="grid gap-4 lg:grid-cols-[1.1fr_0.9fr] lg:items-center">
                <div>
                    <div class="text-xs font-black uppercase tracking-[0.18em] text-gray-400">Эхэлцгээе</div>
                    <h3 class="mt-2 font-bubblegum text-3xl text-orange-800">Хэн бэ? Ямар хамаатан бэ?</h3>
                    <p class="mt-3 text-sm font-bold text-gray-600">
                       Хурдан зөв хариулах тусам өнөө өснө шүү.
                    </p>
                    <button type="button" onclick="startQuiz()" class="mt-5 rounded-full bg-orange-400 px-8 py-3 font-bubblegum text-lg text-white shadow-[0_6px_0_#c2410c] transition hover:-translate-y-0.5 hover:bg-orange-300">
                        Эхлүүлэх
                    </button>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div class="rounded-2xl bg-orange-50 p-4 text-center">
                        <div class="text-xs font-black text-orange-500">Асуулт</div>
                        <div id="start-question-count" class="mt-2 font-bubblegum text-3xl text-orange-800">0</div>
                    </div>
                    <div class="rounded-2xl bg-pink-50 p-4 text-center">
                        <div class="text-xs font-black text-pink-500">Timer</div>
                        <div class="mt-2 font-bubblegum text-3xl text-pink-700">12с</div>
                    </div>
                    <div class="rounded-2xl bg-green-50 p-4 text-center">
                        <div class="text-xs font-black text-green-500">Rule</div>
                        <div class="mt-2 font-bubblegum text-3xl text-green-700">4x</div>
                    </div>
                </div>
            </div>
        </div>

        <div id="quiz-board" class="hidden space-y-4">
            <div class="grid gap-4 lg:grid-cols-[1fr_auto] lg:items-center">
                <div class="rounded-[1.75rem] bg-white/80 p-4 shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <div class="text-xs font-black uppercase tracking-[0.18em] text-gray-400">Ахиц</div>
                            <div class="mt-1 font-bubblegum text-2xl text-orange-800">
                                <span id="question-index">1</span> / <span id="question-total">1</span>
                            </div>
                        </div>
                        <div class="min-w-32 text-right">
                            <div class="text-xs font-black uppercase tracking-[0.18em] text-gray-400">Progress</div>
                            <div id="progress-label" class="mt-1 text-sm font-black text-gray-600">0%</div>
                        </div>
                    </div>
                    <div class="mt-3 h-3 overflow-hidden rounded-full bg-orange-100">
                        <div id="progress-bar" class="h-full rounded-full bg-orange-400 transition-all duration-300" style="width: 0%"></div>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div class="rounded-3xl bg-white/80 px-4 py-3 text-center shadow-sm">
                        <div class="text-xs font-black uppercase tracking-[0.18em] text-gray-400">Оноо</div>
                        <div id="score-value" class="mt-1 font-bubblegum text-2xl text-green-700">0</div>
                    </div>
                    <div class="rounded-3xl bg-white/80 px-4 py-3 text-center shadow-sm">
                        <div class="text-xs font-black uppercase tracking-[0.18em] text-gray-400">Зөв</div>
                        <div id="correct-value" class="mt-1 font-bubblegum text-2xl text-blue-700">0</div>
                    </div>
                    <div class="rounded-3xl bg-white/80 px-4 py-3 text-center shadow-sm">
                        <div class="text-xs font-black uppercase tracking-[0.18em] text-gray-400">Streak</div>
                        <div id="streak-value" class="mt-1 font-bubblegum text-2xl text-pink-700">0</div>
                    </div>
                </div>
            </div>

            <div class="grid gap-4 lg:grid-cols-[1.1fr_0.9fr]">
                <div class="rounded-[2rem] bg-slate-900 p-5 text-white shadow-xl">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div id="question-type" class="text-xs font-black uppercase tracking-[0.24em] text-orange-300">RELATION</div>
                            <h3 id="question-text" class="mt-3 font-bubblegum text-3xl leading-tight"></h3>
                        </div>
                        <div class="quiz-timer-ring rounded-full bg-orange-400/15 p-1.5">
                            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-orange-400 text-center">
                                <div>
                                    <div id="timer-value" class="font-bubblegum text-2xl leading-none text-white">12</div>
                                    <div class="text-[10px] font-black uppercase tracking-[0.18em] text-orange-100">sec</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="question-media" class="mt-5 rounded-[1.75rem] bg-white/10 p-4"></div>
                    <div id="answer-feedback" class="mt-4 min-h-7 text-sm font-black text-orange-100"></div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2" id="answers-grid"></div>
            </div>
        </div>

        <div id="quiz-result" class="hidden space-y-5 text-center rounded-[1.75rem] bg-white/80 p-5 shadow-sm">
            <div class="mx-auto flex h-24 w-24 items-center justify-center rounded-full bg-yellow-100 text-yellow-600 shadow-inner">
                <i data-lucide="trophy" class="h-12 w-12"></i>
            </div>
            <div>
                <div class="text-xs font-black uppercase tracking-[0.22em] text-gray-400">Дууслаа</div>
                <h3 id="result-title" class="mt-2 font-bubblegum text-4xl text-green-800">Гоё тоглолоо!</h3>
                <p id="result-message" class="mt-3 text-sm sm:text-base font-bold text-gray-600"></p>
            </div>

            <div class="grid gap-3 sm:grid-cols-4">
                <div class="rounded-3xl bg-orange-50 p-4 shadow-sm">
                    <div class="text-xs font-black uppercase tracking-[0.18em] text-orange-500">Нийт оноо</div>
                    <div id="result-score" class="mt-2 font-bubblegum text-3xl text-orange-700">0</div>
                </div>
                <div class="rounded-3xl bg-green-50 p-4 shadow-sm">
                    <div class="text-xs font-black uppercase tracking-[0.18em] text-green-500">Зөв</div>
                    <div id="result-correct" class="mt-2 font-bubblegum text-3xl text-green-700">0</div>
                </div>
                <div class="rounded-3xl bg-blue-50 p-4 shadow-sm">
                    <div class="text-xs font-black uppercase tracking-[0.18em] text-blue-500">Accuracy</div>
                    <div id="result-accuracy" class="mt-2 font-bubblegum text-3xl text-blue-700">0%</div>
                </div>
                <div class="rounded-3xl bg-pink-50 p-4 shadow-sm">
                    <div class="text-xs font-black uppercase tracking-[0.18em] text-pink-500">Best streak</div>
                    <div id="result-streak" class="mt-2 font-bubblegum text-3xl text-pink-700">0</div>
                </div>
            </div>

            <div class="flex flex-wrap justify-center gap-3">
                <button type="button" onclick="startQuiz()" class="rounded-full bg-green-400 px-8 py-3 font-bubblegum text-lg text-green-950 shadow-[0_6px_0_#15803d] transition hover:-translate-y-0.5 hover:bg-green-300">
                    Дахин тоглох
                </button>
                <button type="button" onclick="showSelector()" class="rounded-full bg-white px-8 py-3 font-bubblegum text-lg text-gray-700 shadow-sm transition hover:-translate-y-0.5">
                    Өөр тоглоом сонгох
                </button>
            </div>
        </div>
    </div>

    <div id="match-panel" class="hidden space-y-4">
        <div class="flex items-center justify-between gap-3">
            <div>
                <div class="text-xs font-black uppercase tracking-[0.2em] text-green-500">Matching Mode</div>
                <div class="font-bubblegum text-3xl text-green-800">Matching Pairs</div>
            </div>
            <button type="button" onclick="showSelector()" class="rounded-full bg-white px-5 py-2 font-bubblegum text-base text-gray-700 shadow-sm transition hover:-translate-y-0.5">
                Бусад тоглоом
            </button>
        </div>

        <div id="match-start" class="rounded-[1.75rem] bg-white/80 p-5 shadow-sm text-center">
            <div class="mx-auto mb-3 flex h-20 w-20 items-center justify-center rounded-full bg-green-100 text-green-600">
                <i data-lucide="layers" class="w-10 h-10"></i>
            </div>
            <h3 class="font-bubblegum text-3xl text-green-800">Matching Pairs тоглоом</h3>
            <p class="mt-3 text-sm font-bold text-gray-600">
                Нэр болон зургийг зөв тааруулж бүх хосыг нээгээрэй. Өмнөх тоглоом чинь яг энд хадгалагдсан.
            </p>
            <div class="mt-4 grid gap-3 sm:grid-cols-3">
                <div class="rounded-2xl bg-green-50 p-4">
                    <div class="text-xs font-black text-green-500">Pairs</div>
                    <div id="match-start-count" class="mt-2 font-bubblegum text-3xl text-green-800">0</div>
                </div>
                <div class="rounded-2xl bg-orange-50 p-4">
                    <div class="text-xs font-black text-orange-500">Goal</div>
                    <div class="mt-2 font-bubblegum text-3xl text-orange-700">100%</div>
                </div>
                <div class="rounded-2xl bg-blue-50 p-4">
                    <div class="text-xs font-black text-blue-500">Skill</div>
                    <div class="mt-2 font-bubblegum text-3xl text-blue-700">Memory</div>
                </div>
            </div>
            <button type="button" onclick="startMatchGame()" class="mt-5 rounded-full bg-green-400 px-8 py-3 font-bubblegum text-lg text-green-950 shadow-[0_6px_0_#15803d] transition hover:-translate-y-0.5 hover:bg-green-300">
                Эхлүүлэх
            </button>
        </div>

        <div id="match-board" class="hidden rounded-[1.75rem] bg-white/80 p-5 shadow-sm">
            <div class="text-center">
                <h3 class="font-bubblegum text-3xl text-green-800">Хосыг ол</h3>
                <p class="mt-1 text-sm font-bold text-gray-500">Нэр болон зургийг зөв холбоорой</p>
            </div>
            <div class="mt-4 h-3 overflow-hidden rounded-full bg-green-100">
                <div id="match-progress-bar" class="h-full rounded-full bg-green-400 transition-all duration-300" style="width: 0%"></div>
            </div>
            <div class="mt-2 text-center text-xs font-black text-green-700" id="match-progress-text">0 / 0 хос олдсон</div>
            <div class="mt-4 flex items-center justify-center gap-4">
                <div class="rounded-full bg-orange-50 px-4 py-2 text-sm font-black text-orange-700">
                    <i data-lucide="timer" class="mr-1 inline-block w-4 h-4"></i>
                    <span id="match-timer">0</span> сек
                </div>
                <div class="rounded-full bg-blue-50 px-4 py-2 text-sm font-black text-blue-700">
                    <i data-lucide="mouse-pointer-click" class="mr-1 inline-block w-4 h-4"></i>
                    <span id="match-moves">0</span> нүүлт
                </div>
            </div>
            <div id="match-grid" class="mt-5 grid grid-cols-4 gap-2 sm:grid-cols-4"></div>
        </div>

        <div id="match-result" class="hidden rounded-[1.75rem] bg-white/80 p-5 shadow-sm text-center">
            <div class="mx-auto mb-3 flex h-20 w-20 items-center justify-center rounded-full bg-yellow-100 text-yellow-600">
                <i data-lucide="medal" class="w-10 h-10"></i>
            </div>
            <h3 id="match-result-title" class="font-bubblegum text-3xl text-green-800">Амжилттай!</h3>
            <p id="match-result-message" class="mt-3 text-sm font-bold text-gray-600"></p>
            <div class="mt-5 flex flex-wrap justify-center gap-3">
                <button type="button" onclick="startMatchGame()" class="rounded-full bg-green-400 px-8 py-3 font-bubblegum text-lg text-green-950 shadow-[0_6px_0_#15803d] transition hover:-translate-y-0.5 hover:bg-green-300">
                    Дахин тоглох
                </button>
                <button type="button" onclick="showSelector()" class="rounded-full bg-white px-8 py-3 font-bubblegum text-lg text-gray-700 shadow-sm transition hover:-translate-y-0.5">
                    Өөр тоглоом сонгох
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const RELS = {
        gpl: { lbl: 'Өвөө (аавын тал)', col: '#c084fc' },
        gml: { lbl: 'Эмээ (аавын тал)', col: '#f472b6' },
        gpr: { lbl: 'Өвөө (ээжийн тал)', col: '#a855f7' },
        gmr: { lbl: 'Эмээ (ээжийн тал)', col: '#ec4899' },
        dad: { lbl: 'Аав', col: '#60a5fa' },
        mom: { lbl: 'Ээж', col: '#fb7185' },
        uncle: { lbl: 'Авга / нагац ах', col: '#fb923c' },
        aunt: { lbl: 'Авга / нагац эгч', col: '#f472b6' },
        sib: { lbl: 'Ах / Эгч / Дүү', col: '#34d399' },
        me: { lbl: 'Би', col: '#facc15' },
        cousin: { lbl: 'Үеэл', col: '#f59e0b' },
    };

    const ANSWER_COLORS = [
        { shell: 'bg-red-500 text-white', icon: 'triangle', key: 'A' },
        { shell: 'bg-blue-500 text-white', icon: 'diamond', key: 'B' },
        { shell: 'bg-yellow-400 text-yellow-950', icon: 'circle', key: 'C' },
        { shell: 'bg-green-500 text-white', icon: 'square', key: 'D' },
    ];

    @if(auth()->check() && $members->count())
        const SERVER_MEMBERS = {!! $members->map(fn ($member) => [
            'id' => $member->id,
            'name' => $member->name,
            'rel' => $member->rel,
            'emoji' => $member->emoji,
            'photo' => $member->photo ? asset('storage/' . $member->photo) : null,
            'bio' => $member->bio,
        ])->values()->toJson() !!};
    @else
        const SERVER_MEMBERS = null;
    @endif

    const DEFAULTS = [
        { id: 1, name: 'Баяр өвөө', rel: 'gpl', emoji: 'image/huurhun_owoo.png', photo: null, bio: 'Загас барих дуртай.' },
        { id: 2, name: 'Сувд эмээ', rel: 'gml', emoji: 'image/emee.png', photo: null, bio: 'Амттай боов хийдэг.' },
        { id: 3, name: 'Болд аав', rel: 'dad', emoji: 'image/aaw.png', photo: null, bio: 'Машин сайн барина.' },
        { id: 4, name: 'Номин ээж', rel: 'mom', emoji: 'image/eej.png', photo: null, bio: 'Дуулж бүжиглэдэг.' },
        { id: 5, name: 'Би', rel: 'me', emoji: 'image/unaach.png', photo: null, bio: 'Ургийн модоо бүтээж байна.' },
        { id: 6, name: 'Эрдэнэ дүү', rel: 'sib', emoji: 'image/eregtei_duu.png', photo: null, bio: 'Хурдан гүйдэг.' },
    ];

    let members = [];

    let quizState = {
        questions: [],
        currentIndex: 0,
        score: 0,
        correctCount: 0,
        streak: 0,
        bestStreak: 0,
        timer: 12,
        timerHandle: null,
        locked: false,
    };

    let matchState = {
        cards: [],
        flipped: [],
        matched: [],
        moves: 0,
        timer: 0,
        timerHandle: null,
    };

    function deepClone(value) {
        return JSON.parse(JSON.stringify(value));
    }

    function shuffle(items) {
        return items
            .map((item) => ({ item, sort: Math.random() }))
            .sort((a, b) => a.sort - b.sort)
            .map(({ item }) => item);
    }

    function isImgPath(path) {
        return Boolean(path) && (path.startsWith('image/') || path.startsWith('/') || path.startsWith('http') || path.startsWith('data:'));
    }

    function assetPath(path) {
        if (! path) {
            return null;
        }

        if (path.startsWith('/') || path.startsWith('http') || path.startsWith('data:')) {
            return path;
        }

        return `/${path.replace(/^\/+/, '')}`;
    }

    function relationLabel(rel) {
        return RELS[rel]?.lbl ?? rel;
    }

    function relationColor(rel) {
        return RELS[rel]?.col ?? '#d1d5db';
    }

    function darken(hex) {
        const safeHex = hex || '#9ca3af';
        const r = parseInt(safeHex.slice(1, 3), 16);
        const g = parseInt(safeHex.slice(3, 5), 16);
        const b = parseInt(safeHex.slice(5, 7), 16);

        return `rgb(${Math.max(0, r - 70)}, ${Math.max(0, g - 70)}, ${Math.max(0, b - 70)})`;
    }

    function loadMembers() {
        if (SERVER_MEMBERS && SERVER_MEMBERS.length) {
            members = deepClone(SERVER_MEMBERS);
            return;
        }

        try {
            const localMembers = JSON.parse(localStorage.getItem('fm_members') || 'null');
            members = localMembers && localMembers.length ? localMembers : deepClone(DEFAULTS);
        } catch (error) {
            members = deepClone(DEFAULTS);
        }
    }

    function refreshIcons() {
        if (typeof window.createLucideIcons === 'function') {
            window.createLucideIcons();
            return;
        }

        window.addEventListener('load', () => {
            if (typeof window.createLucideIcons === 'function') {
                window.createLucideIcons();
            }
        }, { once: true });
    }

    function resetQuizPanels() {
        document.getElementById('quiz-start').classList.remove('hidden');
        document.getElementById('quiz-board').classList.add('hidden');
        document.getElementById('quiz-result').classList.add('hidden');
    }

    function resetMatchPanels() {
        document.getElementById('match-start').classList.remove('hidden');
        document.getElementById('match-board').classList.add('hidden');
        document.getElementById('match-result').classList.add('hidden');
    }

    function showSelector() {
        clearInterval(quizState.timerHandle);
        clearInterval(matchState.timerHandle);
        resetQuizPanels();
        resetMatchPanels();
        document.getElementById('game-selector').classList.remove('hidden');
        document.getElementById('quiz-panel').classList.add('hidden');
        document.getElementById('match-panel').classList.add('hidden');
        refreshIcons();
    }

    function openMode(mode) {
        clearInterval(quizState.timerHandle);
        clearInterval(matchState.timerHandle);
        document.getElementById('game-selector').classList.add('hidden');
        document.getElementById('quiz-panel').classList.toggle('hidden', mode !== 'quiz');
        document.getElementById('match-panel').classList.toggle('hidden', mode !== 'match');

        if (mode === 'quiz') {
            resetQuizPanels();
        }

        if (mode === 'match') {
            resetMatchPanels();
            updateMatchProgress();
        }

        refreshIcons();
    }

    function uniqueBy(items, selector) {
        const map = new Map();

        items.forEach((item) => {
            const key = selector(item);

            if (! map.has(key)) {
                map.set(key, item);
            }
        });

        return [...map.values()];
    }

    function sampleWrongMembers(correctMember, count) {
        return shuffle(members.filter((member) => member.id !== correctMember.id)).slice(0, count);
    }

    function sampleWrongRelations(correctRel, count) {
        return shuffle(
            uniqueBy(
                members
                    .filter((member) => member.rel !== correctRel)
                    .map((member) => ({ rel: member.rel, label: relationLabel(member.rel) })),
                (item) => item.rel
            )
        ).slice(0, count);
    }

    function buildFaceCard(member, compact = false) {
        const image = member.photo
            ? `<img src="${member.photo}" alt="${member.name}" class="${compact ? 'h-14 w-14' : 'h-20 w-20'} rounded-full object-cover border-4 border-white shadow-lg">`
            : isImgPath(member.emoji)
                ? `<img src="${assetPath(member.emoji)}" alt="${member.name}" class="${compact ? 'h-14 w-14' : 'h-20 w-20'} rounded-full object-cover border-4 border-white shadow-lg">`
                : `<div class="${compact ? 'text-4xl' : 'text-6xl'} leading-none">${member.emoji}</div>`;

        return `
            <div class="flex flex-col items-center text-center">
                ${image}
                <div class="mt-3 font-bubblegum ${compact ? 'text-xl' : 'text-2xl'} text-white">${member.name}</div>
                <div class="mt-1 rounded-full bg-white/15 px-3 py-1 text-xs font-black uppercase tracking-[0.16em] text-orange-100">
                    ${relationLabel(member.rel)}
                </div>
            </div>
        `;
    }

    function createNameToRelationQuestion(member) {
        const wrongRelations = sampleWrongRelations(member.rel, 3);

        if (wrongRelations.length < 3) {
            return null;
        }

        return {
            type: 'Хамаарал',
            prompt: `${member.name} танд хэн бэ?`,
            mediaHtml: `<div class="rounded-[1.5rem] bg-gradient-to-br from-orange-400 to-pink-500 p-5">${buildFaceCard(member)}</div>`,
            options: shuffle([
                { value: member.rel, label: relationLabel(member.rel), correct: true },
                ...wrongRelations.map((item) => ({ value: item.rel, label: item.label, correct: false })),
            ]),
        };
    }

    function createRelationToNameQuestion(member) {
        const wrongMembers = sampleWrongMembers(member, 3);

        if (wrongMembers.length < 3) {
            return null;
        }

        return {
            type: 'Нэр сонгох',
            prompt: `Таны "${relationLabel(member.rel)}" хэн бэ?`,
            mediaHtml: `
                <div class="rounded-[1.5rem] bg-gradient-to-br from-blue-500 to-cyan-500 p-5 text-center">
                    <div class="text-xs font-black uppercase tracking-[0.22em] text-white/80">Find The Person</div>
                    <div class="mt-3 font-bubblegum text-4xl text-white">${relationLabel(member.rel)}</div>
                </div>
            `,
            options: shuffle([
                { value: member.id, label: member.name, correct: true },
                ...wrongMembers.map((item) => ({ value: item.id, label: item.name, correct: false })),
            ]),
        };
    }

    function createPhotoQuestion(member) {
        const wrongMembers = sampleWrongMembers(member, 3);

        if (wrongMembers.length < 3) {
            return null;
        }

        return {
            type: 'Зураг таних',
            prompt: `"${member.name}" аль зураг вэ?`,
            mediaHtml: `
                <div class="rounded-[1.5rem] bg-gradient-to-br from-fuchsia-500 to-violet-600 p-5 text-center">
                    <div class="text-xs font-black uppercase tracking-[0.22em] text-white/80">Picture Round</div>
                    <div class="mt-3 font-bubblegum text-4xl text-white">${member.name}</div>
                </div>
            `,
            options: shuffle([
                { value: member.id, label: buildFaceCard(member, true), correct: true, isHtml: true },
                ...wrongMembers.map((item) => ({ value: item.id, label: buildFaceCard(item, true), correct: false, isHtml: true })),
            ]),
        };
    }

    function buildQuizQuestions() {
        const eligibleMembers = shuffle(members).slice(0, Math.min(6, members.length));
        const questions = [];

        eligibleMembers.forEach((member) => {
            [
                createNameToRelationQuestion(member),
                createRelationToNameQuestion(member),
                createPhotoQuestion(member),
            ].forEach((question) => {
                if (question) {
                    questions.push(question);
                }
            });
        });

        return shuffle(questions).slice(0, Math.min(8, questions.length));
    }

    function updatePreviewCounts() {
        const quizCount = buildQuizQuestions().length;
        const matchCount = Math.min(8, members.length);

        document.getElementById('start-question-count').textContent = quizCount;
        document.getElementById('quiz-count-preview').textContent = quizCount;
        document.getElementById('match-start-count').textContent = matchCount;
        document.getElementById('match-count-preview').textContent = matchCount;
    }

    function resetQuizState() {
        clearInterval(quizState.timerHandle);

        quizState = {
            questions: buildQuizQuestions(),
            currentIndex: 0,
            score: 0,
            correctCount: 0,
            streak: 0,
            bestStreak: 0,
            timer: 12,
            timerHandle: null,
            locked: false,
        };
    }

    function startQuiz() {
        resetQuizState();

        if (quizState.questions.length < 4) {
            alert('Quiz эхлүүлэхэд асуулт бага байна. Гэр бүлийн гишүүдээ арай олон оруулаад үзээрэй.');
            return;
        }

        document.getElementById('quiz-start').classList.add('hidden');
        document.getElementById('quiz-result').classList.add('hidden');
        document.getElementById('quiz-board').classList.remove('hidden');

        renderQuizQuestion();
        refreshIcons();
    }

    function startQuizTimer() {
        clearInterval(quizState.timerHandle);
        quizState.timer = 12;
        document.getElementById('timer-value').textContent = quizState.timer;

        quizState.timerHandle = setInterval(() => {
            quizState.timer -= 1;
            document.getElementById('timer-value').textContent = quizState.timer;

            if (quizState.timer <= 0) {
                clearInterval(quizState.timerHandle);
                handleQuizTimeout();
            }
        }, 1000);
    }

    function renderQuizQuestion() {
        const question = quizState.questions[quizState.currentIndex];

        if (! question) {
            showQuizResult();
            return;
        }

        quizState.locked = false;
        document.getElementById('question-index').textContent = quizState.currentIndex + 1;
        document.getElementById('question-total').textContent = quizState.questions.length;
        document.getElementById('question-type').textContent = question.type;
        document.getElementById('question-text').textContent = question.prompt;
        document.getElementById('question-media').innerHTML = question.mediaHtml;
        document.getElementById('answer-feedback').textContent = 'Хурдан зөв хариулаад score-оо өсгөөрэй.';

        const answersGrid = document.getElementById('answers-grid');
        answersGrid.innerHTML = question.options.map((option, index) => {
            const color = ANSWER_COLORS[index];
            const content = option.isHtml
                ? `<div class="w-full">${option.label}</div>`
                : `<div class="font-bubblegum text-xl leading-tight">${option.label}</div>`;

            return `
                <button type="button" class="quiz-answer ${color.shell} flex min-h-28 items-center gap-4 rounded-[1.75rem] p-4 text-left shadow-lg" onclick="selectQuizAnswer(${index})">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white/20">
                        <i data-lucide="${color.icon}" class="h-6 w-6"></i>
                    </div>
                    <div class="flex-1">
                        <div class="text-xs font-black uppercase tracking-[0.18em] ${color.shell.includes('text-yellow-950') ? 'text-yellow-950/70' : 'text-white/80'}">${color.key}</div>
                        <div class="mt-1">${content}</div>
                    </div>
                </button>
            `;
        }).join('');

        updateQuizHud();
        startQuizTimer();
        refreshIcons();
    }

    function updateQuizHud() {
        const progress = Math.round((quizState.currentIndex / quizState.questions.length) * 100);
        document.getElementById('progress-bar').style.width = `${progress}%`;
        document.getElementById('progress-label').textContent = `${progress}%`;
        document.getElementById('score-value').textContent = quizState.score;
        document.getElementById('correct-value').textContent = quizState.correctCount;
        document.getElementById('streak-value').textContent = quizState.streak;
    }

    function lockQuizAnswers() {
        document.querySelectorAll('#answers-grid .quiz-answer').forEach((button) => {
            button.disabled = true;
        });
    }

    function markQuizAnswers(correctIndex, selectedIndex = null) {
        document.querySelectorAll('#answers-grid .quiz-answer').forEach((button, index) => {
            if (index === correctIndex) {
                button.classList.add('correct');
            } else if (selectedIndex !== null && index === selectedIndex) {
                button.classList.add('wrong');
            } else {
                button.classList.add('dimmed');
            }
        });
    }

    function stripHtml(value) {
        const temp = document.createElement('div');
        temp.innerHTML = value;
        return temp.textContent || temp.innerText || '';
    }

    function selectQuizAnswer(index) {
        if (quizState.locked) {
            return;
        }

        quizState.locked = true;
        clearInterval(quizState.timerHandle);

        const question = quizState.questions[quizState.currentIndex];
        const selected = question.options[index];
        const correctIndex = question.options.findIndex((option) => option.correct);

        lockQuizAnswers();
        markQuizAnswers(correctIndex, index);

        if (selected.correct) {
            const earned = 600 + (quizState.timer * 40) + (quizState.streak * 60);
            quizState.score += earned;
            quizState.correctCount += 1;
            quizState.streak += 1;
            quizState.bestStreak = Math.max(quizState.bestStreak, quizState.streak);
            document.getElementById('answer-feedback').textContent = `Зөв! +${earned} оноо.`;
            burstConfetti();
        } else {
            quizState.streak = 0;
            document.getElementById('answer-feedback').textContent = `Буруу. Зөв хариулт: ${stripHtml(question.options[correctIndex].label)}`;
        }

        updateQuizHud();
        setTimeout(nextQuizQuestion, 1200);
    }

    function handleQuizTimeout() {
        if (quizState.locked) {
            return;
        }

        quizState.locked = true;
        quizState.streak = 0;

        const question = quizState.questions[quizState.currentIndex];
        const correctIndex = question.options.findIndex((option) => option.correct);

        lockQuizAnswers();
        markQuizAnswers(correctIndex);
        document.getElementById('answer-feedback').textContent = `Хугацаа дууслаа. Зөв хариулт: ${stripHtml(question.options[correctIndex].label)}`;
        updateQuizHud();

        setTimeout(nextQuizQuestion, 1200);
    }

    function nextQuizQuestion() {
        quizState.currentIndex += 1;

        if (quizState.currentIndex >= quizState.questions.length) {
            showQuizResult();
            return;
        }

        renderQuizQuestion();
    }

    function showQuizResult() {
        clearInterval(quizState.timerHandle);

        const accuracy = Math.round((quizState.correctCount / quizState.questions.length) * 100);
        let title = 'Сайн тоглолоо!';
        let message = `${quizState.questions.length} асуултаас ${quizState.correctCount}-ыг зөв хариуллаа.`;

        if (accuracy >= 85) {
            title = 'Гайхалтай!';
            message = 'Маш хурдан бөгөөд сайн тоглолоо.';
        } else if (accuracy >= 60) {
title = 'Маш дажгүй!';
            message = 'Дахиад нэг тогловол бүр өндөр оноо авах боломжтой.';
        }

        document.getElementById('quiz-board').classList.add('hidden');
        document.getElementById('quiz-result').classList.remove('hidden');
        document.getElementById('result-title').textContent = title;
        document.getElementById('result-message').textContent = message;
        document.getElementById('result-score').textContent = quizState.score;
        document.getElementById('result-correct').textContent = `${quizState.correctCount}/${quizState.questions.length}`;
        document.getElementById('result-accuracy').textContent = `${accuracy}%`;
        document.getElementById('result-streak').textContent = quizState.bestStreak;
        burstConfetti(24);
        refreshIcons();
    }

    function resetMatchState() {
        clearInterval(matchState.timerHandle);
        matchState = {
            cards: [],
            flipped: [],
            matched: [],
            moves: 0,
            timer: 0,
            timerHandle: null,
        };
    }

    function startMatchGame() {
        resetMatchState();

        if (members.length < 2) {
            alert('Matching тоглоомд дор хаяж 2 гишүүн хэрэгтэй байна.');
            return;
        }

        const pool = members.slice(0, Math.min(8, members.length));
        const pairs = [];

        pool.forEach((member) => {
            pairs.push({
                id: member.id,
                type: 'face',
                name: member.name,
                rel: member.rel,
                emoji: member.emoji,
                photo: member.photo,
                pairKey: member.id,
            });

            pairs.push({
                id: `${member.id}-name`,
                type: 'name',
                name: member.name,
                rel: member.rel,
                pairKey: member.id,
            });
        });

        matchState.cards = shuffle(pairs).map((card, index) => ({
            ...card,
            idx: index,
            flipped: false,
            matched: false,
        }));

        document.getElementById('match-start').classList.add('hidden');
        document.getElementById('match-result').classList.add('hidden');
        document.getElementById('match-board').classList.remove('hidden');
        document.getElementById('match-timer').textContent = '0';
        document.getElementById('match-moves').textContent = '0';

        matchState.timerHandle = setInterval(() => {
            matchState.timer += 1;
            document.getElementById('match-timer').textContent = matchState.timer;
        }, 1000);

        updateMatchProgress();
        renderMatchBoard();
        refreshIcons();
    }

    function renderMatchBoard() {
        const grid = document.getElementById('match-grid');
        grid.innerHTML = '';

        matchState.cards.forEach((card) => {
            const cardNode = document.createElement('div');
            cardNode.id = `memory-card-${card.idx}`;
            cardNode.className = `memory-card ${card.matched ? 'matched' : card.flipped ? 'flipped' : 'face-down'}`;

            if (card.flipped || card.matched) {
                const color = relationColor(card.rel);
                let inner = '';

                if (card.type === 'face') {
                    if (card.photo) {
                        inner += `<img class="memory-avatar" src="${card.photo}" alt="${card.name}">`;
                    } else if (isImgPath(card.emoji)) {
                        inner += `<img class="memory-avatar" src="${assetPath(card.emoji)}" alt="${card.name}">`;
                    } else {
                        inner += `<div class="text-3xl">${card.emoji}</div>`;
                    }

                    inner += `<div class="memory-label mt-2">${card.name}</div>`;
                } else {
                    inner += `<div class="px-2 text-center text-sm font-black text-gray-700">${card.name}</div>`;
                }

                inner += `<div class="memory-badge" style="background:${color}22;color:${darken(color)}">${relationLabel(card.rel)}</div>`;
                cardNode.innerHTML = `<div class="memory-card-inner">${inner}</div>`;
            }

            if (! card.matched) {
                cardNode.onclick = () => flipMatchCard(card.idx);
            }

            grid.appendChild(cardNode);
        });
    }

    function flipMatchCard(index) {
        if (matchState.flipped.length >= 2) {
            return;
        }

        const card = matchState.cards[index];

        if (card.flipped || card.matched) {
            return;
        }

        card.flipped = true;
        matchState.flipped.push(index);
        renderMatchBoard();

        if (matchState.flipped.length === 2) {
            matchState.moves += 1;
            document.getElementById('match-moves').textContent = matchState.moves;

            const [first, second] = matchState.flipped.map((idx) => matchState.cards[idx]);

            if (first.pairKey === second.pairKey && first.type !== second.type) {
                matchState.cards[matchState.flipped[0]].matched = true;
                matchState.cards[matchState.flipped[1]].matched = true;
                matchState.matched.push(first.pairKey);
                matchState.flipped = [];
                updateMatchProgress();
                renderMatchBoard();
                burstConfetti(8);

                if (matchState.matched.length === matchState.cards.length / 2) {
                    clearInterval(matchState.timerHandle);
                    setTimeout(showMatchResult, 500);
                }
            } else {
                setTimeout(() => {
                    matchState.flipped.forEach((idx) => {
                        const el = document.getElementById(`memory-card-${idx}`);
                        if (el) {
                            el.classList.add('wrong-flash');
                        }
                    });

                    setTimeout(() => {
                        matchState.flipped.forEach((idx) => {
                            matchState.cards[idx].flipped = false;
                        });
                        matchState.flipped = [];
                        renderMatchBoard();
                    }, 450);
                }, 650);
            }
        }
    }

    function updateMatchProgress() {
        const total = matchState.cards.length ? matchState.cards.length / 2 : Math.min(8, members.length);
        const found = matchState.matched.length;
        const percent = total ? Math.round((found / total) * 100) : 0;

        document.getElementById('match-progress-bar').style.width = `${percent}%`;
        document.getElementById('match-progress-text').textContent = `${found} / ${total} хос олдсон`;
    }

    function showMatchResult() {
        document.getElementById('match-board').classList.add('hidden');
        document.getElementById('match-result').classList.remove('hidden');
        document.getElementById('match-result-title').textContent = matchState.moves <= matchState.matched.length * 2 ? 'Мундаг байна!' : 'Амжилттай!';
        document.getElementById('match-result-message').textContent = `${matchState.timer} секунд, ${matchState.moves} нүүлтээр бүх хосыг оллоо.`;
        burstConfetti(24);
        refreshIcons();
    }

    function burstConfetti(pieces = 14) {
        const layer = document.getElementById('confetti-layer');
        const colors = ['#ef4444', '#3b82f6', '#facc15', '#22c55e', '#f97316', '#ec4899'];

        for (let i = 0; i < pieces; i += 1) {
            const piece = document.createElement('div');
            piece.className = 'confetti-piece';
            piece.style.left = `${Math.random() * 100}%`;
            piece.style.background = colors[Math.floor(Math.random() * colors.length)];
            piece.style.animationDuration = `${0.9 + Math.random() * 1.2}s`;
            piece.style.animationDelay = `${Math.random() * 0.15}s`;
            piece.style.transform = `translateY(0) rotate(${Math.random() * 180}deg)`;
            layer.appendChild(piece);
            setTimeout(() => piece.remove(), 2200);
        }
    }

    loadMembers();
    updatePreviewCounts();
    updateMatchProgress();
    window.openMode = openMode;
    window.showSelector = showSelector;
    window.startQuiz = startQuiz;
    window.startMatchGame = startMatchGame;
    window.selectQuizAnswer = selectQuizAnswer;
    refreshIcons();
</script>
@endpush
