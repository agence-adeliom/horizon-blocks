@php use Adeliom\HorizonTools\Services\SeoService; @endphp

<div class="results-container">
    <div class="results-header">
        @if(!empty($headerImage))
            <div class="results-header-background" wire:ignore>
                {!! wp_get_attachment_image(attachment_id: $headerImage['ID'], size: 'large',attr: ['class'=>'results-header-image']) !!}
            </div>
        @endif

        <div class="results-header-content">
            @if($displayBreadcrumbs)
                {{-- Affichage du fil d'ariane si nécessaire --}}
                <div wire:ignore>
                    {{SeoService::getBreadcrumbs()}}
                </div>
            @endif

            @if(!empty($headerTitle))
                {{-- Affichage du titre dynamique --}}
                <div class="results-header-title">
                    <h1 class="h1">
                        {{ $headerTitle }}
                    </h1>
                </div>
        @endif
    </div>
    </div>

    <div class="results-search">
        {{-- Champ permettant de modifier la recherche --}}
        <input type="text" wire:model.live.debounce="searchQuery" placeholder="{{__('Saisissez votre recherche...')}}">
    </div>

    @if(!empty($results))
        @if($separateResultsByType)
            <x-search-engine.separated-results :display-type-filters="$displayTypeFilters"
                                               :type-choices="$typeChoices" :results="$results"
                                               :found-post-types="$foundPostTypes" :type-choice="$typeChoice"
                                               :per-page="$perPage" :total-per-type="$totalPerType" />
        @else
            <x-search-engine.merged-results :display-type-filters="$displayTypeFilters" :type-choices="$typeChoices"
                                            :results="$results" :type-choice="$typeChoice"
                                            :found-post-types="$foundPostTypes" :per-page="$perPage"
                                            :total-per-type="$totalPerType" />
        @endif
    @else
        @if(empty($searchQuery))
            Veuillez saisir une requête de recherche.
        @else
            Aucun résultat trouvé pour "{{ $searchQuery }}".
        @endif
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
