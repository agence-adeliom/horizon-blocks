<?php

declare(strict_types=1);

namespace Adeliom\HorizonBlocks\Providers;

use Adeliom\HorizonBlocks\Console\Commands\ImportBlock;
use Roots\Acorn\Exceptions\SkipProviderException;
use Roots\Acorn\Sage\SageServiceProvider;

class HorizonBlocksServiceProvider extends SageServiceProvider
{
	public function boot(): void
	{
		try {
			$this->commands([
				ImportBlock::class,
			]);
		} catch (\Exception $e) {
			throw new SkipProviderException($e->getMessage());
		}
	}
}