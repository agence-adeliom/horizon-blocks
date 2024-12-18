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
use App\PostTypes\FAQ;
use Extended\ACF\Fields\Image;
use Extended\ACF\Fields\Relationship;

class FaqBlock extends AbstractBlock
{
	public static ?string $slug = 'faq';
	public static ?string $title = 'FAQ';
	public static ?string $description = "Présente des questions souvent posées par les utilisateurices, ainsi que des réponses rapides.";

	public const string FIELDS_IMG = 'img';
	public const string FIELDS_QUESTIONS = 'questions';

	public function getFields(): ?iterable
	{
		yield from ContentTab::make()->fields([
			Image::make(__('Petite image'), self::FIELDS_IMG),
			UptitleField::make(),
			HeadingField::make()->required(),
			WysiwygField::minimal(),
			ButtonField::group(),
			Relationship::make(__('Liste de questions'), self::FIELDS_QUESTIONS)
				->minPosts(2)
				->maxPosts(5)
				->postTypes([FAQ::$slug]),

		]);

		yield from LayoutTab::make()->fields([
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
