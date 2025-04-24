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
use Extended\ACF\Fields\ButtonGroup;

class WysiwygBlock extends AbstractBlock
{
	public static ?string $slug = 'wysiwyg';
	public static ?string $title = 'Texte simple';
	public static ?string $mode = 'preview';
	public static ?string $icon = 'text';

	public const FIELD_ALIGNMENT = 'alignment';
	public const VALUE_ALIGNMENT_CENTER = 'center';
	public const VALUE_ALIGNMENT_LEFT = 'left';
	public const VALUE_ALIGNMENT_RIGHT = 'right';

	public function getFields(): ?iterable
	{
		yield from ContentTab::make()->fields([
			UptitleField::make(),
			HeadingField::make(defaultTag: 'h2'),
			WysiwygField::make(),
			ButtonField::group(),
		]);

		yield from LayoutTab::make()->fields([
			ButtonGroup::make(__('Alignement'), self::FIELD_ALIGNMENT)
				->default(self::VALUE_ALIGNMENT_CENTER)
				->choices([
					self::VALUE_ALIGNMENT_LEFT => __('Gauche'),
					self::VALUE_ALIGNMENT_CENTER => __('Centre'),
					self::VALUE_ALIGNMENT_RIGHT => __('Droite'),
				]),
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