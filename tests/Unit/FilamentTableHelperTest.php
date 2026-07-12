<?php

use Codenzia\ProjectEssentials\Tables\FilamentTableHelper;
use Illuminate\Database\Eloquent\Model;

class FilamentTableHelperTestModel extends Model
{
    protected $table = 'search_filter_items';
}

it('escapes LIKE wildcard characters in the search filter query bindings', function (string $input, string $expectedBoundValue) {
    $filters = FilamentTableHelper::withSearchFilter([], 'name');
    $filter = $filters[0];

    $query = FilamentTableHelperTestModel::query();
    $filter->apply($query, ['name' => $input]);

    expect($query->getQuery()->wheres)->toHaveCount(1);
    expect($query->getBindings())->toBe([$expectedBoundValue]);
})->with([
    // A literal "%" in the search term must be escaped so it can't act as a wildcard.
    'percent sign' => ['100%', '%100\\%%'],
    // A literal "_" must be escaped so it can't match any single character.
    'underscore' => ['A_B', '%A\\_B%'],
    // A literal backslash must be escaped so the caller doesn't need to double it.
    'backslash' => ['Back\\Slash', '%Back\\\\Slash%'],
    // Plain substrings are left untouched aside from the wrapping wildcards.
    'plain text' => ['Alpha', '%Alpha%'],
]);

it('builds a Filter for the given column with a hidden label', function () {
    $filters = FilamentTableHelper::withSearchFilter([], 'name');

    expect($filters)->toHaveCount(1);
    expect($filters[0]->getName())->toBe('name');
});
