<?php

declare(strict_types=1);

namespace Adeliom\HorizonBlocks\Blocks\Content;

use Adeliom\HorizonTools\Blocks\AbstractBlock;
use Adeliom\HorizonTools\Fields\Buttons\ButtonField;
use Adeliom\HorizonTools\Fields\Layout\LayoutField;
use Adeliom\HorizonTools\Fields\Tabs\ContentTab;
use Adeliom\HorizonTools\Fields\Tabs\LayoutTab;
use Adeliom\HorizonTools\Fields\Text\HeadingField;
use Adeliom\HorizonTools\Fields\Text\UptitleField;
use Adeliom\HorizonTools\Fields\Text\WysiwygField;
use Extended\ACF\Fields\Image;
use Extended\ACF\Fields\Repeater;

class CardsBlock extends AbstractBlock
{
	public static ?string $slug = 'cards';
	public static ?string $title = 'Remontée de cartes';
	public static ?string $description = 'Affiche deux cartes cliquables, menant chacune vers une page spécifique.';
	public static string $category = 'content';
	public static ?string $icon = 'grid-view';

	public const string FIELD_CARDS = 'cards';

	public function getFields(): ?iterable
	{
		yield from ContentTab::make()->fields([
			UptitleField::make(),
			HeadingField::make(HeadingField::LABEL, HeadingField::NAME, null, 'h2'),
			WysiwygField::minimal(),
			Repeater::make("Cartouches", self::FIELD_CARDS)
				->fields([
					HeadingField::make(),
					WysiwygField::minimal(),
					ButtonField::make()->required(),
					Image::make("Image", "img")->required(),
				])
				->layout('row')
				->collapsed(HeadingField::NAME)
				->minRows(2)
				->maxRows(2),
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
