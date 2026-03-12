{{--
    Responsive Tabs with Overflow Dropdown

    A responsive tab component that automatically folds overflow tabs into a "More" dropdown
    when there isn't enough space to display all tabs.

    =====================================================================================
    USAGE
    =====================================================================================

    Basic usage with Livewire binding:

        <x-project-essentials::responsive-tabs
            :tabs="$this->getTabs()"
            wire:model="activeTab"
        />

    Without Livewire (static):

        <x-project-essentials::responsive-tabs
            :tabs="$tabs"
            active="overview"
        />

    With alignment:

        <x-project-essentials::responsive-tabs
            :tabs="$tabs"
            wire:model="activeTab"
            align="left"
        />

    =====================================================================================
    PROPS
    =====================================================================================

    | Prop       | Type    | Default  | Description                                    |
    |------------|---------|----------|------------------------------------------------|
    | tabs       | array   | []       | Array of tab definitions (see TAB OPTIONS)     |
    | active     | string  | null     | Initial active tab ID (when not using wire:model) |
    | align      | string  | 'center' | Tab alignment: 'left', 'center', or 'right'   |
    | moreLabel  | string  | 'More'   | Label for the overflow dropdown button         |
    | persist    | string  | null     | localStorage key for tab persistence           |

    =====================================================================================
    TAB OPTIONS
    =====================================================================================

    Tabs can be defined as a keyed array (ID inferred from key):

        $tabs = [
            'overview' => [
                'label' => 'Overview',
                'icon' => 'heroicon-o-home',
            ],
            'tasks' => [
                'label' => 'Tasks',
                'icon' => 'heroicon-o-clipboard',
                'badge' => 12,
            ],
        ];

    Tab properties:

    | Property | Type    | Required | Description                                      |
    |----------|---------|----------|--------------------------------------------------|
    | id       | string  | Yes*     | Unique identifier (*auto from key if keyed array)|
    | label    | string  | Yes      | Display text for the tab                         |
    | icon     | string  | No       | Icon component name (e.g., 'heroicon-o-home')    |
    | badge    | int/str | No       | Badge value (uses Filament badge, primary color) |
    | params   | array   | No       | Custom parameters passed through on tab change   |
    | disabled | bool    | No       | Whether the tab is disabled                      |
--}}

@props([
    'tabs' => [],
    'active' => null,
    'moreLabel' => null,
    'align' => 'center',
    'persist' => null,
])

@php
    $normalizedTabs = collect($tabs)
        ->map(function ($tab, $key) {
            $normalized = is_string($key) ? array_merge(['id' => $key], $tab) : $tab;
            $normalized['params'] = $normalized['params'] ?? [];
            return $normalized;
        })
        ->values()
        ->all();

    $defaultActive = $active ?? ($normalizedTabs[0]['id'] ?? null);

    $wireModel = null;
    foreach ($attributes as $key => $value) {
        if (str_starts_with($key, 'wire:model')) {
            $wireModel = $value;
            break;
        }
    }

    $tabsJson = json_encode($normalizedTabs);
    $persistJson = $persist ? "'" . e($persist) . "'" : 'null';

    $alignClasses = [
        'left' => 'justify-start',
        'center' => 'justify-center',
        'right' => 'justify-end',
    ];
    $alignClass = $alignClasses[$align] ?? 'justify-center';
@endphp

<div @if ($wireModel) x-data="responsiveTabs({ activeTabInit: $wire.entangle('{{ $wireModel }}').live, tabs: {{ $tabsJson }}, persistKey: {{ $persistJson }} })"
    @else
        x-data="responsiveTabs({ activeTabInit: '{{ $defaultActive }}', tabs: {{ $tabsJson }}, persistKey: {{ $persistJson }} })" @endif
    {{ $attributes->except(['tabs', 'active', 'moreLabel', 'wire:model', 'wire:model.live', 'align', 'persist'])->class(['w-full']) }}>
    <nav x-ref="nav" class="flex items-center gap-2 {{ $alignClass }}">
        @foreach ($normalizedTabs as $index => $tab)
            <div wire:key="tab-{{ $tab['id'] }}-{{ $index }}" data-tab-item="{{ $index }}"
                x-on:click="{{ $tab['disabled'] ?? false ? '' : 'select(' . $index . ')' }}"
                @if ($tab['disabled'] ?? false) aria-disabled="true" @endif
                class="flex items-center gap-2 px-4 py-2.5 font-medium transition-all whitespace-nowrap shrink-0 border-b-2 border-transparent cursor-pointer {{ $tab['disabled'] ?? false ? 'opacity-50 cursor-not-allowed' : '' }}"
                x-bind:class="{
                    'text-primary-500 dark:text-primary-400 !border-gray-300 dark:!border-gray-600': activeTab === '{{ $tab['id'] }}',
                    'text-gray-600 dark:text-gray-400 hover:text-primary-400 dark:hover:text-gray-200': activeTab !== '{{ $tab['id'] }}',
                    '!hidden': !isVisible({{ $index }})
                }">
                @isset($tab['icon'])
                    @if (str_starts_with($tab['icon'], 'heroicon-') || View::exists('components.' . $tab['icon']))
                        <x-dynamic-component :component="$tab['icon']" class="w-5 h-5" />
                    @endif
                @endisset
                <span>{{ $tab['label'] }}</span>
                @isset($tab['badge'])
                    <x-filament::badge color="primary" size="sm">
                        {{ $tab['badge'] }}
                    </x-filament::badge>
                @endisset
            </div>
        @endforeach

        {{-- More Dropdown --}}
        <div x-ref="moreBtn" x-show="hasOverflow" x-cloak class="relative shrink-0"
            x-on:click.outside="moreOpen = false">
            <div x-on:click="moreOpen = !moreOpen" type="button"
                class="flex items-center gap-2 px-3 py-2.5 font-medium transition-all border-b-2 border-transparent cursor-pointer"
                x-bind:class="isActiveInOverflow
                    ?
                    'text-primary-500 dark:text-primary-400 !border-gray-300 dark:!border-gray-600' :
                    'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200'">
                <x-heroicon-m-ellipsis-horizontal class="w-5 h-5" />
                <span>{{ $moreLabel ?? __('More') }}</span>
                <span x-show="isActiveInOverflow" x-text="'(' + getCurrentTab()?.label + ')'"
                    class="text-primary-500 dark:text-primary-400 text-sm"></span>
                <span x-show="!isActiveInOverflow"
                    class="text-xs bg-gray-200 dark:bg-gray-700 px-1.5 py-0.5 rounded-full min-w-[1.25rem] text-center"
                    x-text="overflowCount"></span>
                <x-heroicon-m-chevron-down class="w-4 h-4 transition-transform duration-200"
                    x-bind:class="{ 'rotate-180': moreOpen }" />
            </div>

            <div x-show="moreOpen" x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-1"
                class="absolute end-0 top-full mt-2 min-w-[200px] py-1.5 bg-white dark:bg-gray-800 rounded-xl shadow-2xl ring-1 ring-gray-200 dark:ring-gray-700"
                style="z-index: 9999;">
                @foreach ($normalizedTabs as $index => $tab)
                    <div wire:key="overflow-tab-{{ $tab['id'] }}-{{ $index }}"
                        x-show="!isVisible({{ $index }})" x-on:click="select({{ $index }})"
                        type="button"
                        class="w-full flex items-center gap-3 px-4 py-2.5 text-start transition-colors cursor-pointer"
                        x-bind:class="{
                            'bg-primary-500/10 text-primary-500': activeTab === '{{ $tab['id'] }}',
                            'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50': activeTab !== '{{ $tab['id'] }}'
                        }">
                        @isset($tab['icon'])
                            @if (str_starts_with($tab['icon'], 'heroicon-') || View::exists('components.' . $tab['icon']))
                                <x-dynamic-component :component="$tab['icon']" class="w-5 h-5" />
                            @endif
                        @endisset
                        <span>{{ $tab['label'] }}</span>
                        @isset($tab['badge'])
                            <x-filament::badge color="primary" size="sm" class="ms-auto">
                                {{ $tab['badge'] }}
                            </x-filament::badge>
                        @endisset
                    </div>
                @endforeach
            </div>
        </div>
    </nav>
</div>
