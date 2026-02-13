<?php

declare(strict_types=1);

namespace Adeliom\HorizonBlocks\View\Components;

use Adeliom\HorizonTools\Services\SeoService;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Breadcrumbs extends Component
{
    public readonly ?string $content;

    public function __construct(public readonly bool $display = true)
    {
        $this->handleContent();
    }

    private function handleContent(): void
    {
        $this->content = SeoService::getBreadcrumbs(echo: false);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.breadcrumbs');
    }
}
