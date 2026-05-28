<?php

use Codenzia\ProjectEssentials\Forms\Components\CardRepeater;

it('can be instantiated', function () {
    $component = CardRepeater::make('items');

    expect($component)->toBeInstanceOf(CardRepeater::class);
    expect($component->getName())->toBe('items');
});

it('has the correct view', function () {
    $component = CardRepeater::make('items');

    expect($component->getView())->toBe('project-essentials::forms.components.card-repeater');
});

it('has default state as empty array', function () {
    $component = CardRepeater::make('items');

    expect($component->getDefaultState())->toBe([]);
});

it('defaults to 1 grid column', function () {
    $component = CardRepeater::make('items');

    expect($component->getGridColumns())->toBe(1);
});

it('allows custom grid columns', function () {
    $component = CardRepeater::make('items')
        ->gridColumns(3);

    expect($component->getGridColumns())->toBe(3);
});

it('has no table header view by default', function () {
    $component = CardRepeater::make('items');

    expect($component->getTableHeaderView())->toBeNull();
});

it('allows setting a table header view', function () {
    $component = CardRepeater::make('items')
        ->tableHeader('forms.components.goal-card-header');

    expect($component->getTableHeaderView())->toBe('forms.components.goal-card-header');
});

it('evaluates table header view from a closure', function () {
    $component = CardRepeater::make('items')
        ->tableHeader(fn (): string => 'forms.components.dynamic-header');

    expect($component->getTableHeaderView())->toBe('forms.components.dynamic-header');
});

it('has default empty message', function () {
    $component = CardRepeater::make('items');

    expect($component->getEmptyMessage())->toBe('No items yet');
});

it('allows custom empty message', function () {
    $component = CardRepeater::make('items')
        ->emptyMessage('Nothing here');

    expect($component->getEmptyMessage())->toBe('Nothing here');
});

it('has default add action label', function () {
    $component = CardRepeater::make('items');

    expect($component->getAddActionLabel())->toBe('Add item');
});

it('allows custom add action label', function () {
    $component = CardRepeater::make('items')
        ->addActionLabel('New milestone');

    expect($component->getAddActionLabel())->toBe('New milestone');
});

it('has no add action icon by default', function () {
    $component = CardRepeater::make('items');

    expect($component->getAddActionIcon())->toBeNull();
});

it('allows custom add action icon', function () {
    $component = CardRepeater::make('items')
        ->addActionIcon('heroicon-o-plus');

    expect($component->getAddActionIcon())->toBe('heroicon-o-plus');
});

it('defaults add action alignment to left', function () {
    $component = CardRepeater::make('items');

    expect($component->getAddActionAlignment())->toBe('left');
});

it('allows custom add action alignment', function () {
    $component = CardRepeater::make('items')
        ->addActionAlignment('center');

    expect($component->getAddActionAlignment())->toBe('center');
});

it('is deletable by default', function () {
    $component = CardRepeater::make('items');

    expect($component->isDeletable())->toBeTrue();
});

it('can disable deletable', function () {
    $component = CardRepeater::make('items')
        ->deletable(false);

    expect($component->isDeletable())->toBeFalse();
});

it('is editable by default', function () {
    $component = CardRepeater::make('items');

    expect($component->isEditable())->toBeTrue();
});

it('can disable editable', function () {
    $component = CardRepeater::make('items')
        ->editable(false);

    expect($component->isEditable())->toBeFalse();
});

it('has no card schema by default (inline mode)', function () {
    $component = CardRepeater::make('items');

    expect($component->hasCardSchema())->toBeFalse();
    expect($component->isInlineMode())->toBeTrue();
});

it('switches to card mode when card schema is set', function () {
    $component = CardRepeater::make('items')
        ->cardSchema(fn ($schema) => $schema->schema([]));

    expect($component->hasCardSchema())->toBeTrue();
    expect($component->isInlineMode())->toBeFalse();
});

it('has no relationship by default', function () {
    $component = CardRepeater::make('items');

    expect($component->hasRelationship())->toBeFalse();
});

it('has no exclude existing field by default', function () {
    $component = CardRepeater::make('items');

    expect($component->getExcludeExistingField())->toBeNull();
});

it('allows setting exclude existing field', function () {
    $component = CardRepeater::make('items')
        ->disableOptionsWhenSelectedInSiblingItems('user_id');

    expect($component->getExcludeExistingField())->toBe('user_id');
});

it('has no card actions by default', function () {
    $component = CardRepeater::make('items');

    expect($component->hasCardActions())->toBeFalse();
});

it('shows card actions on hover by default', function () {
    $component = CardRepeater::make('items');

    expect($component->shouldShowCardActionsOnHover())->toBeTrue();
});

it('can disable show card actions on hover', function () {
    $component = CardRepeater::make('items')
        ->showCardActionsOnHover(false);

    expect($component->shouldShowCardActionsOnHover())->toBeFalse();
});

it('supports full fluent configuration chain', function () {
    $component = CardRepeater::make('milestones')
        ->gridColumns(2)
        ->emptyMessage('No milestones')
        ->addActionLabel('Add milestone')
        ->addActionIcon('heroicon-o-plus')
        ->addActionAlignment('center')
        ->deletable()
        ->editable();

    expect($component->getGridColumns())->toBe(2);
    expect($component->getEmptyMessage())->toBe('No milestones');
    expect($component->getAddActionLabel())->toBe('Add milestone');
    expect($component->getAddActionIcon())->toBe('heroicon-o-plus');
    expect($component->getAddActionAlignment())->toBe('center');
    expect($component->isDeletable())->toBeTrue();
    expect($component->isEditable())->toBeTrue();
});
