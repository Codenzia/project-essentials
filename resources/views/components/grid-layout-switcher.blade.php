@props(['gridSize' => 1])
<style>
    .tooltip-container {
        overflow: visible !important;
    }

    .grid-tooltip {
        z-index: 9999 !important;
    }
</style>

<div x-data="{
    value: {{ $gridSize }},
    showTooltip: false
}" class="flex flex-col w-fit grid-layout-switcher">

    <!-- Tick labels -->
    <div class="relative w-32 h-4">
        <template x-for="i in 6" :key="i">
            <div class="absolute text-xs font-medium transition-colors"
                :class="value == i ? 'text-primary-500 dark:text-primary-400 font-semibold' : 'text-gray-500 dark:text-gray-400'"
                :style="`left: calc((${i - 1}) * 20% + 4px); transform: translateX(-50%);`" x-text="i"></div>
        </template>
    </div>

    <!-- Slider -->
    <div class="relative w-35" @mouseenter="showTooltip = true" @mouseleave="showTooltip = false">
        <input type="range" min="1" max="6" step="1" x-model.number="value"
            @change="$wire.call('updateGridLayout', value)" @input="value = parseInt($el.value)"
            class="w-full accent-primary-500 dark:accent-primary-400 cursor-pointer">

        <!-- Tooltip (floats above, doesn't affect layout) -->
        <div x-show="showTooltip" x-transition
            class="absolute -top-8 right-0 px-2 py-1 bg-gray-900 dark:bg-gray-700 text-white text-xs rounded whitespace-nowrap pointer-events-none z-10">
            <span>Adjust grid columns</span>
        </div>
    </div>
</div>
