<?php

declare(strict_types=1);

namespace Codenzia\ProjectEssentials\Traits;

use Codenzia\ProjectEssentials\Models\ActivityLog;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

trait CanLogsActivity
{
    public static $skipLogging = false;

    public static function bootCanLogsActivity()
    {
        static::created(function ($model) {
            if (! static::$skipLogging && static::shouldLogFromConsole()) {
                $model->logActivity('created');
            }
        });

        static::updated(function ($model) {
            if (! static::$skipLogging && static::shouldLogFromConsole() && $model->isDirty()) {
                $model->logActivity('updated');
            }
        });

        static::deleted(function ($model) {
            if (! static::$skipLogging && static::shouldLogFromConsole()) {
                $model->logActivity('deleted');
            }
        });
    }

    /**
     * Whether activity logging should proceed when the current request is
     * running in the console (artisan commands, queued jobs, scheduled tasks).
     */
    protected static function shouldLogFromConsole(): bool
    {
        if (! app()->runningInConsole()) {
            return true;
        }

        return (bool) config('project-essentials.activity_log.log_console', true);
    }

    public function logActivity(string $description)
    {
        $model = $this;
        $isCreating = $description === 'created';
        $modelName = class_basename($model);

        // Build the list of sensitive keys to redact before persisting to the log.
        $sensitive = array_merge(
            $model->getHidden(),
            ['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes', 'api_token'],
            property_exists($model, 'activityLogExcept') ? (array) $model->activityLogExcept : []
        );

        // Get actor name
        $actor = Auth::user();
        $actorName = 'System';
        if ($actor) {
            $actorName = $actor->name ?? $actor->email ?? 'User ' . ($actor->id ?? '');
        }

        // Get current and original data
        if ($isCreating) {
            $currentData = $model->getAttributes();
            $originalData = [];
            $attributeDiffs = [];
            foreach ($currentData as $key => $val) {
                if ($key !== $model->getKeyName() && ! in_array($key, ['created_at', 'updated_at'])) {
                    if ($val !== null && $val !== '') {
                        $attributeDiffs[$key] = ['old' => null, 'new' => $val];
                    }
                }
            }
        } else {
            $originalData = $model->getOriginal();
            $currentData = $model->getAttributes();
            $dirty = $model->getDirty();
            $attributeDiffs = [];

            foreach ($dirty as $key => $newVal) {
                if ($key === 'updated_at' || $key === 'updated_by_user_id') {
                    continue;
                }
                $oldVal = $originalData[$key] ?? null;
                if ($newVal !== $oldVal) {
                    $attributeDiffs[$key] = ['old' => $oldVal, 'new' => $newVal];
                }
            }

            // Redact sensitive values so a password change logs *that* it changed,
            // not the hash values themselves.
            $attributeDiffs = array_diff_key($attributeDiffs, array_flip($sensitive));
        }

        // Build description text
        if ($isCreating) {
            $descriptionText = $actorName . ' created ' . $modelName;
        } elseif ($description === 'deleted') {
            $id = $model->getKey();
            $descriptionText = $actorName . ' deleted ' . $modelName . ($id ? " (ID: $id)" : '');
        } else {
            // Updated
            $fragments = [];
            $statusChanged = false;

            foreach ($attributeDiffs as $key => $pair) {
                $oldVal = $pair['old'];
                $newVal = $pair['new'];
                $lowerKey = strtolower($key);

                // Skip system fields
                if (in_array($key, ['created_at', 'updated_at', 'created_by_user_id', 'updated_by_user_id'])) {
                    continue;
                }

                // Handle foreign key fields
                // Only log status changes as a single line, skip related fields like closed_reason, closed_by, closed_at, progress
                if (
                    (strpos($lowerKey, 'status') !== false || strpos($lowerKey, 'statue') !== false)
                    && isset($pair['old']) && $pair['old'] !== $newVal
                ) {
                    $fragments[] = 'changed status from "' . $this->stringify($pair['old']) . '" to "' . $this->stringify($newVal) . '"';
                    $statusChanged = true;
                }
                // Skip closed_reason, closed_by, closed_at, progress if status is being changed
                elseif (
                    in_array($lowerKey, ['closed_reason', 'closed_by', 'closed_at', 'progress'])
                    && (array_key_exists('status', $dirty) || array_key_exists('statue', $dirty))
                ) {
                    continue;
                }
                // Handle foreign key fields
                elseif (strpos($lowerKey, '_id') !== false && $lowerKey !== $model->getKeyName()) {
                    $relationName = str_replace('_id', '', $key);
                    $friendlyFieldName = $this->getFriendlyFieldName($relationName);
                    $fragments[] = $friendlyFieldName;
                } elseif (strpos($lowerKey, 'date') !== false) {
                    $fragments[] = $this->getFriendlyFieldName($key);
                } else {
                    $fragments[] = $this->getFriendlyFieldName($key);
                }
            }

            if (empty($fragments)) {
                return; // Nothing changed
            }

            // Format description based on number of changes
            if (count($fragments) === 1 && $statusChanged) {
                $descriptionText = $actorName . ' ' . $fragments[0];
            } elseif (count($fragments) === 1) {
                $descriptionText = $actorName . ' changed ' . $fragments[0];
            } else {
                $descriptionText = $actorName . ' changed ' . implode(', ', $fragments);
            }
        }

        // Redact sensitive attributes before persisting the raw data dumps.
        $safeData = array_diff_key($currentData, array_flip($sensitive));

        // Prepare data for logging
        $dataForLogging = [
            'attributes' => $isCreating ? $safeData : $attributeDiffs,
            'description' => $description,
        ];

        try {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'model_id' => $model->getKey(),
                'model' => static::class,
                'current_data' => $safeData,
                'new_data' => $dataForLogging,
                'description' => $descriptionText,
            ]);
        } catch (Exception $e) {
            // Silently fail if logging fails (don't break the main operation)
            \Log::error('Activity logging failed: ' . $e->getMessage());
        }
    }

    /**
     * Convert a value to a short readable string for descriptions.
     */
    private function stringify($value)
    {
        if (is_null($value)) {
            return 'null';
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if (is_array($value) || is_object($value)) {
            $s = json_encode($value);

            return strlen($s) > 100 ? substr($s, 0, 97) . '...' : $s;
        }

        return (string) $value;
    }

    /**
     * Get the display name for a related model by its ID
     */
    private function getRelatedModelName($relationName, $id)
    {
        if (is_null($id) || $id === '') {
            return 'null';
        }

        // Convert field name to relation name (e.g., client_user_id -> clientUser)
        $actualRelationName = $this->getActualRelationName($relationName);

        // Check if the relation method exists on the model
        if (! method_exists($this, $actualRelationName)) {
            return (string) $id;
        }

        try {
            // Get the relation instance
            $relation = $this->$actualRelationName();

            // Get the related model class
            $relatedModelClass = $relation->getRelated();

            // Try to find the record by ID
            $relatedModel = $relatedModelClass::find($id);

            if (! $relatedModel) {
                return "Deleted ({$id})";
            }

            // Try common name fields in order of preference
            $nameFields = ['name', 'title', 'full_name', 'first_name', 'email', 'username'];

            foreach ($nameFields as $field) {
                if ($relatedModel->hasAttribute($field) && ! empty($relatedModel->$field)) {
                    return $relatedModel->$field;
                }
            }

            // If no name field found, try to use a combination
            if ($relatedModel->hasAttribute('first_name') || $relatedModel->hasAttribute('last_name')) {
                $firstName = $relatedModel->first_name ?? '';
                $lastName = $relatedModel->last_name ?? '';
                $fullName = trim($firstName . ' ' . $lastName);
                if (! empty($fullName)) {
                    return $fullName;
                }
            }

            // Fallback to ID with model name
            return class_basename($relatedModelClass) . " ({$id})";

        } catch (Exception $e) {
            // If any error occurs, return the ID
            return (string) $id;
        }
    }

    /**
     * Convert field name to actual relation method name
     * e.g., client_user_id -> client, user_id -> user, project_owner_user_id -> owner
     */
    private function getActualRelationName($fieldName)
    {
        // Remove _id suffix if exists
        $relationName = str_replace('_id', '', $fieldName);

        // Check if model has custom field-to-relation mappings
        if (method_exists($this, 'getActivityLogFieldMappings')) {
            $customMappings = $this->getActivityLogFieldMappings();
            if (isset($customMappings[$fieldName])) {
                $customRelation = $customMappings[$fieldName];
                if (method_exists($this, $customRelation)) {
                    return $customRelation;
                }
            }
            // Also check without the _id suffix
            if (isset($customMappings[$relationName])) {
                $customRelation = $customMappings[$relationName];
                if (method_exists($this, $customRelation)) {
                    return $customRelation;
                }
            }
        }

        // Convert snake_case to camelCase
        $camelCaseName = Str::camel($relationName);

        // Check if the camelCase method exists
        if (method_exists($this, $camelCaseName)) {
            return $camelCaseName;
        }

        // Try common variations
        $variations = [
            $relationName, // original snake_case
            Str::singular($relationName), // singular form
            Str::singular($camelCaseName), // singular camelCase
        ];

        // Special cases for common patterns
        if (str_contains($relationName, '_user')) {
            $baseRelation = str_replace('_user', '', $relationName);
            $variations[] = $baseRelation; // e.g., client_user_id -> client
            $variations[] = Str::camel($baseRelation);
        }

        foreach ($variations as $variation) {
            if (method_exists($this, $variation)) {
                return $variation;
            }
        }

        // Fallback to original camelCase
        return $camelCaseName;
    }

    /**
     * Convert field name to a friendly display name
     * e.g., client_user -> Client, assigned_user -> Assigned User, project_owner_user -> Project Owner
     */
    private function getFriendlyFieldName($fieldName)
    {
        // Check if model has custom friendly name mappings
        if (method_exists($this, 'getActivityLogFriendlyNames')) {
            $customNames = $this->getActivityLogFriendlyNames();
            if (isset($customNames[$fieldName])) {
                return $customNames[$fieldName];
            }
            // Also check with _id suffix
            $fieldWithId = $fieldName . '_id';
            if (isset($customNames[$fieldWithId])) {
                return $customNames[$fieldWithId];
            }
        }

        // Handle special cases
        if (str_contains($fieldName, '_user')) {
            $baseName = str_replace('_user', '', $fieldName);

            return Str::title(str_replace('_', ' ', $baseName));
        }

        // Convert snake_case to Title Case
        return Str::title(str_replace('_', ' ', $fieldName));
    }
}
