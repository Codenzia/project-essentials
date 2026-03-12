<?php

namespace Codenzia\ProjectEssentials\Tables\Columns;

use Filament\Tables\Columns\Column;

class HtmlColumn extends Column
{
    protected string $view = 'project-essentials::components.html-column';

    protected string $htmlContent = '';

    public function html(string $html): static
    {
        $this->htmlContent = $html;

        return $this;
    }

    public function getHtml(): string
    {
        return $this->evaluate($this->htmlContent);
    }
}
