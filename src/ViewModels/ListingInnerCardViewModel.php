<?php

declare(strict_types=1);

namespace Adeliom\HorizonBlocks\ViewModels;

use Adeliom\HorizonTools\Services\ClassService;
use Illuminate\View\View;

class ListingInnerCardViewModel
{
	public ?string $class = null;
	public ?int $position = null;
	public null|string|array $pages = null;
	public ?int $timesAlreadyDisplayed = null;
	public ?string $bladeComponentName = null;
	public ?string $html = null;

	public function getClass(): ?string
	{
		return $this->class;
	}

	public function setClass(?string $class): self
	{
		$this->class = $class;

		$this->setBladeComponentName(ClassService::convertComponentClassNameToBladeComponentName($class));

		return $this;
	}

	public function getPosition(): ?int
	{
		return $this->position;
	}

	public function setPosition(?int $position): self
	{
		$this->position = $position;

		return $this;
	}

	public function getPages(): null|string|array
	{
		return $this->pages;
	}

	public function setPages(null|string|array $pages): self
	{
		if (is_array($pages)) {
			sort($pages);
		}

		$this->pages = $pages;

		return $this;
	}

	public function getTimesAlreadyDisplayed(): ?int
	{
		return $this->timesAlreadyDisplayed;
	}

	public function setTimesAlreadyDisplayed(int $currentPage, ?int $timesAlreadyDisplayed = null): self
	{
		if (null === $timesAlreadyDisplayed) {
			$timesAlreadyDisplayed = 0;

			if (is_array($this->getPages())) {
				foreach ($this->getPages() as $page) {
					if ($page < $currentPage) {
						$timesAlreadyDisplayed++;
					}
				}
			} elseif (is_string($this->getPages())) {
				$timesAlreadyDisplayed = $currentPage - 1;
			}
		}

		$this->timesAlreadyDisplayed = $timesAlreadyDisplayed;

		return $this;
	}

	public function getBladeComponentName(): ?string
	{
		return $this->bladeComponentName;
	}

	public function setBladeComponentName(?string $bladeComponentName): self
	{
		$this->bladeComponentName = $bladeComponentName;

		return $this;
	}

	public function render(array $data = []): void
	{
		$html = null;

		$class = $this->getClass();

		$component = new $class;

		$view = $component->render();

		if ($view instanceof View) {
			$html = $view->toHtml();
		}

		$this->setHtml($html);
	}

	public function getHtml(array $data = []): ?string
	{
		$this->render(data: $data);

		return $this->html;
	}

	public function setHtml(?string $html): self
	{
		$this->html = $html;

		return $this;
	}

	public function toStdClass(): \stdClass
	{
		$stdClass = new \stdClass();
		$stdClass->class = $this->getClass();
		$stdClass->position = $this->getPosition();
		$stdClass->pages = $this->getPages();
		$stdClass->timesAlreadyDisplayed = $this->getTimesAlreadyDisplayed();
		$stdClass->bladeComponentName = $this->getBladeComponentName();
		$stdClass->html = $this->getHtml();

		return $stdClass;
	}
}