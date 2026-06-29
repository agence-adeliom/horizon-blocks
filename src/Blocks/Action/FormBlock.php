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
	public static ?string $icon = 'feedback';

	public static function getTitle(): ?string
	{
		return __('Formulaire', 'horizon-blocks');
	}

	public static function getDescription(): ?string
	{
		return __("Peut servir de formulaire de contact, d'inscription, demande de devis ou encore d'information.", 'horizon-blocks');
	}

	public const string FIELD_DESC = "desc";
	public const string FIELD_POSITION = "position";
	public const string FIELD_POS_LEFT = "left";
	public const string FIELD_POS_CENTER = "center";
	public const string FIELD_BG_TYPE = 'bgType';
	public const string FIELD_BG_IMAGE = 'bgImage';
	public const string BG_COLOR_TYPE = "bgColorType";
	public const string BG_IMAGE_TYPE = "bgImageType";

	public function getFields(): ?iterable
	{
		yield from ContentTab::make()->fields([
			HeadingField::make(HeadingField::LABEL, HeadingField::NAME, null, 'h2')->required(),
			Text::make(__('Description', 'horizon-blocks'), self::FIELD_DESC),
			OfferField::make(),
			FormField::selectGravityForm(),
		]);

		yield from LayoutTab::make()->fields([
			LayoutField::margin(),
			RadioButton::make(__('Position', 'horizon-blocks'), self::FIELD_POSITION)
				->choices([
					self::FIELD_POS_LEFT => __('Gauche', 'horizon-blocks'),
					self::FIELD_POS_CENTER => __('Centre', 'horizon-blocks'),
				])
				->default(self::FIELD_POS_LEFT),

			RadioButton::make(__("Type de fond", 'horizon-blocks'), self::FIELD_BG_TYPE)
				->choices([
					self::BG_COLOR_TYPE => __('Couleur', 'horizon-blocks'),
					self::BG_IMAGE_TYPE => __('Image', 'horizon-blocks'),
				]),

			Image::make(__("Image de fond", 'horizon-blocks'), self::FIELD_BG_IMAGE)
				->conditionalLogic([
					ConditionalLogic::where(self::FIELD_BG_TYPE, '==', self::BG_IMAGE_TYPE),
				]),

		]);
	}
}
