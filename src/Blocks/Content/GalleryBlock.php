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
use Adeliom\HorizonTools\Services\Compilation\CompilationService;
use Extended\ACF\Fields\Gallery;


class GalleryBlock extends AbstractBlock
{
    public const string FIELD_GALLERY = "gallery";
    public static ?string $slug = 'gallery';
    public static ?string $title = 'Galerie';
    public static ?string $mode = 'preview';
    public static ?string $icon = 'format-gallery';
    public static ?string $description = "Présente des photos ou visuels organisés sous forme de galerie.";

    public function getFields(): ?iterable
    {
        yield from ContentTab::make()->fields([
            UptitleField::make(),
            HeadingField::make(HeadingField::LABEL, HeadingField::NAME, null, 'h1')->required(),
            WysiwygField::simple(),
            ButtonField::group(),
        ]);

        yield from MediaTab::make()->fields([
            Gallery::make("Image principale", self::FIELD_GALLERY),
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
		switch (true) {
			case CompilationService::shouldUseBud():
				CompilationService::getAsset('gallery.js')?->enqueue();
				break;
			default:
				CompilationService::getAsset('resources/scripts/blocks/gallery.ts')?->enqueue();
				break;
		}
    }
}