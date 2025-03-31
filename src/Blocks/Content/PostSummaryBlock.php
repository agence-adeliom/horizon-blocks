<?php

declare(strict_types=1);

namespace Adeliom\HorizonBlocks\Blocks\Content;

use Adeliom\HorizonTools\Blocks\AbstractBlock;
use Adeliom\HorizonTools\Fields\Choices\TrueFalseField;
use Adeliom\HorizonTools\Services\BudService;
use Extended\ACF\ConditionalLogic;
use Extended\ACF\Fields\Text;

class PostSummaryBlock extends AbstractBlock
{
	public static ?string $slug = 'post-summary';
	public static ?string $title = 'Sommaire article';
	public static ?string $mode = 'preview';
	public static ?string $description = 'Donne un aperçu des sections de l’article afin de faciliter la navigation.';

	public const FIELD_IS_TOP = 'top';
	public const FIELD_TITLE = 'title';

	// Value used to scroll the page to the top of the block corresponding to title in the summary
	public const SCROLL_OFFSET = 100;

	public function getFields(): ?iterable
	{
		yield TrueFalseField::make(__('Est-ce la borne supérieure ?'), self::FIELD_IS_TOP)
			->default(true);

		yield Text::make(__('Titre'), self::FIELD_TITLE)->conditionalLogic([
			ConditionalLogic::where(self::FIELD_IS_TOP, '==', 1)
		]);
	}

	public function addToContext(): array
	{
		return [];
	}

	public function renderBlockCallback(): void
	{
		if ($postSummaryJs = BudService::getUrl('post-summary.js')) {
			wp_enqueue_script('post-summary-block', $postSummaryJs);
		}
	}
}
