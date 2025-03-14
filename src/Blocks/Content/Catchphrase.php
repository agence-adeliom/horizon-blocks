<?php

declare(strict_types=1);

namespace Adeliom\HorizonBlocks\Blocks\Content;

use Adeliom\HorizonTools\Blocks\AbstractBlock;
use Adeliom\HorizonTools\Fields\Layout\LayoutField;
use Adeliom\HorizonTools\Fields\Tabs\ContentTab;
use Adeliom\HorizonTools\Fields\Tabs\LayoutTab;
use Adeliom\HorizonTools\Fields\Text\HeadingField;
use Extended\ACF\ConditionalLogic;
use Extended\ACF\Fields\Group;
use Extended\ACF\Fields\Image;
use Extended\ACF\Fields\RadioButton;

class Catchphrase extends AbstractBlock
{
    public static ?string $slug = 'catchphrase';
    public static ?string $title = 'Accroche';
    public static ?string $mode = 'preview';


    final public const string FIELD_BG = 'bg';
    final public const string FIELD_BG_TYPE = 'bg-type';
    final public const string FIELD_BG_IMAGE = 'bg-image';
    final public const string FIELD_BG_COLOR = 'bg-color';
    final public const string BG_COLOR_TYPE = "bg-color-type";
    final public const string BG_IMAGE_TYPE = "bg-image-type";

    public function getFields(): ?iterable
    {
        yield from ContentTab::make()->fields([
            HeadingField::make(HeadingField::LABEL, HeadingField::NAME, null, 'h2')->required(),
        ]);

        yield from LayoutTab::make()->fields([
            Group::make("Fond", self::FIELD_BG)
                ->fields([
                    RadioButton::make("Type de fond", self::FIELD_BG_TYPE)
                        ->choices([
                            self::BG_COLOR_TYPE => 'Couleur',
                            self::BG_IMAGE_TYPE => 'Image',
                        ]),

                    Image::make("Image de fond", self::FIELD_BG_IMAGE)
                        ->conditionalLogic([
                            ConditionalLogic::where(self::FIELD_BG_TYPE, '==', self::BG_IMAGE_TYPE),
                        ]),

                    RadioButton::make("Couleur de fond", self::FIELD_BG_COLOR)
                        ->choices([
                            'bg-neutral-100'  => 'Gris',
                            'bg-color-01-100' => 'Couleur',
                        ])
                        ->conditionalLogic([
                            ConditionalLogic::where(self::FIELD_BG_TYPE, '==', self::BG_COLOR_TYPE),
                        ]),
                ]),

            LayoutField::margin(),
        ]);
    }
}
