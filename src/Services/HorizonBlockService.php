<?php

declare(strict_types=1);

namespace Adeliom\HorizonBlocks\Services;

use Adeliom\HorizonBlocks\Blocks\Action\CtaBlock;
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
		return [
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
			]
		];
	}
}