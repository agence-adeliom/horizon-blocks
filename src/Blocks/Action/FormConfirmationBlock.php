<?php

declare(strict_types=1);

namespace Adeliom\HorizonBlocks\Blocks\Action;

use Adeliom\HorizonTools\Blocks\AbstractBlock;
use Adeliom\HorizonTools\Fields\Buttons\ButtonField;
use Adeliom\HorizonTools\Fields\Layout\LayoutField;
use Adeliom\HorizonTools\Fields\Tabs\ContentTab;
use Adeliom\HorizonTools\Fields\Tabs\LayoutTab;
use Adeliom\HorizonTools\Fields\Text\HeadingField;
use Adeliom\HorizonTools\Fields\Text\IconField;
use Adeliom\HorizonTools\Fields\Text\WysiwygField;

class FormConfirmationBlock extends AbstractBlock
{
	public static ?string $slug = 'form-confirmation';
	public static ?string $title = 'Validation de formulaire';

	public function getFields(): ?iterable
	{
		yield from ContentTab::make()->fields([
			IconField::make(),
			HeadingField::make()->required(),
			WysiwygField::make(),
			ButtonField::group(),
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