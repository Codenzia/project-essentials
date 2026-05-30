<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div x-data="{ state: @entangle($getStatePath()) }" class="flex items-center">
        <!-- Decrement Button -->
        <button type="button"
            @click="state = Math.max(0, parseInt(state) - 1)"
            class="bg-gray-100 dark:bg-gray-700 dark:hover:bg-gray-600 dark:border-gray-600 hover:bg-gray-200
                   border border-gray-300 rounded-s-lg p-3 h-11 focus:ring-gray-100 dark:focus:ring-gray-700
                   focus:ring-2 focus:outline-none">
            <svg class="w-3 h-3 text-gray-900 dark:text-white" aria-hidden="true" fill="none" viewBox="0 0 18 2">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 1h16"/>
            </svg>
        </button>

        <!-- Number Input (Prevents Null & Ensures Min 0) -->
        <input type="number" x-model="state" min="0"
            @input="state = state.replace(/\D/g, '') || '0'"
            @blur="if (state === '') state = '0'"
            class="cr-counter bg-gray-50 border-x-0 border-gray-300 h-11 text-center text-gray-900 text-sm
                   focus:ring-blue-500 focus:border-blue-500 block w-full py-2.5
                   dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white
                   dark:focus:ring-blue-500 dark:focus:border-blue-500"
            placeholder="0" />

        <!-- Increment Button -->
        <button type="button"
            @click="state = Math.max(0, parseInt(state) + 1)"
            class="bg-gray-100 dark:bg-gray-700 dark:hover:bg-gray-600 dark:border-gray-600 hover:bg-gray-200
                   border border-gray-300 rounded-e-lg p-3 h-11 focus:ring-gray-100 dark:focus:ring-gray-700
                   focus:ring-2 focus:outline-none">
            <svg class="w-3 h-3 text-gray-900 dark:text-white" aria-hidden="true" fill="none" viewBox="0 0 18 18">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 1v16M1 9h16"/>
            </svg>
        </button>
    </div>
</x-dynamic-component>
