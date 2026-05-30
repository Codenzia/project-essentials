<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div x-data="{
        value: $wire.entangle('{{ $getStatePath() }}'),
        step: @js($getStep()),
        interval: null,
        isDragging: false,
        isDisabled: @js($isDisabled()),
        init() {
            this.value = Math.min(100, Math.max(0, this.value));
        },
        startAdjusting(change) {
            if (this.isDisabled) return;
            this.stopAdjusting();
            this.interval = setInterval(() => {
                this.value = Math.min(100, Math.max(0, this.value + change));
            }, 100);
        },
        stopAdjusting() {
            clearInterval(this.interval);
            this.interval = null;
        },
        updateProgress(event) {
            if (this.isDisabled) return;
            if (this.isDragging || event.type === 'click') {
                const rect = event.currentTarget.getBoundingClientRect();
                const x = event.clientX - rect.left;
                const width = rect.width;
                this.value = Math.min(100, Math.max(0, Math.round((x / width) * 100)));
            }
        }
    }" wire:key="{{ $getId() }}-{{ $isDisabled() ? 'disabled' : 'enabled' }}"
        class="flex items-center space-x-2 p-2 border rounded-xl border-gray-200 dark:border-gray-500/40 bg-transparent">

        @if (!$isDisabled())
            <!-- Decrement Button -->
            <button type="button"
                class="px-2  rounded-lg hover:bg-primary-500 active:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed"
                @mousedown="startAdjusting(-step)" @mouseup="stopAdjusting" @mouseleave="stopAdjusting"
                @click="if (!isDisabled) value = Math.max(0, value - step)" :disabled="isDisabled">
                -
            </button>
        @endif
        <!-- Progress Bar -->
        <div class="relative flex-1 bg-gray-200 dark:bg-white/10 h-6 rounded-full overflow-hidden"
            :class="{
                'cursor-pointer': !isDisabled,
                'cursor-not-allowed opacity-50': isDisabled
            }"
            @mousedown="if (!isDisabled) { isDragging = true; updateProgress($event); }"
            @mousemove="if (!isDisabled) updateProgress($event)" @mouseup="isDragging = false"
            @mouseleave="isDragging = false" @click="if (!isDisabled) updateProgress($event)">
            <div class="bg-primary-500 h-full transition-all duration-100" :style="`width: ${value}%`">
            </div>
            <span class="absolute inset-0 flex items-center justify-center text-xs font-semibold select-none">
                <span x-text="`${value}%`" :class="{ 'text-white': value > 50, 'text-secondary': value <= 50 }"></span>
            </span>
        </div>

        @if (!$isDisabled())
            <!-- Increment Button -->
            <button type="button"
                class="px-2 rounded-lg hover:bg-primary-500 active:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed"
                @mousedown="startAdjusting(step)" @mouseup="stopAdjusting" @mouseleave="stopAdjusting"
                @click="if (!isDisabled) value = Math.min(100, value + step)" :disabled="isDisabled">
                +
            </button>
        @endif
    </div>
</x-dynamic-component>
