<?php

declare(strict_types=1);

namespace Codenzia\ProjectEssentials\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageSetting extends Model
{
    protected $fillable = [
        'user_id',
        'page',
        'scope',
        'settings',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'order' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        $userModel = config('project-essentials.user_model', 'App\\Models\\User');

        return $this->belongsTo($userModel);
    }

    /**
     * Get the page setting record for a user, page, and optional scope.
     */
    public static function getForUser(int $userId, string $page, ?string $scope = null): ?static
    {
        $scope = $scope ?? '';

        return static::query()
            ->where('user_id', $userId)
            ->where('page', $page)
            ->where('scope', $scope)
            ->first();
    }

    /**
     * Create or update settings for a user + page + scope.
     */
    public static function persist(int $userId, string $page, array $settings, ?array $order = null, ?string $scope = null): static
    {
        $scope = $scope ?? '';

        $keys = [
            'user_id' => $userId,
            'page' => $page,
            'scope' => $scope,
        ];

        $values = [
            'settings' => $settings,
            'order' => $order,
        ];

        try {
            return static::updateOrCreate($keys, $values);
        } catch (\Illuminate\Database\UniqueConstraintViolationException) {
            // Loser of a concurrent insert race retries as an update.
            return static::updateOrCreate($keys, $values);
        }
    }
}
