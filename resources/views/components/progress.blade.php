<div {{ $attributes->merge(['class' => 'flex items-center gap-6']) }}>
    <div class="relative flex-1 min-w-[200px] max-w-full flex-shrink-0" style="aspect-ratio: 2 / 1;">
        <svg class="w-full h-full block" viewBox="0 0 36 18" preserveAspectRatio="xMidYMid meet">
            <defs>
                <linearGradient id="progressGradient" x1="0%" y1="100%" x2="100%" y2="0%">
                    <stop offset="0%" style="stop-color:var(--{{ $color }}-900)" />
                    <stop offset="100%" style="stop-color:var(--{{ $color }}-500)" />
                </linearGradient>
            </defs>
            <path class="dark:text-gray-800 text-gray-200" stroke="currentColor" stroke-linecap="round" stroke-width="2"
                fill="none" d="M2 18 A16 16 0 0 1 34 18"></path>
            <path stroke="url(#progressGradient)" stroke-linecap="round" stroke-width="4" fill="none"
                stroke-dasharray="{{ $dashValue }}, {{ $arcLength }}" d="M2 18 A16 16 0 0 1 34 18">
            </path>
        </svg>

        <div class="absolute inset-0 flex flex-col items-center justify-end pb-1 mb-6"
            style="container-type: inline-size;">
            <p class="text-xl font-semibold text-gray-900 dark:text-white leading-tight"
                style="font-size: clamp(0.875rem, 10cqi, 1.25rem);">{{ $progress }} %</p>
            @if ($showText)
                <p class="text-md font-normal dark:text-gray-400 text-gray-500"
                    style="font-size: clamp(0.75rem, 8cqi, 1rem);">{{ $label }}</p>
            @endif
        </div>
    </div>
</div>
