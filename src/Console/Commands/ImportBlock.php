<?php

declare(strict_types=1);

namespace Adeliom\HorizonTools\Console\Commands;

use Illuminate\Console\Command;

class ImportBlock extends Command
{
	protected $signature = 'import:block {name}';
	protected $description = 'Import a block from Horizon Blocks';

	public function handle(): void
	{

	}
}