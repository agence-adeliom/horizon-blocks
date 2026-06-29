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

class CatchphraseBlock extends AbstractBlock
{
    public static ?string $slug = 'catchphrase';
    public static ?string $mode = 'preview';
    public static ?string $icon = 'format-quote';

    public static function getTitle(): ?string
    {
        return __('Accroche', 'horizon-blocks');
    }

    public static function getDescription(): ?string
    {
        return __("Met en avant une phrase d'accroche pour capter l'attention.", 'horizon-blocks');
    }

    public const string FIELD_BG = 'bg';
    public const string FIELD_BG_TYPE = 'bgType';
    public const string FIELD_BG_IMAGE = 'bgImage';
    public const string FIELD_BG_COLOR = 'bgColor';
    public const string BG_COLOR_TYPE = "bgColorType";
    public const string BG_IMAGE_TYPE = "bgImageType";

    public function getFields(): ?iterable
    {
        yield from ContentTab::make()->fields([
            HeadingField::make(HeadingField::LABEL, HeadingField::NAME, null, 'h2')->required(),
        ]);

        yield from LayoutTab::make()->fields([
            Group::make(__("Fond", 'horizon-blocks'), self::FIELD_BG)
                ->fields([
                    RadioButton::make(__("Type de fond", 'horizon-blocks'), self::FIELD_BG_TYPE)
                        ->choices([
                            self::BG_COLOR_TYPE => __('Couleur', 'horizon-blocks'),
                            self::BG_IMAGE_TYPE => __('Image', 'horizon-blocks'),
                        ]),

                    Image::make(__("Image de fond", 'horizon-blocks'), self::FIELD_BG_IMAGE)
                        ->conditionalLogic([
                            ConditionalLogic::where(self::FIELD_BG_TYPE, '==', self::BG_IMAGE_TYPE),
                        ]),

                    RadioButton::make(__("Couleur de fond", 'horizon-blocks'), self::FIELD_BG_COLOR)
                        ->choices([
                            'bg-neutral-100'  => __('Gris', 'horizon-blocks'),
                            'bg-color-01-100' => __('Couleur', 'horizon-blocks'),
                        ])
                        ->conditionalLogic([
                            ConditionalLogic::where(self::FIELD_BG_TYPE, '==', self::BG_COLOR_TYPE),
                        ]),
                ]),

            LayoutField::margin(),
        ]);
    }
}
