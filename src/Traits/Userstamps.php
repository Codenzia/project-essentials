<?php

declare(strict_types=1);

namespace Codenzia\ProjectEssentials\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait Userstamps
{
    protected static function bootUserstamps()
    {
        static::creating(function (Model $model) {
            if (Auth::check() && is_null($model->created_by_user_id)) {
                $model->created_by_user_id = Auth::id();
                $model->updated_by_user_id = Auth::id();
            }
        });

        static::updating(function (Model $model) {
            if (Auth::check() && $model->isDirty() && ! $model->isDirty('updated_by_user_id')) {
                $model->updated_by_user_id = Auth::id();
            }
        });
    }

    public function createdByUser(): BelongsTo
    {
        $userModel = config('project-essentials.user_model', 'App\\Models\\User');

        return $this->belongsTo($userModel, 'created_by_user_id');
    }

    public function updatedByUser(): BelongsTo
    {
        $userModel = config('project-essentials.user_model', 'App\\Models\\User');

        return $this->belongsTo($userModel, 'updated_by_user_id');
    }
}
