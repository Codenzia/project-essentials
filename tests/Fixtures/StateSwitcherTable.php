<?php

declare(strict_types=1);

namespace Codenzia\ProjectEssentials\Tests\Fixtures;

use Codenzia\ProjectEssentials\Tables\Columns\StateSwitcher;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Contracts\TranslatableContentDriver;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\MessageBag;
use Livewire\Component;

class StateSwitcherTable extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    /**
     * Whether the fixture registers a host authorization hook on the column.
     * StateSwitcher denies every inline change while no hook is configured.
     */
    public bool $authorizeChanges = false;

    public function makeFilamentTranslatableContentDriver(): ?TranslatableContentDriver
    {
        return null;
    }

    /**
     * Livewire v4 + Testbench (no web middleware) makes the inherited
     * getErrorBag() return null on first render, crashing Livewire's own
     * SupportValidation hook before the table can render. Return a real bag so
     * the render reaches the column under test. Harness shim only.
     */
    public function getErrorBag(): MessageBag
    {
        return new MessageBag;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(StateSwitcherRecord::query())
            ->columns([
                StateSwitcher::make('status')
                    ->authorizeStateChangeUsing($this->authorizeChanges ? fn (): bool => true : null),
            ]);
    }

    public function render(): string
    {
        return <<<'BLADE'
        <div>{{ $this->table }}</div>
        BLADE;
    }
}
