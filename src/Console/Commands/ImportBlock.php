<?php

declare(strict_types=1);

namespace Adeliom\HorizonBlocks\Console\Commands;

use Adeliom\HorizonTools\Blocks\AbstractBlock;
use Adeliom\HorizonTools\Services\ClassService;
use Adeliom\HorizonTools\Services\CommandService;
use Adeliom\HorizonTools\Services\FileService;
use Composer\InstalledVersions;
use Illuminate\Console\Command;

class ImportBlock extends Command
{
	protected $signature = 'import:block';
	protected $description = 'Import a block from Horizon Blocks';

	public function handle(): void
	{
		$classes = ClassService::getAllImportableBlockClasses();
		$paths = array_keys($classes);
		$names = array_values($classes);
		$shortNames = [];

		foreach (ClassService::getAllImportableBlockClasses() as $path => $importableBlock) {
			$shortNames[$importableBlock] = str_replace('Adeliom\\HorizonBlocks\\Blocks\\', '', $importableBlock);
		}

		if ($index = $this->choice('Which block would you like to import?', array_values($shortNames), 0)) {
			$namespaceToImport = array_search($index, $shortNames);
			$pathToFile = array_search($namespaceToImport, $classes);

			if ($namespaceToImport && $pathToFile) {
				$blockContent = file_get_contents($pathToFile);

				$blockContent = str_replace('Adeliom\\HorizonBlocks\\Blocks\\', 'App\\Blocks\\', $blockContent);

				$structure = CommandService::getFolderStructure(str_replace('\\', '/', $index));
				$folders = $structure['folders'];
				$className = $structure['class'];

				$path = get_template_directory() . '/app/Blocks/';
				$filepath = $path . $structure['path'];

				$result = CommandService::handleClassCreation(AbstractBlock::class, $filepath, $path, $folders, $className, $blockContent);

				switch ($result) {
					case 'already_exists':
						$this->error('Block already exists!');
						break;
					case 'success':
						$this->info('Block imported successfully at ' . $filepath);
						break;
				}
			}
		}
	}
}