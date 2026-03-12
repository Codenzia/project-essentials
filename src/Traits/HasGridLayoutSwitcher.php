<?php

namespace Codenzia\ProjectEssentials\Traits;

use Filament\Actions\Action;
use Illuminate\Support\Facades\Session;

/**
 * Trait HasGridLayoutSwitcher
 *
 * Provides properties and methods for managing grid layout size, session persistence,
 * and dispatching Livewire events from a host component.
 *
 */
trait HasGridLayoutSwitcher
{
    /**
     * Public property to hold the grid size.
     */
    public int $gridSize = 2;

    public function bootHasGridLayoutSwitcher(): void
    {
        $this->initializeGridLayoutSwitcher();
    }

    protected function getGridLayoutSessionKey(): string
    {
        // Use the FQCN of the using class as a unique prefix.
        $prefix = str_replace(['\\', '.'], '_', get_class($this));
        return $prefix . '_grid_size';
    }

    /**
     * Internal method to initialize the grid size from the session.
     */
    protected function initializeGridLayoutSwitcher(): void
    {
        $sessionKey = $this->getGridLayoutSessionKey();

        // Access session data and set the property on the host component.
        $this->gridSize = Session::get($sessionKey, $this->gridSize);
    }

    public function getDynamicGridSize(): int
    {
        $sessionKey = $this->getGridLayoutSessionKey();
        return session($sessionKey, $this->gridSize);
    }

    /**
     * The public method called by the Blade file/Action via $wire.call('updateGridLayout', value).
     */
    public function updateGridLayout(int $state): void
    {
        $sessionKey = $this->getGridLayoutSessionKey();

        // 1. Save to session
        Session::put($sessionKey, $state);

        // 2. Update host component property
        $this->gridSize = $state;

        // 3. (OPTIONAL): Dispatch the event (incase we have interested listeners)
        $this->dispatch('grid-size-updated', $this->gridSize);

        $this->resetTable();
    }

    /**
     * Helper to easily add this action to Filament.
     */
    public  function makeGridLayoutSwitcherAction(): Action
    {
        return Action::make('grid_slider')
            ->label(__('Grid'))
            ->icon('heroicon-o-view-columns')
            ->view('project-essentials::components.grid-layout-switcher', ['gridSize' => $this->getDynamicGridSize()])
            ->extraAttributes(['class' => 'fi-ta-header-action-item grid-icon-button text-white mx-1']);
    }
}
