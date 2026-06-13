@php
    $allActions = $group->getActions();
    $mainAction = $allActions[0] ?? null;
    $dropdownActions = $allActions;
@endphp
<div class='flex items-stretch h-[39px]! overflow-hidden spilt-button '>
    {{-- The Main Action Button on the left --}}
    @if ($mainAction)
        <x-filament::button :wire:click="$mainAction->getLivewireClickHandler()" :color="$mainAction->getColor()" :icon="$mainAction->getIcon()"
            :icon-position="$mainAction->getIconPosition()" :size="$mainAction->getSize()"
            class="rounded-r-none! border-r-2! border-black/20 py-0! flex-1 h-full! dark:text-white"
            {{-- :disabled="$anyActionsMounted" --}}>
            {{ $mainAction->getLabel() }}
        </x-filament::button>
    @endif

    {{-- The Action Group dropdown, containing all actions from the array (including the main one) --}}
    @if (count($dropdownActions) > 0)
        <div class="flex items-stretch">
            @php
                $dropdownGroup = \Filament\Actions\ActionGroup::make($dropdownActions)
                    ->label('')
                    ->icon('heroicon-o-chevron-down')
                    ->color($mainAction->getColor() ?? 'primary')
                    ->size($mainAction->getSize())
                    ->iconSize(Filament\Support\Enums\IconSize::Large)
                    ->button()
                    ->iconPosition(Filament\Support\Enums\IconPosition::After)
                    ->dropdown()
                    ->dropdownPlacement('bottom-end')
                    ->extraAttributes([
                        'class' => 'rounded-l-none! border-l-0! mr-3 h-[39px]!',
                    ]);
            @endphp
            {{ $dropdownGroup }}
        </div>
    @endif
    <x-filament-actions::modals />
</div>
