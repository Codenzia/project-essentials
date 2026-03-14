@php
    $record = $getRecord();
    $state = $record->real_progress ?? 0;
    $offset = (100 - $state) * 1.38;
@endphp
<div class="relative flex items-center justify-center">
    <svg class="w-32 h-32" x-cloak aria-hidden="true" viewBox="0 0 88 88">
        <defs>
            <linearGradient id="progress-gradient" x1="100%" y1="0%" x2="0%" y2="0%">
                <stop offset="0%" style="stop-color:var(--primary-900);stop-opacity:1" />
                <stop offset="100%" style="stop-color:var(--primary-500);stop-opacity:1 " />
            </linearGradient>
        </defs>

        <circle class="dark:text-gray-800 text-gray-200" stroke-width="4" stroke="currentColor" fill="transparent"
            cx="44" cy="44" r="22" transform="rotate(-90 44 44)" />
        @if ($state > 0)
            <circle class="stroke-linecap-round" stroke-width="5" stroke="url(#progress-gradient)"
                :stroke-dasharray="138" :stroke-dashoffset="{{ $offset }}" fill="transparent" cx="44"
                cy="44" r="22" transform="rotate(-90 44 44)" />
        @endif
    </svg>
    <div
        class="absolute text-xs fw-600 transform -translate-x-1/2 -translate-y-1/2 top-1/2 left-1/2 text-gray-900 dark:text-white">
        {{ $state > 0 ? number_format($state, 1) : 0 }}%
    </div>
</div>
