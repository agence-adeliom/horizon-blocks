<?php

declare(strict_types=1);

namespace Adeliom\HorizonBlocks\Blocks\Content;

use Adeliom\HorizonTools\Blocks\AbstractBlock;
use Adeliom\HorizonTools\Fields\Layout\LayoutField;
use Adeliom\HorizonTools\Fields\Tabs\ContentTab;
use Adeliom\HorizonTools\Fields\Tabs\LayoutTab;
use Adeliom\HorizonTools\Fields\Text\HeadingField;
use Adeliom\HorizonTools\Fields\Text\UptitleField;
use Adeliom\HorizonTools\Fields\Text\WysiwygField;
use Extended\ACF\Fields\File;
use Extended\ACF\Fields\Repeater;
use Extended\ACF\Fields\Text;

class DocumentsBlock extends AbstractBlock
{
    public static ?string $slug = 'documents';
    public static ?string $title = 'Documents';
    public static ?string $mode = 'preview';

    public const string FIELD_DOCUMENTS = 'documents';

    public function getFields(): ?iterable
    {
        yield from ContentTab::make()->fields([
            UptitleField::make(),
            HeadingField::make(HeadingField::LABEL, HeadingField::NAME, null, 'h2'),
            WysiwygField::minimal(),
            Repeater::make("Documents", self::FIELD_DOCUMENTS)
                ->fields([
                    Text::make("Titre du document", "title")->required(),
                    File::make("Document", "file")->required()
                ])
                ->layout('row')
                ->collapsed(HeadingField::NAME)
                ->minRows(1)
                ->maxRows(10),
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
