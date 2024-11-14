<?php

declare(strict_types=1);

namespace Adeliom\HorizonBlocks\Services;

use Adeliom\HorizonBlocks\Blocks\Action\CtaBlock;
use Adeliom\HorizonBlocks\Blocks\Content\CardsBlock;
use Adeliom\HorizonBlocks\Blocks\Content\TitleTextBlock;
use Adeliom\HorizonBlocks\Blocks\Listing\ListingBlock;
use Adeliom\HorizonBlocks\Livewire\Listing\Listing;

class HorizonBlockService
{
	public const string REQUIRES_LIVEWIRE = 'requiresLivewire';
	public const string ADDITIONAL_FILES = 'additionalFiles';
	public const string LIVEWIRE_COMPONENTS = 'livewireComponents';

	public static function getAvailableBlocks(): array
	{
		$blocks = [
			TitleTextBlock::class => [
				self::REQUIRES_LIVEWIRE => false,
				self::ADDITIONAL_FILES => [],
				self::LIVEWIRE_COMPONENTS => [],
			],
			ListingBlock::class => [
				self::REQUIRES_LIVEWIRE => true,
				self::ADDITIONAL_FILES => [],
				self::LIVEWIRE_COMPONENTS => [
					Listing::class,
				],
			],
			CtaBlock::class => [
				self::REQUIRES_LIVEWIRE => false,
				self::ADDITIONAL_FILES => [],
				self::LIVEWIRE_COMPONENTS => [],
			],
			CardsBlock::class => [
				self::REQUIRES_LIVEWIRE => false,
				self::ADDITIONAL_FILES => [],
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