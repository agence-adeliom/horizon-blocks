<?php

declare(strict_types=1);

namespace Adeliom\HorizonBlocks\Blocks\Listing;

use Adeliom\HorizonTools\Blocks\AbstractBlock;
use Adeliom\HorizonTools\Services\BudService;
use Adeliom\HorizonTools\Services\SearchEngineService;
use Extended\ACF\Fields\Message;

class SearchEngineResultsBlock extends AbstractBlock
{
	public static ?string $slug = 'search-engine-results';
	public static ?string $title = 'Résultats du moteur de recherche';
	public static ?string $mode = 'preview';

	public function getFields(): ?iterable
	{
		if ($configUrl = SearchEngineService::getSearchEngineConfigPageUrl()) {
			yield Message::make(__('Configuration du moteur de recherche'))
				->body(sprintf('Pour configurer le moteur de recherche, rendez-vous dans <a target="_blank" href="%s">les options du moteur de recherche</a>', $configUrl));
		}
	}

	public function addToContext(): array
	{
		return [];
	}

	public function renderBlockCallback(): void
	{
		if ($searchEngineJs = BudService::getUrl('search-engine-results.js')) {
			wp_enqueue_script('search-engine-results-block', $searchEngineJs);
		}

		if ($searchEngineCss = BudService::getUrl('search-engine-results.css')) {
			wp_enqueue_script('search-engine-results-block', $searchEngineCss);
		}
	}
}
