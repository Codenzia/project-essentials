<?php

use Codenzia\ProjectEssentials\Models\ActivityLog;
use Codenzia\ProjectEssentials\Tests\Fixtures\LoggableWidget;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

beforeEach(function () {
    Schema::create('loggable_widgets', function ($table) {
        $table->id();
        $table->string('name');
        $table->string('password')->nullable();
        $table->string('api_token')->nullable();
        $table->string('internal_note')->nullable();
        $table->json('meta')->nullable();
        $table->timestamps();
    });
});

afterEach(function () {
    Schema::dropIfExists('loggable_widgets');
});

it('never leaks hidden attributes into the activity log data', function () {
    $widget = LoggableWidget::create([
        'name' => 'Widget A',
        'password' => 'super-secret',
        'api_token' => 'token-123',
    ]);

    $log = ActivityLog::query()->where('model_id', $widget->id)->latest('id')->first();

    expect($log)->not->toBeNull();

    $payload = json_encode($log->new_data) . json_encode($log->current_data);

    expect($payload)->not->toContain('super-secret')
        ->and($payload)->not->toContain('token-123');
});

it('never leaks activityLogExcept keys into the diff on update', function () {
    $widget = LoggableWidget::create(['name' => 'Widget B']);

    $widget->update(['name' => 'Widget B Renamed', 'internal_note' => 'secret note']);

    $log = ActivityLog::query()->where('model_id', $widget->id)->latest('id')->first();

    $payload = json_encode($log->new_data);

    expect($payload)->toContain('Widget B Renamed')
        ->and($payload)->not->toContain('secret note');
});

it('records that a password changed instead of dropping the event entirely', function () {
    $widget = LoggableWidget::create(['name' => 'Widget C', 'password' => 'first-secret']);

    $countBefore = ActivityLog::query()->where('model_id', $widget->id)->count();

    $widget->update(['password' => 'second-secret']);

    $log = ActivityLog::query()->where('model_id', $widget->id)->latest('id')->first();
    $payload = json_encode($log->new_data) . json_encode($log->current_data);

    expect(ActivityLog::query()->where('model_id', $widget->id)->count())->toBe($countBefore + 1)
        ->and($log->description)->toContain('Password')
        ->and($payload)->toContain(LoggableWidget::ACTIVITY_LOG_REDACTED)
        ->and($payload)->not->toContain('second-secret')
        ->and($payload)->not->toContain('first-secret');
});

it('redacts a sensitive key nested inside an otherwise permitted attribute', function () {
    $widget = LoggableWidget::create([
        'name' => 'Widget D',
        'meta' => ['integration' => ['api_token' => 'nested-token-123', 'endpoint' => 'https://example.test']],
    ]);

    $widget->update(['meta' => ['integration' => ['api_token' => 'nested-token-456', 'endpoint' => 'https://example.test']]]);

    $payload = ActivityLog::query()
        ->where('model_id', $widget->id)
        ->get()
        ->map(fn (ActivityLog $log): string => json_encode($log->new_data) . json_encode($log->current_data))
        ->implode('');

    expect($payload)->not->toContain('nested-token-123')
        ->and($payload)->not->toContain('nested-token-456')
        ->and($payload)->toContain('example.test');
});

it('restores the previous suppression state even when the callback throws', function () {
    expect(LoggableWidget::$skipLogging)->toBeFalse();

    try {
        LoggableWidget::withoutActivityLogging(function (): void {
            LoggableWidget::create(['name' => 'Widget E']);

            throw new RuntimeException('boom');
        });
    } catch (RuntimeException) {
        // the suppression must not survive the failure
    }

    expect(LoggableWidget::$skipLogging)->toBeFalse()
        ->and(ActivityLog::query()->where('description', 'like', '%Widget E%')->count())->toBe(0);
});
