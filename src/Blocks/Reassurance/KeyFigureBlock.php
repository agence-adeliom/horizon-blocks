<?php

declare(strict_types=1);

namespace Adeliom\HorizonBlocks\Blocks\Reassurance;

use Adeliom\HorizonTools\Blocks\AbstractBlock;
use Adeliom\HorizonTools\Fields\Layout\LayoutField;
use Adeliom\HorizonTools\Fields\Tabs\ContentTab;
use Adeliom\HorizonTools\Fields\Tabs\LayoutTab;
use Adeliom\HorizonTools\Fields\Text\FontAwesomeIcon;
use Adeliom\HorizonTools\Fields\Text\HeadingField;
use Adeliom\HorizonTools\Fields\Text\UptitleField;
use Adeliom\HorizonTools\Fields\Text\WysiwygField;
use Extended\ACF\Fields\ButtonGroup;
use Extended\ACF\Fields\Repeater;
use Extended\ACF\Fields\Text;

class KeyFigureBlock extends AbstractBlock
{
    public const string FIELD_ITEMS = 'items';
    public const string FIELD_ICON = 'icon';
    public const string FIELD_TITLE = 'title';
    public const string FIELD_DATA = 'data';
    public const string FIELD_TYPE = 'type';
    private const int TITLE_MAX_LENGTH = 100;
    public static ?string $slug = 'key-figure';
    public static ?string $icon = 'chart-bar';

    public static function getTitle(): ?string
    {
        return __('Chiffres clés', 'horizon-blocks');
    }

    public static function getDescription(): ?string
    {
        return __("Chiffres percutants destinés à renforcer la crédibilité ou souligner des données marquantes.", 'horizon-blocks');
    }

    public function getFields(): ?iterable
    {
        yield from ContentTab::make()->fields([
            UptitleField::make(),
            HeadingField::make(HeadingField::LABEL, HeadingField::NAME, null, 'h2')->required(),
            WysiwygField::minimal(),
            Repeater::make(__('Éléments', 'horizon-blocks'), self::FIELD_ITEMS)
                ->minRows(3)
                ->maxRows(4)
                ->helperText(__("Pour garantir une mise en page cohérente et harmonieuse sur le site, il est recommandé de remplir les mêmes champs pour chaque élément de ce bloc. Par exemple, si vous renseignez les champs 'Icône' et 'Donnée' pour un élément, assurez-vous de le faire pour tous les autres éléments. Cela permettra d'optimiser l'affichage de vos informations.", 'horizon-blocks'))
                ->layout('block')
                ->collapsed(self::FIELD_TITLE)
                ->fields([
                    FontAwesomeIcon::make(__('Icône', 'horizon-blocks'), self::FIELD_ICON)->format('object'),
                    Text::make(__('Donnée', 'horizon-blocks'), self::FIELD_DATA),
                    Text::make(__('Titre', 'horizon-blocks'), self::FIELD_TITLE)
                        ->maxLength(self::TITLE_MAX_LENGTH)
                        ->helperText(sprintf(__('Maximum %s caractères', 'horizon-blocks'), self::TITLE_MAX_LENGTH)),
                ]),
        ]);

        yield from LayoutTab::make()->fields([
            LayoutField::margin(),
            ButtonGroup::make(__('Type', 'horizon-blocks'), self::FIELD_TYPE)
                ->choices([
                    'default' => __('Par défaut', 'horizon-blocks'),
                    'with_bg' => __('Avec fond', 'horizon-blocks'),
                    'framed'  => __('Cartouches encadrées', 'horizon-blocks'),
                ])
                ->default('default'),
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
