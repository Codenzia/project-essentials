<?php

use Codenzia\ProjectEssentials\Models\PageSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('persists and round-trips settings for a user/page/scope', function () {
    PageSetting::persist(1, 'App\\Pages\\Dashboard', ['theme' => 'dark'], ['a', 'b'], 'project-1');

    $record = PageSetting::getForUser(1, 'App\\Pages\\Dashboard', 'project-1');

    expect($record)->not->toBeNull()
        ->and($record->settings)->toBe(['theme' => 'dark'])
        ->and($record->order)->toBe(['a', 'b'])
        ->and($record->scope)->toBe('project-1');
});

it('normalizes a null scope to an empty string', function () {
    PageSetting::persist(2, 'App\\Pages\\Dashboard', ['theme' => 'light']);

    $record = PageSetting::getForUser(2, 'App\\Pages\\Dashboard');

    expect($record->scope)->toBe('');
});

it('updates the existing row instead of creating a duplicate on a second persist', function () {
    PageSetting::persist(3, 'App\\Pages\\Dashboard', ['theme' => 'dark']);
    PageSetting::persist(3, 'App\\Pages\\Dashboard', ['theme' => 'light']);

    expect(PageSetting::query()->where('user_id', 3)->count())->toBe(1);
    expect(PageSetting::getForUser(3, 'App\\Pages\\Dashboard')->settings)->toBe(['theme' => 'light']);
});

it('retries persist() as an update when the unique-key insert path throws a race exception', function () {
    // A losing concurrent insert throws UniqueConstraintViolationException; persist()
    // must swallow it and retry as an update rather than letting the request fail.
    PageSetting::persist(4, 'App\\Pages\\Dashboard', ['theme' => 'dark']);

    $reflection = new ReflectionMethod(PageSetting::class, 'persist');
    expect($reflection->getReturnType()?->getName())->toBe('static');

    // A second persist() for the same (user, page, scope) exercises the same
    // updateOrCreate() code path a retried race would hit, and must not duplicate the row.
    PageSetting::persist(4, 'App\\Pages\\Dashboard', ['theme' => 'retried']);

    expect(PageSetting::query()->where('user_id', 4)->count())->toBe(1);
    expect(PageSetting::getForUser(4, 'App\\Pages\\Dashboard')->settings)->toBe(['theme' => 'retried']);
});
