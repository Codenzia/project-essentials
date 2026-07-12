@props([
    'items' => [],   // each: ['date' => string, 'title' => string, 'badge' => ?string, 'color' => 'success'|'warning'|'danger'|'info'|'gray']
    'empty' => null, // message shown when there are no items
])

{{-- Vertical timeline of dated entries with a status dot and an optional badge.
     Reusable — the consuming app maps its records to the item shape:
     <x-project-essentials::payment-timeline :items="[
         ['date' => '11 Aug 2026', 'title' => '520 JOD paid — Flat', 'badge' => 'Confirmed', 'color' => 'success'],
     ]" empty="No payments yet." /> --}}
@php
    $dotClasses = [
        'success' => 'bg-emerald-500',
        'warning' => 'bg-amber-500',
        'danger' => 'bg-red-500',
        'info' => 'bg-sky-500',
        'gray' => 'bg-gray-400',
    ];
@endphp

<div>
    @forelse ($items as $item)
        @php $color = $item['color'] ?? 'gray'; @endphp
        <div class="flex gap-3">
            {{-- rail: dot + connector --}}
            <div class="flex flex-col items-center">
                <span @class(['mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full', $dotClasses[$color] ?? 'bg-gray-400'])></span>
                @unless ($loop->last)
                    <span class="my-1 w-px flex-1 bg-gray-200 dark:bg-white/10"></span>
                @endunless
            </div>

            {{-- entry: date + title on the left, status badge on the right --}}
            <div @class(['flex min-w-0 flex-1 items-start justify-between gap-3 pb-5', 'mb-5 border-b border-gray-100 dark:border-white/5' => ! $loop->last])>
                <div class="min-w-0">
                    <p class="text-xs text-gray-400 dark:text-gray-500">{{ $item['date'] ?? '' }}</p>
                    <p class="truncate text-sm font-medium text-gray-900 dark:text-white">{{ $item['title'] ?? '' }}</p>
                </div>
                @if (! empty($item['badge']))
                    <x-filament::badge :color="$color" class="shrink-0">{{ $item['badge'] }}</x-filament::badge>
                @endif
            </div>
        </div>
    @empty
        <p class="py-6 text-center text-sm text-gray-400 dark:text-gray-500">{{ $empty }}</p>
    @endforelse
</div>
