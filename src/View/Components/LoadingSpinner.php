<?php

declare(strict_types=1);

namespace Codenzia\ProjectEssentials\View\Components;

use Illuminate\View\Component;

class LoadingSpinner extends Component
{
    public bool $show;

    public int $delay;

    public string $blur;

    public string $opacity;

    public string $message;

    public string $size;

    public string $variant;

    public bool $fullscreen;

    public bool $showProgress;

    public string $theme;

    /**
     * Create a new component instance.
     *
     * @param  bool  $show  - Whether the spinner is initially visible
     * @param  int  $delay  - Delay in ms before showing spinner (prevents flash for fast loads)
     * @param  string  $blur  - Backdrop blur intensity: 'none', 'sm', 'md', 'lg', 'xl'
     * @param  string  $opacity  - Background opacity: '0', '5', '10', '25', '50', '75', '90', '95'
     * @param  string  $message  - Loading message to display
     * @param  string  $size  - Spinner size: 'sm', 'md', 'lg', 'xl'
     * @param  string  $variant  - Visual style: 'minimal', 'elegant', 'orbital', 'pulse'
     * @param  bool  $fullscreen  - Whether to cover the entire viewport
     * @param  bool  $showProgress  - Show indeterminate progress bar
     * @param  string  $theme  - Color theme: 'auto', 'light', 'dark'
     */
    public function __construct(
        bool $show = false,
        int $delay = 200,
        string $blur = 'md',
        string $opacity = '0',
        string $message = '',
        string $size = 'md',
        string $variant = 'elegant',
        bool $fullscreen = true,
        bool $showProgress = false,
        string $theme = 'auto'
    ) {
        $this->show = $show;
        $this->delay = $delay;
        $this->blur = $this->validateBlur($blur);
        $this->opacity = $this->validateOpacity($opacity);
        $this->message = empty($message) ? __('Please wait') : $message;
        $this->size = $this->validateSize($size);
        $this->variant = $this->validateVariant($variant);
        $this->fullscreen = $fullscreen;
        $this->showProgress = $showProgress;
        $this->theme = $this->validateTheme($theme);
    }

    private function validateBlur(string $blur): string
    {
        return in_array($blur, ['none', 'sm', 'md', 'lg', 'xl']) ? $blur : 'md';
    }

    private function validateOpacity(string $opacity): string
    {
        return in_array($opacity, ['0', '5', '10', '25', '50', '75', '90', '95']) ? $opacity : '90';
    }

    private function validateSize(string $size): string
    {
        return in_array($size, ['sm', 'md', 'lg', 'xl']) ? $size : 'md';
    }

    private function validateVariant(string $variant): string
    {
        return in_array($variant, ['minimal', 'elegant', 'orbital', 'pulse']) ? $variant : 'elegant';
    }

    private function validateTheme(string $theme): string
    {
        return in_array($theme, ['auto', 'light', 'dark']) ? $theme : 'auto';
    }

    /**
     * Get the size classes for the spinner
     */
    public function getSizeClasses(): array
    {
        return match ($this->size) {
            'sm' => ['spinner' => 'w-6 h-6', 'text' => 'text-sm', 'gap' => 'gap-2'],
            'md' => ['spinner' => 'w-10 h-10', 'text' => 'text-base', 'gap' => 'gap-3'],
            'lg' => ['spinner' => 'w-14 h-14', 'text' => 'text-lg', 'gap' => 'gap-4'],
            'xl' => ['spinner' => 'w-20 h-20', 'text' => 'text-xl', 'gap' => 'gap-5'],
            default => ['spinner' => 'w-10 h-10', 'text' => 'text-base', 'gap' => 'gap-3'],
        };
    }

    /**
     * Get blur classes
     * Note: Blur is automatically disabled when opacity is 0 (transparent)
     */
    public function getBlurClass(): string
    {
        // No blur effect on transparent backgrounds - it would create a visible frosted effect
        if ($this->getOpacityValue() === 0.0) {
            return '';
        }

        return match ($this->blur) {
            'none' => '',
            'sm' => 'backdrop-blur-sm',
            'md' => 'backdrop-blur-md',
            'lg' => 'backdrop-blur-lg',
            'xl' => 'backdrop-blur-xl',
            default => 'backdrop-blur-md',
        };
    }

    /**
     * Get the opacity value as a decimal
     */
    private function getOpacityValue(): float
    {
        return match ($this->opacity) {
            '0' => 0,
            '5' => 0.05,
            '10' => 0.1,
            '25' => 0.25,
            '50' => 0.5,
            '75' => 0.75,
            '90' => 0.9,
            '95' => 0.95,
            default => 0.9,
        };
    }

    /**
     * Get inline style for background opacity (works regardless of Tailwind JIT)
     * Supports both light and dark modes via CSS custom properties
     */
    public function getOpacityStyle(): string
    {
        $opacityValue = $this->getOpacityValue();

        // Return empty for transparent - let CSS class handle it
        if ($opacityValue === 0.0) {
            return '';
        }

        // Return CSS with light mode color; dark mode handled via CSS in the component
        return "background-color: rgba(255, 255, 255, {$opacityValue});";
    }

    /**
     * Check if the background should be fully transparent
     */
    public function isTransparent(): bool
    {
        return $this->getOpacityValue() === 0.0;
    }

    /**
     * Get the raw opacity value for use in CSS variables
     */
    public function getOpacityRaw(): float
    {
        return $this->getOpacityValue();
    }

    public function render()
    {
        return view('project-essentials::components.loading-spinner');
    }
}
