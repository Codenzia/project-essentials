<?php

namespace Codenzia\ProjectEssentials\Traits;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/**
 * Trait HasPageSettings
 *
 * This trait allows you to easily add a "Settings" action to the header of any Filament page or resource.
 * It provides a modal with a customizable form and persists settings per user and page class.
 */
trait HasPageSettings
{
    /**
     * Get the page settings action for the header.
     *
     * @param  array|null  $schema  Optional form schema to override the default.
     */
    protected function getPageSettingsAction(?array $schema = null): Action
    {
        return Action::make('pageSettings')
            ->label('')
            ->icon('heroicon-o-cog-6-tooth')
            ->color('gray')
            ->modalHeading(__('Page Settings'))
            ->slideOver()
            ->modalWidth('lg')
            ->form($schema ?? $this->getPageSettingsFormSchema())
            ->fillForm($this->getPageSettingsData())
            ->action(function (array $data) {
                $this->savePageSettings($data);

                Notification::make()
                    ->success()
                    ->title(__('Settings updated successfully.'))
                    ->send();
            });
    }

    /**
     * An alias for getPageSettingsAction to easily pass config inputs.
     */
    public function settings(array $inputs): Action
    {
        return $this->getPageSettingsAction($inputs);
    }

    /**
     * Define the default form schema for the settings modal.
     */
    protected function getPageSettingsFormSchema(): array
    {
        return [];
    }

    /**
     * Get the default data to fill the settings form.
     */
    protected function getPageSettingsData(): array
    {
        $cacheKey = $this->getPageSettingsCacheKey();
        $cached = Cache::get($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }

        return [];
    }

    /**
     * Handle the persistence of the page settings.
     */
    protected function savePageSettings(array $data): void
    {
        Cache::forever($this->getPageSettingsCacheKey(), $data);

        $this->onPageSettingsUpdated($data);
    }

    /**
     * Hook called after settings are updated.
     */
    protected function onPageSettingsUpdated(array $data): void
    {
        // Override this method to perform actions after saving settings
    }

    /**
     * Helper to get a specific setting value.
     */
    protected function getPageSetting(string $key, mixed $default = null): mixed
    {
        $settings = $this->getPageSettingsData();

        return Arr::get($settings, $key, $default);
    }

    /**
     * Cache key for page settings per user and page class.
     */
    protected function getPageSettingsCacheKey(): string
    {
        return 'page_settings:' . Auth::id() . ':' . static::class;
    }
}
