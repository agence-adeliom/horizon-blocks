<?php

declare(strict_types=1);

namespace Adeliom\HorizonBlocks\Blocks\Content;

use Adeliom\HorizonTools\Blocks\AbstractBlock;
use Adeliom\HorizonTools\Fields\Buttons\ButtonField;
use Adeliom\HorizonTools\Fields\Layout\LayoutField;
use Adeliom\HorizonTools\Fields\Medias\ImageField;
use Adeliom\HorizonTools\Fields\Tabs\ContentTab;
use Adeliom\HorizonTools\Fields\Tabs\LayoutTab;
use Adeliom\HorizonTools\Fields\Text\HeadingField;
use Adeliom\HorizonTools\Fields\Text\UptitleField;
use Adeliom\HorizonTools\Services\BudService;
use Extended\ACF\Fields\Image;
use Extended\ACF\Fields\Repeater;
use Extended\ACF\Fields\Text;
use Extended\ACF\Fields\Textarea;

class ArgumentBlock extends AbstractBlock
{
    public const string FIELD_ARGS = "args";
    public const string FIELD_ARG_TITLE = "arg_title";
    public const string FIELD_ARG_DESC = "arg_desc";
    public const string FIELD_ARG_IMG = "arg_img";
    public static ?string $slug = 'argument';
    public static ?string $title = 'Arguments';

    public function getFields(): ?iterable
    {
        yield from ContentTab::make()->fields([
            UptitleField::make(),
            HeadingField::make(HeadingField::LABEL, HeadingField::NAME, null, 'h2')->required(),
            Repeater::make(__("Liste d'arguments"), self::FIELD_ARGS)
                ->fields([
                    Text::make(__("Titre de l'argument"), self::FIELD_ARG_TITLE)->maxLength(100)->helperText(__("Maximum 100 caractères"))->required(),
                    Textarea::make(__("Description de l'argument"), self::FIELD_ARG_DESC)->maxLength(220)->helperText(__("Maximum 220 caractères"))->required(),
                    ImageField::make(__("Image de l'argument"), self::FIELD_ARG_IMG)->ratio(1000, 1000)->required(),
                ])
                ->collapsed(self::FIELD_ARG_TITLE)
                ->minRows(3)
                ->button(__("Ajouter un argument")),

            ButtonField::make()->required(),
        ]);

        yield from LayoutTab::make()->fields([
            LayoutField::margin()
        ]);
    }

    public function addToContext(): array
    {
        return [];
    }

    public function renderBlockCallback(): void
    {
        if ($argumentsCss = BudService::getUrl('arguments.css')) {
            wp_enqueue_style('arguments-block-css', $argumentsCss);
        }

        if ($argumentsJs = BudService::getUrl('arguments.js')) {
            wp_enqueue_script('arguments-block-js', $argumentsJs);
        }
    }
}
