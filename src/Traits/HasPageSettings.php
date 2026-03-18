<?php

declare(strict_types=1);

namespace Codenzia\ProjectEssentials\Traits;

use Codenzia\ProjectEssentials\Models\PageSetting;
use Codenzia\ProjectEssentials\Models\PageSettingDefinition;
use Codenzia\ProjectEssentials\Models\PageSettingPreset;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions as SchemaActions;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

/**
 * Trait HasPageSettings
 *
 * Adds a database-backed, per-user settings modal to any Filament page or widget.
 *
 * Features:
 * - Database persistence (per user + page + optional scope)
 * - In-memory memoization (single DB read per request)
 * - Drag-and-drop reordering via sortable form fields
 * - Section grouping for organized settings
 * - Admin presets (named configurations that users can apply)
 * - Help text per setting
 * - Live preview (reactive Livewire updates)
 * - Apply to role/team (admin bulk-apply)
 * - Fluent setting definitions via PageSettingDefinition
 * - Default values registry
 * - Scoped settings (e.g., per project, per context)
 * - Reset to defaults
 */
trait HasPageSettings
{
    /**
     * In-memory cache of settings for the current request.
     */
    private ?array $resolvedPageSettings = null;

    /**
     * In-memory cache of order for the current request.
     */
    private ?array $resolvedPageOrder = null;

    /**
     * Cached definitions from the last getPageSettingsAction() call.
     */
    private ?array $cachedDefinitions = null;

    /**
     * Optional scope key for scoped settings (e.g., project ID).
     * Override this method in your page to enable scoped settings.
     */
    protected function getPageSettingsScope(): ?string
    {
        return null;
    }

    /**
     * Whether to enable live preview (reactive updates without submit).
     * Override to return true for instant feedback.
     */
    protected function pageSettingsLivePreview(): bool
    {
        return false;
    }

    /**
     * Get the page settings action for the header.
     *
     * @param  array|null  $schema  Optional form schema (raw Filament components or PageSettingDefinition[]).
     */
    protected function getPageSettingsAction(?array $schema = null): Action
    {
        $definitions = $this->resolveDefinitions($schema);
        $this->cachedDefinitions = $definitions;
        $formSchema = $this->buildFormSchema($definitions);

        return Action::make('pageSettings')
            ->label('')
            ->icon('heroicon-o-cog-6-tooth')
            ->color('gray')
            ->modalHeading(__('Page Settings'))
            ->slideOver()
            ->modalWidth('lg')
            ->form($formSchema)
            ->fillForm(fn (): array => $this->getPageSettingsData())
            ->action(function (array $data) {
                $this->savePageSettings($data);

                Notification::make()
                    ->success()
                    ->title(__('Settings updated successfully.'))
                    ->send();
            })
            ->modalFooterActions(fn (Action $action): array => [
                $action->getModalSubmitAction()
                    ->label(__('Save')),
                $this->getResetToDefaultsFooterAction(),
                $action->getModalCancelAction(),
            ]);
    }

    /**
     * An alias for getPageSettingsAction to easily pass config inputs.
     */
    public function settings(array $inputs): Action
    {
        return $this->getPageSettingsAction($inputs);
    }

    /**
     * Define the default form schema for the settings modal.
     * Override this to provide fluent PageSettingDefinition instances.
     *
     * @return array<PageSettingDefinition|\Filament\Forms\Components\Component>
     */
    protected function getPageSettingsFormSchema(): array
    {
        return [];
    }

    /**
     * Define default values for all settings.
     * Override this to provide sensible defaults before the user ever opens the modal.
     */
    protected function getPageSettingsDefaults(): array
    {
        $definitions = $this->cachedDefinitions ?? $this->resolveDefinitions();
        $defaults = [];

        foreach ($definitions as $definition) {
            if ($definition instanceof PageSettingDefinition) {
                Arr::set($defaults, $definition->getKey(), $definition->getDefault());
            }
        }

        return $defaults;
    }

    /**
     * Get the current settings data (merged with defaults).
     */
    protected function getPageSettingsData(): array
    {
        if ($this->resolvedPageSettings !== null) {
            return $this->resolvedPageSettings;
        }

        $defaults = $this->getPageSettingsDefaults();

        $record = PageSetting::getForUser(
            (int) Auth::id(),
            $this->normalizePageKey(),
            $this->getPageSettingsScope()
        );

        $stored = $record?->settings ?? [];

        $this->resolvedPageSettings = array_merge($defaults, $stored);
        $this->resolvedPageOrder = $record?->order;

        return $this->resolvedPageSettings;
    }

    /**
     * Save settings to the database.
     */
    protected function savePageSettings(array $data): void
    {
        $order = $this->extractOrderFromData($data);

        PageSetting::persist(
            (int) Auth::id(),
            $this->normalizePageKey(),
            $data,
            $order,
            $this->getPageSettingsScope()
        );

        // Bust in-memory cache
        $this->resolvedPageSettings = null;
        $this->resolvedPageOrder = null;

        $this->onPageSettingsUpdated($data);
    }

    /**
     * Hook called after settings are updated.
     */
    protected function onPageSettingsUpdated(array $data): void
    {
        // Override this method to perform actions after saving settings
    }

    /**
     * Get a specific setting value.
     */
    protected function getPageSetting(string $key, mixed $default = null): mixed
    {
        $settings = $this->getPageSettingsData();

        return Arr::get($settings, $key, $default);
    }

    /**
     * Get the saved order for settings (for sortable items).
     */
    protected function getPageSettingsOrder(): ?array
    {
        // Ensure data is loaded
        $this->getPageSettingsData();

        return $this->resolvedPageOrder;
    }

    /**
     * Reset the current user's settings to defaults.
     */
    public function resetPageSettingsToDefaults(): void
    {
        PageSetting::query()
            ->where('user_id', Auth::id())
            ->where('page', $this->normalizePageKey())
            ->where('scope', $this->getPageSettingsScope())
            ->delete();

        $this->resolvedPageSettings = null;
        $this->resolvedPageOrder = null;

        $this->onPageSettingsUpdated($this->getPageSettingsDefaults());

        Notification::make()
            ->success()
            ->title(__('Settings reset to defaults.'))
            ->send();
    }

    // ─── Presets ────────────────────────────────────────────

    /**
     * Load a preset's settings for the current user.
     */
    public function applyPageSettingsPreset(int $presetId): void
    {
        $preset = PageSettingPreset::find($presetId);
        if (! $preset) {
            return;
        }

        $this->savePageSettings($preset->settings ?? []);

        Notification::make()
            ->success()
            ->title(__('Preset ":name" applied.', ['name' => $preset->name]))
            ->send();
    }

    /**
     * Save the current settings as a new preset (admin feature).
     */
    public function saveAsPageSettingsPreset(string $name, bool $isDefault = false): void
    {
        if ($isDefault) {
            // Remove existing default for this page
            PageSettingPreset::query()
                ->where('page', $this->normalizePageKey())
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        PageSettingPreset::create([
            'name' => $name,
            'page' => $this->normalizePageKey(),
            'settings' => $this->getPageSettingsData(),
            'order' => $this->getPageSettingsOrder(),
            'is_default' => $isDefault,
            'created_by_user_id' => Auth::id(),
        ]);

        Notification::make()
            ->success()
            ->title(__('Preset ":name" saved.', ['name' => $name]))
            ->send();
    }

    /**
     * Delete a preset (admin feature).
     */
    public function deletePageSettingsPreset(int $presetId): void
    {
        PageSettingPreset::query()
            ->where('id', $presetId)
            ->where('page', $this->normalizePageKey())
            ->delete();

        Notification::make()
            ->success()
            ->title(__('Preset deleted.'))
            ->send();
    }

    /**
     * Apply settings to all users with a specific role (admin bulk-apply).
     */
    public function applyPageSettingsToRole(string $role): void
    {
        $userModel = config('project-essentials.user_model', 'App\\Models\\User');
        $users = $userModel::role($role)->pluck('id');
        $settings = $this->getPageSettingsData();
        $order = $this->getPageSettingsOrder();
        $page = $this->normalizePageKey();
        $scope = $this->getPageSettingsScope();

        foreach ($users as $userId) {
            PageSetting::persist((int) $userId, $page, $settings, $order, $scope);
        }

        Notification::make()
            ->success()
            ->title(__('Settings applied to :count users with role ":role".', [
                'count' => $users->count(),
                'role' => $role,
            ]))
            ->send();
    }

    /**
     * Apply settings to all users in a department/team (admin bulk-apply).
     */
    public function applyPageSettingsToTeam(int $teamId, string $teamRelation = 'department_id'): void
    {
        $userModel = config('project-essentials.user_model', 'App\\Models\\User');
        $users = $userModel::where($teamRelation, $teamId)->pluck('id');
        $settings = $this->getPageSettingsData();
        $order = $this->getPageSettingsOrder();
        $page = $this->normalizePageKey();
        $scope = $this->getPageSettingsScope();

        foreach ($users as $userId) {
            PageSetting::persist((int) $userId, $page, $settings, $order, $scope);
        }

        Notification::make()
            ->success()
            ->title(__('Settings applied to :count team members.', [
                'count' => $users->count(),
            ]))
            ->send();
    }

    // ─── Internal Helpers ───────────────────────────────────

    /**
     * Normalize page class name to a slug-safe key.
     */
    protected function normalizePageKey(): string
    {
        return str_replace('\\', '.', static::class);
    }

    /**
     * Resolve definitions — accept raw Filament components, PageSettingDefinition[], or mixed.
     *
     * @return array<PageSettingDefinition|\Filament\Forms\Components\Component>
     */
    private function resolveDefinitions(?array $schema = null): array
    {
        return $schema ?? $this->getPageSettingsFormSchema();
    }

    /**
     * Build the Filament form schema from definitions.
     * Groups definitions by their group and adds presets selector.
     */
    private function buildFormSchema(array $definitions): array
    {
        $presets = PageSettingPreset::getForPage($this->normalizePageKey());
        $schema = [];

        // Preset selector (if any presets exist)
        if ($presets->isNotEmpty()) {
            $schema[] = Select::make('_preset')
                ->label(__('Load Preset'))
                ->placeholder(__('Choose a preset...'))
                ->options($presets->pluck('name', 'id')->toArray())
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set) use ($presets) {
                    if (! $state) {
                        return;
                    }
                    $preset = $presets->firstWhere('id', (int) $state);
                    if ($preset) {
                        foreach ($preset->settings as $key => $value) {
                            $set($key, $value);
                        }
                    }
                })
                ->dehydrated(false)
                ->helperText(__('Apply a saved configuration preset'));
        }

        // Group definitions by group
        $grouped = collect($definitions)->groupBy(function ($def) {
            if ($def instanceof PageSettingDefinition) {
                return $def->getGroup();
            }

            return '';
        });

        foreach ($grouped as $group => $items) {
            $components = $items->map(function ($def) {
                if ($def instanceof PageSettingDefinition) {
                    $component = $def->toFormComponent();

                    if ($this->pageSettingsLivePreview()) {
                        $component->reactive()
                            ->afterStateUpdated(function ($state, string $statePath) {
                                $this->onPageSettingPreviewUpdated($statePath, $state);
                            });
                    }

                    return $component;
                }

                // Raw Filament component — pass through, but add live preview if enabled
                if ($this->pageSettingsLivePreview() && method_exists($def, 'reactive')) {
                    $def->reactive()
                        ->afterStateUpdated(function ($state, string $statePath) {
                            $this->onPageSettingPreviewUpdated($statePath, $state);
                        });
                }

                return $def;
            })->toArray();

            if ($group !== '' && $group !== null) {
                // Collect keys for toggleable items in this group
                $groupKeys = $items
                    ->filter(fn ($def) => $def instanceof PageSettingDefinition)
                    ->map(fn (PageSettingDefinition $def) => $def->getKey())
                    ->values()
                    ->toArray();

                $toggleActions = [];
                if (count($groupKeys) > 1) {
                    $toggleActions = [
                        SchemaActions::make([
                            Action::make("selectAll_{$group}")
                                ->label(__('Select All'))
                                ->link()
                                ->size('sm')
                                ->action(function (callable $set) use ($groupKeys) {
                                    foreach ($groupKeys as $key) {
                                        $set($key, true);
                                    }
                                }),
                            Action::make("deselectAll_{$group}")
                                ->label(__('Deselect All'))
                                ->link()
                                ->color('gray')
                                ->size('sm')
                                ->action(function (callable $set) use ($groupKeys) {
                                    foreach ($groupKeys as $key) {
                                        $set($key, false);
                                    }
                                }),
                        ]),
                    ];
                }

                $schema[] = Section::make(__($group))
                    ->schema([...$toggleActions, ...$components])
                    ->collapsible()
                    ->compact();
            } else {
                $schema = array_merge($schema, $components);
            }
        }

        return $schema;
    }

    /**
     * Called during live preview when a setting value changes.
     * Override this to reactively update your page UI.
     */
    protected function onPageSettingPreviewUpdated(string $key, mixed $value): void
    {
        // Override in your page to handle live preview updates
    }

    /**
     * Extract any order-related data from the form submission.
     */
    private function extractOrderFromData(array &$data): ?array
    {
        if (isset($data['_order'])) {
            $order = $data['_order'];
            unset($data['_order']);

            return is_array($order) ? $order : null;
        }

        return $this->resolvedPageOrder;
    }

    /**
     * Build a "Reset to Defaults" footer action for the modal.
     */
    private function getResetToDefaultsFooterAction(): \Filament\Actions\Action
    {
        return Action::make('resetToDefaults')
            ->label(__('Reset to Defaults'))
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading(__('Reset Settings'))
            ->modalDescription(__('Are you sure you want to reset all settings to their defaults? This cannot be undone.'))
            ->action(function () {
                $this->resetPageSettingsToDefaults();
                $this->unmountAction();
            });
    }
}
