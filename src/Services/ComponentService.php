<?php

declare(strict_types=1);

namespace Adeliom\HorizonBlocks\Services;

class ComponentService
{
	public static function getAllBundleComponents(): array
	{
		$components = [];

		$path = __DIR__ . '/../../resources/views/components';

		if (file_exists($path)) {
			// Get all file paths in folder and subfolders
			$files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));

			foreach ($files as $file) {
				$filename = $file->getFilename();

				if (!in_array($filename, ['.', '..'])) {
					$components[] = [
						'name' => $filename,
						'path' => $file->getPathname()
					];
				}
			}
		}

		return $components;
	}
}