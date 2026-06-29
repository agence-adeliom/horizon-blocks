<?php

declare(strict_types=1);

namespace Adeliom\HorizonBlocks\Blocks\Content;

use Adeliom\HorizonBlocks\Concerns\EnqueuesBlockAssets;
use Adeliom\HorizonTools\Blocks\AbstractBlock;
use Adeliom\HorizonTools\Fields\Buttons\ButtonField;
use Adeliom\HorizonTools\Fields\Layout\LayoutField;
use Adeliom\HorizonTools\Fields\Tabs\ContentTab;
use Adeliom\HorizonTools\Fields\Tabs\LayoutTab;
use Adeliom\HorizonTools\Fields\Tabs\MediaTab;
use Adeliom\HorizonTools\Fields\Text\HeadingField;
use Adeliom\HorizonTools\Fields\Text\UptitleField;
use Adeliom\HorizonTools\Fields\Text\WysiwygField;
use Extended\ACF\Fields\Gallery;


class PhotoCarouselBlock extends AbstractBlock
{
    use EnqueuesBlockAssets;

    public const string FIELD_GALLERY = "gallery";
    public static ?string $slug = 'photo-carousel';
    public static ?string $mode = 'preview';
    public static ?string $icon = 'format-image';

    public static function getTitle(): ?string
    {
        return __('Carrousel photo', 'horizon-blocks');
    }

    public static function getDescription(): ?string
    {
        return __("Présente une série de photos dans un carrousel plein cadre, avec légende et navigation.", 'horizon-blocks');
    }

    public function getFields(): ?iterable
    {
        yield from ContentTab::make()->fields([
            UptitleField::make(),
            HeadingField::make(HeadingField::LABEL, HeadingField::NAME, null, 'h1')->required(),
            WysiwygField::simple(),
            ButtonField::group(),
        ]);

        yield from MediaTab::make()->fields([
            Gallery::make(__("Photos", 'horizon-blocks'), self::FIELD_GALLERY)->required(),
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
        $this->enqueueBlockScript('photo-carousel');
    }
}
