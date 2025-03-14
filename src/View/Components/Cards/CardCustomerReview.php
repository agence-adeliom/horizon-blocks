<?php

namespace Adeliom\HorizonBlocks\View\Components\Cards;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CardCustomerReview extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public ?array $review = null
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.cards.card-customer-review');
    }
}
