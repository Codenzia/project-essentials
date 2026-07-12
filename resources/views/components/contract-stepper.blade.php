@props([
    'steps' => [],      // ordered array of step labels
    'current' => 1,     // 1-based index of the active step
])

{{-- Horizontal progress stepper: a single connector track sits behind the row of
     circles; the completed portion is filled up to the current step. Circles are
     evenly spaced (equal-width columns) and painted opaque so they mask the track,
     leaving a clean line between them. Reusable — pass any labels + current index:
     <x-project-essentials::contract-stepper :steps="['One','Two','Three']" :current="2" /> --}}
@php
    $total = count($steps);
    $pct = $total > 1 ? max(0.0, min(100.0, (($current - 1) / ($total - 1)) * 100)) : 0;
@endphp

<div class="overflow-x-auto">
    <div class="relative mx-auto min-w-[22rem]">
        {{-- connector track, centred on the circles (h-9 → vertical centre 18px).
             Inset by half a column (2rem) so it runs centre-to-centre. --}}
        <div class="pointer-events-none absolute top-[17px] right-8 left-8" aria-hidden="true">
            <div class="h-0.5 w-full rounded bg-gray-200 dark:bg-gray-700"></div>
            <div class="absolute inset-y-0 left-0 rounded bg-primary-500 rtl:left-auto rtl:right-0" style="width: {{ $pct }}%"></div>
        </div>

        <div class="relative flex justify-between">
            @foreach ($steps as $i => $label)
                @php
                    $n = $i + 1;
                    $done = $n < $current;
                    $active = $n === $current;
                @endphp

                <div class="flex flex-col items-center gap-2" style="width: 4rem;">
                    <div @class([
                        'flex h-9 w-9 items-center justify-center rounded-full text-sm font-semibold',
                        'bg-primary-500 text-white shadow-sm shadow-primary-500/30' => $done,
                        'bg-primary-500 text-white ring-4 ring-primary-500/20' => $active,
                        'border-2 border-gray-300 bg-white text-gray-400 dark:border-white/20 dark:bg-gray-900 dark:text-gray-500' => ! $done && ! $active,
                    ])>
                        @if ($done)
                            @svg('heroicon-m-check', 'h-5 w-5')
                        @else
                            {{ $n }}
                        @endif
                    </div>

                    <span @class([
                        'px-1 text-center text-xs leading-tight',
                        'font-semibold text-primary-600 dark:text-primary-400' => $active,
                        'font-medium text-gray-700 dark:text-gray-300' => $done,
                        'text-gray-400 dark:text-gray-500' => ! $done && ! $active,
                    ])>{{ $label }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>
