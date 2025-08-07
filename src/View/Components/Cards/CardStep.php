<?php

declare(strict_types=1);

namespace Adeliom\HorizonBlocks\View\Components\Cards;

use App\PostTypes\FAQ;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CardStep extends Component
{
	/**
	 * Create a new component instance.
	 */
	public function __construct(public readonly array $step) {}

	/**
	 * Get the view / contents that represent the component.
	 */
	public function render(): View|Closure|string
	{
		return view('components.cards.card-step');
	}
}
