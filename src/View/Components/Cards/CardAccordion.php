<?php

declare(strict_types=1);

namespace Adeliom\HorizonBlocks\View\Components\Cards;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CardAccordion extends Component
{
	public function __construct(
		public ?string $title = null,
		public ?string $content = null,
	)
	{
	}

	/**
	 * Get the view / contents that represent the component.
	 */
	public function render(): View|Closure|string
	{
		return view('components.cards.card-accordion');
	}
}
