<?php

declare(strict_types=1);

namespace Adeliom\HorizonBlocks\Concerns;

use Adeliom\HorizonTools\Services\Compilation\CompilationService;

trait EnqueuesBlockAssets
{
	protected function enqueueBlockScript(string $name, bool $withCss = false): void
	{
		if (CompilationService::shouldUseBud()) {
			CompilationService::getAsset("{$name}.js")?->enqueue();

			if ($withCss) {
				CompilationService::getAsset("{$name}.css")?->enqueue();
			}

			return;
		}

		$method = $withCss ? 'enqueueAll' : 'enqueue';
		CompilationService::getAsset("resources/scripts/blocks/{$name}.ts")?->{$method}();
	}

	protected function enqueueBlockStyle(string $name): void
	{
		$asset = CompilationService::shouldUseBud()
			? "{$name}.css"
			: "resources/styles/blocks/{$name}.css";

		CompilationService::getAsset($asset)?->enqueue();
	}
}
