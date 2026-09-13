<?php

declare(strict_types=1);

use Codenzia\ProjectEssentials\Forms\Components\CounterInput;

/**
 * Resolve the component's own closure validation rule.
 */
function counterRule(CounterInput $component): Closure
{
    $rules = (fn () => $this->rules)->call($component);

    foreach ($rules as [$rule, $condition]) {
        $resolved = $component->evaluate($rule);

        if ($resolved instanceof Closure) {
            return $resolved;
        }
    }

    throw new RuntimeException('CounterInput registered no closure validation rule.');
}

function counterFailures(CounterInput $component, mixed $value): array
{
    $messages = [];

    counterRule($component)('quantity', $value, function (string $message) use (&$messages): void {
        $messages[] = $message;
    });

    return $messages;
}

it('defaults to a lower bound of zero and no upper bound', function () {
    $component = CounterInput::make('quantity');

    expect($component->getMinValue())->toBe(0)
        ->and($component->getMaxValue())->toBeNull();
});

it('accepts fluent bounds', function () {
    $component = CounterInput::make('quantity')
        ->minValue(2)
        ->maxValue(9);

    expect($component->getMinValue())->toBe(2)
        ->and($component->getMaxValue())->toBe(9);
});

it('rejects a value below the minimum on the server', function () {
    expect(counterFailures(CounterInput::make('quantity'), -1))->not->toBeEmpty();
});

it('rejects a value above the maximum on the server', function () {
    expect(counterFailures(CounterInput::make('quantity')->maxValue(5), 6))->not->toBeEmpty();
});

it('rejects a value that is not a whole number', function () {
    expect(counterFailures(CounterInput::make('quantity'), 'ten'))->not->toBeEmpty()
        ->and(counterFailures(CounterInput::make('quantity'), '1.5'))->not->toBeEmpty();
});

it('accepts a whole number inside the bounds, and a blank value', function () {
    $component = CounterInput::make('quantity')->maxValue(10);

    expect(counterFailures($component, 3))->toBeEmpty()
        ->and(counterFailures($component, '3'))->toBeEmpty()
        ->and(counterFailures($component, ''))->toBeEmpty()
        ->and(counterFailures($component, null))->toBeEmpty();
});
