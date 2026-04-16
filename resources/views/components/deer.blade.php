@props(['message' => 'Сайн уу! 🦌', 'msgId' => 'deer-msg', 'size' => 'md'])

@php
    $sizeClass = match($size) {
        'sm'  => 'w-20 h-20',
        'lg'  => 'w-36 h-36',
        default => 'w-28 h-28',
    };
@endphp

<div class="flex flex-col items-start flex-shrink-0">
    <div class="deer-bubble bg-white rounded-2xl px-4 py-2 shadow-md border-2 border-green-300 mb-2.5 max-w-[200px]">
        <span class="font-bubblegum text-sm text-green-800 leading-snug" id="{{ $msgId }}">{{ $message }}</span>
    </div>
    <img src="{{ asset('mongoliandeer.png') }}"
         class="deer-img {{ $sizeClass }} object-contain"
         alt="буга"/>
</div>
