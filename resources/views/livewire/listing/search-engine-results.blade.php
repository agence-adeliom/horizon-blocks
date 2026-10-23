@php
    use Adeliom\HorizonTools\Services\SeoService;
    use Adeliom\HorizonTools\Services\StringService;
@endphp

<div class="results-container">
    <div class="results-header">
        @if (!empty($headerImage))
            <div class="results-header-background" wire:ignore>
                {!! wp_get_attachment_image(
                    attachment_id: $headerImage['ID'],
                    size: 'large',
                    attr: ['class' => 'results-header-image'],
                ) !!}
            </div>
        @endif

        <div class="results-header-content">
            @if ($displayBreadcrumbs)
                {{-- Affichage du fil d'ariane si nécessaire --}}
                <div wire:ignore>
                    {{ SeoService::getBreadcrumbs() }}
                </div>
            @endif

            @if (!empty($headerTitle))
                {{-- Affichage du titre dynamique --}}
                <div class="results-header-title">
                    <h1 class="h1">
                        {{ $headerTitle }}
                    </h1>
                </div>
            @endif
        </div>
    </div>

    <form class="results-search" role="search" aria-label="{{ __('Recherche', 'horizon-blocks') }}" @submit.prevent>
        {{-- Champ permettant de modifier la recherche --}}
        <label for="search-engine-query">{{ __('Rechercher sur le site', 'horizon-blocks') }}</label>
        <input id="search-engine-query" type="search" wire:model.live.debounce="searchQuery"
            placeholder="{{ __('Saisissez votre recherche...', 'horizon-blocks') }}">
    </form>

    <p class="sr-only" role="status" aria-atomic="true">
        @if (empty($searchQuery))
            {{ __('Veuillez saisir une requête de recherche.', 'horizon-blocks') }}
        @elseif(empty($results))
            {{ sprintf(__('Aucun résultat trouvé pour « %s ».', 'horizon-blocks'), $searchQuery) }}
        @elseif($separateResultsByType)
            @php($total = array_sum(array_column($results, 'total')))
            {{ $total }} {{ StringService::singularOrPlural($total, 'résultat', 'résultats') }}
        @else
            {{ $results['total'] }} {{ StringService::singularOrPlural($results['total'], 'résultat', 'résultats') }}
        @endif
    </p>

    @if (!empty($results))
        @if ($separateResultsByType)
            <x-search-engine.separated-results :display-type-filters="$displayTypeFilters" :type-choices="$typeChoices" :results="$results" :found-post-types="$foundPostTypes"
                :type-choice="$typeChoice" :per-page="$perPage" :total-per-type="$totalPerType" />
        @else
            <x-search-engine.merged-results :display-type-filters="$displayTypeFilters" :type-choices="$typeChoices" :results="$results" :type-choice="$typeChoice"
                :found-post-types="$foundPostTypes" :per-page="$perPage" :total-per-type="$totalPerType" />
        @endif
    @else
        <p aria-hidden="true">
            @if (empty($searchQuery))
                {{ __('Veuillez saisir une requête de recherche.', 'horizon-blocks') }}
            @else
                {{ sprintf(__('Aucun résultat trouvé pour « %s ».', 'horizon-blocks'), $searchQuery) }}
            @endif
        </p>
    @endif
</div>

@script
    <script>
        $wire.on('setMetaTitle', (params) => {
            if (params[0]?.title) {
                document.title = params[0].title;
            }
        });
    </script>
@endscript
