@php
    $columns = $this->getToggleableColumnPresentation();
    $total = count($columns);
    $visible = 0;
    foreach ($columns as $col) {
        $visible += $col['visible'] ? 1 : 0;
    }
@endphp
<div class="rounded-full toolbar-action-filter px-3 py-2.5">
    <x-filament::dropdown placement="bottom-end">
        {{-- Trigger --}}
        <x-slot name="trigger">
            <x-filament::icon-button icon="heroicon-o-adjustments-horizontal" size="sm" color="gray"
                tooltip="{{ __('Toggle columns on/off') }}" />
        </x-slot>

        {{-- Dropdown Content --}}
        <div class="flex flex-col">

            {{-- Header: Show/Hide All --}}
            <div class="border-b border-gray-200 dark:border-gray-700 pb-1">
                <x-filament::dropdown.list>
                    <x-filament::dropdown.list.item tag="button" type="button" wire:click="toggleAllColumns"
                        :icon="$this->getToggleAllIcon()" color="primary" wire:loading.attr="disabled" wire:key="toggle-all-columns"
                        class="text-xs hover:underline hover:font-bold">
                        {{ $this->getToggleAllLabel() }}
                    </x-filament::dropdown.list.item>
                </x-filament::dropdown.list>
            </div>

            {{-- Body: Scrollable Column List --}}
            <div class="max-h-fit overflow-y-auto">
                <x-filament::dropdown.list>
                    @foreach ($columns as $col)
                        <x-filament::dropdown.list.item tag="button" type="button"
                            wire:click="toggleColumnVisibility('{{ $col['key'] }}')" :icon="$col['icon']"
                            :color="$col['color']" wire:loading.attr="disabled" wire:key="toggle-col-{{ $col['key'] }}"
                            class="text-xs hover:underline hover:font-bold">
                            {{ $col['label'] }}
                        </x-filament::dropdown.list.item>
                    @endforeach
                </x-filament::dropdown.list>
            </div>

        </div>
    </x-filament::dropdown>
    @if ($visible < $total)
        <h6 class="text-xs text-gray-400 font-medium">{{ $visible }}/{{ $total }}</h6>
    @endif

</div>
