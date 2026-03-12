<?php

namespace Codenzia\ProjectEssentials\Traits;

use Filament\Actions\Action;

//***************************************** THIS FIXES A BUG IN FILAMENT/ACTIONS Version v3.3.32 *****************************************
// IMPORTANT: Any Livewire component using this trait MUST ALSO USE
// Filament\Actions\Concerns\InteractsWithActions (for unmountAction and $mountedActions)
// AND Filament\Infolists\Concerns\InteractsWithInfolists (for unmountInfolistAction and $mountedInfolistActions).
// Filament's Page classes (like ViewRecord, CreateRecord) typically already include these.
//****************************************************************************************************************************************
trait CanFixFilamentActionCancel
{
    /**
     * Configures actions for the component, overriding default Filament behavior.
     * Specifically, addresses an issue where `modalCancelAction` callbacks on
     * `StaticAction` instances were executing on modal render instead of on button click.
     *
     * The `modalCancelAction` expects a Livewire method name (string) for its `action()`
     * when rendering `wire:click` attributes for modal footer buttons (e.g., 'Close').
     * Providing a PHP `Closure` directly to `StaticAction->action()` causes the Closure
     * to execute during the component's rendering phase, rather than on button click.
     *
     * This fix ensures the 'Close' button correctly triggers a Livewire method (`fixActionCancel`)
     * when clicked, allowing for proper server-side state cleanup.
     *
     * @param Action $action The Filament Action being configured.
     * @return void
     */
    protected function configureAction(Action $action): void
    {
        $action->modalCancelAction(
            fn(Action $action) => $action
                ->action('fixActionCancel')
        );
    }

    /**
     * Livewire method called when a modal's cancel/close button is clicked.
     * This method intelligently determines which type of action is mounted
     * and calls the appropriate unmount method.
     *
     * This method must be public so Livewire can call it via `wire:click`.
     *
     * @return void
     */
    public function fixActionCancel()
    {
        // Prioritize unmounting an Infolist Action if one is active.
        // This is crucial because Infolist Actions have their own separate stack.
        if (isset($this->mountedInfolistActions) && count($this->mountedInfolistActions)) {
            // Unmount the action from the Infolist's specific stack.
            // Parameters: (shouldCancelParentActions, shouldCloseModal)
            $this->unmountInfolistAction(true, false);
        }
        // If no Infolist Action is mounted, check for and unmount a general Action.
        // This covers actions from Filament Tables, Forms, or general page actions.
        elseif (isset($this->mountedActions) && count($this->mountedActions)) {
            // Unmount the action from the general action stack.
            // Parameters: (shouldCancelParentActions, shouldCloseModal)
            $this->unmountAction(true, false);
        }
    }


    //Support filament tables as well
    protected function configureTableAction(Action $action): void
    {
        $action->modalCancelAction(
            fn(Action $action) => $action
                ->action('fixTableActionCancel')
        );
    }

    public function fixTableActionCancel()
    {
        if (isset($this->mountedTableActions) && count($this->mountedTableActions)) {
            // Parameters: (shouldCancelParentActions, shouldCloseModal)
            $this->unmountTableAction(true, false);
        }
    }
}
