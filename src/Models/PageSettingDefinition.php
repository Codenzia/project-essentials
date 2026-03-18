<?php

declare(strict_types=1);

namespace Codenzia\ProjectEssentials\Models;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;

/**
 * Fluent builder for defining page settings.
 *
 * Usage:
 *   PageSettingDefinition::toggle('show_archived')
 *       ->label('Show Archived')
 *       ->description('Include archived items in the list')
 *       ->group('Display')
 *       ->default(false);
 */
class PageSettingDefinition
{
    protected string $key;

    protected string $type = 'toggle';

    protected string $label = '';

    protected string $description = '';

    protected string $group = '';

    protected mixed $default = null;

    protected array $options = [];

    protected bool $sortable = false;

    public function __construct(string $key)
    {
        $this->key = $key;
    }

    public static function toggle(string $key): static
    {
        $instance = new static($key);
        $instance->type = 'toggle';
        $instance->default = false;

        return $instance;
    }

    public static function checkbox(string $key): static
    {
        $instance = new static($key);
        $instance->type = 'checkbox';
        $instance->default = false;

        return $instance;
    }

    public static function select(string $key): static
    {
        $instance = new static($key);
        $instance->type = 'select';

        return $instance;
    }

    public static function text(string $key): static
    {
        $instance = new static($key);
        $instance->type = 'text';
        $instance->default = '';

        return $instance;
    }

    public function label(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function description(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function group(string $group): static
    {
        $this->group = $group;

        return $this;
    }

    public function default(mixed $default): static
    {
        $this->default = $default;

        return $this;
    }

    public function options(array $options): static
    {
        $this->options = $options;

        return $this;
    }

    public function sortable(bool $sortable = true): static
    {
        $this->sortable = $sortable;

        return $this;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getLabel(): string
    {
        return $this->label ?: str($this->key)->headline()->toString();
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getGroup(): string
    {
        return $this->group;
    }

    public function getDefault(): mixed
    {
        return $this->default;
    }

    public function isSortable(): bool
    {
        return $this->sortable;
    }

    /**
     * Convert this definition to a Filament form component.
     */
    public function toFormComponent(): Component
    {
        $component = match ($this->type) {
            'toggle' => Toggle::make($this->key)->default($this->default),
            'checkbox' => Checkbox::make($this->key)->default($this->default),
            'select' => Select::make($this->key)->options($this->options)->default($this->default),
            'text' => TextInput::make($this->key)->default($this->default),
        };

        $component->label(__($this->getLabel()));

        if ($this->description !== '') {
            $component->helperText(__($this->description));
        }

        return $component;
    }
}
