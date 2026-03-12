{{--
    Loading Spinner Component

    Usage Examples:
    <x-project-essentials::loading-spinner />
    <x-project-essentials::loading-spinner message="Processing your request..." variant="orbital" />
    <x-project-essentials::loading-spinner :show="$isLoading" size="lg" :show-progress="true" />

    Wire/Livewire:
    <x-project-essentials::loading-spinner wire:loading />

    Alpine.js controlled:
    <x-project-essentials::loading-spinner x-show="isSubmitting" />
--}}

@php
    $sizeClasses = $getSizeClasses();
    $blurClass = $getBlurClass();
    $opacityStyle = $getOpacityStyle();
    $opacityRaw = $getOpacityRaw();
    $isTransparent = $isTransparent();

    $themeClass = match ($theme) {
        'light' => 'loading-spinner--light',
        'dark' => 'loading-spinner--dark',
        default => '',
    };
@endphp

<div x-data="{
    visible: @js($show),
    shouldShow: false,
    delayTimeout: null,

    init() {
        this.$watch('visible', (value) => {
            if (value) {
                this.delayTimeout = setTimeout(() => {
                    this.shouldShow = true;
                }, {{ $delay }});
            } else {
                clearTimeout(this.delayTimeout);
                this.shouldShow = false;
            }
        });

        // Initialize if already visible
        if (this.visible) {
            this.delayTimeout = setTimeout(() => {
                this.shouldShow = true;
            }, {{ $delay }});
        }
    },

    show() {
        this.visible = true;
    },

    hide() {
        this.visible = false;
    }
}" x-show="shouldShow" x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0" x-cloak role="status" aria-live="polite" aria-label="{{ $message }}"
    style="{{ $opacityStyle }} --spinner-opacity: {{ $opacityRaw }};"
    class="loading-spinner {{ $themeClass }} ... {{ $fullscreen ? 'fixed inset-0' : 'absolute inset-0' }} z-999999 pointer-events-auto flex flex-col items-center justify-center {{ $blurClass }}"
    {{ $attributes }}>
    {{-- Progress Bar (optional) --}}
    @if ($showProgress)
        <div class="loading-spinner__progress absolute top-0 left-0 right-0 h-1 overflow-hidden">
            <div
                class="loading-spinner__progress-bar h-full bg-gradient-to-r from-indigo-500 via-purple-500 to-indigo-500 dark:from-indigo-400 dark:via-purple-400 dark:to-indigo-400">
            </div>
        </div>
    @endif

    {{-- Main Content Container --}}
    <div class="loading-spinner__content flex flex-col items-center {{ $sizeClasses['gap'] }}">

        {{-- Spinner Variants --}}
        @if ($variant === 'minimal')
            {{-- Minimal: Clean single ring --}}
            <div class="loading-spinner__minimal {{ $sizeClasses['spinner'] }} relative">
                <svg class="w-full h-full" viewBox="0 0 50 50" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="25" cy="25" r="20" stroke="currentColor" stroke-width="4"
                        class="text-slate-200 dark:text-slate-700" />
                    <circle cx="25" cy="25" r="20" stroke="currentColor" stroke-width="4"
                        stroke-linecap="round" stroke-dasharray="80 45"
                        class="text-indigo-600 dark:text-indigo-400 loading-spinner__rotate" />
                </svg>
            </div>
        @elseif($variant === 'elegant')
            {{-- Elegant: Dual ring with gradient --}}
            <div class="loading-spinner__elegant {{ $sizeClasses['spinner'] }} relative">
                <div
                    class="absolute inset-0 rounded-full bg-gradient-to-r from-indigo-500/20 to-purple-500/20 dark:from-indigo-400/20 dark:to-purple-400/20 blur-xl loading-spinner__pulse">
                </div>
                <svg class="w-full h-full relative z-10" viewBox="0 0 50 50" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="spinnerGradient-{{ $variant }}" x1="0%" y1="0%"
                            x2="100%" y2="100%">
                            <stop offset="0%" stop-color="currentColor"
                                class="text-indigo-600 dark:text-indigo-400" />
                            <stop offset="100%" stop-color="currentColor"
                                class="text-purple-600 dark:text-purple-400" />
                        </linearGradient>
                    </defs>
                    <circle cx="25" cy="25" r="20" stroke="currentColor" stroke-width="3"
                        class="text-slate-100 dark:text-slate-800" />
                    <circle cx="25" cy="25" r="20" stroke="url(#spinnerGradient-{{ $variant }})"
                        stroke-width="3" stroke-linecap="round" stroke-dasharray="31.4 94.2"
                        class="loading-spinner__rotate" style="transform-origin: center;" />
                    <circle cx="25" cy="25" r="3" fill="currentColor"
                        class="text-indigo-600 dark:text-indigo-400 loading-spinner__pulse" />
                </svg>
            </div>
        @elseif($variant === 'orbital')
            {{-- Orbital: Multiple orbiting dots --}}
            <div class="loading-spinner__orbital {{ $sizeClasses['spinner'] }} relative">
                <div class="absolute inset-0 flex items-center justify-center">
                    <div
                        class="w-2 h-2 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 dark:from-indigo-400 dark:to-purple-500">
                    </div>
                </div>
                <div class="absolute inset-0 loading-spinner__orbit-1">
                    <div
                        class="absolute top-0 left-1/2 -translate-x-1/2 w-2.5 h-2.5 rounded-full bg-indigo-500 dark:bg-indigo-400 shadow-lg shadow-indigo-500/50">
                    </div>
                </div>
                <div class="absolute inset-2 loading-spinner__orbit-2">
                    <div
                        class="absolute top-0 left-1/2 -translate-x-1/2 w-2 h-2 rounded-full bg-purple-500 dark:bg-purple-400 shadow-lg shadow-purple-500/50">
                    </div>
                </div>
                <div class="absolute inset-4 loading-spinner__orbit-3">
                    <div
                        class="absolute top-0 left-1/2 -translate-x-1/2 w-1.5 h-1.5 rounded-full bg-pink-500 dark:bg-pink-400 shadow-lg shadow-pink-500/50">
                    </div>
                </div>
                <svg class="absolute inset-0 w-full h-full opacity-20" viewBox="0 0 50 50">
                    <circle cx="25" cy="25" r="23" stroke="currentColor" stroke-width="0.5" fill="none"
                        class="text-slate-400 dark:text-slate-600" />
                    <circle cx="25" cy="25" r="17" stroke="currentColor" stroke-width="0.5" fill="none"
                        class="text-slate-400 dark:text-slate-600" />
                    <circle cx="25" cy="25" r="11" stroke="currentColor" stroke-width="0.5" fill="none"
                        class="text-slate-400 dark:text-slate-600" />
                </svg>
            </div>
        @elseif($variant === 'pulse')
            {{-- Pulse: Expanding rings --}}
            <div
                class="loading-spinner__pulse-variant {{ $sizeClasses['spinner'] }} relative flex items-center justify-center">
                <div
                    class="absolute inset-0 rounded-full border-2 border-indigo-500/60 dark:border-indigo-400/60 loading-spinner__ring-1">
                </div>
                <div
                    class="absolute inset-0 rounded-full border-2 border-purple-500/40 dark:border-purple-400/40 loading-spinner__ring-2">
                </div>
                <div
                    class="absolute inset-0 rounded-full border-2 border-pink-500/20 dark:border-pink-400/20 loading-spinner__ring-3">
                </div>
                <div
                    class="w-3 h-3 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 dark:from-indigo-400 dark:to-purple-500 shadow-lg">
                </div>
            </div>
        @endif

        {{-- Loading Message --}}
        @if ($message)
            <p
                class="loading-spinner__message {{ $sizeClasses['text'] }} font-medium text-slate-600 dark:text-slate-300 text-center max-w-xs">
                <span class="loading-spinner__message-text">{{ $message }}</span>
                <span class="loading-spinner__dots inline-flex ml-0.5">
                    <span class="loading-spinner__dot">.</span>
                    <span class="loading-spinner__dot">.</span>
                    <span class="loading-spinner__dot">.</span>
                </span>
            </p>
        @endif
    </div>

    <span class="sr-only">{{ __('Please wait while content is loading') }}</span>
</div>

<style>
    @media (prefers-color-scheme: dark) {
        .loading-spinner:not(.loading-spinner--light):not(.loading-spinner--transparent) {
            background-color: rgba(15, 23, 42, var(--spinner-opacity, 0.9)) !important;
        }
    }
    .dark .loading-spinner:not(.loading-spinner--light):not(.loading-spinner--transparent) {
        background-color: rgba(15, 23, 42, var(--spinner-opacity, 0.9)) !important;
    }
    @keyframes spinner-rotate { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    @keyframes spinner-pulse { 0%, 100% { opacity: 1; transform: scale(1); } 50% { opacity: 0.5; transform: scale(0.95); } }
    @keyframes orbit-1 { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    @keyframes orbit-2 { from { transform: rotate(120deg); } to { transform: rotate(480deg); } }
    @keyframes orbit-3 { from { transform: rotate(240deg); } to { transform: rotate(600deg); } }
    @keyframes ring-pulse-1 { 0% { transform: scale(0.8); opacity: 1; } 100% { transform: scale(1.4); opacity: 0; } }
    @keyframes ring-pulse-2 { 0% { transform: scale(0.8); opacity: 0.8; } 100% { transform: scale(1.6); opacity: 0; } }
    @keyframes ring-pulse-3 { 0% { transform: scale(0.8); opacity: 0.6; } 100% { transform: scale(1.8); opacity: 0; } }
    @keyframes progress-slide { 0% { transform: translateX(-100%); } 100% { transform: translateX(200%); } }
    @keyframes dot-bounce { 0%, 60%, 100% { transform: translateY(0); opacity: 1; } 30% { transform: translateY(-4px); opacity: 0.6; } }
    .loading-spinner__rotate { animation: spinner-rotate 1s linear infinite; transform-origin: center; }
    .loading-spinner__pulse { animation: spinner-pulse 2s ease-in-out infinite; }
    .loading-spinner__orbit-1 { animation: orbit-1 1.5s linear infinite; }
    .loading-spinner__orbit-2 { animation: orbit-2 2s linear infinite; }
    .loading-spinner__orbit-3 { animation: orbit-3 2.5s linear infinite; }
    .loading-spinner__ring-1 { animation: ring-pulse-1 1.5s ease-out infinite; }
    .loading-spinner__ring-2 { animation: ring-pulse-2 1.5s ease-out infinite; animation-delay: 0.3s; }
    .loading-spinner__ring-3 { animation: ring-pulse-3 1.5s ease-out infinite; animation-delay: 0.6s; }
    .loading-spinner__progress-bar { width: 50%; animation: progress-slide 1.5s ease-in-out infinite; }
    .loading-spinner__dot { animation: dot-bounce 1.4s ease-in-out infinite; }
    .loading-spinner__dot:nth-child(1) { animation-delay: 0s; }
    .loading-spinner__dot:nth-child(2) { animation-delay: 0.2s; }
    .loading-spinner__dot:nth-child(3) { animation-delay: 0.4s; }
    .loading-spinner--light { background-color: rgba(255, 255, 255, var(--spinner-opacity, 0.9)) !important; }
    .loading-spinner--light .loading-spinner__message { color: #475569 !important; }
    .loading-spinner--dark { background-color: rgba(15, 23, 42, var(--spinner-opacity, 0.9)) !important; }
    .loading-spinner--dark .loading-spinner__message { color: #cbd5e1 !important; }
    .loading-spinner--transparent,
    .loading-spinner--transparent.loading-spinner--light,
    .loading-spinner--transparent.loading-spinner--dark { background-color: transparent !important; }
    @media (prefers-reduced-motion: reduce) {
        .loading-spinner__rotate, .loading-spinner__pulse, .loading-spinner__orbit-1, .loading-spinner__orbit-2,
        .loading-spinner__orbit-3, .loading-spinner__ring-1, .loading-spinner__ring-2, .loading-spinner__ring-3,
        .loading-spinner__progress-bar, .loading-spinner__dot { animation: none; }
        .loading-spinner__rotate { opacity: 0.7; }
        .loading-spinner__dots { display: none; }
    }
    [x-cloak] { display: none !important; }
</style>
