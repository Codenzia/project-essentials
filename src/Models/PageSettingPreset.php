<?php

declare(strict_types=1);

namespace Codenzia\ProjectEssentials\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageSettingPreset extends Model
{
    protected $fillable = [
        'name',
        'page',
        'scope',
        'settings',
        'order',
        'is_default',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'order' => 'array',
            'is_default' => 'boolean',
        ];
    }

    public function createdBy(): BelongsTo
    {
        $userModel = config('project-essentials.user_model', 'App\\Models\\User');

        return $this->belongsTo($userModel, 'created_by_user_id');
    }

    /**
     * Get all presets for a specific page + scope.
     */
    public static function getForPage(string $page, ?string $scope = null): Collection
    {
        return static::query()
            ->where('page', $page)
            ->where('scope', $scope ?? '')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get the default preset for a page + scope (if any).
     */
    public static function getDefaultForPage(string $page, ?string $scope = null): ?static
    {
        return static::query()
            ->where('page', $page)
            ->where('scope', $scope ?? '')
            ->where('is_default', true)
            ->first();
    }
}
