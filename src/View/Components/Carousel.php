<?php

namespace Codenzia\ProjectEssentials\View\Components;

use Illuminate\View\Component;

class Carousel extends Component
{
    public $slides;

    public bool $autoplay;

    public bool $indicators;

    public bool $controls;

    /**
     * Create a new component instance.
     */
    public function __construct($slides, $autoplay = true, $indicators = true, $controls = true)
    {
        $this->slides = $slides;
        $this->autoplay = $autoplay;
        $this->indicators = $indicators;
        $this->controls = $controls;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('project-essentials::components.carousel');
    }
}
