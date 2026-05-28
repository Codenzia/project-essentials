<?php

declare(strict_types=1);

namespace Codenzia\ProjectEssentials\Forms\Components;

use Closure;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\Concerns\CanGenerateUuids;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Fluent;

/**
 * CardRepeater — a card-based repeater Field.
 *
 * Displays items as read-only cards (cardSchema) with inline editing (formSchema).
 * State is an array of items keyed by UUID (new) or "record-{id}" (saved).
 * Only one item can be in edit/add mode at a time.
 *
 * Relationship support:
 *   ->relationship()              Uses the field name as the relationship.
 *   ->relationship('custom')      Explicit relationship name.
 *
 *   Auto-detects the relationship type:
 *   - HasMany     → creates, updates, and deletes related records.
 *   - BelongsToMany → syncs IDs (attach/detach, no record creation).
 *
 *   For pivot data on BelongsToMany, pass a custom save closure:
 *   ->relationship('members', function ($relationship, $state) {
 *       $relationship->sync(collect($state)->mapWithKeys(fn ($item) => [
 *           $item['id'] => ['role' => $item['role']],
 *       ]));
 *   })
 *
 * Usage:
 *   CardRepeater::make('milestones')
 *       ->relationship()
 *       ->gridColumns(1)
 *       ->cardSchema(fn (Schema $schema) => $schema->schema([...]))
 *       ->formSchema([...])
 *       ->addActionLabel('Add milestone')
 *       ->addActionIcon('heroicon-o-plus')
 *       ->deletable()
 */
class CardRepeater extends Field
{
    use CanGenerateUuids;

    protected string $view = 'project-essentials::forms.components.card-repeater';

    // ─── Schema builders ──────────────────────────────────────
    protected ?Closure $cardSchemaBuilder = null;

    protected array | Closure $formSchema = [];

    // ─── Card actions ─────────────────────────────────────────
    protected array | Closure $cardActions = [];

    protected bool $showCardActionsOnHover = true;

    // ─── Layout ───────────────────────────────────────────────
    protected int | Closure $gridColumnsCount = 1;

    protected string | Closure | null $emptyMessage = null;

    protected string | Closure | null $addLabel = null;

    protected string | Closure | null $addIcon = null;

    protected string | Closure | null $addAlignment = null;

    // ─── Deletable / Editable ───────────────────────────────────
    protected bool | Closure $isDeletable = true;

    protected bool | Closure $isEditable = true;

    protected ?string $excludeExistingField = null;

    // ─── Relationship ───────────────────────────────────────────
    protected string | Closure | null $relationshipName = null;

    protected ?Collection $cachedExistingRecords = null;

    // ═══════════════════════════════════════════════════════════
    //  SETUP
    // ═══════════════════════════════════════════════════════════

    protected function setUp(): void
    {
        parent::setUp();

        $this->default([]);

        // Hydrate: convert sequential array to UUID-keyed
        $this->afterStateHydrated(static function (CardRepeater $component, ?array $rawState): void {
            $items = [];
            foreach ($rawState ?? [] as $key => $itemData) {
                // Keep existing keys (e.g. record-{id}), generate UUID for new
                if (is_int($key)) {
                    $items[$component->generateUuid() ?? count($items)] = $itemData;
                } else {
                    $items[$key] = $itemData;
                }
            }
            $component->rawState($items);
        });

        // Dehydrate: strip internal flags, return values only
        $this->mutateDehydratedStateUsing(static function (CardRepeater $component, ?array $state): array {
            return array_map(function ($item) {
                if (is_array($item)) {
                    unset($item['__editing__'], $item['__confirmed__'], $item['__original__']);
                }

                return $item;
            }, array_values($state ?? []));
        });

        // Register actions
        $this->registerActions([
            fn (): Action => $this->getAddItemAction(),
            fn (): Action => $this->getDeleteItemAction(),
            fn (): Action => $this->getEditItemAction(),
            fn (): Action => $this->getDoneEditingAction(),
            fn (): Action => $this->getCancelEditAction(),
            fn (): Action => $this->getConfirmAddAction(),
            fn (): Action => $this->getCancelNewAction(),
        ]);
    }

    // ═══════════════════════════════════════════════════════════
    //  FORM SCHEMA (for edit/add)
    // ═══════════════════════════════════════════════════════════

    public function formSchema(array | Closure $schema): static
    {
        $this->formSchema = $schema;
        $this->schema($schema); // Register with Filament's child component system

        return $this;
    }

    public function getFormSchemaComponents(): array
    {
        return $this->evaluate($this->formSchema);
    }

    // ═══════════════════════════════════════════════════════════
    //  CARD SCHEMA (read-only display)
    // ═══════════════════════════════════════════════════════════

    public function cardSchema(Closure $builder): static
    {
        $this->cardSchemaBuilder = $builder;

        return $this;
    }

    public function hasCardSchema(): bool
    {
        return $this->cardSchemaBuilder !== null;
    }

    public function getEvaluatedCardSchema(Model | Fluent $record, int $index = 0, int $total = 1): array
    {
        if (! $this->cardSchemaBuilder) {
            return [];
        }
        $schema = Schema::make($this->getLivewire());
        if ($record instanceof Model) {
            $schema->record($record);
        } else {
            // Raw data mode: set record as array so $getRecord() returns the Fluent
            $schema->state($record->toArray());
        }
        $result = $this->evaluate($this->cardSchemaBuilder, ['schema' => $schema]);
        if (! $result) {
            return [];
        }

        $cardContext = [
            'index' => $index,
            'total' => $total,
            'isFirst' => $index === 0,
            'isLast' => $index === $total - 1,
        ];

        // For raw data, pass the item data so blades can access it
        if ($record instanceof Fluent) {
            $cardContext['rawRecord'] = $record;
        }

        return array_map(function ($component) use ($cardContext) {
            if (method_exists($component, 'viewData')) {
                $component->viewData($cardContext);
            }

            return $component;
        }, $result->getComponents());
    }

    // ═══════════════════════════════════════════════════════════
    //  CARD ACTIONS
    // ═══════════════════════════════════════════════════════════

    public function cardActions(array | Closure $actions): static
    {
        $this->cardActions = $actions;

        return $this;
    }

    public function showCardActionsOnHover(bool $v = true): static
    {
        $this->showCardActionsOnHover = $v;

        return $this;
    }

    public function hasCardActions(): bool
    {
        return count($this->evaluate($this->cardActions) ?: []) > 0;
    }

    public function shouldShowCardActionsOnHover(): bool
    {
        return $this->showCardActionsOnHover;
    }

    public function getEvaluatedCardActions(Model | Fluent $record): array
    {
        return array_map(function ($a) use ($record) {
            if ($a instanceof Action || $a instanceof ActionGroup) {
                return (clone $a)->record($record);
            }

            return $a;
        }, $this->evaluate($this->cardActions) ?: []);
    }

    // ═══════════════════════════════════════════════════════════
    //  CHILD SCHEMAS (only for the editing item)
    // ═══════════════════════════════════════════════════════════

    public function isInlineMode(): bool
    {
        return ! $this->hasCardSchema();
    }

    public function getDefaultChildSchemas(): array
    {
        $rawState = $this->getRawState() ?? [];
        $schemas = [];
        $isInline = $this->isInlineMode();

        foreach ($rawState as $key => $data) {
            if (! is_array($data)) {
                continue;
            }

            // In inline mode: render schema for ALL items (saved + confirmed)
            // In card mode: render schema only for the editing item
            $shouldRender = $isInline
                ? (empty($data['__editing__']) && (str_starts_with($key, 'record-') || ! empty($data['__confirmed__'])))
                : ! empty($data['__editing__']);

            // Always render the editing item's schema (for add/edit forms)
            if (! empty($data['__editing__'])) {
                $shouldRender = true;
            }

            if (! $shouldRender) {
                continue;
            }

            $schema = $this
                ->getChildSchema()
                ->statePath($key)
                ->inlineLabel(false);

            // In inline mode, disable fields in view mode
            if ($isInline && empty($data['__editing__']) && $this->isViewMode()) {
                $schema->disabled();
            }

            // Set the correct model context for relationship-based fields
            if ($this->hasRelationship()) {
                $existingRecords = $this->getCachedExistingRecords();
                if (isset($existingRecords[$key])) {
                    $schema->model($existingRecords[$key]);
                } else {
                    $schema->model($this->getRelatedModel());
                }
            }

            // Disable already-selected options in sibling items
            if ($this->excludeExistingField) {
                $usedIds = $this->getValues($this->excludeExistingField);
                $excludeField = $this->excludeExistingField;

                foreach ($schema->getComponents() as $component) {
                    if ($component instanceof Select && $component->getName() === $excludeField) {
                        $component->disableOptionWhen(fn (string $value) => in_array($value, $usedIds));

                        break;
                    }
                }
            }

            $schemas[$key] = $schema->getClone();

            // In card mode, only one editing item at a time
            if (! $isInline && ! empty($data['__editing__'])) {
                break;
            }

        }

        return $schemas;
    }

    // ═══════════════════════════════════════════════════════════
    //  ACTIONS
    // ═══════════════════════════════════════════════════════════

    public function getAddItemAction(): Action
    {
        $action = Action::make('addItem')
            ->label($this->getAddActionLabel())
            ->color('gray')
            ->button();

        $icon = $this->getAddActionIcon();
        if ($icon) {
            $action->icon($icon);
        }

        return $action
            ->action(function (CardRepeater $component): void {
                $uuid = $component->generateUuid();
                $items = $component->getRawState() ?? [];

                if ($component->isInlineMode()) {
                    // Inline mode: add a confirmed empty row (fields always visible)
                    $items[$uuid] = ['__confirmed__' => true];
                } else {
                    // Card mode: open the add form
                    $items[$uuid] = ['__editing__' => true];
                }

                $component->rawState($items);
                $component->getChildSchema($uuid)?->fill();
                $component->callAfterStateUpdated();
            })
            ->hidden(function (CardRepeater $component): bool {
                if ($component->isViewMode()) {
                    return true;
                }

                foreach ($component->getRawState() ?? [] as $data) {
                    if (is_array($data) && ! empty($data['__editing__'])) {
                        return true;
                    }
                }

                return false;
            });
    }

    protected function isViewMode(): bool
    {
        // Component may be inspected standalone (unit tests construct
        // CardRepeater::make('x') with no parent Schema, so $container is
        // uninitialized). In that case there's no Livewire context to read
        // and "view mode" cannot apply — answer false.
        try {
            $livewire = $this->getLivewire();
        } catch (\Error) {
            return false;
        }

        if (! $livewire instanceof ViewRecord) {
            return false;
        }

        // When the CardRepeater is rendered inside an action modal (e.g. edit
        // slide-over on a ViewRecord page), it should behave as editable.
        return ! $livewire->mountedActions;
    }

    public function getDeleteItemAction(): Action
    {
        return Action::make('deleteItem')
            ->icon('heroicon-m-trash')
            ->color('danger')
            ->iconButton()
            ->action(function (array $arguments, CardRepeater $component): void {
                $items = $component->getRawState();
                unset($items[$arguments['item']]);
                $component->rawState($items);
                $component->callAfterStateUpdated();
            })
            ->visible($this->isDeletable() && ! $this->isViewMode());
    }

    public function getEditItemAction(): Action
    {
        return Action::make('editItem')
            ->icon('heroicon-m-pencil-square')
            ->color('gray')
            ->iconButton()
            ->visible(! $this->isViewMode())
            ->action(function (array $arguments, CardRepeater $component): void {
                $key = $arguments['item'] ?? '';
                $items = $component->getRawState();
                if (isset($items[$key])) {
                    $original = $items[$key];
                    unset($original['__editing__'], $original['__original__']);
                    $items[$key]['__editing__'] = true;
                    $items[$key]['__original__'] = $original;
                    $component->rawState($items);
                }
            });
    }

    public function getDoneEditingAction(): Action
    {
        return Action::make('doneEditing')
            ->label(__('Done'))
            ->color('primary')
            ->action(function (array $arguments, CardRepeater $component): void {
                $key = $arguments['item'] ?? '';
                $component->getChildSchema($key)?->validate();

                $items = $component->getRawState();
                if (isset($items[$key])) {
                    unset($items[$key]['__editing__'], $items[$key]['__original__']);
                    $component->rawState($items);
                }
            });
    }

    public function getCancelEditAction(): Action
    {
        return Action::make('cancelEdit')
            ->label(__('Cancel'))
            ->color('gray')
            ->action(function (array $arguments, CardRepeater $component): void {
                $items = $component->getRawState();
                $key = $arguments['item'] ?? '';
                if (isset($items[$key]['__original__'])) {
                    $items[$key] = $items[$key]['__original__'];
                    $component->rawState($items);
                }
            });
    }

    public function getConfirmAddAction(): Action
    {
        return Action::make('confirmAdd')
            ->label(__('Add'))
            ->color('primary')
            ->action(function (array $arguments, CardRepeater $component): void {
                $key = $arguments['item'] ?? '';
                $schema = $component->getChildSchema($key);
                $schema?->validate();

                $items = $component->getRawState();
                if (isset($items[$key])) {
                    unset($items[$key]['__editing__']);
                    $items[$key]['__confirmed__'] = true;
                    $component->rawState($items);
                }
            });
    }

    public function getCancelNewAction(): Action
    {
        return Action::make('cancelNew')
            ->label(__('Cancel'))
            ->color('gray')
            ->action(function (array $arguments, CardRepeater $component): void {
                $items = $component->getRawState();
                unset($items[$arguments['item'] ?? '']);
                $component->rawState($items);
                $component->callAfterStateUpdated();
            });
    }

    // ═══════════════════════════════════════════════════════════
    //  LAYOUT & CONFIG
    // ═══════════════════════════════════════════════════════════

    public function gridColumns(int | Closure $c): static
    {
        $this->gridColumnsCount = $c;

        return $this;
    }

    public function getGridColumns(): int
    {
        return $this->evaluate($this->gridColumnsCount);
    }

    public function emptyMessage(string | Closure $m): static
    {
        $this->emptyMessage = $m;

        return $this;
    }

    public function getEmptyMessage(): string
    {
        return $this->evaluate($this->emptyMessage) ?? __('No items yet');
    }

    public function addActionLabel(string | Closure $l): static
    {
        $this->addLabel = $l;

        return $this;
    }

    public function getAddActionLabel(): string
    {
        return $this->evaluate($this->addLabel) ?? __('Add item');
    }

    public function addActionIcon(string | Closure $i): static
    {
        $this->addIcon = $i;

        return $this;
    }

    public function getAddActionIcon(): ?string
    {
        return $this->evaluate($this->addIcon);
    }

    public function addActionAlignment(string | Closure $a): static
    {
        $this->addAlignment = $a;

        return $this;
    }

    public function getAddActionAlignment(): string
    {
        return $this->evaluate($this->addAlignment) ?? 'left';
    }

    public function deletable(bool | Closure $c = true): static
    {
        $this->isDeletable = $c;

        return $this;
    }

    public function isDeletable(): bool
    {
        return ! $this->isViewMode() && (bool) $this->evaluate($this->isDeletable);
    }

    public function editable(bool | Closure $c = true): static
    {
        $this->isEditable = $c;

        return $this;
    }

    public function isEditable(): bool
    {
        return ! $this->isViewMode() && (bool) $this->evaluate($this->isEditable);
    }

    /**
     * Disable options in a named Select field when they are already selected in sibling items.
     * Works like Filament's disableOptionsWhenSelectedInSiblingRepeaterItems() but for CardRepeater.
     */
    public function disableOptionsWhenSelectedInSiblingItems(string $field): static
    {
        $this->excludeExistingField = $field;

        return $this;
    }

    public function getExcludeExistingField(): ?string
    {
        return $this->excludeExistingField ?? null;
    }

    // ═══════════════════════════════════════════════════════════
    //  RELATIONSHIP (thin convenience layer)
    // ═══════════════════════════════════════════════════════════

    public function relationship(string | Closure | null $name = null, ?Closure $saveUsing = null): static
    {
        $this->relationshipName = $name ?? $this->getName();

        $this->loadStateFromRelationshipsUsing(static function (CardRepeater $component): void {
            $component->clearCachedExistingRecords();

            $records = $component->getCachedExistingRecords();

            $state = $records->map(fn (Model $record): array => $record->attributesToArray())->toArray();

            $component->rawState($state);
        });

        $this->saveRelationshipsUsing(static function (CardRepeater $component, ?array $state) use ($saveUsing): void {
            if (! is_array($state)) {
                $state = [];
            }

            $relationship = $component->getRelationship();

            // Custom save logic provided by caller
            if ($saveUsing) {
                $saveUsing($relationship, $state);

                return;
            }

            $existingRecords = $component->getCachedExistingRecords();
            $relatedKeyName = $relationship->getRelated()->getKeyName();

            // BelongsToMany: sync existing records (attach/detach)
            if ($relationship instanceof BelongsToMany) {
                $foreignPivotKeyName = $relationship->getRelatedPivotKeyName();

                $ids = collect($state)
                    ->map(fn ($item) => $item[$relatedKeyName] ?? $item[$foreignPivotKeyName] ?? null)
                    ->filter()
                    ->unique()
                    ->values()
                    ->toArray();

                $relationship->sync($ids);

                return;
            }

            // HasOneOrMany: create/update/delete records
            $recordsToDelete = [];
            foreach ($existingRecords->pluck($relatedKeyName) as $key) {
                if (! array_key_exists("record-{$key}", $state)) {
                    $recordsToDelete[] = $key;
                }
            }

            if (filled($recordsToDelete)) {
                $relationship->whereKey($recordsToDelete)->get()->each(fn (Model $r) => $r->delete());
            }

            $savedRecords = [];

            foreach ($state as $itemKey => $itemData) {
                unset($itemData['__editing__'], $itemData['__confirmed__'], $itemData['__original__']);

                if ($record = ($existingRecords[$itemKey] ?? null)) {
                    $record->fill($itemData)->save();
                    $savedRecords[$itemKey] = $record;

                    continue;
                }

                $relatedModel = $component->getRelatedModel();
                $newRecord = new $relatedModel;
                $newRecord->fill($itemData);
                $relationship->save($newRecord);
                $savedRecords[$itemKey] = $newRecord;
            }

            // Cascade saveRelationships to child components (e.g. nested
            // MorphToMany selects like bscPerspectives) so their relationship
            // sync logic is executed against the correct saved record.
            foreach ($savedRecords as $itemKey => $record) {
                $childSchema = $component->getChildSchema($itemKey);
                if (! $childSchema) {
                    continue;
                }
                $childSchema->model($record)->saveRelationships();
            }
        });

        $this->dehydrated(false);

        return $this;
    }

    public function hasRelationship(): bool
    {
        return filled($this->getRelationshipName());
    }

    public function getRelationshipName(): ?string
    {
        return $this->evaluate($this->relationshipName);
    }

    public function getRelationship(): HasOneOrMany | BelongsToMany | null
    {
        if (! $this->hasRelationship()) {
            return null;
        }

        return $this->getModelInstance()->{$this->getRelationshipName()}();
    }

    /** @return class-string<Model> */
    public function getRelatedModel(): string
    {
        return $this->getRelationship()->getModel()::class;
    }

    public function getCachedExistingRecords(): Collection
    {
        if ($this->cachedExistingRecords) {
            return $this->cachedExistingRecords;
        }

        $relationship = $this->getRelationship();
        $relatedKeyName = $relationship->getRelated()->getKeyName();

        return $this->cachedExistingRecords = $relationship->getResults()
            ->mapWithKeys(fn (Model $item): array => ["record-{$item[$relatedKeyName]}" => $item]);
    }

    public function clearCachedExistingRecords(): void
    {
        $this->cachedExistingRecords = null;
    }

    /**
     * Get card records: for saved items returns cached DB records,
     * for confirmed (pending) items creates temporary model instances.
     */
    public function getCardRecords(): Collection
    {
        $rawState = $this->getRawState() ?? [];
        $records = collect();

        // Raw data mode (no relationship) — wrap each item in a generic object
        if (! $this->hasRelationship()) {
            foreach ($rawState as $key => $data) {
                if (! is_array($data) || ! empty($data['__editing__'])) {
                    continue;
                }
                $cleanData = collect($data)->except(['__editing__', '__confirmed__', '__original__'])->toArray();
                // Use an anonymous class that supports property access for Blade
                $obj = new Fluent($cleanData);
                $records->put($key, $obj);
            }

            return $records;
        }

        // Relationship mode — load from DB
        $existing = $this->getCachedExistingRecords();

        foreach ($rawState as $key => $data) {
            if (! is_array($data)) {
                continue;
            }

            if ($existing->has($key)) {
                // Saved record — merge state edits onto it
                $record = clone $existing->get($key);
                $stateData = collect($data)->except(['__editing__', '__confirmed__', '__original__'])->toArray();
                $record->forceFill($stateData);
                $records->put($key, $record);
            } elseif (! empty($data['__confirmed__'])) {
                // Pending new item — try to load existing record (BelongsToMany)
                // or create temp model (HasMany)
                $relationship = $this->getRelationship();
                $relatedKeyName = $relationship->getRelated()->getKeyName();
                $cleanData = collect($data)->except(['__editing__', '__confirmed__', '__original__'])->toArray();

                $relatedId = $cleanData[$relatedKeyName]
                    ?? $cleanData[$relationship instanceof BelongsToMany ? $relationship->getRelatedPivotKeyName() : '']
                    ?? null;

                if ($relatedId && $relationship instanceof BelongsToMany) {
                    // Load actual record from DB
                    $record = $relationship->getRelated()->find($relatedId);
                    if ($record) {
                        $records->put($key, $record);
                    }
                } else {
                    // Create temp model for HasMany
                    $relatedModel = $this->getRelatedModel();
                    $temp = new $relatedModel;
                    $temp->forceFill($cleanData);
                    $records->put($key, $temp);
                }
            }
        }

        return $records;
    }

    /**
     * Get values of a given field from all committed items (saved + confirmed),
     * excluding the item currently being edited/added.
     */
    public function getValues(string $field): array
    {
        return collect($this->getRawState() ?? [])
            ->filter(fn ($item) => is_array($item) && empty($item['__editing__']))
            ->map(fn ($item) => $item[$field] ?? null)
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }

    // ═══════════════════════════════════════════════════════════
    //  HELPERS
    // ═══════════════════════════════════════════════════════════

    public function getLabel(): string | Htmlable | null
    {
        return parent::getLabel();
    }

    protected function resolveDefaultClosureDependencyForEvaluationByName(string $parameterName): array
    {
        return match ($parameterName) {
            'schema' => [Schema::make($this->getLivewire())],
            default => parent::resolveDefaultClosureDependencyForEvaluationByName($parameterName),
        };
    }
}
