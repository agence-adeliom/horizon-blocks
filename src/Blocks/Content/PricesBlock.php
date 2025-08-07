<?php

declare(strict_types=1);

namespace App\Blocks\Content;

use Adeliom\HorizonTools\Blocks\AbstractBlock;
use Adeliom\HorizonTools\Fields\Buttons\ButtonField;
use Adeliom\HorizonTools\Fields\Layout\LayoutField;
use Adeliom\HorizonTools\Fields\Tabs\ContentTab;
use Adeliom\HorizonTools\Fields\Tabs\LayoutTab;
use Adeliom\HorizonTools\Fields\Text\HeadingField;
use Adeliom\HorizonTools\Fields\Text\IconField;
use Adeliom\HorizonTools\Fields\Text\UptitleField;
use Adeliom\HorizonTools\Fields\Text\WysiwygField;
use Extended\ACF\Fields\Group;
use Extended\ACF\Fields\Number;
use Extended\ACF\Fields\Repeater;
use Extended\ACF\Fields\Text;

class PricesBlock extends AbstractBlock
{
	public static ?string $slug = 'prices';
	public static ?string $title = 'Tarifs';
	public static ?string $mode = 'preview';

	public const string FIELD_PRICES = 'prices';
	public const string FIELD_PRICE_TITLE = 'title';
	public const string FIELD_PRICE_SUBTITLE = 'subtitle';
	public const string FIELD_PRICE_VALUE = 'price';
	public const string FIELD_PRICE_VALUE_SUFFIX = 'priceSuffix';
	public const string FIELD_PRICE_SUB_VALUE_PREFIX = 'subPricePrefix';
	public const string FIELD_PRICE_SUB_VALUE = 'subPrice';
	public const string FIELD_PRICE_SUB_VALUE_SUFFIX = 'subPriceSuffix';
	public const string FIELD_PRICE_CHARACTERISTICS = 'characteristics';
	public const string FIELD_PRICE_CHARACTERISTIC_TITLE = 'title';
	public const string FIELD_PRICE_CHARACTERISTIC_ITEMS = 'items';
	public const string FIELD_PRICE_CHARACTERISTIC_ITEM_TITLE = 'title';

	public function getFields(): ?iterable
	{
		yield from ContentTab::make()->fields([
			UptitleField::make(),
			HeadingField::make()->required(),
			WysiwygField::default(),
			Repeater::make(__('Tarifs'), self::FIELD_PRICES)
				->minRows(1)
				->maxRows(3)
				->button(__('Ajouter un tarif'))
				->layout('block')
				->fields([
					Text::make(__('Titre'), self::FIELD_PRICE_TITLE),
					Text::make(__('Sous-titre'), self::FIELD_PRICE_SUBTITLE),
					Group::make(__('Prix'), self::FIELD_PRICE_VALUE)->fields([
						Number::make(__('Prix'), self::FIELD_PRICE_VALUE)
							->helperText(__('Le prix sera automatiquement formaté')),
						Text::make(__('Suffixe du prix'), self::FIELD_PRICE_VALUE_SUFFIX),
					]),
					Group::make(__('Sous-prix'), self::FIELD_PRICE_SUB_VALUE)
						->fields([
							Text::make(__('Préfixe du sous-prix'), self::FIELD_PRICE_SUB_VALUE_PREFIX),
							Number::make(__('Sous-prix'), self::FIELD_PRICE_SUB_VALUE)
								->helperText(__('Le prix sera automatiquement formaté')),
							Text::make(__('Suffixe du sous-prix'), self::FIELD_PRICE_SUB_VALUE_SUFFIX),
						]),

					ButtonField::types(),

					Repeater::make(__('Caractéristiques'), self::FIELD_PRICE_CHARACTERISTICS)
						->layout('block')
						->button(__('Ajouter un groupe'))
						->fields([
							IconField::make()->format("object"),
							Text::make(__('Titre'), self::FIELD_PRICE_CHARACTERISTIC_TITLE),
							Repeater::make(__('Éléments'), self::FIELD_PRICE_CHARACTERISTIC_ITEMS)
								->button(__('Ajouter une caractéristique'))
								->fields([
									Text::make(__('Titre'), self::FIELD_PRICE_CHARACTERISTIC_ITEM_TITLE)
								])
						])
				])
		]);

		yield from LayoutTab::make()->fields([
			LayoutField::margin(),
		]);
	}

	public function addToContext(): array
	{
		return [];
	}

	public function renderBlockCallback(): void
	{
		return;
	}
}

