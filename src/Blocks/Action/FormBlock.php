<?php

declare(strict_types=1);

namespace Adeliom\HorizonBlocks\Blocks\Action;

use Adeliom\HorizonTools\Blocks\AbstractBlock;
use Adeliom\HorizonTools\Fields\Layout\LayoutField;
use Adeliom\HorizonTools\Fields\OfferField;
use Adeliom\HorizonTools\Fields\Tabs\ContentTab;
use Adeliom\HorizonTools\Fields\Tabs\LayoutTab;
use Adeliom\HorizonTools\Fields\Text\HeadingField;
use Adeliom\HorizonTools\Fields\Select\FormField;
use Extended\ACF\ConditionalLogic;
use Extended\ACF\Fields\Image;
use Extended\ACF\Fields\RadioButton;
use Extended\ACF\Fields\Text;

class FormBlock extends AbstractBlock
{
	public static ?string $slug = 'form';
	public static ?string $title = 'Formulaire';
	public static ?string $description = "Peut servir de formulaire de contact, d'inscription, demande de devis ou encore d'information.";
    public static ?string $icon = 'feedback';

	public const string FIELD_DESC = "desc";
	public const string FIELD_POSITION = "position";
	public const string FIELD_POS_LEFT = "left";
	public const string FIELD_POS_CENTER = "center";
	public const string FIELD_BG_TYPE = 'bg-type';
	public const string FIELD_BG_IMAGE = 'bg-image';
	public const string BG_COLOR_TYPE = "bg-color-type";
	public const string BG_IMAGE_TYPE = "bg-image-type";

	public function getFields(): ?iterable
	{
		yield from ContentTab::make()->fields([
			HeadingField::make()->required(),
			Text::make('Description', self::FIELD_DESC),
			OfferField::make(),
			FormField::selectGravityForm(),
		]);

		yield from LayoutTab::make()->fields([
			LayoutField::margin(),
			RadioButton::make('Position', self::FIELD_POSITION)
				->choices([
					self::FIELD_POS_LEFT => 'Gauche',
					self::FIELD_POS_CENTER => 'Centre',
				])
				->default(self::FIELD_POS_LEFT),

			RadioButton::make("Type de fond", self::FIELD_BG_TYPE)
				->choices([
					self::BG_COLOR_TYPE => 'Couleur',
					self::BG_IMAGE_TYPE => 'Image',
				]),

			Image::make("Image de fond", self::FIELD_BG_IMAGE)
				->conditionalLogic([
					ConditionalLogic::where(self::FIELD_BG_TYPE, '==', self::BG_IMAGE_TYPE),
				]),

		]);
	}
}