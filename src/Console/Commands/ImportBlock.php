<?php

declare(strict_types=1);

namespace Adeliom\HorizonBlocks\Console\Commands;

use Adeliom\HorizonBlocks\Services\HorizonBlockService;
use Adeliom\HorizonPostTypes\Console\Commands\ImportPostType;
use Adeliom\HorizonTools\Blocks\AbstractBlock;
use Adeliom\HorizonTools\Services\ClassService;
use Adeliom\HorizonTools\Services\CommandService;
use Adeliom\HorizonTools\Services\Compilation\CompilationService;
use Adeliom\HorizonTools\Services\FileService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use function Laravel\Prompts\search;

class ImportBlock extends Command
{
    protected $signature = 'import:block {quickImportSlug?}';
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

        $blockNames = collect(array_values($shortNames));

        $namespaceToImport = null;

        // On essaie de récupérer le "hash" du block
        if ($quickImportSlug = $this->argument('quickImportSlug')) {
            foreach (array_keys($shortNames) as $shortName) {
                if (Str::of($shortName)->endsWith(Str::of($quickImportSlug)->replace('/', '\\')->toString())) {
                    $namespaceToImport = $shortName;
                    break;
                }
            }
        }

        // S'il n'y a pas de hash fourni, ou s'il ne correspond à rien, on demande à l'utilisateur·ice de choisir
        if (
            null === $namespaceToImport &&
            ($index = search(
                label: 'Name of the block to import',
                options: fn(string $value) => $blockNames
                    ->filter(fn($name) => Str::contains($name, $value, ignoreCase: true))
                    ->values()
                    ->all(),
                scroll: 10,
            ))
        ) {
            $namespaceToImport = array_search($index, $shortNames);
        }

        // Si on a un block à importer :
        if (null !== $namespaceToImport) {
            $blockExtraData = $availableBlocks[$namespaceToImport];

            if (!empty($blockExtraData[HorizonBlockService::REQUIRED_POSTTYPES])) {
                $this->handleAssociatedPostTypes($blockExtraData[HorizonBlockService::REQUIRED_POSTTYPES]);
            }

            $pathToBlockControllerFile = ClassService::getFilePathFromClassName($namespaceToImport);

            if (file_exists($pathToBlockControllerFile)) {
                $shortName = $fullNames[$namespaceToImport];

                $structure = CommandService::getFolderStructure(str_replace('\\', '/', $shortName));
                $folders = $structure['folders'];
                $className = $structure['class'];

                if (isset($blockExtraData[HorizonBlockService::ADMINS]) && is_array($blockExtraData[HorizonBlockService::ADMINS])) {
                    $this->handleAssociatedAdmins(adminClasses: $blockExtraData[HorizonBlockService::ADMINS]);
                }

                if (isset($blockExtraData[HorizonBlockService::COMPONENTS]) && is_array($blockExtraData[HorizonBlockService::COMPONENTS])) {
                    $this->handleAdditionalComponents(componentClasses: $blockExtraData[HorizonBlockService::COMPONENTS]);
                }

                if (
                    isset($blockExtraData[HorizonBlockService::ASSET_FILES]) &&
                    is_array($blockExtraData[HorizonBlockService::ASSET_FILES])
                ) {
                    $this->handleAdditionalFiles($blockExtraData[HorizonBlockService::ASSET_FILES]);
                }

                $this->createBlockBladeFile(className: $className, folders: $folders);
                $this->createBlockControllerFile(
                    className: $className,
                    folders: $folders,
                    pathToBlockControllerFile: $pathToBlockControllerFile,
                    structure: $structure,
                );

                if (isset($blockExtraData[HorizonBlockService::LIVEWIRE_COMPONENTS])) {
                    foreach ($blockExtraData[HorizonBlockService::LIVEWIRE_COMPONENTS] as $livewireClass) {
                        $this->createLivewireTemplate(className: $livewireClass);
                        $this->createLivewireComponent(className: $livewireClass);
                    }
                }

                $this->handleAssociatedIllustration($shortName);
            }
        }
    }

    private function handleAssociatedPostTypes(array $requiredPostTypes = []): void
    {
        if (!empty($requiredPostTypes)) {
            foreach ($requiredPostTypes as $requiredPostTypeClassName) {
                if (!class_exists($requiredPostTypeClassName)) {
                    continue;
                }

                $requiredPostTypeClassInstance = new $requiredPostTypeClassName();
                $postTypeName = $requiredPostTypeClassName::$slug;

                if ($postTypeConfig = $requiredPostTypeClassInstance->getConfig()) {
                    if (!empty($postTypeConfig['args']['labels']['name'])) {
                        $postTypeName = $postTypeConfig['args']['labels']['name'];
                    } elseif (!empty($postTypeConfig['args']['label'])) {
                        $postTypeName = $postTypeConfig['args']['label'];
                    }
                }

                $shortName = str_replace('Adeliom\\HorizonPostTypes\\PostTypes\\', '', $requiredPostTypeClassName);

                $pathToPostTypeControllerFile = ClassService::getFilePathFromClassName($requiredPostTypeClassName);

                $structure = CommandService::getFolderStructure(str_replace('\\', '/', $shortName));
                $folders = $structure['folders'];
                $className = $structure['class'];

                $expectedClassName = 'App\\PostTypes\\' . $className;

                if (class_exists($expectedClassName)) {
                    $this->info('Required postType ' . $expectedClassName . ' already exists, skipping import.');
                    continue;
                }

                if (
                    !$this->confirm(
                        sprintf(
                            'The block requires the post-type "%s" (%s). Do you want to import it now?',
                            $postTypeName,
                            $requiredPostTypeClassName,
                        ),
                        true,
                    )
                ) {
                    continue;
                }

                $importPostTypeCommand = new ImportPostType();
                $importPostTypeCommand->createPostTypeControllerFile(
                    className: $className,
                    folders: $folders,
                    pathToPostTypeControllerFile: $pathToPostTypeControllerFile,
                    structure: $structure,
                    instance: $this,
                );
            }
        }
    }

    private function handleAssociatedIllustration(string $shortName): void
    {
        $hypotheticalImagePathWithoutExtension = Str::of($shortName)
            ->kebab()
            ->replace('\\', '/')
            ->replace('-block', '')
            ->replace('/-', '/');

        $hypotheticalFullPathWithoutExtension = $this->getHorizonBlockImagesDirectory() . $hypotheticalImagePathWithoutExtension;
        $hypotheticalFolderPath = explode('/', $hypotheticalFullPathWithoutExtension);

        $fileName = array_pop($hypotheticalFolderPath);

        $hypotheticalFolderPath = implode('/', $hypotheticalFolderPath) . '/';
        $fullPath = null;
        $fullFileName = null;

        foreach (scandir($hypotheticalFolderPath) as $fileFullName) {
            if (str_starts_with($fileFullName, $fileName)) {
                $fullPath = $hypotheticalFolderPath . $fileFullName;
                $fullFileName = $fileFullName;
                break;
            }
        }

        if (null !== $fullPath && null !== $fullFileName && file_exists($fullPath)) {
            $inTemplatePath = $this->getBlockImagesPath() . $hypotheticalImagePathWithoutExtension->replace($fileName, $fullFileName);

            if (file_exists($inTemplatePath)) {
                $this->error('Illustration already exists at ' . $inTemplatePath);
            } else {
                $this->newLine();
                $this->info('Handling block illustration...');

                FileService::filePutContentsAndCreateMissingDirectories($inTemplatePath, file_get_contents($fullPath));

                $this->info('Copied illustration to ' . $inTemplatePath);
            }
        }
    }

    private function handleAssociatedAdmins(array $adminClasses): void
    {
        $this->newLine();
        $this->info('Handling additional admin classes...');

        foreach ($adminClasses as $adminClass) {
            if ($classFile = ClassService::getFilePathFromClassName($adminClass)) {
                if (file_exists($classFile)) {
                    $adminClassContent = file_get_contents($classFile);
                    $adminClassContent = str_replace('Adeliom\\HorizonBlocks\\Admin\\', 'App\\Admin\\', $adminClassContent);

                    $path = $this->getTemplatePath() . '/app/Admin/';

                    $folderStructure = explode('horizon-blocks/src/Admin/', $classFile);

                    if ($folderStructure[1]) {
                        $folders = array_filter(
                            array_map(function ($folder) {
                                return str_ends_with($folder, '.php') ? null : $folder;
                            }, explode('/', $folderStructure[1])),
                        );

                        if (!empty($folders)) {
                            $folderPath = $path;

                            foreach ($folders as $folder) {
                                $folderPath = rtrim($folderPath, '/') . '/' . $folder;

                                // Create folder if it doesn't exist
                                if (!file_exists($folderPath)) {
                                    mkdir($folderPath, 0755, true);
                                }
                            }

                            $adminFullPath = rtrim($path, '/') . '/' . $folderStructure[1];
                            FileService::filePutContentsAndCreateMissingDirectories($adminFullPath, $adminClassContent);
                        }
                    }
                }
            }
        }
    }

    private function handleAdditionalComponents(array $componentClasses)
    {
        $this->newLine();
        $this->info('Handling additional components...');

        foreach ($componentClasses as $componentClass) {
            if ($classFile = ClassService::getFilePathFromClassName($componentClass)) {
                if (file_exists($classFile)) {
                    $results = explode($this->getHorizonBlockComponentClassesDirectory(), $classFile);

                    if (isset($results[1])) {
                        $componentFileName = $results[1];

                        $templateClassFileName = $this->getTemplatePath() . $this->getComponentClassesDirectory() . $componentFileName;

                        if (!file_exists($templateClassFileName)) {
                            $this->info('Copying ' . $classFile . ' to ' . $templateClassFileName);

                            $content = file_get_contents($classFile);

                            $namespace = implode('\\', array_slice(explode('\\', $componentClass), 0, -1));
                            $newNamespace = 'App\\View\\' . implode('\\', array_slice(explode('\\', $componentClass), 3, -1));

                            $content = str_replace($namespace, $newNamespace, $content);

                            FileService::filePutContentsAndCreateMissingDirectories($templateClassFileName, $content);

                            // Get line container return view
                            $lines = file($classFile);
                            $lineNumber = 0;
                            foreach ($lines as $lineNumber => $line) {
                                if (strpos($line, 'return view') !== false) {
                                    if (preg_match("/return view\('?\"?([a-zA-Z.-]+)'?\"?\)/", $line, $m)) {
                                        if (isset($m[1])) {
                                            $folders = explode('.', $m[1]);

                                            $fileName = end($folders) . '.blade.php';
                                            unset($folders[count($folders) - 1]);
                                            $filePath = implode('/', $folders) . '/' . $fileName;

                                            $templatePath = $this->getViewsPath() . $filePath;
                                            $horizonPath = $this->getHorizonViewsDirectory() . $filePath;

                                            if (file_exists($horizonPath)) {
                                                if (!file_exists($templatePath)) {
                                                    $this->info('Copying ' . $horizonPath . ' to ' . $templatePath);
                                                    FileService::filePutContentsAndCreateMissingDirectories(
                                                        $templatePath,
                                                        file_get_contents($horizonPath),
                                                    );
                                                } else {
                                                    $this->error('File already exists at ' . $templatePath);
                                                }
                                            }
                                        }
                                    }
                                    break;
                                }
                            }
                        } else {
                            $this->error('File already exists at ' . $templateClassFileName);
                        }
                    }
                }
            }
        }
    }

    private function handleAdditionalFiles(array $filePaths): void
    {
        if ($filePaths) {
            $filesToCompilator = [];

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
                            FileService::filePutContentsAndCreateMissingDirectories($newFilePath, file_get_contents($horizonFilePath));
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

                        if (!isset($filesToCompilator[$rootName])) {
                            $filesToCompilator[$rootName] = [self::TYPE_SCRIPT => [], self::TYPE_STYLE => []];
                        }

						$filesToCompilator[$rootName][$type] = $budString;
					}
				}
			}

			switch (true) {
				case CompilationService::shouldUseVite():
					$this->appendFilesToVite($filesToCompilator);
					break;
				default:
					$this->appendFilesToBud($filesToCompilator);
					break;
			}
		}
	}

	private function appendFilesToVite(array $toHandle): void
	{
		if ($viteFilePath = $this->getViteConfigPath()) {
			$this->newLine();
			$this->info('Handling Vite file...');

			foreach ($toHandle as $name => $assets) {
				$paths = [];

				if (!empty($assets[self::TYPE_SCRIPT])) {
					$paths[] = $assets[self::TYPE_SCRIPT];
				}

				if (!empty($assets[self::TYPE_STYLE])) {
					$paths[] = $assets[self::TYPE_STYLE];
				}

				if (!empty($paths)) {
					$viteName = last(explode('/', $name));

					$paths = array_map(function ($path) {
						return sprintf("'%s'", $path);
					}, $paths);

					$viteConfigContent = file_get_contents($viteFilePath);

					if (!$viteConfigContent) {
						$this->error('Vite config file is empty');
					} else {
						$viteConfigContent = preg_replace_callback(
							'/laravel\(\s*{[^}]*?input:\s*\[([^\]]*)\]/s',
							function ($matches) use ($paths) {
								$inputBlock = $matches[1];

								// Détection de l'indentation de base (celle de `input: [`)
								preg_match('/^( *)(input:\s*\[)/m', $matches[0], $indentMatch);
								$baseIndent = $indentMatch[1] ?? '  '; // fallback à 2 espaces
								$itemIndent = $baseIndent . '  '; // indentation des items (ex: 2 niveaux)

								// Nettoyage et normalisation
								$existingPaths = array_map('trim', explode(',', trim($inputBlock)));
								$existingPaths = array_filter($existingPaths); // supprime les lignes vides

								// Remove indentation from existing paths
								$existingPaths = array_map(function ($path) {
									return trim($path, "\n\r\t ");
								}, $existingPaths);

								// Ajout des nouveaux chemins s'ils ne sont pas déjà présents
								foreach ($paths as $newPath) {
									if (!in_array(trim($newPath, "'\""), array_map(fn($p) => trim($p, "'\""), $existingPaths))) {
										$existingPaths[] = trim($newPath, "\n\r\t ");
									}
								}

								// Reconstruction avec indentation propre
								$newInput = "input: [\n";
								foreach ($existingPaths as $i => $path) {
									$comma = $i === array_key_last($existingPaths) ? '' : ',';
									$newInput .= $itemIndent . $path . $comma . "\n";
								}
								$newInput .= $baseIndent . "]";

								return preg_replace('/input:\s*\[[^\]]*\]/s', $newInput, $matches[0]);
							},
							$viteConfigContent
						);

						FileService::filePutContentsAndCreateMissingDirectories($viteFilePath, $viteConfigContent);

						$this->info(sprintf('Added Vite line for %s', $viteName));
					}
				}
			}
        }
    }

    private function appendFilesToBud(array $toHandle): void
    {
        if ($budFilePath = $this->getBudConfigPath()) {
            $this->newLine();
            $this->info('Handling Bud file...');

            foreach ($toHandle as $name => $assets) {
                $paths = [];

                if (!empty($assets[self::TYPE_SCRIPT])) {
                    $paths[] = $assets[self::TYPE_SCRIPT];
                }

                if (!empty($assets[self::TYPE_STYLE])) {
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

                        FileService::filePutContentsAndCreateMissingDirectories($budFilePath, $newBudFileContent);

                        $this->info(sprintf('Added Bud line for %s : %s', $budName, $budLineSingleQuotes));
                    } else {
                        $containedLine = str_contains($budFileContent, $budLine) ? $budLine : $budLineSingleQuotes;
                        $this->error(sprintf('Bud line already exists for %s (%s)', $budName, $containedLine));
                    }
                }
            }
        }
    }

    private function getBudConfigPath(): string
    {
        return $this->getTemplatePath() . '/bud.config.js';
    }

    private function getViteConfigPath(): string
	{
		return $this->getTemplatePath() . '/vite.config.js';
	}

	private function getImagesDirectory(): string
	{
		return '/resources/images/';
	}

    private function getBlockImagesDirectory(): string
    {
        return $this->getImagesDirectory() . 'admin/blocks/';
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

    private function getComponentClassesDirectory(): string
    {
        return '/app/View/Components/';
    }

    private function getHorizonBlockComponentClassesDirectory(): string
    {
        return '/src/View/Components/';
    }

    private function getHorizonBlockAdminClassesDirectory(): string
    {
        return '/src/Admin/';
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

    private function getHorizonViewsDirectory()
    {
        return $this->getHorizonRoot() . $this->getViewsDirectory();
    }

    private function getLivewireHorizonViewsDirectory(): string
    {
        return $this->getHorizonViewsDirectory() . 'livewire/';
    }

    private function getHorizonScriptsDirectory(): string
    {
        return $this->getHorizonRoot() . $this->getScriptsDirectory();
    }

    private function getHorizonStylesDirectory(): string
    {
        return $this->getHorizonRoot() . $this->getStylesDirectory();
    }

    private function getHorizonImagesDirectory(): string
    {
        return $this->getHorizonRoot() . $this->getImagesDirectory();
    }

    private function getHorizonBlockImagesDirectory(): string
    {
        return $this->getHorizonRoot() . $this->getBlockImagesDirectory();
    }

    private function getViewsPath(): string
    {
        return $this->getTemplatePath() . $this->getViewsDirectory();
    }

    private function getBlockImagesPath(): string
    {
        return $this->getTemplatePath() . $this->getBlockImagesDirectory();
    }

    public function getTemplatePath(): ?string
    {
        if (function_exists('get_template_directory')) {
            return get_template_directory();
        }

        return null;
    }

    private function createLivewireTemplate(string $className): void
    {
        $this->newLine();
        $this->info(sprintf('Handling Livewire template for %s...', $className));

        if ($pathToLivewireClass = ClassService::getFilePathFromClassName(className: $className)) {
            $livewireViewsPath = $this->getLivewireViewsDirectory();
            $livewireHorizonViewsPath = $this->getLivewireHorizonViewsDirectory();

            if (file_exists($livewireHorizonViewsPath)) {
                $explode = explode('src/Livewire', $pathToLivewireClass);

                if (isset($explode[1])) {
                    $nameBase = rtrim(ltrim($explode[1], '/'), '.php');

                    // Convertit les majuscules en tirets suivis de minuscules
                    $converted = strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $nameBase));
                    // Remplace les espaces ou les barres obliques par des tirets
                    $converted = str_replace([' ', '/'], ['-', '/'], $converted);

                    $name = $converted . '.blade.php';

                    if (file_exists($livewireHorizonViewsPath . $name)) {
                        $finalPath = $livewireViewsPath . $name;

                        if (file_exists($finalPath)) {
                            $this->error(sprintf('Livewire template already exists at %s', $livewireViewsPath . $name));
                            return;
                        }

                        FileService::filePutContentsAndCreateMissingDirectories(
                            $finalPath,
                            file_get_contents($livewireHorizonViewsPath . $name),
                        );
                    }
                }
            }
        }
    }

    private function createLivewireComponent(string $className): void
    {
        $this->newLine();
        $this->info(sprintf('Handling Livewire controller for %s...', $className));

        if ($pathToLivewireClass = ClassService::getFilePathFromClassName(className: $className)) {
            $livewireContent = file_get_contents($pathToLivewireClass);
            $livewireContent = str_replace('Adeliom\\HorizonBlocks\\Livewire\\', 'App\\Livewire\\', $livewireContent);

            $path = $this->getTemplatePath() . '/app/Livewire/';
            $structure = CommandService::getFolderStructure(
                str_replace('\\', '/', str_replace('Adeliom\\HorizonBlocks\\Livewire\\', '', $className)),
            );

            $finalPath = $path . $structure['path'];

            if (file_exists($finalPath)) {
                $this->error(sprintf('Livewire controller already exists at %s', $finalPath));
                return;
            }

            FileService::filePutContentsAndCreateMissingDirectories($finalPath, $livewireContent);
        }
    }

    private function createBlockControllerFile(string $className, array $folders, string $pathToBlockControllerFile, array $structure): void
    {
        $this->newLine();
        $this->info('Handling block controller...');

        $blockClassContent = file_get_contents($pathToBlockControllerFile);
        $blockClassContent = str_replace('Adeliom\\HorizonBlocks\\Blocks\\', 'App\\Blocks\\', $blockClassContent);

        $path = $this->getTemplatePath() . '/app/Blocks/';
        $filepath = $path . $structure['path'];

        $result = CommandService::handleClassCreation(AbstractBlock::class, $filepath, $path, $folders, $className, $blockClassContent);

        if ($result === 'already_exists') {
            $this->error(sprintf('Block controller already exists at %s', $filepath));
        }
    }

    private function createBlockBladeFile(string $className, array $folders): void
    {
        $this->newLine();
        $this->info('Handling block template...');

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
            $this->error(sprintf('Block template already exists at %s', $blockPath . $slug . '.blade.php'));
            return;
        }

        $blockViewPath = __DIR__ . '/../../..' . $this->getViewsDirectory() . 'blocks/' . $folderPath . $slug . '.blade.php';

        if (!file_exists($blockViewPath)) {
            $this->error(sprintf('Block template not found at %s', $blockViewPath));
            return;
        }

        FileService::filePutContentsAndCreateMissingDirectories($blockPath . $slug . '.blade.php', file_get_contents($blockViewPath));
    }
}
