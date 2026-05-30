<?php

namespace Codenzia\ProjectEssentials\Traits;

use Carbon\Carbon;
use Livewire\Attributes\On;

trait HasDashboardDateFilter
{
    public ?string $selectedSection = null;

    /**
     * Load widget immediately after page render (x-init) instead of on scroll (x-intersect).
     */
    public static function getDefaultProperties(): array
    {
        return ['lazy' => 'on-load'];
    }

    #[On('update-dashboard-filter')]
    public function updateDashboardFilter(?string $selectedSection = null): void
    {
        $this->selectedSection = $selectedSection;
        $this->filterUpdated();
    }

    public function filterUpdated(): void
    {
        // Override this method to perform actions when the filter updates
        if (method_exists($this, 'updateChartData')) {
            $this->updateChartData();
        }
    }

    protected function getFilterStartDate(): ?Carbon
    {
        return match ($this->selectedSection) {
            'year' => Carbon::now()->subYear(),
            '6 months ago' => Carbon::now()->subMonths(6),
            '3 months ago' => Carbon::now()->subMonths(3),
            'Last month' => Carbon::now()->subMonth(),
            default => null,
        };
    }
}
