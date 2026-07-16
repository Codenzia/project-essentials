{{-- Collapsible month sections for the payment-timeline grouped/combined modes.
     Expects: $groups (collection of ['label', 'items', 'late']), $dotClasses.
     The late group (when present) is first and starts expanded; the first month
     group (newest) also starts expanded; older months start collapsed. The grid
     0fr→1fr transition animates the height without measuring content. --}}
@foreach ($groups as $group)
    @php $startOpen = $group['late'] || $loop->first || ($loop->index === 1 && $groups[0]['late']); @endphp

    <div x-data="{ open: @js($startOpen) }" @class(['rounded-xl', 'border border-amber-500/15 bg-amber-500/5' => $group['late']])>
        <button
            type="button"
            x-on:click="open = ! open"
            class="flex w-full items-center gap-2 px-1.5 pb-1.5 pt-3 text-xs font-semibold uppercase tracking-wider text-gray-400 transition hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300"
        >
            <span class="pe-payment-timeline-chevron inline-flex shrink-0">
                <svg
                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2.4" class="h-3.5 w-3.5"
                    :style="{ transition: 'transform .25s ease', transform: open ? 'rotate(90deg)' : 'rotate(0deg)' }"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </span>

            @if ($group['late'])
                <span class="h-2 w-2 shrink-0 rounded-full bg-amber-500"></span>
            @endif

            <span class="truncate">{{ $group['label'] }}</span>
            <span class="font-medium normal-case tracking-normal">({{ count($group['items']) }})</span>
        </button>

        <div
            style="display: grid; transition: grid-template-rows .3s ease;"
            :style="{ 'grid-template-rows': open ? '1fr' : '0fr' }"
        >
            <div style="overflow: hidden;" class="px-1.5">
                @foreach ($group['items'] as $item)
                    @include('project-essentials::components.payment-timeline.row', ['item' => $item, 'last' => $loop->last, 'dotClasses' => $dotClasses])
                @endforeach
            </div>
        </div>
    </div>
@endforeach
