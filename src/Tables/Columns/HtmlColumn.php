<?php

namespace Codenzia\ProjectEssentials\Tables\Columns;

use Filament\Tables\Columns\Column;
use Illuminate\Support\Str;

class HtmlColumn extends Column
{
    protected string $view = 'project-essentials::components.html-column';

    protected string $htmlContent = '';

    protected bool $shouldSanitize = true;

    /**
     * Set the HTML content. Content is sanitized by default; call
     * dangerouslyAllowUnsanitizedHtml() only for fully developer-authored markup.
     */
    public function html(string $html): static
    {
        $this->htmlContent = $html;

        return $this;
    }

    /**
     * Disable HTML sanitization. Only use when the content is entirely
     * developer-authored and contains no user-controlled data.
     */
    public function dangerouslyAllowUnsanitizedHtml(): static
    {
        $this->shouldSanitize = false;

        return $this;
    }

    public function getHtml(): string
    {
        $html = (string) $this->evaluate($this->htmlContent);

        return $this->shouldSanitize
            ? Str::sanitizeHtml($html)
            : $html;
    }
}
