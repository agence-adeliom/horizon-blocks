<?php

declare(strict_types=1);

namespace Adeliom\HorizonBlocks\Livewire\Listing;

use Adeliom\HorizonTools\Admin\SearchEngineOptionsAdmin;
use Adeliom\HorizonTools\Services\PostService;
use Adeliom\HorizonTools\Services\SearchEngineService;
use Adeliom\HorizonTools\Services\SeoService;
use Illuminate\View\View;
use Livewire\Component;

class SearchEngineResults extends Component
{
	public array $types = [];
	public int $perPage = 12;
	public int|array $page = 1;
	public bool $separateResultsByType = false;
	public bool $displayTypeFilters = false;
	public bool $displayBreadcrumbs = false;
	public array $results = [];
	public array $typeChoices = [];
	public string $typeChoice = self::VALUE_ALL_TYPE;
	public array $resultsPerType = [];
	public array $typesToFetch = [];
	public ?string $searchQuery = '';
	public ?string $headerTitle = null;
	public ?array $headerImage = null;
	public ?array $foundPostTypes = [];
	public ?array $totalPerType = [];
	public bool $addPageInMetaTitle = true;

	private readonly array $searchConfig;

	public const VALUE_ALL_TYPE = 'all';

	public function mount(): void
	{
		$this->triggerChange();
	}

	public function updated(): void
	{
		$this->triggerChange();
	}

	/**
	 * Updates the meta-title based on the search query.
	 */
	public function updatedSearchQuery(): void
	{
		if ($metaTitle = SearchEngineService::getMetaTitle()) {
			if (str_contains($metaTitle, SearchEngineOptionsAdmin::SEARCH_PLACEHOLDER)) {
				$metaTitle = str_replace(SearchEngineOptionsAdmin::SEARCH_PLACEHOLDER, $this->searchQuery, $metaTitle);
				$metaTitle = $this->handleMetaTitlePagination($metaTitle);
				$metaTitle = sprintf('%s %s', $metaTitle, SeoService::getMetaTitleSuffix());

				$this->dispatch('setMetaTitle', [
					'title' => $metaTitle,
				]);
			}
		}
	}

	private function handleMetaTitlePagination($metaTitle): string
	{
		if ($this->addPageInMetaTitle) {
			if (is_array($this->page) || $this->page > 1) {
				$metaTitle = SeoService::appendPageToMetaTitle($metaTitle, $this->page);
			}
		}

		return $metaTitle;
	}

	/**
	 * Runs the necessary methods to reinitialize the component's data
	 */
	public function triggerChange(): void
	{
		if (is_admin()) {
			// Here to display results in the Gutenberg editor by default
			$this->searchQuery = 'a';
		}

		if (!isset($this->searchConfig)) {
			$this->searchConfig = SearchEngineService::getSearchEngineConfig();
		}

		$this->initConfig();
		$this->initData();
		$this->fetchData();
	}

	/**
	 * Sets the search query and triggers a re-fetch of the results.
	 */
	public function setPage(int $page, ?string $postType = null): void
	{
		$hasChanged = false;

		if (null === $postType) {
			$this->page = $page;
			$hasChanged = true;
		} else {
			if (isset($this->page[$postType])) {
				$this->page[$postType] = $page;
				$hasChanged = true;
			}
		}

		if ($hasChanged) {
			if ($this->addPageInMetaTitle) {
				$this->updatedSearchQuery();
			}

			$this->triggerChange();
		}
	}

	public function setTypePage(string $postType, int $page): void
	{
		$this->setPage($page, $postType);
	}

	protected function queryString(): array
	{
		return [
			'searchQuery' => [
				'as' => SearchEngineService::getSearchEngineGETParameter() ?? 'recherche',
			],
			'page' => [
				'as' => SearchEngineService::getSearchEnginePageGETParameter() ?? 'pagination',
				'except' => '1',
			],
			'typeChoice' => [
				'as' => 'type',
				'except' => self::VALUE_ALL_TYPE,
			]
		];
	}

	/**
	 * Initializes the configuration based on the search engine settings.
	 */
	private function initConfig(): void
	{
		$this->separateResultsByType = SearchEngineService::getSeparateByTypes();
		$this->displayTypeFilters = SearchEngineService::getAllowFilterByType();
		$this->displayBreadcrumbs = SearchEngineService::getDisplayBreadcrumbs();
		$this->addPageInMetaTitle = SearchEngineService::getAddPageToMetaTitle();

		if ($perPage = SearchEngineService::getPerPage()) {
			$this->perPage = $perPage;
		}

		if ($postTypes = SearchEngineService::getPostTypes()) {
			$this->types = $postTypes;
			$this->typesToFetch = $postTypes;
		}


		if ($getParameter = SearchEngineService::getSearchEngineGETParameter()) {
			$this->searchParam = $getParameter;
		}

		if ($headerTitle = SearchEngineService::getHeaderTitle()) {
			if (str_contains($headerTitle, SearchEngineOptionsAdmin::SEARCH_PLACEHOLDER)) {
				$headerTitle = str_replace(SearchEngineOptionsAdmin::SEARCH_PLACEHOLDER, $this->searchQuery, $headerTitle);
			}

			$this->headerTitle = $headerTitle;
		}

		if ($headerImage = SearchEngineService::getHeaderImage()) {
			$this->headerImage = $headerImage;
		}

		if ($this->separateResultsByType && is_int($this->page)) {
			$this->page = [];

			foreach ($this->types as $type) {
				$this->page[$type] = 1;
			}
		} elseif (!$this->separateResultsByType && is_array($this->page)) {
			$this->page = 1;
		}
	}

	/**
	 * Initializes the data for the component, including type choices and results to fetch.
	 */
	private function initData(): void
	{
		$this->typeChoices[self::VALUE_ALL_TYPE] = 'Tous les résultats';

		foreach ($this->types as $typeSlug) {
			if ($label = PostService::getPostPrettyNameBySlug($typeSlug)) {
				$this->typeChoices[$typeSlug] = $label;
			}
		}

		if (!$this->separateResultsByType) {
			if ($this->displayTypeFilters && $this->typeChoice !== self::VALUE_ALL_TYPE && in_array($this->typeChoice, $this->types)) {
				$this->typesToFetch = [$this->typeChoice];
			} else {
				$this->typesToFetch = $this->types;
			}
		} else {
			foreach ($this->page as $postTypeSlug => $page) {
				if (is_numeric($page)) {
					$page = intval($page);
				}

				$this->page[$postTypeSlug] = $page;
			}
		}
	}

	private function fetchData(): void
	{
		$this->foundPostTypes = [];
		$this->totalPerType = [];

		$this->results = $this->getResults(foundPostTypes: $this->foundPostTypes);

		$this->handleTypeChoices();
		$this->handlePageReset();
	}

	private function handleTypeChoices(): void
	{
		if ($this->separateResultsByType) {
			foreach ($this->typeChoices as $typeSlug => $typeChoice) {
				if ($typeSlug !== self::VALUE_ALL_TYPE) {
					if (empty($this->results[$typeSlug])) {
						unset($this->typeChoices[$typeSlug]);

						if ($this->typeChoice === $typeSlug) {
							$this->typeChoice = self::VALUE_ALL_TYPE;
						}
					}
				}
			}
		} else {
			foreach ($this->typeChoices as $typeSlug => $typeChoice) {
				if ($typeSlug !== self::VALUE_ALL_TYPE && $typeSlug === $this->typeChoice) {
					if (!in_array($this->typeChoice, $this->foundPostTypes)) {
						$this->typeChoice = self::VALUE_ALL_TYPE;
					}
				}
			}
		}
	}

	/**
	 * Fetches the search results based on the current configuration.
	 */
	private function getResults(array &$foundPostTypes = []): array
	{
		if (empty($this->searchQuery)) {
			return [];
		}

		$hasResetTypeToFilter = false;

		return SearchEngineService::searchPostTypes(postTypes: $this->types, onlyGetResultsFromPostTypes: $this->typesToFetch, query: $this->searchQuery, separateResultsByType: $this->separateResultsByType, page: $this->page, perPage: $this->perPage, foundPostTypes: $foundPostTypes, totalPerType: $this->totalPerType, hasResetTypeToFilter: $hasResetTypeToFilter);
	}

	/**
	 * Handles the reset of the page number when there are no results.
	 */
	private function handlePageReset(): void
	{
		if (!$this->separateResultsByType) {
			if (isset($this->results['total']) && $this->results['total'] === 0) {
				$this->setPage(1);
			}
		} else {
			foreach ($this->results as $postTypeSlug => $postTypeData) {
				$postTypeData['extraHandleParams'] = [$postTypeSlug];

				$this->results[$postTypeSlug] = $postTypeData;

				if (!empty($this->results[$postTypeSlug]) && $this->results[$postTypeSlug]['total'] === 0) {
					$this->setPage(1, $postTypeSlug);
				}
			}
		}
	}

	public function clickOnFilter(): void
	{
		// Empty method just to allow specific loading attribute
	}

	public function render(): View
	{
		return view('livewire.listing.search-engine-results');
	}
}
