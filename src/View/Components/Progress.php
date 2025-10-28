<?php

namespace Codenzia\ProjectEssentials\View\Components;

use Illuminate\View\Component;

class Progress extends Component
{
    public float $progress;

    public string $color;

    public float $arcLength;

    public float $dashValue = 0;

    public bool $showText;

    public string $label;

    /**
     * Create a new component instance.
     *
     * Use loose constructor types so Blade-provided attributes (often strings) won't break math.
     */
    public function __construct($progress = 0, $color = 'primary', $label = null, $showText = true)
    {
        $this->progress = (float) $progress;
        $this->color = $color;
        $this->arcLength = 50.265;
        $this->dashValue = ($this->arcLength * $this->progress) / 100;
        $this->label = $label ?? "__('Progress')";
        $this->showText = (bool) $showText;
    }

    /**
     * Render the component view.
     * Return the view only — Laravel automatically exposes public properties to the view.
     */
    public function render()
    {
        return view('project-essentials::components.progress');
    }
}
