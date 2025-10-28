<?php

namespace Codenzia\ProjectEssentials\View\Components;

use Filament\Infolists\Components\Entry;
use Illuminate\Contracts\Support\Htmlable;
use Closure;

class CarouselEntry extends Entry
{
    protected string $view = 'project-essentials::components.carousel-entry';

    protected bool $navigation = false;
    protected bool $pagination = false;
    protected string $paginationType = 'bullets';
    protected bool $paginationClickable = false;
    protected bool $paginationDynamicBullets = false;
    protected bool $paginationHideOnClick = false;
    protected int $paginationDynamicMainBullets = 1;
    protected bool $scrollbar = false;
    protected int $scrollbarDragSize = 50;
    protected bool $scrollbarDraggable = false;
    protected bool $scrollbarSnapOnRelease = false;
    protected bool $scrollbarHide = true;
    protected int $height = 300;
    protected bool $autoplay = false;
    protected int $autoplayDelay = 3000;
    protected string $effect = 'slide';
    protected int $cardsPerSlideOffset = 8;
    protected bool $centeredSlides = false;
    protected int $slidesPerView = 1;
    protected array $cardSchema = [];

    public function navigation(bool $condition = true): static
    {
        $this->navigation = $condition;

        return $this;
    }

    public function pagination(bool $condition = true): static
    {
        $this->pagination = $condition;

        return $this;
    }

    public function paginationType(string $type): static
    {
        $this->paginationType = $type;

        return $this;
    }

    public function paginationClickable(bool $condition = true): static
    {
        $this->paginationClickable = $condition;

        return $this;
    }

    public function paginationDynamicBullets(bool $condition = true): static
    {
        $this->paginationDynamicBullets = $condition;

        return $this;
    }

    public function paginationHideOnClick(bool $condition = true): static
    {
        $this->paginationHideOnClick = $condition;

        return $this;
    }

    public function paginationDynamicMainBullets(int $count): static
    {
        $this->paginationDynamicMainBullets = $count;

        return $this;
    }

    public function scrollbar(bool $condition = true): static
    {
        $this->scrollbar = $condition;

        return $this;
    }

    public function scrollbarDragSize(int $size): static
    {
        $this->scrollbarDragSize = $size;

        return $this;
    }

    public function scrollbarDraggable(bool $condition = true): static
    {
        $this->scrollbarDraggable = $condition;

        return $this;
    }

    public function scrollbarSnapOnRelease(bool $condition = true): static
    {
        $this->scrollbarSnapOnRelease = $condition;

        return $this;
    }

    public function scrollbarHide(bool $condition = true): static
    {
        $this->scrollbarHide = $condition;

        return $this;
    }

    public function height(int $height): static
    {
        $this->height = $height;

        return $this;
    }

    public function autoplay(bool $condition = true): static
    {
        $this->autoplay = $condition;

        return $this;
    }

    public function autoplayDelay(int $delay): static
    {
        $this->autoplayDelay = $delay;

        return $this;
    }

    public function effect(string $effect): static
    {
        $this->effect = $effect;

        return $this;
    }

    public function cardsPerSlideOffset(int $offset): static
    {
        $this->cardsPerSlideOffset = $offset;

        return $this;
    }

    public function centeredSlides(bool $condition = true): static
    {
        $this->centeredSlides = $condition;

        return $this;
    }

    public function slidesPerView(int $count): static
    {
        $this->slidesPerView = $count;

        return $this;
    }

    public function cardSchema(array $schema): static
    {
        $this->cardSchema = $schema;

        return $this;
    }
    
    public function getNavigation(): bool
    {
        return $this->navigation;
    }

    public function getPagination(): bool
    {
        return $this->pagination;
    }

    public function getPaginationType(): string
    {
        return $this->paginationType;
    }

    public function getPaginationClickable(): bool
    {
        return $this->paginationClickable;
    }

    public function getPaginationDynamicBullets(): bool
    {
        return $this->paginationDynamicBullets;
    }

    public function getPaginationHideOnClick(): bool
    {
        return $this->paginationHideOnClick;
    }

    public function getPaginationDynamicMainBullets(): int
    {
        return $this->paginationDynamicMainBullets;
    }

    public function getScrollbar(): bool
    {
        return $this->scrollbar;
    }

    public function getScrollbarDragSize(): int
    {
        return $this->scrollbarDragSize;
    }

    public function getScrollbarDraggable(): bool
    {
        return $this->scrollbarDraggable;
    }

    public function getScrollbarSnapOnRelease(): bool
    {
        return $this->scrollbarSnapOnRelease;
    }

    public function getScrollbarHide(): bool
    {
        return $this->scrollbarHide;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function getAutoplay(): bool
    {
        return $this->autoplay;
    }

    public function getAutoplayDelay(): int
    {
        return $this->autoplayDelay;
    }

    public function getEffect(): string
    {
        return $this->effect;
    }

    public function getCardsPerSlideOffset(): int
    {
        return $this->cardsPerSlideOffset;
    }

    public function getCenteredSlides(): bool
    {
        return $this->centeredSlides;
    }

    public function getSlidesPerView(): int
    {
        return $this->slidesPerView;
    }

    public function getCardSchema(): array
    {
        return $this->cardSchema;
    }
}
