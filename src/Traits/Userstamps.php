<?php

namespace Codenzia\ProjectEssentials\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

trait Userstamps
{
    protected static function bootUserstamps()
    {
        // Check if the application is running in the console (Artisan commands)
        // AND if a console command argument actually exists.
        $isConsole = app()->runningInConsole();

        // If it's a console command AND it's one of the migration commands
        if (! $isConsole) {
            // For all other cases (web requests, non-migration console commands),
            // perform the strict schema check.
            $model = new static;
            $table = $model->getTable();
            // check $table if found in database
            if (! Schema::hasTable($table)) {
                return "Table '{$table}' does not exist in the database. Please run the migrations first.";
            }
            if (! Schema::hasColumn($table, 'created_by_user_id')) {
                throw new RuntimeException("Model '" . get_class($model) . "' (table '{$table}') is missing the 'created_by_user_id' column required by the Userstamps trait.");
            }

            if (! Schema::hasColumn($table, 'updated_by_user_id')) {
                throw new RuntimeException("Model '" . get_class($model) . "' (table '{$table}') is missing the 'updated_by_user_id' column required by the Userstamps trait.");
            }
        }

        static::creating(function (Model $model) {
            if (Schema::hasColumn($model->getTable(), 'created_by_user_id')) {
                if (Auth::check() && is_null($model->created_by_user_id)) {
                    $model->created_by_user_id = Auth::id();
                    if (Schema::hasColumn($model->getTable(), 'updated_by_user_id')) {
                        $model->updated_by_user_id = Auth::id();
                    }
                }
            }
        });

        static::updating(function (Model $model) {
            if (Schema::hasColumn($model->getTable(), 'updated_by_user_id')) {
                if (Auth::check() && $model->isDirty() && ! $model->isDirty('updated_by_user_id')) {
                    $model->updated_by_user_id = Auth::id();
                }
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
