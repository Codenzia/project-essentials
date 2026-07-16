@props([
    'items' => [],           // each: ['date' => string, 'title' => string, 'badge' => ?string, 'color' => 'success'|'warning'|'danger'|'info'|'gray',
                             //        'date_iso' => ?string (sortable Y-m-d — REQUIRED for grouped/combined), 'late' => ?bool (pins into the Late group)]
    'empty' => null,         // message shown when there are no items
    'mode' => 'list',        // 'list' (default, original flat timeline) | 'scroll' | 'grouped' | 'combined'
    'height' => '24.75rem',  // viewport height for the scroll/combined modes; 'fill' (or true) stretches to the parent (flex-1)
    'lateLabel' => null,     // heading of the pinned late group (defaults to "Late")
])

{{-- Vertical timeline of dated entries with a status dot and an optional badge.
     Modes:
       list     — the original flat timeline; renders items in the order given.
       scroll   — flat timeline in a fixed-height viewport: hidden scrollbar,
                  wheel + pointer-drag/touch scrolling with momentum, edge fades.
       grouped  — entries collapse into month sections (newest month open) with a
                  pinned, expanded "Late" group on top for items flagged late:true.
                  Requires date_iso on every item; the component re-sorts (late
                  oldest-first, the rest newest-first).
       combined — grouped inside the scroll viewport.
     grouped/combined degrade gracefully (grouped→list, combined→scroll, plus a
     logged warning) when any item lacks date_iso, so old item shapes never error.

     <x-project-essentials::payment-timeline mode="combined" :items="[
         ['date' => '11 Aug 2026', 'date_iso' => '2026-08-11', 'title' => '520 JOD paid — Flat', 'badge' => 'Paid', 'color' => 'success'],
         ['date' => '5 Aug 2026', 'date_iso' => '2026-08-05', 'title' => '900 JOD — 6 days late', 'badge' => 'Late', 'color' => 'warning', 'late' => true],
     ]" empty="No payments yet." /> --}}
@php
    use Illuminate\Support\Carbon;

    $items = collect($items)->values();

    $mode = in_array($mode, ['list', 'scroll', 'grouped', 'combined'], true) ? $mode : 'list';
    $needsGroups = in_array($mode, ['grouped', 'combined'], true);

    // Grouping needs a sortable date on EVERY item — degrade instead of erroring
    // so consumers still on the original item shape keep rendering.
    if ($needsGroups && $items->contains(fn ($item) => empty($item['date_iso']))) {
        logger()->warning('payment-timeline: mode "'.$mode.'" requires date_iso on every item — falling back to "'.($mode === 'combined' ? 'scroll' : 'list').'".');
        $mode = $mode === 'combined' ? 'scroll' : 'list';
        $needsGroups = false;
    }

    $groups = collect();

    if ($needsGroups) {
        $late = $items->filter(fn ($item) => (bool) ($item['late'] ?? false))->sortBy('date_iso')->values();
        $rest = $items->reject(fn ($item) => (bool) ($item['late'] ?? false))->sortByDesc('date_iso')->values();

        $groups = $rest
            ->groupBy(fn ($item) => substr((string) $item['date_iso'], 0, 7))
            ->map(fn ($list) => [
                'label' => Carbon::parse($list->first()['date_iso'])->translatedFormat('F Y'),
                'items' => $list->values(),
                'late' => false,
            ])
            ->values();

        if ($late->isNotEmpty()) {
            $groups->prepend([
                'label' => $lateLabel ?? __('Late'),
                'items' => $late,
                'late' => true,
            ]);
        }
    }

    $scrollable = in_array($mode, ['scroll', 'combined'], true);

    // 'fill' makes the scroll viewport stretch to the parent (flex-1) instead of a
    // fixed pixel height — for cards whose height is driven by a taller sibling.
    $fill = $scrollable && ($height === 'fill' || $height === true);

    $dotClasses = [
        'success' => 'bg-emerald-500',
        'warning' => 'bg-amber-500',
        'danger' => 'bg-red-500',
        'info' => 'bg-sky-500',
        'gray' => 'bg-gray-400',
    ];
@endphp

@once
    <style>
        .pe-payment-timeline-scroll::-webkit-scrollbar { display: none; }
        [dir=rtl] .pe-payment-timeline-chevron { transform: scaleX(-1); }
    </style>
@endonce

<div @class(['flex min-h-0 flex-1 flex-col' => $fill])>
    @if ($items->isEmpty())
        <p class="py-6 text-center text-sm text-gray-400 dark:text-gray-500">{{ $empty }}</p>
    @else
        @if ($scrollable)
            {{-- Fixed-height viewport: hidden scrollbar, native wheel/touch scroll plus
                 pointer-drag with momentum. The mask fades rows at the cut edges. A drag
                 swallows the trailing click so group headers don't toggle accidentally. --}}
            <div
                @class(['pe-payment-timeline-scroll', 'min-h-0 flex-1' => $fill])
                x-data="{
                    dragging: false, moved: false, lastY: 0, vel: 0, raf: null,
                    down(e) {
                        this.dragging = true; this.moved = false; this.lastY = e.clientY; this.vel = 0;
                        if (this.raf) cancelAnimationFrame(this.raf);
                        this.$el.setPointerCapture(e.pointerId);
                    },
                    move(e) {
                        if (! this.dragging) return;
                        const dy = e.clientY - this.lastY;
                        if (Math.abs(dy) > 2) this.moved = true;
                        this.$el.scrollTop -= dy; this.vel = dy; this.lastY = e.clientY;
                    },
                    up() {
                        if (! this.dragging) return;
                        this.dragging = false;
                        const glide = () => {
                            this.vel *= 0.94;
                            if (Math.abs(this.vel) < 0.4) return;
                            this.$el.scrollTop -= this.vel;
                            this.raf = requestAnimationFrame(glide);
                        };
                        glide();
                    },
                    swallow(e) {
                        if (this.moved) { e.stopPropagation(); e.preventDefault(); this.moved = false; }
                    },
                }"
                x-on:pointerdown="down"
                x-on:pointermove="move"
                x-on:pointerup="up"
                x-on:pointercancel="up"
                x-on:click.capture="swallow"
                :style="{ cursor: dragging ? 'grabbing' : 'grab', 'user-select': dragging ? 'none' : 'auto' }"
                style="{{ $fill ? '' : 'height: '.$height.'; ' }}overflow-y: auto; overscroll-behavior: contain; scrollbar-width: none; touch-action: pan-y; mask-image: linear-gradient(to bottom, transparent 0, #000 0.875rem, #000 calc(100% - 1.125rem), transparent 100%); -webkit-mask-image: linear-gradient(to bottom, transparent 0, #000 0.875rem, #000 calc(100% - 1.125rem), transparent 100%); padding-block: 0.25rem;"
            >
                @if ($needsGroups)
                    @include('project-essentials::components.payment-timeline.groups', ['groups' => $groups, 'dotClasses' => $dotClasses])
                @else
                    @foreach ($items as $item)
                        @include('project-essentials::components.payment-timeline.row', ['item' => $item, 'last' => $loop->last, 'dotClasses' => $dotClasses])
                    @endforeach
                @endif
            </div>
        @elseif ($needsGroups)
            @include('project-essentials::components.payment-timeline.groups', ['groups' => $groups, 'dotClasses' => $dotClasses])
        @else
            @foreach ($items as $item)
                @include('project-essentials::components.payment-timeline.row', ['item' => $item, 'last' => $loop->last, 'dotClasses' => $dotClasses])
            @endforeach
        @endif
    @endif
</div>
