<?php

declare(strict_types=1);

namespace Adeliom\HorizonBlocks\View\Components\SearchEngine;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SeparatedResults extends Component
{
    public function __construct(public readonly array $results, public readonly string $typeChoice, public readonly bool $displayTypeFilters = true, public readonly array $typeChoices = [], public readonly array $foundPostTypes = [], public readonly int $perPage = 12, public readonly array $totalPerType = [], public readonly string $loadingClass = 'blur', public readonly string $blockLoadingClass = '!blur-none')
    {
        //
    }

    public function render(): View|Closure|string
    {
        return view('components.search-engine.separated-results');
    }
}
