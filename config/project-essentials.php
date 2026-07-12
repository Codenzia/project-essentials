<?php

return [
    /**
     * The User model class used by the Userstamps trait
     * for the createdByUser() and updatedByUser() relationships.
     */
    'user_model' => 'App\\Models\\User',

    /**
     * Page Settings configuration.
     */
    'page_settings' => [
        /**
         * The team/department column on the User model.
         * Used by applyPageSettingsToTeam() for bulk-applying settings.
         */
        'team_column' => 'department_id',
    ],

    /**
     * Activity logging configuration.
     */
    'activity_log' => [
        /**
         * Whether model writes triggered from the console (artisan commands,
         * queued jobs, scheduled tasks) should also be audited.
         */
        'log_console' => true,
    ],
];
