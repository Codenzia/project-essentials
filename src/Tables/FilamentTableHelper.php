<?php

namespace Codenzia\ProjectEssentials\Tables;

use Filament\Forms\Components\TextInput;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class FilamentTableHelper
{
    /**
     * Returns a fresh array of common timestamp and userstamp column definitions.
     * Fresh instances are built per call so column state never leaks between tables.
     */
    private static function getStaticStampDefinitions(): array
    {
        return [
            TextColumn::make('created_at')
                ->dateTime()
                ->date(config('app.date_format'))
                ->sortable()
                ->size(TextSize::ExtraSmall)
                ->toggleable(isToggledHiddenByDefault: true)
                ->label('Created At'),

            TextColumn::make('updated_at')
                ->dateTime()
                ->date(config('app.date_format'))
                ->size(TextSize::ExtraSmall)
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true)
                ->label('Updated At'),

            TextColumn::make('createdByUser.name')
                ->label('Created By')
                ->size(TextSize::ExtraSmall)
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('updatedByUser.name')
                ->label('Updated By')
                ->size(TextSize::ExtraSmall)
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    /* Function to add time and user stamp columns to any filament table */
    public static function appendStampColumns(Table $table, bool $avoidDuplicates = false): Table
    {
        $existingColumns = collect($table->getColumns())->keyBy(fn ($column) => $column->getName());
        // Use the static getter method to get the stamp definitions
        $stampDefinitions = self::getStaticStampDefinitions();

        if ($avoidDuplicates) {
            $columnsToAdd = [];

            foreach ($stampDefinitions as $column) {
                if (! $existingColumns->has($column->getName())) {
                    $columnsToAdd[] = $column;
                }
            }
        } else {
            $columnsToAdd = $stampDefinitions;
        }

        if (! empty($columnsToAdd)) {
            $table->columns(array_merge($existingColumns->values()->all(), $columnsToAdd));
        }

        return $table;
    }

    /**
     * Adds common timestamp and userstamp columns to an array of existing columns,
     * ensuring no duplicates are added.
     *
     * @param  array  $initialColumns  The array of columns to which stamp columns should be added.
     * @return array The combined array of columns with unique stamp columns appended.
     */
    public static function withStampColumns(array $initialColumns): array
    {
        $existingColumnNames = Collection::make($initialColumns)->keyBy(fn ($column) => $column->getName());

        $columnsToAppend = [];

        // Use the static getter method to get the stamp definitions
        $stampDefinitions = self::getStaticStampDefinitions();

        foreach ($stampDefinitions as $column) {
            if (! $existingColumnNames->has($column->getName())) {
                $columnsToAppend[] = $column;
            }
        }

        return array_merge($initialColumns, $columnsToAppend);
    }

    // Use for table filters...
    public static function withSearchFilter(array $filters, string $column = 'name', string $label = 'Search by name'): array
    {
        return array_merge(
            $filters,
            [
                Filter::make($column)
                    ->schema([
                        TextInput::make($column)
                            ->placeholder(__($label))
                            ->extraAttributes(['class' => 'search-filter'])
                            ->label(''),
                    ])
                    ->query(function (Builder $query, array $data) use ($column): Builder {
                        return $query
                            ->when(
                                $data[$column],
                                function (Builder $query, $value) use ($column): Builder {
                                    $escaped = addcslashes($value, '%_\\');

                                    return $query->where($column, 'like', "%{$escaped}%");
                                },
                            );
                    }),
            ]
        );
    }
}
