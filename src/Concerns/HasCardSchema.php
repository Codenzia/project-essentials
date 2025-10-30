<?php

declare(strict_types=1);

namespace Codenzia\ProjectEssentials\Concerns;

use Closure;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

trait HasCardSchema
{
    protected ?Closure $cardSchemaBuilder = null;

    /**
     * Configure the card schema using the Schema builder pattern.
     */
    public function cardSchema(Closure $builder): static
    {
        $this->cardSchemaBuilder = $builder;

        return $this;
    }

    /**
     * Get the configured card schema for a specific record.
     *
     * If no $record is provided, the method will attempt to resolve one from the current
     * component / Livewire context (if possible). Prefer passing the Model explicitly.
     *
     * @param Model|null $record
     */
    public function getCardSchema(?Model $record = null): ?Schema
    {
        if ($this->cardSchemaBuilder === null) {
            return null;
        }

        // Resolve Livewire context
        $livewire = $this->getLivewire();

        // If no record was passed, try to obtain one from the component or livewire instance
        if ($record === null) {
            // If the current component has getRecord(), use it
            if (method_exists($this, 'getRecord')) {
                try {
                    $record = $this->getRecord();
                } catch (\Throwable $e) {
                    $record = null;
                }
            }

            // Otherwise try livewire's getRecord() or a public 'record' property
            if ($record === null && $livewire !== null) {
                if (method_exists($livewire, 'getRecord')) {
                    try {
                        $record = $livewire->getRecord();
                    } catch (\Throwable $e) {
                        $record = null;
                    }
                } elseif (isset($livewire->record)) {
                    try {
                        $record = $livewire->record;
                    } catch (\Throwable $e) {
                        $record = null;
                    }
                }
            }
        }

        $schema = Schema::make($livewire);

        // Attach the record if available
        if ($record instanceof Model) {
            $schema = $schema->record($record);
        }

        return $this->evaluate($this->cardSchemaBuilder, ['schema' => $schema]);
    }

    /**
     * @return array<mixed>
     */
    protected function resolveDefaultClosureDependencyForEvaluationByName(string $parameterName): array
    {
        return match ($parameterName) {
            'schema' => [Schema::make($this->getLivewire())],
            default => parent::resolveDefaultClosureDependencyForEvaluationByName($parameterName),
        };
    }
}