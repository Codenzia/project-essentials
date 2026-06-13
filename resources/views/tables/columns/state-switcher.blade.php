@php
    $isDisabled = $isDisabled();
    $state = $getState();
    $onLabel = $column?->getOnLabel() ?? __('Active');
    $offLabel = $column?->getOffLabel() ?? __('Inactive');
    $isOn = $column ? $column->isActiveState($state) : false;
@endphp

<div class="flex items-center justify-center">
    <div
        wire:key="{{ $this->getId() }}.table.record.{{ $recordKey }}.column.{{ $getName() }}.toggle-column.{{ $state ? 'true' : 'false' }}">
        <div x-data="{
            state: @js($isOn),
            isLoading: false,
            error: undefined,
        }" wire:ignore
            {{ $attributes->merge($getExtraAttributes(), escape: false)->class(['fi-ta-toggle']) }}>

            <button type="button" role="switch" aria-checked="false" x-bind:aria-checked="state.toString()"
                @if (!$isDisabled) x-on:click.stop="
                        if (isLoading) return;

                        const newState = !state;
                        isLoading = true;

                        try {
                            const response = await $wire.updateTableColumnState(
                                @js($getName()),
                                @js($this->getId()),
                                newState,
                            );

                            if (response?.error) {
                                error = response.error;
                                setTimeout(() => error = undefined, 3000);
                            } else {
                                state = newState;
                                error = undefined;
                            }
                        } catch (e) {
                            error = 'An error occurred';
                            setTimeout(() => error = undefined, 3000);
                        } finally {
                            isLoading = false;
                        }
                    " @endif
                x-tooltip="error || false" x-bind:disabled="isLoading || @js($isDisabled)"
                class="relative inline-flex items-center justify-center h-10 px-3 py-2 rounded-full transition-all duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2"
                x-bind:class="{
                    'opacity-50 cursor-not-allowed': isLoading || @js($isDisabled),
                    'cursor-pointer hover:scale-105': !isLoading && !@js($isDisabled),
                    'bg-primary-600 hover:bg-primary-700 focus:ring-primary-500 dark:bg-primary-500 dark:hover:bg-primary-600': state,
                    'bg-red-600 hover:bg-red-700 focus:ring-red-500 dark:bg-red-500 dark:hover:bg-red-600': !state,
                    'w-24': true
                }">
                <div x-show="isLoading" x-cloak class="absolute inset-0 flex items-center justify-center">
                    <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                </div>

                <div x-show="state && !isLoading" x-cloak class="flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="ml-2 text-xs font-medium text-white" x-text="@js($onLabel)"></span>
                </div>

                <div x-show="!state && !isLoading" x-cloak class="flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                    <span class="ml-2 text-xs font-medium text-white" x-text="@js($offLabel)"></span>
                </div>
            </button>

            <div x-show="error" x-cloak x-transition
                class="absolute top-full mt-1 left-1/2 transform -translate-x-1/2 z-10">
                <div class="bg-red-100 border border-red-400 text-red-700 px-2 py-1 rounded text-xs whitespace-nowrap">
                    <span x-text="error"></span>
                </div>
            </div>
        </div>
    </div>
</div>
