<?php

declare(strict_types=1);

namespace Adeliom\HorizonBlocks\Blocks\Content;

use Adeliom\HorizonTools\Blocks\AbstractBlock;
use Adeliom\HorizonTools\Fields\Buttons\ButtonField;
use Adeliom\HorizonTools\Fields\Layout\LayoutField;
use Adeliom\HorizonTools\Fields\Tabs\ContentTab;
use Adeliom\HorizonTools\Fields\Tabs\LayoutTab;
use Adeliom\HorizonTools\Fields\Tabs\MediaTab;
use Adeliom\HorizonTools\Fields\Text\HeadingField;
use Adeliom\HorizonTools\Fields\Text\UptitleField;
use Adeliom\HorizonTools\Fields\Text\WysiwygField;
use Extended\ACF\Fields\Image;

class HighlightBlock extends AbstractBlock
{
    public const string FIELD_MAIN_IMAGE = "main_image";
    public static ?string $slug = 'highlight';
    public static ?string $icon = 'lightbulb';
    public static ?string $title = 'Mise en avant';
    public static ?string $mode = 'preview';
    public static ?string $description = 'Valorise un contenu spécifique comme une offre, un produit ou une information, afin d’attirer l’attention et guider vers le bouton CTA.';

    public function getFields(): ?iterable
    {
        yield from ContentTab::make()->fields([
            UptitleField::make(),
            HeadingField::make(HeadingField::LABEL, HeadingField::NAME, null, 'h1')->required(),
            WysiwygField::simple(),
            ButtonField::group(),
        ]);
        yield from MediaTab::make()->fields([
            Image::make("Image principale", self::FIELD_MAIN_IMAGE),
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