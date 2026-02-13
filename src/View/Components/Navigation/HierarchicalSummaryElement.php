<?php

declare(strict_types=1);

namespace Adeliom\HorizonBlocks\View\Components\Navigation;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class HierarchicalSummaryElement extends Component
{
    public function __construct(public readonly array $element, public ?array $titleOverrides = [])
    {
        if (null === $this->titleOverrides) {
            $this->titleOverrides = [];
        }
    }

    public function render(): View|Closure|string
    {
        return view('components.navigation.hierarchical-summary-element');
    }
}
