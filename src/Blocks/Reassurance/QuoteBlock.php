<?php

declare(strict_types=1);

namespace Adeliom\HorizonBlocks\Blocks\Reassurance;

use Adeliom\HorizonTools\Blocks\AbstractBlock;
use Adeliom\HorizonTools\Fields\Tabs\ContentTab;
use Adeliom\HorizonTools\Fields\Text\HeadingField;

class QuoteBlock extends AbstractBlock
{
	public static ?string $slug = 'quote';
	public static ?string $title = 'Citation';
	public static ?string $description = "Mise en avant d'une citation, un témoignage ou un extrait de texte.";
	public static ?string $mode = 'preview';
	public static string $category = 'reassurance';
    public static ?string $icon = 'format-quote';


	public function getFields(): ?iterable
	{
		yield from ContentTab::make()->fields([
			HeadingField::make()->required(),
		]);
	}
}