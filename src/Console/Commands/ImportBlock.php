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

	private const TYPE_SCRIPT = 'script';
	private const TYPE_STYLE = 'style';

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

			if (isset($blockExtraData[HorizonBlockService::REQUIRES_LIVEWIRE]) && $blockExtraData[HorizonBlockService::REQUIRES_LIVEWIRE]) {
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

				if (isset($blockExtraData[HorizonBlockService::ASSET_FILES]) && is_array($blockExtraData[HorizonBlockService::ASSET_FILES])) {
					$this->handleAdditionalFiles($blockExtraData[HorizonBlockService::ASSET_FILES]);
				}

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

	private function handleAdditionalFiles(array $filePaths): void
	{
		if ($filePaths) {
			$filesToBud = [];

			$this->newLine();
			$this->info(sprintf('Handling %d additional file·s...', count($filePaths)));

			foreach ($filePaths as $filePath) {
				$type = null;
				$sourcePath = null;
				$relativePath = null;
				$horizonPath = null;
				$fileName = null;

				$extension = pathinfo($filePath, PATHINFO_EXTENSION);
				$fileName = pathinfo($filePath, PATHINFO_FILENAME);

				switch ($extension) {
					case 'ts':
						$type = self::TYPE_SCRIPT;
						$sourcePath = $this->getHorizonScriptsDirectory();
						$relativePath = $this->getScriptsDirectory();
						break;
					case 'css':
						$type = self::TYPE_STYLE;
						$sourcePath = $this->getHorizonStylesDirectory();
						$relativePath = $this->getStylesDirectory();
						break;
					default:
						break;
				}

				if ($type && $sourcePath && $relativePath) {
					$horizonFilePath = rtrim($sourcePath, ltrim($relativePath, '/')) . '/' . $filePath;

					if (file_exists($horizonFilePath)) {
						$newFilePath = $this->getTemplatePath() . '/' . $filePath;

						if (!file_exists($newFilePath)) {
							$this->info('Copying ' . $horizonFilePath . ' to ' . $newFilePath);
							file_put_contents($newFilePath, file_get_contents($horizonFilePath));
						} else {
							$this->error('File already exists at ' . $newFilePath);
						}

						$budString = null;
						$rootName = null;

						switch ($type) {
							case self::TYPE_SCRIPT:
								$rootName = ltrim($filePath, $this->getScriptsDirectory());
								$rootName = str_replace($fileName . '.' . $extension, $fileName, $rootName);
								$budString = sprintf('@scripts/%s', $rootName);
								break;
							case self::TYPE_STYLE:
								$rootName = ltrim($filePath, $this->getStylesDirectory());
								$rootName = str_replace($fileName . '.' . $extension, $fileName, $rootName);
								$budString = sprintf('@styles/%s', $rootName);
								break;
							default:
								break;
						}

						if (!isset($filesToBud[$rootName])) {
							$filesToBud[$rootName] = [self::TYPE_SCRIPT => [], self::TYPE_STYLE => []];
						}

						$filesToBud[$rootName][$type] = $budString;
					}
				}
			}

			$this->appendFilesToBud($filesToBud);
		}
	}

	private function appendFilesToBud(array $toHandle): void
	{
		if ($budFilePath = $this->getBudConfigPath()) {
			$this->newLine();
			$this->info('Handling Bud file...');

			foreach ($toHandle as $name => $assets) {
				$paths = [];

				if (isset($assets[self::TYPE_SCRIPT]) && !empty($assets[self::TYPE_SCRIPT])) {
					$paths[] = $assets[self::TYPE_SCRIPT];
				}

				if (isset($assets[self::TYPE_STYLE]) && !empty($assets[self::TYPE_STYLE])) {
					$paths[] = $assets[self::TYPE_STYLE];
				}

				if (!empty($paths)) {
					$budName = last(explode('/', $name));
					$budLine = sprintf('.entry("%s", %s)', $budName, json_encode($paths, JSON_UNESCAPED_SLASHES));
					$budLineSingleQuotes = str_replace('"', "'", $budLine);

					$budFileContent = file_get_contents($budFilePath);

					if (!str_contains($budFileContent, $budLine) && !str_contains($budFileContent, $budLineSingleQuotes)) {
						// Insert line after the last .entry taking tabs and spaces into account
						$lastEntry = strrpos($budFileContent, '.entry(');
						$lastEntryEnd = strpos($budFileContent, ')', $lastEntry) + 1;

						$firstPart = substr($budFileContent, 0, $lastEntryEnd);
						$secondPart = substr($budFileContent, $lastEntryEnd);

						$newBudFileContent = $firstPart . PHP_EOL . '    ' . $budLineSingleQuotes . $secondPart;

						file_put_contents($budFilePath, $newBudFileContent);

						$this->info(sprintf('Added bud line for %s', $budName));
					} else {
						$this->info(sprintf('Bud line already exists for %s', $budName));
					}
				}
			}
		}
	}

	private function getBudConfigPath(): string
	{
		return $this->getTemplatePath() . '/bud.config.js';
	}

	private function getViewsDirectory(): string
	{
		return '/resources/views/';
	}

	private function getScriptsDirectory(): string
	{
		return '/resources/scripts/';
	}

	private function getStylesDirectory(): string
	{
		return '/resources/styles/';
	}

	private function getBlockViewsDirectory(): string
	{
		return $this->getViewsPath() . 'blocks/';
	}

	private function getLivewireViewsDirectory(): string
	{
		return $this->getViewsPath() . 'livewire/';
	}

	private function getHorizonRoot(): string
	{
		return __DIR__ . '/../../..';
	}

	private function getLivewireHorizonViewsDirectory(): string
	{
		return $this->getHorizonRoot() . $this->getViewsDirectory() . 'livewire/';
	}

	private function getHorizonScriptsDirectory(): string
	{
		return $this->getHorizonRoot() . $this->getScriptsDirectory();
	}

	private function getHorizonStylesDirectory(): string
	{
		return $this->getHorizonRoot() . $this->getStylesDirectory();
	}

	private function getViewsPath(): string
	{
		return $this->getTemplatePath() . $this->getViewsDirectory();
	}

	private function getTemplatePath(): ?string
	{
		if (function_exists('get_template_directory')) {
			return get_template_directory();
		}

		return null;
	}

	private function createLivewireTemplate(string $className): void
	{
		if ($pathToLivewireClass = ClassService::getFilePathFromClassName(className: $className)) {
			$livewireViewsPath = $this->getLivewireViewsDirectory();
			$livewireHorizonViewsPath = $this->getLivewireHorizonViewsDirectory();

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

			$path = $this->getTemplatePath() . '/app/Livewire/';
			$structure = CommandService::getFolderStructure(str_replace('\\', '/', str_replace('Adeliom\\HorizonBlocks\\Livewire\\', '', $className)));

			file_put_contents($path . $structure['path'], $livewireContent);
		}
	}

	private function createBlockControllerFile(string $className, array $folders, string $pathToBlockControllerFile, array $structure)
	{
		$blockClassContent = file_get_contents($pathToBlockControllerFile);
		$blockClassContent = str_replace('Adeliom\\HorizonBlocks\\Blocks\\', 'App\\Blocks\\', $blockClassContent);

		$path = $this->getTemplatePath() . '/app/Blocks/';
		$filepath = $path . $structure['path'];

		$result = CommandService::handleClassCreation(AbstractBlock::class, $filepath, $path, $folders, $className, $blockClassContent);
	}

	private function createBlockBladeFile(string $className, array $folders): void
	{
		$blockViewsPath = $this->getBlockViewsDirectory();
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