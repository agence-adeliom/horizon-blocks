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

class TitleTextBlock extends AbstractBlock
{
	public static ?string $slug = 'title-text';
	public static ?string $icon = 'editor-textcolor';

	public static function getTitle(): ?string
	{
		return __('Titre texte', 'horizon-blocks');
	}

	public static function getDescription(): ?string
	{
		return __("Affiche un titre accompagné d'un texte explicatif.", 'horizon-blocks');
	}

	public function getFields(): ?iterable
	{
		yield from ContentTab::make()->fields([
			UptitleField::make(),
			HeadingField::make(HeadingField::LABEL, HeadingField::NAME, null, 'h2')->required(),
			WysiwygField::default(),
			ButtonField::group(),
		]);

		yield from LayoutTab::make()->fields([
			LayoutField::margin(),
		]);
	}
}
