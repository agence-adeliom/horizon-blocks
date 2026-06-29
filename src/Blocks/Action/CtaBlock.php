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
	public static ?string $icon = 'megaphone';

	public static function getTitle(): ?string
	{
		return __("Section call-to-action", 'horizon-blocks');
	}

	public static function getDescription(): ?string
	{
		return __("Incite l'utilisateur à effectuer une action spécifique dans un objectif de conversion.", 'horizon-blocks');
	}

	public const string FIELD_APPARENCE = "appearance";
	public const string FIELD_APPARENCE_DEFAULT = "default";
	public const string FIELD_APPARENCE_FULL_WIDTH = "fullWidth";

	public function getFields(): ?iterable
	{
		yield from ContentTab::make()->fields([
			HeadingField::make(HeadingField::LABEL, HeadingField::NAME, null, 'h2')->required(),
			WysiwygField::minimal()->helperText(__("1 ou 2 phrases maximum recommandées.", 'horizon-blocks')),
			ButtonField::types(),
		]);

		yield from LayoutTab::make()->fields([
			LayoutField::margin(),
			RadioButton::make(__("Apparence", 'horizon-blocks'), self::FIELD_APPARENCE)
				->choices([
					self::FIELD_APPARENCE_DEFAULT => __("Défaut", 'horizon-blocks'),
					self::FIELD_APPARENCE_FULL_WIDTH => __("Pleine largeur", 'horizon-blocks'),
				])
				->default("default")
				->required(),
		]);
	}
}
