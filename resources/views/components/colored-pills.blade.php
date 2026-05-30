@php
    $component = $column ?? ($entry ?? null);
    $record = $getRecord();
    $items = $component->resolveItems($record);
    $totalItems = count($items);
    $visibleLimit = $component->getVisibleLimit();
    $visibleItems = $items->take($visibleLimit);
    $hiddenItems = $items->slice($visibleLimit);
    $hiddenCount = $totalItems - $visibleLimit;
    $showSubtitle = $component->getShowSubtitleInHover();
    $centered = method_exists($component, 'isCentered') && $component->isCentered();
@endphp

<div class="{{ $centered ? 'text-center' : 'text-left' }} break-words whitespace-normal">
    @if ($totalItems > 0)
        <div class="flex flex-wrap {{ $centered ? 'justify-center gap-2' : 'gap-1.5' }}">
            @foreach ($visibleItems as $item)
                @php
                    $label = $component->resolveItemLabel($item);
                    $color = $component->resolveItemColor($item);
                    $size = $component->resolveItemSize($item);
                    $tooltip = $component->resolveItemTooltip($item);
                @endphp
                <span class="inline-flex items-center px-2 py-0.5 {{ $size }} font-medium border rounded-full {{ $color }}"
                      @if ($tooltip) title="{{ $tooltip }}" @endif>
                    {{ $label }}
                </span>
            @endforeach

            @if ($hiddenCount > 0)
                <span class="inline-flex items-center"
                      x-data="{
                          showPreview: false,
                          pos: { top: 0, left: 0 },
                          openAbove: false,
                          showCard() {
                              const rect = $el.getBoundingClientRect();
                              const spaceBelow = window.innerHeight - rect.bottom;
                              this.openAbove = spaceBelow < 200;
                              this.pos.left = rect.left + (rect.width / 2);
                              this.pos.top = this.openAbove ? rect.top : rect.bottom;
                              this.showPreview = true;
                          }
                      }"
                      @mouseenter="showCard()"
                      @mouseleave="showPreview = false">

                    <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-medium text-gray-400 cursor-default rounded-full bg-gray-500/10 border border-gray-500/20 hover:bg-gray-500/20 transition-colors">
                        +{{ $hiddenCount }}
                    </span>

                    {{-- Hover card — teleported to body to escape table overflow:hidden --}}
                    <template x-teleport="body">
                        <div x-show="showPreview"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 translate-y-1"
                             x-cloak
                             :style="{
                                 position: 'fixed',
                                 left: pos.left + 'px',
                                 top: openAbove ? 'auto' : pos.top + 8 + 'px',
                                 bottom: openAbove ? (window.innerHeight - pos.top + 8) + 'px' : 'auto',
                                 transform: 'translateX(-50%)',
                             }"
                             class="z-9999 w-max max-w-xs overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-xl ring-1 ring-black/5 dark:ring-white/5"
                             @mouseenter="showPreview = true"
                             @mouseleave="showPreview = false">

                            {{-- Header --}}
                            <div class="flex items-center gap-1.5 px-3 py-2 border-b border-gray-100 dark:border-gray-700">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5 text-gray-400">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />
                                </svg>
                                <span class="text-xs font-semibold text-gray-600 dark:text-gray-300">
                                    {{ trans_choice($component->getHoverLabel(), $hiddenCount) }}
                                </span>
                            </div>

                            {{-- Items grid --}}
                            <div class="px-3 py-2.5">
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach ($hiddenItems as $item)
                                        @php
                                            $label = $component->resolveItemLabel($item);
                                            $color = $component->resolveItemColor($item);
                                            $size = $component->resolveItemSize($item);
                                            $subtitle = $showSubtitle ? $component->resolveItemSubtitle($item) : null;
                                        @endphp
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 {{ $size }} font-medium border rounded-full {{ $color }}">
                                            {{ $label }}
                                            @if ($subtitle)
                                                <span class="opacity-60">{{ $subtitle }}</span>
                                            @endif
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </template>
                </span>
            @endif
        </div>
    @else
        <span class="inline-flex items-center px-3 py-1 text-xs italic text-gray-300 dark:text-gray-500/70 rounded-full bg-gray-300/40 dark:bg-gray-600/20">
            {{ __($component->getEmptyLabel()) }}
        </span>
    @endif
</div>
