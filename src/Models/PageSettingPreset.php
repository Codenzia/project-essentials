<?php

declare(strict_types=1);

namespace Codenzia\ProjectEssentials\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageSettingPreset extends Model
{
    protected $fillable = [
        'name',
        'page',
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
     * Get all presets for a specific page.
     */
    public static function getForPage(string $page): \Illuminate\Database\Eloquent\Collection
    {
        return static::query()
            ->where('page', $page)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get the default preset for a page (if any).
     */
    public static function getDefaultForPage(string $page): ?static
    {
        return static::query()
            ->where('page', $page)
            ->where('is_default', true)
            ->first();
    }
}
