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
