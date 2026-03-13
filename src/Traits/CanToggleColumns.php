<?php

namespace Codenzia\ProjectEssentials\Traits;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Support\Enums\IconSize;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\Layout\Grid;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\View\TablesRenderHook;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;

trait CanToggleColumns
{
    /** @var array<Column|Split|Stack|Grid> */
    protected array $columns = [];

    /** @var array<Column> */
    protected array $toggleableColumns = [];

    /** @var array<string,bool> */
    protected array $columnToggleState = [];

    protected ?string $uniqueViewName = null;

    /** Cache filtered columns for this request */
    protected array $filteredCache = [];

    protected bool $toggleDropdownRegistered = false;

    #[On('updateColumns')]
    public function updateColumns(): void
    {
        $this->dispatch('$refresh');
    }

    public function bootedCanToggleColumns()
    {
        $this->registerToggleableDropdown();
    }

    public function registerToggleableDropdown()
    {
        if ($this->toggleDropdownRegistered) {
            return;
        }

        FilamentView::registerRenderHook(
            TablesRenderHook::TOOLBAR_START,
            fn (): View => view('project-essentials::components.can-toggle-table-columns'),
            scopes: static::class, // important, ties the Blade into the table Livewire context
        );

        $this->toggleDropdownRegistered = true;
    }

    /**
     * Entry point: sets up columns, toggleables, and applies toggle state.
     */
    public function makeColumnsForToggle(array $columns, ?string $uniqueViewName = null): array
    {
        $this->uniqueViewName = $uniqueViewName;
        $this->columns = $columns;
        $this->toggleableColumns = $this->buildToggleableColumns($columns);

        // Override with any session values
        $this->columnToggleState = array_merge(
            $this->columnToggleState,
            $this->getColumnToggleStateFromSession()
        );

        return $this->getTableColumnsFromToggle();
    }

    /**
     * Returns the full or filtered column list (cached per request).
     */
    protected function getTableColumnsFromToggle(): array
    {
        $cacheKey = $this->uniqueViewName ?? 'all';

        if (isset($this->filteredCache[$cacheKey])) {
            return $this->filteredCache[$cacheKey];
        }

        $this->filteredCache[$cacheKey] = $this->filterColumnsRecursive($this->columns);

        return $this->filteredCache[$cacheKey];
    }

    /**
     * Recursively apply toggle state: keep all columns but hide when off.
     */
    protected function filterColumnsRecursive(array $columns): array
    {
        $result = [];

        foreach ($columns as $column) {
            if ($column instanceof Split || $column instanceof Stack || $column instanceof Grid) {
                $filteredChildren = $this->filterColumnsRecursive($column->getComponents() ?? []);
                if (! empty($filteredChildren)) {
                    $cloned = clone $column;
                    $cloned->components($filteredChildren);
                    $result[] = $cloned;
                }

                continue;
            }

            if ($column instanceof Column) {
                $key = $this->getColumnKey($column);
                $cloned = clone $column;

                if (($this->columnToggleState[$key] ?? true) === false) {
                    $cloned->hidden();
                }

                $result[] = $cloned;
            }
        }

        return $result;
    }

    /**
     * Build a flat array of toggleable columns and disable default Filament toggle.
     */
    protected function buildToggleableColumns(array $columns): array
    {
        $toggleable = [];

        foreach ($columns as $col) {
            if ($col instanceof Column && method_exists($col, 'isToggleable') && $col->isToggleable()) {

                // Respect isToggledHiddenByDefault as the initial state
                $this->columnToggleState[$this->getColumnKey($col)] = ! ($col->isToggledHiddenByDefault() ?? false);

                // Remove ->toggleable from column to prevent default filament UI/handling
                $col->toggleable(false);

                $toggleable[] = $col;
            }

            if (method_exists($col, 'getComponents')) {
                $toggleable = array_merge($toggleable, $this->buildToggleableColumns($col->getComponents() ?? []));
            }
        }

        return $toggleable;
    }

    /**
     * Get toggle state from session (once per request).
     */
    protected function getColumnToggleStateFromSession(): array
    {
        return session()->get($this->getTableToggleStateFromSessionKey(), []);
    }

    /**
     * Toggle buttons data for UI layer.
     */
    protected function getToggleableColumnPresentation(): array
    {
        return collect($this->toggleableColumns)->map(function ($col) {
            $key = $this->getColumnKey($col);
            $isVisible = ($this->columnToggleState[$key] ?? true);

            return [
                'key' => $key,
                'label' => $col->getLabel() ?: $col->getName(),
                'visible' => $isVisible,

                // centralize all UI constants here
                'icon' => $isVisible ? 'heroicon-s-check-circle' : 'heroicon-s-eye-slash',
                'color' => $isVisible ? 'primary' : 'danger',
                'iconSize' => 'large',
            ];
        })->values()->all();
    }

    public function makeToggleableAction(string $key, string $label, bool $isVisible): Action
    {
        return Action::make($key)
            ->label($label)
            ->icon($isVisible ? 'heroicon-s-check-circle' : 'heroicon-s-eye-slash')
            ->iconSize(IconSize::Medium)
            ->color($isVisible ? 'primary' : 'danger')
            ->action(fn ($record) => $this->toggleColumnVisibility($key));
    }

    /**
     * Build ActionGroup dropdown to toggle columns on/off.
     */
    public function makeToggleableActionDropdown($margin = 0): ActionGroup
    {
        // Add the Show All / Hide All action first
        $actions = [
            Action::make('toggle_all')
                ->label($this->getToggleAllLabel())
                ->icon($this->getToggleAllIcon())
                ->iconSize(IconSize::Medium)
                ->color('primary')
                ->action(fn () => $this->toggleAllColumns()),
        ];

        // Add individual column toggle actions
        $columnActions = collect($this->toggleableColumns)
            ->map(function ($col) {
                $key = $this->getColumnKey($col);
                $isVisible = ($this->columnToggleState[$key] ?? true);

                return $this->makeToggleableAction(
                    $key,
                    $col->getLabel() ?? $col->getName(),
                    $isVisible
                );
            })
            ->all();

        // Merge all actions
        $actions = array_merge($actions, $columnActions);

        return ActionGroup::make($actions)
            ->tooltip(__('Toggle columns on/off'))
            ->label('')
            ->icon('heroicon-o-adjustments-horizontal')
            ->color('white')
            ->iconSize(IconSize::Medium)
            ->iconButton()
            ->extraAttributes([
                'class' => 'table-columns-toggle',
                'style' => $margin > 0 ? "margin-right: {$margin}px !important;" : '',
            ]);
    }

    /**
     * Generate a unique key for each column.
     */
    protected function getColumnKey(Column $column): string
    {
        return $column->getName() ?? spl_object_hash($column);
    }

    /**
     * Generate a unique session key for this table/view.
     */
    public function getTableToggleStateFromSessionKey(): string
    {
        $keyParts = [static::class];

        if (! empty($this->uniqueViewName)) {
            $keyParts[] = $this->uniqueViewName;
        }

        return 'filament.tables.toggles.' . md5(implode('|', $keyParts));
    }

    /**
     * Check if a column is currently visible
     */
    public function isColumnVisible(string $columnKey): bool
    {
        return $this->columnToggleState[$columnKey] ?? true;
    }

    /**
     * Reset all column toggles
     */
    public function resetColumnToggles(): void
    {
        $sessionKey = $this->getTableToggleStateFromSessionKey();
        session()->forget($sessionKey);
        $this->cachedFilteredColumns = null;
        $this->dispatch('updateColumns');
    }

    public function toggleColumnVisibility(string $key): void
    {
        $current = $this->columnToggleState[$key] ?? true;
        $new = ! $current;

        $this->columnToggleState[$key] = $new;

        $state = $this->getColumnToggleStateFromSession();
        $state[$key] = $new;

        session()->put($this->getTableToggleStateFromSessionKey(), $state);
        $this->dispatch('updateColumns');
    }

    /** --------------------------
     * New Show All / Hide All Methods
     * -------------------------- */

    /**
     * Determine if all toggleable columns are currently visible
     */
    protected function areAllColumnsVisible(): bool
    {
        return collect($this->toggleableColumns)
            ->every(fn ($col) => ($this->columnToggleState[$this->getColumnKey($col)] ?? true));
    }

    /**
     * Returns label for the toggle-all action
     */
    public function getToggleAllLabel(): string
    {
        return $this->areAllColumnsVisible() ? 'Hide All' : 'Show All';
    }

    /**
     * Returns icon for the toggle-all action
     */
    public function getToggleAllIcon(): string
    {
        return $this->areAllColumnsVisible() ? 'heroicon-s-eye-slash' : 'heroicon-s-eye';
    }

    /**
     * Toggle all columns on/off
     */
    public function toggleAllColumns(): void
    {
        $show = ! $this->areAllColumnsVisible();
        $state = [];
        foreach ($this->toggleableColumns as $col) {
            $state[$this->getColumnKey($col)] = $show;
        }
        session()->put($this->getTableToggleStateFromSessionKey(), $state);
        $this->dispatch('updateColumns');
    }
}
