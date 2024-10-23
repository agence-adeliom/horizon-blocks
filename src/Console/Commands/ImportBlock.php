<?php

declare(strict_types=1);

namespace Adeliom\HorizonBlocks\Console\Commands;

use Adeliom\HorizonBlocks\Services\HorizonBlockService;
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
		$availableBlocks = HorizonBlockService::getAvailableBlocks();
		$classes = array_keys($availableBlocks);
		$shortNames = [];
		$fullNames = [];

		foreach ($classes as $className) {
			$blockExtraData = $availableBlocks[$className];

			$fullNames[$className] = str_replace('Adeliom\\HorizonBlocks\\Blocks\\', '', $className);
			$shortNames[$className] = $className::$title;

			if (isset($blockExtraData[HorizonBlockService::REQUIRES_LIVEWIRE])) {
				$shortNames[$className] .= ' (Requires Livewire)';
			}
		}

		if (empty($shortNames)) {
			$this->error('No block to import!');
			return;
		}

		if ($index = $this->choice('Which block would you like to import?', array_values($shortNames), 0)) {
			$namespaceToImport = array_search($index, $shortNames);
			$blockExtraData = $availableBlocks[$namespaceToImport];

			$pathToBlockControllerFile = ClassService::getFilePathFromClassName($namespaceToImport);

			if (file_exists($pathToBlockControllerFile)) {
				$shortName = $fullNames[$namespaceToImport];

				$structure = CommandService::getFolderStructure(str_replace('\\', '/', $shortName));
				$folders = $structure['folders'];
				$className = $structure['class'];

				$this->createBlockBladeFile(className: $className, folders: $folders);
				$this->createBlockControllerFile(className: $className, folders: $folders, pathToBlockControllerFile: $pathToBlockControllerFile, structure: $structure);

				if (isset($blockExtraData[HorizonBlockService::LIVEWIRE_COMPONENTS])) {
					foreach ($blockExtraData[HorizonBlockService::LIVEWIRE_COMPONENTS] as $livewireClass) {
						$this->createLivewireTemplate(className: $livewireClass);
						$this->createLivewireComponent(className: $livewireClass);
					}
				}
			}
		}
	}

	private function getViewsDirectory(): string
	{
		return '/resources/views/';
	}

	private function getViewsPath(): string
	{
		return get_template_directory() . $this->getViewsDirectory();
	}

	private function createLivewireTemplate(string $className)
	{
		if ($pathToLivewireClass = ClassService::getFilePathFromClassName(className: $className)) {
			$livewireViewsPath = $this->getViewsPath() . 'livewire/';

			$livewireHorizonViewsPath = __DIR__ . '/../../..' . $this->getViewsDirectory() . 'livewire/';

			if (file_exists($livewireHorizonViewsPath)) {
				$explode = explode('src/Livewire', $pathToLivewireClass);

				if (isset($explode[1])) {
					$name = strtolower(rtrim(ltrim($explode[1], '/'), '.php')) . '.blade.php';

					if (file_exists($livewireHorizonViewsPath . $name)) {
						file_put_contents($livewireViewsPath . $name, file_get_contents($livewireHorizonViewsPath . $name));
					}
				}
			}
		}
	}

	private function createLivewireComponent(string $className)
	{
		if ($pathToLivewireClass = ClassService::getFilePathFromClassName(className: $className)) {
			$livewireContent = file_get_contents($pathToLivewireClass);
			$livewireContent = str_replace('Adeliom\\HorizonBlocks\\Livewire\\', 'App\\Livewire\\', $livewireContent);

			$path = get_template_directory() . '/app/Livewire/';
			$structure = CommandService::getFolderStructure(str_replace('\\', '/', str_replace('Adeliom\\HorizonBlocks\\Livewire\\', '', $className)));

			file_put_contents($path . $structure['path'], $livewireContent);
		}
	}

	private function createBlockControllerFile(string $className, array $folders, string $pathToBlockControllerFile, array $structure)
	{
		$blockClassContent = file_get_contents($pathToBlockControllerFile);
		$blockClassContent = str_replace('Adeliom\\HorizonBlocks\\Blocks\\', 'App\\Blocks\\', $blockClassContent);

		$path = get_template_directory() . '/app/Blocks/';
		$filepath = $path . $structure['path'];

		$result = CommandService::handleClassCreation(AbstractBlock::class, $filepath, $path, $folders, $className, $blockClassContent);
	}

	private function createBlockBladeFile(string $className, array $folders): void
	{
		$blockViewsPath = $this->getViewsPath() . 'blocks/';
		$slug = ClassService::slugifyClassName($className);

		// We remove unecessary block template suffix
		if (str_ends_with($slug, '-block')) {
			$slug = substr($slug, 0, -6);
		}

		$folderPath = '';

		// We create the block folder if it doesn't exist
		if (!file_exists($blockViewsPath)) {
			mkdir($blockViewsPath);
		}

		// We create the block folder structure if it doesn't exist
		$blockPath = $blockViewsPath;
		foreach ($folders as $folder) {
			$blockPath .= strtolower($folder) . '/';
			$folderPath .= strtolower($folder) . '/';

			if (!file_exists($blockPath)) {
				mkdir($blockPath);
			}
		}

		// We create the block template file if it doesn't exist
		if (file_exists($blockPath . $slug . '.blade.php')) {
			$this->error('Block already exists!');
			return;
		}

		$blockViewPath = __DIR__ . '/../../..' . $this->getViewsDirectory() . 'blocks/' . $folderPath . $slug . '.blade.php';

		if (!file_exists($blockViewPath)) {
			$this->error('Block template not found at ' . $blockViewPath);
			return;
		}

		file_put_contents($blockPath . $slug . '.blade.php', file_get_contents($blockViewPath));
	}
}