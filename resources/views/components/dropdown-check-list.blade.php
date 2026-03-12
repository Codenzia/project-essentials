@props([
    'options' => [],
    'enumClass' => null,
    'label' => '',
    'icon' => null,
    'color' => 'gray',
    'tooltip' => '',
    'name' => 'filter',
    'allowAll' => true,
    'searchable' => false,
    'maxHeight' => '14rem',
])

@php
    $isRich = false;

    if ($enumClass && enum_exists($enumClass)) {
        $options = collect($enumClass::cases())
            ->mapWithKeys(
                fn($case) => [
                    $case->value => method_exists($case, 'getLabel') ? $case->getLabel() : $case->name,
                ],
            )
            ->toArray();
    } else {
        // Detect rich options (have 'avatar' or 'subtitle' keys)
        $first = collect($options)->first();
        $isRich = is_array($first) && (isset($first['avatar']) || isset($first['subtitle']));

        if ($isRich) {
            // Keep full option data, keyed by value
            $options = collect($options)->keyBy('value')->toArray();
        } else {
            $options = collect($options)->mapWithKeys(fn($case) => [$case['value'] => $case['label']])->toArray();
        }
    }

    // Build a simple value => label map for Alpine search
    $searchMap = $isRich
        ? collect($options)->mapWithKeys(fn($opt, $key) => [$key => $opt['label'] . ' ' . ($opt['subtitle'] ?? '')])->toArray()
        : $options;
@endphp

<div x-data="{
    value: [],
    search: '',
    options: @js($searchMap),
    selectAll() {
        this.value = [];
    },
    toggleOption(val) {
        const idx = this.value.indexOf(val);
        if (idx === -1) {
            this.value = [...this.value, val];
        } else {
            this.value = this.value.filter(v => v !== val);
        }
    },
    isSelected(val) {
        return this.value.includes(val);
    },
    isVisible(key) {
        if (!this.search) return true;
        const text = this.options[key] || '';
        return text.toLowerCase().includes(this.search.toLowerCase());
    },
    get hasVisibleOptions() {
        return Object.keys(this.options).some(key => this.isVisible(key));
    },
    get isAllSelected() {
        return this.value.length === 0;
    },
    get isFiltered() {
        return this.value.length > 0;
    }
}" x-modelable="value" {{ $attributes }}>
    <x-filament::dropdown>
        <x-slot name="trigger">
            <x-filament::button
                class="flex items-center gap-1 px-4 text-xs bg-white dark:bg-gray-900 h-12 rounded-lg font-light text-gray-400"
                size="md" :color="$color" :icon="$icon" :tooltip="__($tooltip)">
                {{ __($label) }}
                <template x-if="isFiltered">
                    <span
                        class="inline-flex items-center justify-center text-primary-500 rounded-lg text-xs font-semibold">
                        <span x-text="value.length"></span> / {{ count($options) }}
                    </span>
                </template>
            </x-filament::button>
        </x-slot>

        <x-filament::dropdown.list>
            <div class="text-xs mt-1 space-y-1 p-2">
                @if ($searchable)
                    <div class="relative mb-2">
                        <x-heroicon-o-magnifying-glass
                            class="absolute left-2 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none" />
                        <input type="text" x-model.debounce.200ms="search"
                            placeholder="{{ __('Search...') }}"
                            class="w-full pl-7 pr-2 py-1.5 text-xs rounded border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-1 focus:ring-primary-500 focus:border-primary-500"
                            @click.stop />
                    </div>
                @endif

                @if ($allowAll)
                    <label class="flex items-center gap-2 hover:underline hover:font-bold cursor-pointer"
                        :class="{ 'text-primary-600 font-medium': isAllSelected }">
                        <input type="radio" :checked="isAllSelected" @click="selectAll()"
                            name="{{ $name }}_all" class="text-primary-600 focus:ring-primary-500">
                        <span>{{ __('All') }}</span>
                    </label>
                    <hr class="my-1 border-gray-200 dark:border-gray-700">
                @endif

                <div @if ($searchable) style="max-height: {{ $maxHeight }}; overflow-y: auto;" @endif>
                    @foreach ($options as $value => $optionData)
                        @php
                            $optLabel = $isRich ? $optionData['label'] : $optionData;
                            $optAvatar = $isRich ? ($optionData['avatar'] ?? null) : null;
                            $optSubtitle = $isRich ? ($optionData['subtitle'] ?? null) : null;
                        @endphp
                        <label class="flex items-center gap-2 cursor-pointer rounded px-1 py-1 hover:bg-gray-50 dark:hover:bg-gray-700/50"
                            x-show="isVisible(@js((string) $value))">
                            <input type="checkbox" :checked="isSelected(@js((string) $value))"
                                @click="toggleOption(@js((string) $value))"
                                class="text-primary-600 focus:ring-primary-500 shrink-0">
                            @if ($optAvatar)
                                <img src="{{ $optAvatar }}" alt=""
                                    class="w-6 h-6 rounded-full object-cover shrink-0" />
                            @endif
                            <div class="min-w-0">
                                <span class="block truncate">{{ $optLabel }}</span>
                                @if ($optSubtitle)
                                    <span class="block text-[10px] text-gray-400 dark:text-gray-500 truncate">{{ $optSubtitle }}</span>
                                @endif
                            </div>
                        </label>
                    @endforeach

                    @if ($searchable)
                        <p class="text-gray-400 dark:text-gray-500 py-1 text-center" x-show="search && !hasVisibleOptions" x-cloak>
                            {{ __('No matches') }}
                        </p>
                    @endif
                </div>
            </div>
        </x-filament::dropdown.list>
    </x-filament::dropdown>
</div>
