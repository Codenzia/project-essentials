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
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
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
     * How many users a bulk apply writes per query.
     */
    private const PAGE_SETTINGS_BULK_CHUNK = 500;

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
     * Scope key that presets belong to. Defaults to the page settings scope, so a
     * scoped page only ever sees, saves and deletes presets of its own scope.
     * Override to return null for page-wide presets shared by every scope.
     */
    protected function getPageSettingsPresetScope(): ?string
    {
        return $this->getPageSettingsScope();
    }

    /**
     * The team the current user may bulk-apply settings to. Resolved on the server —
     * never accepted from the request. Bulk apply to a team is refused while this
     * returns null.
     */
    protected function getPageSettingsTeamId(): ?int
    {
        return null;
    }

    /**
     * Authorize admin-level page-settings operations (presets, bulk apply).
     * Override in your page to grant access; denied by default.
     *
     * Example: return auth()->user()->can('manage page settings');
     */
    protected function canManagePageSettingsPresets(): bool
    {
        return false;
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
            ->schema($formSchema)
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
     * @return array<PageSettingDefinition|Component>
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

        if ($record !== null) {
            $stored = $record->settings ?? [];
            $order = $record->order;
        } else {
            // No personal row yet — seed from the page's default preset, if one is marked.
            $preset = PageSettingPreset::getDefaultForPage(
                $this->normalizePageKey(),
                $this->getPageSettingsPresetScope()
            );

            $stored = $preset?->settings ?? [];
            $order = $preset?->order;
        }

        $this->resolvedPageSettings = $this->mergePageSettings($defaults, $stored);
        $this->resolvedPageOrder = $order;

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
            ->where('scope', $this->getPageSettingsScope() ?? '')
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
        $preset = PageSettingPreset::query()
            ->whereKey($presetId)
            ->where('page', $this->normalizePageKey())
            ->where('scope', $this->getPageSettingsPresetScope() ?? '')
            ->first();
        if (! $preset) {
            return;
        }

        $settings = $preset->settings ?? [];

        // Carry the preset's own item order across with its values.
        if ($preset->order !== null) {
            $settings['_order'] = $preset->order;
        }

        $this->savePageSettings($settings);

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
        abort_unless($this->canManagePageSettingsPresets(), 403);

        $presetScope = $this->getPageSettingsPresetScope() ?? '';

        if ($isDefault) {
            // Remove existing default for this page + scope
            PageSettingPreset::query()
                ->where('page', $this->normalizePageKey())
                ->where('scope', $presetScope)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        PageSettingPreset::create([
            'name' => $name,
            'page' => $this->normalizePageKey(),
            'scope' => $presetScope,
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
        abort_unless($this->canManagePageSettingsPresets(), 403);

        PageSettingPreset::query()
            ->where('id', $presetId)
            ->where('page', $this->normalizePageKey())
            ->where('scope', $this->getPageSettingsPresetScope() ?? '')
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
        abort_unless($this->canManagePageSettingsPresets(), 403);

        $userModel = config('project-essentials.user_model', 'App\\Models\\User');

        if (! method_exists($userModel, 'scopeRole')) {
            throw new \BadMethodCallException(
                "applyPageSettingsToRole() requires the spatie/laravel-permission role scope on [{$userModel}]."
            );
        }

        $count = $this->upsertPageSettingsForQuery($userModel::role($role));

        Notification::make()
            ->success()
            ->title(__('Settings applied to :count users with role ":role".', [
                'count' => $count,
                'role' => $role,
            ]))
            ->send();
    }

    /**
     * Apply settings to every user in the admin's own department/team (admin bulk-apply).
     *
     * The team is resolved server-side through getPageSettingsTeamId() and the column
     * through config — neither is accepted from the request.
     */
    public function applyPageSettingsToTeam(): void
    {
        abort_unless($this->canManagePageSettingsPresets(), 403);

        $teamId = $this->getPageSettingsTeamId();

        abort_if($teamId === null, 403);

        $teamColumn = config('project-essentials.page_settings.team_column', 'department_id');
        $userModel = config('project-essentials.user_model', 'App\\Models\\User');

        $count = $this->upsertPageSettingsForQuery($userModel::query()->where($teamColumn, $teamId));

        Notification::make()
            ->success()
            ->title(__('Settings applied to :count team members.', [
                'count' => $count,
            ]))
            ->send();
    }

    /**
     * Bulk-upsert the current settings/order for every user the query matches, in
     * chunks so a large role/team cannot exhaust memory or the SQL binding limit.
     *
     * @return int Number of users written to.
     */
    private function upsertPageSettingsForQuery(Builder $query): int
    {
        $settings = $this->getPageSettingsData();
        $order = $this->getPageSettingsOrder();
        $page = $this->normalizePageKey();
        $scope = $this->getPageSettingsScope();
        $keyName = $query->getModel()->getKeyName();

        $count = 0;

        $query
            ->select([$query->getModel()->getQualifiedKeyName()])
            ->chunkById(self::PAGE_SETTINGS_BULK_CHUNK, function (Collection $users) use (&$count, $page, $settings, $order, $scope, $keyName): void {
                $count += $this->upsertPageSettingsForUsers($users->pluck($keyName), $page, $settings, $order, $scope);
            }, column: $keyName);

        return $count;
    }

    /**
     * Bulk-upsert the same settings/order for a collection of user ids in a single query.
     *
     * @return int Number of rows written.
     */
    private function upsertPageSettingsForUsers(Collection $userIds, string $page, array $settings, ?array $order, ?string $scope): int
    {
        if ($userIds->isEmpty()) {
            return 0;
        }

        $scope ??= '';
        $now = now();

        $rows = $userIds->map(fn ($userId): array => [
            'user_id' => (int) $userId,
            'page' => $page,
            'scope' => $scope,
            'settings' => json_encode($settings),
            'order' => json_encode($order),
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        PageSetting::upsert($rows, ['user_id', 'page', 'scope'], ['settings', 'order', 'updated_at']);

        return count($rows);
    }

    // ─── Internal Helpers ───────────────────────────────────

    /**
     * Merge stored settings over defaults recursively so a newly added nested default
     * survives an older stored array. Lists are replaced wholesale, never appended.
     */
    private function mergePageSettings(array $defaults, array $stored): array
    {
        foreach ($stored as $key => $value) {
            if (
                is_array($value)
                && ! array_is_list($value)
                && isset($defaults[$key])
                && is_array($defaults[$key])
                && ! array_is_list($defaults[$key])
            ) {
                $defaults[$key] = $this->mergePageSettings($defaults[$key], $value);

                continue;
            }

            $defaults[$key] = $value;
        }

        return $defaults;
    }

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
     * @return array<PageSettingDefinition|Component>
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
        $presets = PageSettingPreset::getForPage($this->normalizePageKey(), $this->getPageSettingsPresetScope());
        $schema = [];

        // Preset selector (if any presets exist)
        if ($presets->isNotEmpty()) {
            $schema[] = Select::make('_preset')
                ->label(__('Load Preset'))
                ->placeholder(__('Choose a preset...'))
                ->options($presets->pluck('name', 'id')->toArray())
                ->live()
                ->afterStateUpdated(function ($state, Set $set) use ($presets) {
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
                        $component->live()
                            ->afterStateUpdated(function ($state, string $statePath) {
                                $this->onPageSettingPreviewUpdated($statePath, $state);
                            });
                    }

                    return $component;
                }

                // Raw Filament component — pass through, but add live preview if enabled
                if ($this->pageSettingsLivePreview() && method_exists($def, 'live')) {
                    $def->live()
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
                                ->action(function (Set $set) use ($groupKeys) {
                                    foreach ($groupKeys as $key) {
                                        $set($key, true);
                                    }
                                }),
                            Action::make("deselectAll_{$group}")
                                ->label(__('Deselect All'))
                                ->link()
                                ->color('gray')
                                ->size('sm')
                                ->action(function (Set $set) use ($groupKeys) {
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
    private function getResetToDefaultsFooterAction(): Action
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
