<x-filament::section class="my-4">
    @if ($getLabel())
    <x-slot name="heading">
        <div class="flex items-center gap-2">
            @if ($getIcon())
            <span class="text-gray-500 dark:text-white">
                <x-filament::icon
                    :icon="$getIcon()"
                    class="h-5 w-5 text-gray-500 dark:text-white"
                />
            </span>
            @endif
            <span>{{ $getLabel() }}</span>
        </div>
    </x-slot>
    @endif
    {{-- Content goes here --}}
<div
    ax-load-src="{{ \Filament\Support\Facades\FilamentAsset::getScriptSrc('project-essentials-scripts', 'codenzia/project-essentials') }}"
    x-data="{
        init() {
            new Swiper($refs.container, {
                loop: true,
                navigation: {
                    nextEl: $refs.nextEl,
                    prevEl: $refs.prevEl,
                },
                pagination: {
                    el: $refs.paginationEl,
                    type: '{{ $getPaginationType() }}',
                    clickable: {{ $getPaginationClickable() ? 'true' : 'false' }},
                    dynamicBullets: {{ $getPaginationDynamicBullets() ? 'true' : 'false' }},
                    hideOnClick: {{ $getPaginationHideOnClick() ? 'true' : 'false' }},
                    dynamicMainBullets: {{ $getPaginationDynamicMainBullets() }},
                },
                scrollbar: {
                    el: $refs.scrollbarEl,
                    dragSize: {{ $getScrollbarDragSize() }},
                    draggable: {{ $getScrollbarDraggable() ? 'true' : 'false' }},
                    snapOnRelease: {{ $getScrollbarSnapOnRelease() ? 'true' : 'false' }},
                    hide: {{ $getScrollbarHide() ? 'true' : 'false' }},
                },
                height: {{ $getHeight() }},
                autoplay: {
                    enabled: {{ $getAutoplay() ? 'true' : 'false' }},
                    delay: {{ $getAutoplayDelay() }},
                },
                effect: '{{ $getEffect() }}',
                cardsEffect: {
                    perSlideOffset: {{ $getCardsPerSlideOffset() }},
                },
                centeredSlides: {{ $getCenteredSlides() ? 'true' : 'false' }},
                slidesPerView: {{ $getSlidesPerView() }},
                spaceBetween: 20,
            });
        }
    }"
>

    <div x-ref="container" class="swiper-container mt-4">
        <div class="swiper-wrapper">
            @foreach ($getState() as $item)
                <div class="swiper-slide">
                    <div class="carousel-card dark:bg-gray-800 bg-white p-4 rounded-lg shadow">
                        @foreach ($getEvaluatedCardSchema($item) as $widget)
                            {!! $widget->toHtml() !!}
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
        
        @if ($getPagination())
            <div x-ref="paginationEl" class="swiper-pagination"></div>
        @endif

        @if ($getNavigation())
            <div x-ref="prevEl" class="swiper-button-prev"></div>
            <div x-ref="nextEl" class="swiper-button-next"></div>
        @endif

        @if ($getScrollbar())
            <div x-ref="scrollbarEl" class="swiper-scrollbar"></div>
        @endif
    </div>
</div>
</x-filament::section>
