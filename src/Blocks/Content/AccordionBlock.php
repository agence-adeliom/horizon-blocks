<?php

declare(strict_types=1);

namespace Adeliom\HorizonBlocks\Blocks\Content;

use Adeliom\HorizonBlocks\Services\FaqSchemaBuilder;
use Adeliom\HorizonTools\Blocks\AbstractBlock;
use Adeliom\HorizonTools\Fields\Buttons\ButtonField;
use Adeliom\HorizonTools\Fields\Choices\TrueFalseField;
use Adeliom\HorizonTools\Fields\Layout\LayoutField;
use Adeliom\HorizonTools\Fields\Tabs\ContentTab;
use Adeliom\HorizonTools\Fields\Tabs\LayoutTab;
use Adeliom\HorizonTools\Fields\Text\HeadingField;
use Adeliom\HorizonTools\Fields\Text\UptitleField;
use Adeliom\HorizonTools\Fields\Text\WysiwygField;
use Adeliom\HorizonTools\Services\SeoService;
use Extended\ACF\Fields\Image;
use Extended\ACF\Fields\Repeater;
use Extended\ACF\Fields\Text;

class AccordionBlock extends AbstractBlock
{
	public static ?string $slug = 'accordion';
	public static ?string $icon = 'list-view';

	public static function getTitle(): ?string
	{
		return __('Accordéon', 'horizon-blocks');
	}

	public static function getDescription(): ?string
	{
		return __("Présente une liste d'éléments repliables, chacun dépliant son contenu au clic.", 'horizon-blocks');
	}

	public const string FIELD_IMG = 'img';
	public const string FIELD_ITEMS = 'items';
	public const string FIELD_ITEM_TITLE = 'itemTitle';
	public const string FIELD_ITEM_CONTENT = 'itemContent';
	public const string FIELD_SCHEMA_ORG = 'schemaOrg';

	public function getFields(): ?iterable
	{
		yield from ContentTab::make()->fields([
			Image::make(__('Petite image', 'horizon-blocks'), self::FIELD_IMG),
			UptitleField::make(),
			HeadingField::make(HeadingField::LABEL, HeadingField::NAME, null, 'h2')->required(),
			WysiwygField::minimal(),
			ButtonField::group(),
			Repeater::make(__('Éléments', 'horizon-blocks'), self::FIELD_ITEMS)
				->fields([
					Text::make(__('Titre', 'horizon-blocks'), self::FIELD_ITEM_TITLE)
						->maxLength(150)
						->helperText(__('Maximum 150 caractères', 'horizon-blocks'))
						->required(),
					WysiwygField::minimal(__('Contenu', 'horizon-blocks'), self::FIELD_ITEM_CONTENT)
						->required(),
				])
				->layout('row')
				->collapsed(self::FIELD_ITEM_TITLE)
				->minRows(2)
				->button(__('Ajouter un élément', 'horizon-blocks')),
			TrueFalseField::make(__('Activer le balisage schema.org (FAQ)', 'horizon-blocks'), self::FIELD_SCHEMA_ORG)
				->helperText(__("À n'activer que s'il s'agit d'une véritable FAQ. Sur un accordéon générique, ce balisage peut nuire au référencement.", 'horizon-blocks'))
				->default(false),
		]);

		yield from LayoutTab::make()->fields([
			LayoutField::margin(),
		]);
	}

	public function addToContext(): array
	{
		$fields = get_fields();

		if (empty($fields[self::FIELD_SCHEMA_ORG]) || !SeoService::isCurrentPageIndexed()) {
			return ['structuredData' => null];
		}

		$items = [];

		foreach ($fields[self::FIELD_ITEMS] ?? [] as $item) {
			$items[] = [
				'title' => $item[self::FIELD_ITEM_TITLE] ?? null,
				'content' => $item[self::FIELD_ITEM_CONTENT] ?? null,
			];
		}

		return [
			'structuredData' => FaqSchemaBuilder::build($items, get_bloginfo('name')),
		];
	}

	public function renderBlockCallback(): void
	{
		return;
	}
}
