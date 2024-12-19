<?php

declare(strict_types=1);

namespace Adeliom\HorizonBlocks\Blocks\Hero;

use Adeliom\HorizonTools\Blocks\AbstractBlock;
use Adeliom\HorizonTools\Fields\Layout\LayoutField;
use Adeliom\HorizonTools\Fields\OfferField;
use Adeliom\HorizonTools\Fields\Select\FormField;
use Adeliom\HorizonTools\Fields\Tabs\ContentTab;
use Adeliom\HorizonTools\Fields\Tabs\LayoutTab;
use Adeliom\HorizonTools\Fields\Text\HeadingField;
use Adeliom\HorizonTools\Fields\Text\WysiwygField;
use Extended\ACF\Fields\Text;

class HeroForm extends AbstractBlock
{
	public const string FORM_TITLE = "form-title";
	final public const string FIELD_DESC = "desc";
	public static ?string $slug = 'hero-form';
	public static ?string $title = 'Haut de page avec formulaire';
	public static ?string $mode = 'preview';
	public static string $category = 'hero';

	public function getFields(): ?iterable
	{
		yield from ContentTab::make()->fields([
			HeadingField::make()->required(),
			WysiwygField::make(),
			OfferField::make(),
			HeadingField::make("Titre au dessus du formulaire", self::FORM_TITLE)->required(),
			Text::make("Description du formulaire", self::FIELD_DESC),
			FormField::selectGravityForm(),
		]);

		yield from LayoutTab::make()->fields([
			LayoutField::margin(),
			LayoutField::choicesBackgroundType(),
		]);
	}
}
