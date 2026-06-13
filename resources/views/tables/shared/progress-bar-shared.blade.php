@php
    $progress = isset($getState) ? $getState() : $state;
    $progress = max(0, min(100, (float) ($progress ?? 0)));
    $component = $column ?? $entry;
    $label = $component->getProgressLabel();
    $align = $component->getAlignValue();
    $hideValue = $component->getHideValue();

    // Get dynamic font sizes
    $labelSizeClass = 'text-' . $component->getLabelFontSize();
    $valueSizeClass = 'text-' . $component->getValueFontSize();

    $isInline = in_array($align, ['before', 'after']);

    // for the no-label case (top positions)
    $alignmentClass = match ($align) {
        'left' => 'justify-start text-left',
        'center' => 'justify-center text-center',
        'right' => 'justify-end text-right',
        default => 'justify-end text-right',
    };

    // for the with-label case (top positions)
    $valuePosClass = match ($align) {
        'left' => 'ml-2',
        'center' => 'absolute left-1/2 -translate-x-1/2',
        'right' => 'ml-auto',
        default => 'ml-auto',
    };
@endphp

<div @class([
    'relative flex w-full h-full min-w-24 mb-2',
    'flex-col' => !$isInline,
    'flex-row items-center gap-2' => $isInline,
])>

    {{-- 1. BEFORE Case --}}
    @if ($isInline && $align === 'before' && !$hideValue)
        <div class="{{ $valueSizeClass }} text-gray-400 whitespace-nowrap">
            ({{ $progress > 0 ? number_format($progress, 0) : 0 }}%)
        </div>
    @endif

    <div class="flex-1">
        @if (!$isInline)
            @if (empty($label))
                @if (!$hideValue)
                    <div class="relative flex w-full mb-1 text-gray-400 {{ $alignmentClass }} {{ $valueSizeClass }}">
                        ({{ $progress > 0 ? number_format($progress, 0) : 0 }}%)
                    </div>
                @endif
            @else
                <div class="relative dark:text-white flex items-center w-full mb-2 {{ $labelSizeClass }}">
                    <span class="font-semibold truncate">{{ $label }}</span>
                    @if (!$hideValue)
                        <span class="text-gray-400 {{ $valuePosClass }} {{ $valueSizeClass }}">
                            ({{ $progress > 0 ? number_format($progress, 0) : 0 }}%)
                        </span>
                    @endif
                </div>
            @endif
        @endif

        <div @class([
            'relative h-2 overflow-hidden rounded-full bg-orange',
            'mt-1' => empty($label) && !$isInline,
        ])>
            <div class="absolute top-0 left-0 h-2 rounded bg-gradient-to-r from-primary-900 to-primary-500"
                style="width: {{ $progress }}%;">
            </div>
        </div>
    </div>

    {{-- 2. AFTER Case --}}
    @if ($isInline && $align === 'after' && !$hideValue)
        <div class="{{ $valueSizeClass }} text-gray-400 whitespace-nowrap">
            ({{ $progress > 0 ? number_format($progress, 0) : 0 }}%)
        </div>
    @endif
</div>
