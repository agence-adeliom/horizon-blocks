<?php

declare(strict_types=1);

namespace Adeliom\HorizonBlocks\Services;

use Adeliom\HorizonBlocks\Admin\Post\PostSummaryAdmin;
use Adeliom\HorizonBlocks\Blocks\Action\CtaBlock;
use Adeliom\HorizonBlocks\Blocks\Action\FormBlock;
use Adeliom\HorizonBlocks\Blocks\Action\FormConfirmationBlock;
use Adeliom\HorizonBlocks\Blocks\Content\ArgumentBlock;
use Adeliom\HorizonBlocks\Blocks\Content\CardsBlock;
use Adeliom\HorizonBlocks\Blocks\Content\Catchphrase;
use Adeliom\HorizonBlocks\Blocks\Content\DocumentsBlock;
use Adeliom\HorizonBlocks\Blocks\Content\FaqBlock;
use Adeliom\HorizonBlocks\Blocks\Content\GalleryBlock;
use Adeliom\HorizonBlocks\Blocks\Content\HighlightBlock;
use Adeliom\HorizonBlocks\Blocks\Content\PostSummaryBlock;
use Adeliom\HorizonBlocks\Blocks\Content\StepBlock;
use Adeliom\HorizonBlocks\Blocks\Content\TextMediaBlock;
use Adeliom\HorizonBlocks\Blocks\Content\TitleTextBlock;
use Adeliom\HorizonBlocks\Blocks\Content\WysiwygBlock;
use Adeliom\HorizonBlocks\Blocks\Hero\HeroBlock;
use Adeliom\HorizonBlocks\Blocks\Hero\HeroForm;
use Adeliom\HorizonBlocks\Blocks\Listing\ListingBlock;
use Adeliom\HorizonBlocks\Blocks\Listing\SearchEngineResultsBlock;
use Adeliom\HorizonBlocks\Blocks\Reassurance\CustomerReviewBlock;
use Adeliom\HorizonBlocks\Blocks\Reassurance\KeyFigureBlock;
use Adeliom\HorizonBlocks\Blocks\Reassurance\LogosBlock;
use Adeliom\HorizonBlocks\Blocks\Reassurance\QuoteBlock;
use Adeliom\HorizonBlocks\Livewire\Listing\Listing;
use Adeliom\HorizonBlocks\Livewire\Listing\SearchEngineResults;
use Adeliom\HorizonBlocks\View\Components\Cards\CardFaq;
use Adeliom\HorizonBlocks\View\Components\Cards\CardStep;
use Adeliom\HorizonBlocks\View\Components\Cards\CardBasic;
use Adeliom\HorizonBlocks\View\Components\Cards\CardCustomerReview;
use Adeliom\HorizonBlocks\View\Components\Cards\CardDocument;
use Adeliom\HorizonBlocks\View\Components\Content\TextMedia;
use Adeliom\HorizonBlocks\View\Components\Offer;
use App\View\Components\SearchEngine\MergedResults;
use App\View\Components\SearchEngine\SeparatedResults;

class HorizonBlockService
{
	public const string REQUIRES_LIVEWIRE = 'requiresLivewire';
	public const string ASSET_FILES = 'additionalFiles';
	public const string LIVEWIRE_COMPONENTS = 'livewireComponents';
	public const string COMPONENTS = 'components';
	public const ADMINS = 'admins';

	public static function getAvailableBlocks(): array
	{
		$blocks = [
			TitleTextBlock::class => [
				self::REQUIRES_LIVEWIRE => false,
				self::ASSET_FILES => [],
				self::LIVEWIRE_COMPONENTS => [],
			],
			WysiwygBlock::class => [
				self::REQUIRES_LIVEWIRE => false,
				self::ASSET_FILES => [],
				self::LIVEWIRE_COMPONENTS => [],
			],
			ListingBlock::class => [
				self::REQUIRES_LIVEWIRE => true,
				self::ASSET_FILES => [
					'resources/scripts/blocks/listing.ts',
				],
				self::LIVEWIRE_COMPONENTS => [Listing::class],
			],
			SearchEngineResultsBlock::class => [
				self::REQUIRES_LIVEWIRE => true,
				self::ASSET_FILES => [
					'resources/styles/components/blocks/search-engine-results.css',
				],
				self::LIVEWIRE_COMPONENTS => [SearchEngineResults::class],
				self::COMPONENTS => [
					MergedResults::class,
					SeparatedResults::class,
				],
			],
			CtaBlock::class => [
				self::REQUIRES_LIVEWIRE => false,
				self::ASSET_FILES => [],
				self::LIVEWIRE_COMPONENTS => [],
			],
			ArgumentBlock::class => [
				self::REQUIRES_LIVEWIRE => false,
				self::ASSET_FILES => [
					'resources/scripts/blocks/arguments.ts',
				],
				self::LIVEWIRE_COMPONENTS => [],
			],
			CardsBlock::class => [
				self::REQUIRES_LIVEWIRE => false,
				self::ASSET_FILES => [],
				self::LIVEWIRE_COMPONENTS => [],
				self::COMPONENTS => [
					CardBasic::class
				],
			],
			Catchphrase::class => [
				self::REQUIRES_LIVEWIRE => false,
				self::ASSET_FILES => [],
				self::LIVEWIRE_COMPONENTS => [],
			],
            HighlightBlock::class => [
                self::REQUIRES_LIVEWIRE => false,
                self::ASSET_FILES => [],
                self::LIVEWIRE_COMPONENTS => [],
            ],
			DocumentsBlock::class => [
				self::REQUIRES_LIVEWIRE => false,
				self::ASSET_FILES => [],
				self::LIVEWIRE_COMPONENTS => [],
				self::COMPONENTS => [
					CardDocument::class
				],
			],
			StepBlock::class => [
				self::REQUIRES_LIVEWIRE => false,
				self::ASSET_FILES => [
					'resources/scripts/blocks/steps.ts',
				],
				self::LIVEWIRE_COMPONENTS => [],
				self::COMPONENTS => [
					CardStep::class
				],
			],
			CustomerReviewBlock::class => [
				self::REQUIRES_LIVEWIRE => false,
				self::ASSET_FILES => [
					'resources/scripts/blocks/customer-review.ts',
				],
				self::LIVEWIRE_COMPONENTS => [],
				self::COMPONENTS => [
					CardCustomerReview::class
				],
			],
			KeyFigureBlock::class => [
				self::REQUIRES_LIVEWIRE => false,
				self::ASSET_FILES => [],
				self::LIVEWIRE_COMPONENTS => [],
			],
			LogosBlock::class => [
				self::REQUIRES_LIVEWIRE => false,
				self::ASSET_FILES => [
					'resources/scripts/blocks/logos.ts',
				],
				self::LIVEWIRE_COMPONENTS => [],
			],
            GalleryBlock::class => [
                self::REQUIRES_LIVEWIRE => false,
                self::ASSET_FILES => [
                    'resources/scripts/blocks/gallery.ts',
                ],
                self::LIVEWIRE_COMPONENTS => [],
            ],
			QuoteBlock::class => [
				self::REQUIRES_LIVEWIRE => false,
				self::ASSET_FILES => [],
				self::LIVEWIRE_COMPONENTS => [],
			],
            HeroBlock::class => [
                self::REQUIRES_LIVEWIRE => false,
                self::ASSET_FILES => [],
                self::LIVEWIRE_COMPONENTS => [],
            ],
			FaqBlock::class => [
				self::REQUIRES_LIVEWIRE => false,
				self::ASSET_FILES => [],
				self::LIVEWIRE_COMPONENTS => [],
				self::COMPONENTS => [
					CardFaq::class
				],
			],
			HeroForm::class => [
				self::REQUIRES_LIVEWIRE => false,
				self::ASSET_FILES => [],
				self::LIVEWIRE_COMPONENTS => [],
				self::COMPONENTS => [
					Offer::class
				],
			],
			FormBlock::class => [
				self::REQUIRES_LIVEWIRE => false,
				self::ASSET_FILES => [],
				self::LIVEWIRE_COMPONENTS => [],
				self::COMPONENTS => [
					Offer::class
				],
			],
			FormConfirmationBlock::class => [
				self::REQUIRES_LIVEWIRE => false,
				self::ASSET_FILES => [],
				self::LIVEWIRE_COMPONENTS => [],
				self::COMPONENTS => [],
			],
			PostSummaryBlock::class => [
				self::REQUIRES_LIVEWIRE => false,
				self::ASSET_FILES => [
					'resources/scripts/blocks/post-summary.ts',
				],
				self::LIVEWIRE_COMPONENTS => [],
				self::COMPONENTS => [],
				self::ADMINS => [PostSummaryAdmin::class],
			],
			TextMediaBlock::class => [
				self::REQUIRES_LIVEWIRE => false,
				self::ASSET_FILES => [],
				self::COMPONENTS => [TextMedia::class],
				self::LIVEWIRE_COMPONENTS => [],
			]
		];

		// Filter blocks by putting ones requiring Livewire last
		uasort($blocks, function ($a, $b) {
			if ($a[self::REQUIRES_LIVEWIRE] === $b[self::REQUIRES_LIVEWIRE]) {
				return 0;
			}

			return $a[self::REQUIRES_LIVEWIRE] ? 1 : -1;
		});

		return $blocks;
	}
}