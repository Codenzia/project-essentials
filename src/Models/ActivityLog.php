<?php

declare(strict_types=1);

namespace Codenzia\ProjectEssentials\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'model_id',
        'model',
        'current_data',
        'new_data',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'current_data' => 'array',
            'new_data' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        $userModel = config('project-essentials.user_model', 'App\\Models\\User');

        return $this->belongsTo($userModel);
    }
}
