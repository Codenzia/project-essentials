<?php

declare(strict_types=1);

namespace Codenzia\ProjectEssentials\Tests\Fixtures;

use Codenzia\ProjectEssentials\Traits\HasPageSettings;

/**
 * Minimal host for the HasPageSettings trait. Exposes the protected surface the
 * page-settings tests need without booting a full Filament page.
 */
class PageSettingsHost
{
    use HasPageSettings;

    public ?string $scope = null;

    /** @var array<string, mixed> */
    public array $defaults = [];

    public function pageKey(): string
    {
        return $this->normalizePageKey();
    }

    /**
     * @return array<string, mixed>
     */
    public function data(): array
    {
        return $this->getPageSettingsData();
    }

    /**
     * @return array<int, mixed>|null
     */
    public function order(): ?array
    {
        return $this->getPageSettingsOrder();
    }

    public function applyPreset(int $presetId): void
    {
        $this->applyPageSettingsPreset($presetId);
    }

    /**
     * Drop the per-request memoization so a test can re-read after a write.
     */
    public function forgetResolved(): void
    {
        $this->resolvedPageSettings = null;
        $this->resolvedPageOrder = null;
    }

    protected function getPageSettingsScope(): ?string
    {
        return $this->scope;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getPageSettingsDefaults(): array
    {
        return $this->defaults;
    }
}
