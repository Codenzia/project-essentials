<?php

declare(strict_types=1);

namespace Codenzia\ProjectEssentials\Traits;

use InvalidArgumentException;

trait HasProgressBarViewComponent
{
    protected ?string $progressLabel = null;

    protected string $alignValue = 'right'; // default

    protected bool $hideValue = false; // default

    protected string $labelFontSize = 'md'; // default text-md

    protected string $valueFontSize = 'sm'; // default text-sm

    /**
     * Set the progress label text.
     */
    public function progressLabel(string $label): static
    {
        $this->progressLabel = $label;

        // no forced alignment anymore — since left, center, right are valid with label
        return $this;
    }

    public function getProgressLabel(): ?string
    {
        return $this->progressLabel;
    }

    /**
     * Set the font size for the label (xs, sm, md, lg, xl).
     */
    public function labelFontSize(string $size): static
    {
        $this->labelFontSize = $size;

        return $this;
    }

    public function getLabelFontSize(): string
    {
        return $this->labelFontSize;
    }

    /**
     * Set the font size for the percentage value (xs, sm, md, lg, xl).
     */
    public function valueFontSize(string $size): static
    {
        $this->valueFontSize = $size;

        return $this;
    }

    public function getValueFontSize(): string
    {
        return $this->valueFontSize;
    }

    /**
     * Set alignment of the percentage text (left, center, right, before, after).
     *
     * @throws InvalidArgumentException
     */
    public function alignValue(string $alignment): static
    {
        $allowed = ['left', 'center', 'right', 'before', 'after'];

        if (! in_array($alignment, $allowed, true)) {
            throw new InvalidArgumentException(
                'Invalid alignment value. Allowed: left, center, right, before, after'
            );
        }

        // ✅ now all three are valid, regardless of whether label is set
        $this->alignValue = $alignment;

        return $this;
    }

    public function getAlignValue(): string
    {
        return $this->alignValue;
    }

    public function hideValue(bool $hide = true): static
    {
        $this->hideValue = $hide;

        return $this;
    }

    public function getHideValue(): bool
    {
        return $this->hideValue;
    }
}
