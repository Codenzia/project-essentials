@php
    $statePath = $getStatePath();
    $placeholder = $getPlaceholder() ?? __('Select date range');
    $dateFormat = $getDateFormat();
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        x-data="{
            state: @entangle($statePath),
            open: false,
            get displayValue() {
                if (this.state?.from && this.state?.to) {
                    return this.formatDate(this.state.from) + ' - ' + this.formatDate(this.state.to);
                }
                if (this.state?.from) {
                    return this.formatDate(this.state.from) + ' - ...';
                }
                if (this.state?.to) {
                    return '... - ' + this.formatDate(this.state.to);
                }
                return '';
            },
            formatDate(dateStr) {
                if (!dateStr) return '';
                const d = new Date(dateStr + 'T00:00:00');
                return d.toLocaleDateString(@js(str_replace('_', '-', app()->getLocale())), {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                });
            },
            clear() {
                this.state = { from: null, to: null };
            },
        }"
        class="relative"
        x-on:click.outside="open = false"
        x-on:keydown.escape.window="open = false"
    >
        {{-- Trigger: matches Filament DatePicker structure --}}
        <div class="fi-input-wrp fi-fo-date-time-picker">
            <div class="fi-input-wrp-content-ctn">
                <button type="button" x-on:click="open = !open" class="fi-fo-date-time-picker-trigger">
                    <input
                        readonly
                        placeholder="{{ $placeholder }}"
                        :value="displayValue"
                        class="fi-fo-date-time-picker-display-text-input"
                    />
                </button>
            </div>
            <div x-show="state?.from || state?.to" x-cloak class="fi-input-wrp-suffix" style="padding-inline-end: 0.5rem;">
                <button
                    type="button"
                    x-on:click.stop="clear(); open = false"
                    class="text-gray-400 hover:text-gray-500 dark:text-gray-500 dark:hover:text-gray-400"
                >
                    <x-heroicon-m-x-circle class="h-4 w-4" />
                </button>
            </div>
        </div>

        {{-- Dropdown --}}
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="opacity-0 -translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-1"
            x-cloak
            class="absolute z-30 mt-1 min-w-[20rem] rounded-lg bg-white p-3 shadow-lg ring-1 ring-gray-950/5
                   dark:bg-gray-900 dark:ring-white/10"
        >
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('From') }}</label>
                    <x-filament::input.wrapper>
                        <x-filament::input type="date" x-model="state.from" />
                    </x-filament::input.wrapper>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('To') }}</label>
                    <x-filament::input.wrapper>
                        <x-filament::input type="date" x-model="state.to" />
                    </x-filament::input.wrapper>
                </div>
            </div>
        </div>
    </div>
</x-dynamic-component>
