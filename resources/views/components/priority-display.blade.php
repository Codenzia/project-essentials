@php
    $component = $column ?? ($entry ?? null);

    $rawState = null;
    if (isset($getState) && is_callable($getState)) {
        $rawState = $getState();
    } elseif (isset($getRecord) && is_callable($getRecord) && isset($getName) && is_callable($getName)) {
        $record = $getRecord();
        $columnName = $getName();
        $rawState = data_get($record, $columnName);
    } else {
        $rawState = $state ?? null;
    }

    // If the state is a backed enum with HasLabel, use that
    $label = $rawState;
    $color = null;
    $icon = null;

    if ($rawState instanceof \Filament\Support\Contracts\HasLabel) {
        $label = $rawState->getLabel();
    }
    if ($rawState instanceof \Filament\Support\Contracts\HasColor) {
        $color = $rawState->getColor();
    }
    if ($rawState instanceof \Filament\Support\Contracts\HasIcon) {
        $icon = $rawState->getIcon();
    }

    // Allow column/entry to override color
    if (isset($component) && method_exists($component, 'getTextColor')) {
        $color = $component->getTextColor() ?? $color;
    }

    // Fallback: if enum has static color()/icon() methods
    if ($color === null && is_object($rawState) && method_exists($rawState, 'color')) {
        $color = $rawState::color($rawState->value);
    }
    if ($icon === null && is_object($rawState) && method_exists($rawState, 'icon')) {
        $icon = $rawState::icon($rawState->value);
    }
@endphp

<div class="m-0 p-0">
    @if ($icon)
        @svg($icon, 'inline mb-1')
    @endif
    <div class="inline text-xs" @if($color) style="color: {{ $color }}" @endif>{{ $label }}</div>
</div>
