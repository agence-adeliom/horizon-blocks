<?php

declare(strict_types=1);

namespace Adeliom\HorizonBlocks\Blocks\Hero;

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

class HeroBlock extends AbstractBlock
{
    public const string FIELD_MAIN_IMAGE = "mainImage";
    public static ?string $slug = 'hero';
    public static ?string $icon = 'admin-home';
    public static string $category = 'hero';

    public static function getTitle(): ?string
    {
        return __('Haut de page', 'horizon-blocks');
    }

    public static function getDescription(): ?string
    {
        return __("Premier élément de la page, offrant une introduction percutante.", 'horizon-blocks');
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
            Image::make(__("Image principale", 'horizon-blocks'), self::FIELD_MAIN_IMAGE),
        ]);

        yield from LayoutTab::make()->fields([
            LayoutField::darkMode(),
            LayoutField::margin(),
        ]);
    }
}
