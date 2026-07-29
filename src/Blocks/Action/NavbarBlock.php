<?php

declare(strict_types=1);

namespace Adeliom\HorizonBlocks\Blocks\Action;

use Adeliom\HorizonBlocks\Concerns\EnqueuesBlockAssets;
use Adeliom\HorizonTools\Blocks\AbstractBlock;
use Adeliom\HorizonTools\Fields\Buttons\ButtonField;
use Adeliom\HorizonTools\Fields\Tabs\ContentTab;
use Extended\ACF\Fields\Repeater;

class NavbarBlock extends AbstractBlock
{
    use EnqueuesBlockAssets;

    public const string FIELD_ANCHORS = 'anchors';

    /** Nom du groupe produit par ButtonField, utilisé pour l'ancre comme pour le CTA. */
    public const string FIELD_BUTTON = ButtonField::BUTTON;

    public static ?string $slug = 'navbar';
    public static ?string $mode = 'preview';

    /**
     * Icône d'ancre (Font Awesome). Le jeu de dashicons de WordPress n'a pas d'équivalent :
     * ses pictos de navigation évoquent tous un menu, pas une ancre dans la page.
     */
    public static ?string $icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><path d="M320 128C302.3 128 288 142.3 288 160C288 177.7 302.3 192 320 192C337.7 192 352 177.7 352 160C352 142.3 337.7 128 320 128zM224 160C224 107 267 64 320 64C373 64 416 107 416 160C416 201.8 389.3 237.4 352 250.5L352 508.4C414.9 494.1 462.2 438.7 463.9 371.9L447.8 386C437.8 394.7 422.7 393.7 413.9 383.7C405.1 373.7 406.2 358.6 416.2 349.8L480.2 293.8C489.2 285.9 502.8 285.9 511.8 293.8L575.8 349.8C585.8 358.5 586.8 373.7 578.1 383.7C569.4 393.7 554.2 394.7 544.2 386L528 371.9C525.9 485 433.6 576 320 576C206.4 576 114.1 485 112 371.9L95.8 386.1C85.8 394.8 70.7 393.8 61.9 383.8C53.1 373.8 54.2 358.7 64.2 349.9L128.2 293.9C137.2 286 150.8 286 159.8 293.9L223.8 349.9C233.8 358.6 234.8 373.8 226.1 383.8C217.4 393.8 202.2 394.8 192.2 386.1L176.1 372C177.9 438.8 225.2 494.2 288 508.5L288 250.6C250.7 237.4 224 201.9 224 160.1z"/></svg>';

    public static function getTitle(): ?string
    {
        return __('Barre de navigation interne (ancres)', 'horizon-blocks');
    }

    public static function getDescription(): ?string
    {
        return __(
            'Barre de liens collante, affichée en desktop uniquement, qui suit le défilement et renvoie vers les ' .
                "sections de la page. Le lien correspondant à la section à l'écran est mis en avant automatiquement. " .
                "Un bouton d'appel à l'action ferme la barre à droite. Si le projet a un header collant, lui donner " .
                'sa hauteur via la variable CSS --navbar-anchors-offset (voir styles/blocks/navbar.css).',
            'horizon-blocks',
        );
    }

    public function getFields(): ?iterable
    {
        yield from ContentTab::make()->fields([
            Repeater::make(__("Liste d'ancres", 'horizon-blocks'), self::FIELD_ANCHORS)
                ->fields([ButtonField::make(label: __('Ancre', 'horizon-blocks'))->required()])
                ->helperText(
                    __(
                        'Chaque lien doit pointer vers une ancre de la page, sous la forme <code>#mon-ancre</code>. ' .
                            "L'identifiant se déclare sur le bloc de destination, dans « Avancé › Ancre HTML » de sa " .
                            'barre latérale. Glisser-déposer les lignes pour définir leur ordre.',
                        'horizon-blocks',
                    ),
                )
                ->minRows(1)
                ->button(__('Ajouter une ancre', 'horizon-blocks')),
            ButtonField::make(label: __('Bouton', 'horizon-blocks'))->required(),
        ]);
    }

    public function addToContext(): array
    {
        return [];
    }

    public function renderBlockCallback(): void
    {
        $this->enqueueBlockScript('navbar');
        $this->enqueueBlockStyle('navbar');
    }
}
