@props([
    'class' => 'text-left break-words whitespace-normal relative',
    'tags' => null,
    'contextLimit' => 2,
])

@php
    // Primary: use state from the component (works in both Eloquent and schema-driven contexts)
    $tagsState = isset($getState) ? $getState() : $state ?? null;

    // Fallback: use $tags prop, then try the record's tags relationship
    if ($tags === null) {
        if (! empty($tagsState)) {
            $tags = is_array($tagsState) ? collect($tagsState) : $tagsState;
        } elseif (isset($getRecord) && $getRecord()) {
            $tags = $getRecord()->tags ?? collect();
        } else {
            $tags = collect();
        }
    }

    // Ensure $tags is countable
    if (is_array($tags)) {
        $tags = collect($tags);
    }

    $totalTags = count($tags);

    $component = $column ?? ($entry ?? null);

    /** -----------------------------------------
     * Component-driven config (with defaults)
     * ---------------------------------------- */
    $contextLimit =
        isset($component) && method_exists($component, 'getContextLimit')
            ? $component->getContextLimit()
            : $contextLimit ?? 2;

    $class =
        isset($component) && method_exists($component, 'getExtraAttributes')
            ? $component->getExtraAttributes()['class'] ?? $class
            : $class;
    /** -----------------------------------------
     * Colors
     * ---------------------------------------- */
    $colorClasses = [
        'bg-red-100 border-red-300 text-red-700',
        'bg-blue-100 border-blue-300 text-blue-700',
        'bg-green-100 border-green-300 text-green-700',
        'bg-yellow-100 border-yellow-300 text-yellow-700',
        'bg-purple-100 border-purple-300 text-purple-700',
        'bg-pink-100 border-pink-300 text-pink-700',
        'bg-indigo-100 border-indigo-300 text-indigo-700',
        'bg-teal-100 border-teal-300 text-teal-700',
        'bg-orange-100 border-orange-300 text-orange-700',
        'bg-cyan-100 border-cyan-300 text-cyan-700',
        'bg-lime-100 border-lime-300 text-lime-700',
        'bg-emerald-100 border-emerald-300 text-emerald-700',
        'bg-sky-100 border-sky-300 text-sky-700',
        'bg-violet-100 border-violet-300 text-violet-700',
        'bg-fuchsia-100 border-fuchsia-300 text-fuchsia-700',
        'bg-rose-100 border-rose-300 text-rose-700',
    ];

    $useRandomColors = isset($component) && method_exists($component, 'getUseRandomColors')
        ? $component->getUseRandomColors()
        : false;
@endphp

<div class="{{ $class }}">
    <div class="flex flex-wrap gap-2">
        @if ($totalTags > 0)
            @foreach ($tags->take($contextLimit) as $tag)
                @php
                    // Handle both object and array tag structures
                    $tagName = is_object($tag) ? $tag->name : $tag['name'] ?? $tag;

                    $tagColor = $useRandomColors
                        ? $colorClasses[crc32((string) $tagName) % count($colorClasses)]
                        : 'bg-gradient-to-r from-primary-900/70 to-primary-500/70 text-white';
                @endphp

                <h6 class="inline-flex items-center px-3 py-1 text-xs border rounded-full {{ $tagColor }}"
                    title="{{ $tagName }}">
                    {{ $tagName }}
                </h6>
            @endforeach

            @if ($totalTags > $contextLimit)
                <h6 class="inline-flex items-center px-2 py-1 text-xs text-gray-500">
                    +{{ $totalTags - $contextLimit }}
                </h6>
            @endif
        @else
            {{-- Default no tags --}}
            <h6
                class="inline-flex items-center px-3 py-1 text-xs italic text-gray-300 dark:text-gray-500/70 rounded-full bg-gray-300/40 dark:bg-gray-600/20">
                {{ __('No Tags') }}
            </h6>
        @endif
    </div>
</div>
