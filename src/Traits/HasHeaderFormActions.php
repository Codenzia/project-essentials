<?php

namespace Codenzia\ProjectEssentials\Traits;

use Filament\Actions\Action;

/**
 * Trait HasHeaderFormActions
 *
 * This trait allows you to automatically reuse the same
 * form actions (Save, Cancel, Save & Continue Editing, etc.)
 * that normally appear in the footer of a Filament form
 * and display them in the page header as well.
 *
 * Why use this?
 *  - Keeps labels, translations, icons, and behavior consistent
 *    between the header and footer actions.
 *  - No need to manually redefine Save/Cancel in each page.
 *  - Future-proof: if Filament adds or changes default form
 *    actions, they will automatically appear in the header too.
 *
 * Usage:
 *  - Add `use HasHeaderFormActions;` to your EditRecord or
 *    CreateRecord page class.
 *  - The header will then show the same actions as the footer.
 *
 * Notes:
 *  - By default, ALL form actions are mirrored in the header.
 *    If you only want specific ones (e.g., Save + Cancel),
 *    filter them inside the `getHeaderActions()` method.
 *  - Ensure the form element has id="form" (default in Filament).
 */
trait HasHeaderFormActions
{
    /**
     * Override the default header actions to mirror the
     * form actions (Save, Cancel, etc.) from the footer.
     *
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return collect($this->getFormActions())
            ->map(fn($action) => $action->formId('form'))
            ->all();
    }
}
