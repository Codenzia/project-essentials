<div class="flex items-center gap-6">
    <div class="relative progress-box">
        <svg class="w-full h-full" viewBox="0 0 36 18">
            <defs>
                <linearGradient id="progressGradient" x1="0%" y1="100%" x2="100%" y2="0%">
                    <stop offset="0%" style="stop-color:var(--{{ $color }}-900)" />
                    <stop offset="100%" style="stop-color:var(--{{ $color }}-500)" />
                </linearGradient>
            </defs>
            <path class="dark:text-gray-800 text-gray-200" stroke="currentColor" stroke-linecap="round"
                stroke-width="2" fill="none" d="M2 18 A16 16 0 0 1 34 18"></path>
            <path stroke="url(#progressGradient)" stroke-linecap="round" stroke-width="4" fill="none"
                stroke-dasharray="{{ $dashValue }}, {{ $arcLength }}" d="M2 18 A16 16 0 0 1 34 18">
            </path>
        </svg>
        <div class="absolute inset-0 flex flex-col items-center justify-center mt-10 text-md dark:text-white">
            {{ $progress }} %
            @if($showText)
            <p class="text-md dark:text-[#919EAB] text-[#919EAB]">{{ $label }}</p>
            @endif
        </div>
    </div>
</div>