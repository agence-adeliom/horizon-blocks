<?php

declare(strict_types=1);

namespace Adeliom\HorizonBlocks\Blocks\Content;

use Adeliom\HorizonBlocks\Concerns\EnqueuesBlockAssets;
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
use Extended\ACF\Fields\Text;

class StepBlock extends AbstractBlock
{
	use EnqueuesBlockAssets;

	public static ?string $slug = 'step';
	public static ?string $title = 'Étapes';
	public static ?string $icon = 'list-view';

	public const string FIELD_STEPS = 'steps';
	public const string FIELD_STEP_TITLE = 'title';
	public const string FIELD_STEP_CONTENT = 'content';
	public const string FIELD_STEP_IMG = 'img';

	public function getFields(): ?iterable
	{
		yield from ContentTab::make()->fields([
			UptitleField::make(),
			HeadingField::make(HeadingField::LABEL, HeadingField::NAME, null, 'h2')->required(),
			ButtonField::make(),
			Repeater::make("Étapes", self::FIELD_STEPS)
				->fields([
					UptitleField::make()->required(),
					Text::make("Titre de l'étape", self::FIELD_STEP_TITLE)->required(),
					WysiwygField::minimal("Contenu de l'étape", self::FIELD_STEP_CONTENT)->required(),
					Image::make("Image de l'étape", self::FIELD_STEP_IMG)
						->required(),
				])
				->collapsed(self::FIELD_STEP_TITLE)
				->layout('block')
				->minRows(3)
				->maxRows(3),
		]);

		yield from LayoutTab::make()->fields([
			LayoutField::margin(),
		]);
	}


	public function renderBlockCallback(): void
	{
		$this->enqueueBlockScript('steps', withCss: true);
	}
}
