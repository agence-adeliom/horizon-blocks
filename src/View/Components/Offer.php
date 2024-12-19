<?php

declare(strict_types=1);

namespace Adeliom\HorizonBlocks\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Offer extends Component
{
	public ?string $fullClass = null;

	public function __construct(public array $fields = [], private ?string $class = null)
	{
		$this->handleFields();
	}

	private function handleFields(): void
	{
		$classes = ['bg-secondary flex items-baseline gap-2 rounded-card p-card w-full'];

		if (null !== $this->class) {
			$classes[] = $this->class;
		}

		$this->fullClass = implode(' ', array_filter($classes));
	}

	public function render(): View|Closure|string
	{
		return view('components.offer');
	}
}
