<?php

declare(strict_types=1);

namespace Adeliom\HorizonBlocks\View\Components\Cards;

use Adeliom\HorizonBlocks\Blocks\Content\PricesBlock;
use Adeliom\HorizonTools\Fields\Buttons\ButtonField;
use Adeliom\HorizonTools\Services\NumberService;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CardPrice extends Component
{
	public ?string $title = null;
	public ?string $subtitle = null;
	public ?string $price = null;
	public ?string $subPrice = null;
	public ?array $button = null;
	public null|false|array $characteristics = null;

	public function __construct(private readonly array $fields = [])
	{
		$this->handleData();
	}

	private function handleData(): void
	{
		if (isset($this->fields[PricesBlock::FIELD_PRICE_TITLE])) {
			$this->title = $this->fields[PricesBlock::FIELD_PRICE_TITLE];
		}

		if (isset($this->fields[PricesBlock::FIELD_PRICE_SUBTITLE])) {
			$this->subtitle = $this->fields[PricesBlock::FIELD_PRICE_SUBTITLE];
		}

		if (isset($this->fields[PricesBlock::FIELD_PRICE_VALUE])) {
			if (isset($this->fields[PricesBlock::FIELD_PRICE_VALUE][PricesBlock::FIELD_PRICE_VALUE])) {
				$rawPrice = $this->fields[PricesBlock::FIELD_PRICE_VALUE][PricesBlock::FIELD_PRICE_VALUE];

				$price = NumberService::formatPrice(price: $rawPrice);

				if (isset($this->fields[PricesBlock::FIELD_PRICE_VALUE][PricesBlock::FIELD_PRICE_VALUE_SUFFIX])) {
					$price = sprintf('%s %s', $price, $this->fields[PricesBlock::FIELD_PRICE_VALUE][PricesBlock::FIELD_PRICE_VALUE_SUFFIX]);
				}

				if ($price) {
					$this->price = $price;
				}
			}
		}

		if (isset($this->fields[PricesBlock::FIELD_PRICE_SUB_VALUE])) {
			if (isset($this->fields[PricesBlock::FIELD_PRICE_SUB_VALUE][PricesBlock::FIELD_PRICE_SUB_VALUE])) {
				$rawSubPrice = $this->fields[PricesBlock::FIELD_PRICE_SUB_VALUE][PricesBlock::FIELD_PRICE_SUB_VALUE];

				$subPrice = NumberService::formatPrice(price: $rawSubPrice);

				if (isset($this->fields[PricesBlock::FIELD_PRICE_SUB_VALUE][PricesBlock::FIELD_PRICE_SUB_VALUE_PREFIX])) {
					$subPrice = sprintf('%s %s', $this->fields[PricesBlock::FIELD_PRICE_SUB_VALUE][PricesBlock::FIELD_PRICE_SUB_VALUE_PREFIX], $subPrice);
				}

				if (isset($this->fields[PricesBlock::FIELD_PRICE_SUB_VALUE][PricesBlock::FIELD_PRICE_SUB_VALUE_SUFFIX])) {
					$subPrice = sprintf('%s %s', $subPrice, $this->fields[PricesBlock::FIELD_PRICE_SUB_VALUE][PricesBlock::FIELD_PRICE_SUB_VALUE_SUFFIX]);
				}

				if ($subPrice) {
					$this->subPrice = $subPrice;
				}
			}
		}

		if (isset($this->fields[ButtonField::BUTTON])) {
			$this->button = $this->fields[ButtonField::BUTTON];
		}

		if (isset($this->fields[PricesBlock::FIELD_PRICE_CHARACTERISTICS])) {
			$this->characteristics = $this->fields[PricesBlock::FIELD_PRICE_CHARACTERISTICS];
		}
	}

	public function render(): View|Closure|string
	{
		return view('components.cards.card-price');
	}
}

