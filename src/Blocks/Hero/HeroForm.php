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
	public const string FORM_TITLE = "formTitle";
	public const string FIELD_DESC = "desc";
	public static ?string $slug = 'hero-form';
	public static ?string $mode = 'preview';
	public static string $category = 'hero';
	public static ?string $icon = 'forms';

	public static function getTitle(): ?string
	{
		return __('Haut de page avec formulaire', 'horizon-blocks');
	}

	public static function getDescription(): ?string
	{
		return __('Haut de page combinant une introduction et un formulaire pour favoriser la conversion.', 'horizon-blocks');
	}

	public function getFields(): ?iterable
	{
		yield from ContentTab::make()->fields([
			HeadingField::make(HeadingField::LABEL, HeadingField::NAME, null, 'h1')->required(),
			WysiwygField::make(),
			OfferField::make(),
			HeadingField::make(__("Titre au dessus du formulaire", 'horizon-blocks'), self::FORM_TITLE)->required(),
			Text::make(__("Description du formulaire", 'horizon-blocks'), self::FIELD_DESC),
			FormField::selectGravityForm(),
		]);

		yield from LayoutTab::make()->fields([
			LayoutField::margin(),
			LayoutField::choicesBackgroundType(),
		]);
	}
}
