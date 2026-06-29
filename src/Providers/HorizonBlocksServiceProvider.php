<?php

declare(strict_types=1);

namespace Adeliom\HorizonBlocks\Providers;

use Adeliom\HorizonBlocks\Console\Commands\ImportBlock;
use Illuminate\Support\Facades\Blade;
use Roots\Acorn\Exceptions\SkipProviderException;
use Roots\Acorn\Sage\SageServiceProvider;

class HorizonBlocksServiceProvider extends SageServiceProvider
{
	public function boot(): void
	{
		try {
			$this->loadHorizonTextdomain('horizon-blocks', dirname(__DIR__, 2) . '/languages');

			$this->commands([
				ImportBlock::class,
			]);

			Blade::anonymousComponentPath(__DIR__ . '/../../resources/views/components', 'horizon');
		} catch (\Exception $e) {
			throw new SkipProviderException($e->getMessage());
		}
	}

	protected function loadHorizonTextdomain(string $domain, string $packageLangDir): void
	{
		$locale = determine_locale();

		$override = trailingslashit(WP_LANG_DIR) . 'horizon/' . $domain . '-' . $locale . '.mo';
		$mofile = is_readable($override)
			? $override
			: rtrim($packageLangDir, '/') . '/' . $domain . '-' . $locale . '.mo';

		if (is_readable($mofile)) {
			load_textdomain($domain, $mofile);
		}
	}
}