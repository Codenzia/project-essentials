{{-- One timeline entry — shared by every payment-timeline mode.
     Expects: $item (array), $last (bool), $dotClasses (array<string,string>). --}}
@php $color = $item['color'] ?? 'gray'; @endphp

<div class="flex gap-3">
    {{-- rail: dot + connector --}}
    <div class="flex flex-col items-center">
        <span @class(['mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full', $dotClasses[$color] ?? 'bg-gray-400'])></span>
        @unless ($last)
            <span class="my-1 w-px flex-1 bg-gray-200 dark:bg-white/10"></span>
        @endunless
    </div>

    {{-- entry: date + title on the left, status badge on the right --}}
    <div @class(['flex min-w-0 flex-1 items-start justify-between gap-3 pb-5', 'mb-5 border-b border-gray-100 dark:border-white/5' => ! $last])>
        <div class="min-w-0">
            <p class="text-xs text-gray-400 dark:text-gray-500">{{ $item['date'] ?? '' }}</p>
            <p class="truncate text-sm font-medium text-gray-900 dark:text-white">{{ $item['title'] ?? '' }}</p>
        </div>
        @if (! empty($item['badge']))
            <x-filament::badge :color="$color" class="shrink-0">{{ $item['badge'] }}</x-filament::badge>
        @endif
    </div>
</div>
