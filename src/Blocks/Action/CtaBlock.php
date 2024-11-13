<?php

declare(strict_types=1);

namespace Adeliom\HorizonBlocks\Blocks\Action;

use Adeliom\HorizonTools\Blocks\AbstractBlock;
use Adeliom\HorizonTools\Fields\Buttons\ButtonField;
use Adeliom\HorizonTools\Fields\Layout\LayoutField;
use Adeliom\HorizonTools\Fields\Tabs\ContentTab;
use Adeliom\HorizonTools\Fields\Tabs\LayoutTab;
use Adeliom\HorizonTools\Fields\Text\HeadingField;
use Adeliom\HorizonTools\Fields\Text\WysiwygField;
use Extended\ACF\Fields\RadioButton;

class CtaBlock extends AbstractBlock
{
	public static ?string $slug = 'cta';
	public static ?string $title = "Section call-to-action";
	public static ?string $description = "Incite l'utilisateur à effectuer une action spécifique dans un objectif de conversion.";

	public const string FIELD_APPARENCE = "appearance";
	public const string FIELD_APPARENCE_DEFAULT = "default";
	public const string FIELD_APPARENCE_FULL_WIDTH = "full-width";

	public function getFields(): ?iterable
	{
		yield from ContentTab::make()->fields([
			HeadingField::make()->required(),
			WysiwygField::minimal()->helperText("1 ou 2 phrases maximum recommandées."),
			ButtonField::types(),
		]);

		yield from LayoutTab::make()->fields([
			LayoutField::margin(),
			RadioButton::make(__("Apparence"), self::FIELD_APPARENCE)
				->choices([
					self::FIELD_APPARENCE_DEFAULT => "Défaut",
					self::FIELD_APPARENCE_FULL_WIDTH => "Pleine largeur",
				])
				->default("default")
				->required(),
		]);
	}
}
