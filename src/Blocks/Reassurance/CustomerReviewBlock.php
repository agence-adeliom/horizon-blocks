<?php

declare(strict_types=1);

namespace Adeliom\HorizonBlocks\Blocks\Reassurance;

use Adeliom\HorizonTools\Blocks\AbstractBlock;
use Adeliom\HorizonTools\Fields\Layout\LayoutField;
use Adeliom\HorizonTools\Fields\Tabs\ContentTab;
use Adeliom\HorizonTools\Fields\Tabs\LayoutTab;
use Adeliom\HorizonTools\Fields\Text\HeadingField;
use Adeliom\HorizonTools\Fields\Text\UptitleField;
use Adeliom\HorizonTools\Fields\Text\WysiwygField;
use Adeliom\HorizonTools\Services\Compilation\CompilationService;
use App\Admin\OptionPageAdmin;
use App\PostTypes\CustomerReview;
use Extended\ACF\Fields\Message;
use Extended\ACF\Fields\Relationship;

class CustomerReviewBlock extends AbstractBlock
{

    public const string FIELD_REVIEWS = 'reviews';
    public static ?string $slug = 'customer-review';
    public static ?string $title = 'Avis clients';
    public static ?string $description = 'Affiche une série de témoignages clients, ainsi que la note globale attribuée.';

    public function getFields(): ?iterable
    {
        yield from ContentTab::make()->fields([
            UptitleField::make(),
            HeadingField::make(HeadingField::LABEL, HeadingField::NAME, null, 'h2')->required(),
            WysiwygField::default(),
            Message::make("Information")
                ->body("La note globale est gérée au niveau général de votre thème."),
            Relationship::make("Avis clients", self::FIELD_REVIEWS)
                ->minPosts(2)
                ->maxPosts(6)
                ->postTypes([CustomerReview::$slug])->required(),
        ]);

        yield from LayoutTab::make()->fields([
            LayoutField::margin(),
        ]);
    }

    public function addToContext(): array
    {
        $options = get_field(OptionPageAdmin::FIELD_REVIEWS_FIELDS, 'option');
        $globalRating = $options[OptionPageAdmin::FIELD_GLOBAL_RATING] ?? null;
        $btnRating = $options[OptionPageAdmin::FIELD_BTN_REVIEWS] ?? null;

        return [
            OptionPageAdmin::FIELD_GLOBAL_RATING => $globalRating,
            OptionPageAdmin::FIELD_BTN_REVIEWS   => $btnRating,
        ];
    }

    public function renderBlockCallback(): void
    {
		switch (true) {
			case CompilationService::shouldUseBud():
				CompilationService::getAsset('customer-review.js')?->enqueueAll();
				break;
			default:
				CompilationService::getAsset('resources/scripts/blocks/customer-review.ts')?->enqueueAll();
				break;
		}
    }
}
