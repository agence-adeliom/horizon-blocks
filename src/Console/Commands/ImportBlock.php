<?php

declare(strict_types=1);

namespace Adeliom\HorizonBlocks\Console\Commands;

use Adeliom\HorizonTools\Blocks\AbstractBlock;
use Adeliom\HorizonTools\Services\ClassService;
use Adeliom\HorizonTools\Services\CommandService;
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
		$fullNames = [];

		foreach (ClassService::getAllImportableBlockClasses() as $path => $importableBlock) {
			$title = null;
			$fullNames[$importableBlock] = str_replace('Adeliom\\HorizonBlocks\\Blocks\\', '', $importableBlock);
			$shortNames[$importableBlock] = $importableBlock::$title;
		}

		if ($index = $this->choice('Which block would you like to import?', array_values($shortNames), 0)) {
			$namespaceToImport = array_search($index, $shortNames);
			$pathToFile = array_search($namespaceToImport, $classes);
			$index = $fullNames[$namespaceToImport];

			if ($namespaceToImport && $pathToFile) {
				$blockContent = file_get_contents($pathToFile);

				$blockContent = str_replace('Adeliom\\HorizonBlocks\\Blocks\\', 'App\\Blocks\\', $blockContent);

				$structure = CommandService::getFolderStructure(str_replace('\\', '/', $index));
				$folders = $structure['folders'];
				$className = $structure['class'];

				$path = get_template_directory() . '/app/Blocks/';
				$rootPath = '/resources/views/blocks/';
				$templatePath = get_template_directory() . $rootPath;
				$filepath = $path . $structure['path'];

				$slug = ClassService::slugifyClassName($className);

				if (str_ends_with($slug, '-block')) {
					$slug = substr($slug, 0, -6);
				}

				$folderPath = '';

				if (!file_exists($templatePath)) {
					mkdir($templatePath);
				}

				foreach ($folders as $folder) {
					$templatePath .= strtolower($folder) . '/';
					$folderPath .= strtolower($folder) . '/';

					if (!file_exists($templatePath)) {
						mkdir($templatePath);
					}
				}

				if (file_exists($templatePath . $slug . '.blade.php')) {
					$this->error('Block already exists!');
					return;
				}

				$localPath = __DIR__ . '/../../..' . $rootPath . $folderPath . $slug . '.blade.php';

				if (!file_exists($localPath)) {
					$this->error('Block template not found at ' . $localPath);
					return;
				}

				// Create block class
				$result = CommandService::handleClassCreation(AbstractBlock::class, $filepath, $path, $folders, $className, $blockContent);

				// Create block template
				file_put_contents($templatePath . $slug . '.blade.php', file_get_contents($localPath));

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