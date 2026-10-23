@php
    use Adeliom\HorizonTools\Services\StringService;
    use App\Livewire\Listing\SearchEngineResults;
@endphp

<div id="listing-container" search-results-container="separated">
    @if ($displayTypeFilters)
        {{-- Affichage du filtre par type de résultat --}}
        <fieldset class="results-filters">
            <legend>{{ __('Type de contenu', 'horizon-blocks') }}</legend>

            @foreach ($typeChoices as $typeSlug => $typeName)
                <input id="type_{{ $typeSlug }}" type="radio" name="type_filter" wire:model.live="typeChoice"
                    value="{{ $typeSlug }}" @if ($typeChoice === $typeSlug) checked="checked" @endif>
                <label for="type_{{ $typeSlug }}">
                    {{ $typeName }}

                    @if ($typeSlug !== SearchEngineResults::VALUE_ALL_TYPE && !empty($totalPerType[$typeSlug]))
                        <span>
                            {{-- Nombre de résultats par type --}}
                            ({{ $totalPerType[$typeSlug] }}<span class="sr-only">
                                {{ StringService::singularOrPlural($totalPerType[$typeSlug], 'résultat', 'résultats') }}</span>)
                        </span>
                    @endif
                </label>

                @if ($typeSlug !== 'all')
                    {{-- Style permettant de masquer en fonction du type --}}
                    <style>
                        [search-results-container="separated"]:has(#type_{{ $typeSlug }}[name="type_filter"]:checked) [search-results]:not([search-results="{{ $typeSlug }}"]) {
                            display: none;
                        }

                        [search-results-container="separated"]:has(#type_{{ $typeSlug }}[name="type_filter"]:checked) [search-results] [search-results-title] {
                            display: none;
                        }
                    </style>
                @endif
            @endforeach
        </fieldset>
    @endif
    {{-- Conteneur des résultats séparés par type --}}
    <div class="grid grid-cols-1 gap-4">
        @foreach ($results as $postTypeSlug => $postTypeData)
            <section search-results="{{ $postTypeSlug }}" aria-labelledby="search-results-title-{{ $postTypeSlug }}"
                class="@if (!$loop->first) mt-8 @endif">
                <div search-results-title class="flex gap-2">
                    {{-- Titre de la section de résultats, avec son nombre de résultats --}}
                    <h2 id="search-results-title-{{ $postTypeSlug }}">
                        {{ $postTypeData['title'] }}
                        <span>({{ $postTypeData['total'] }}<span class="sr-only">
                                {{ StringService::singularOrPlural($postTypeData['total'], 'résultat', 'résultats') }}</span>)</span>
                    </h2>
                </div>

                {{-- Affichage des résultats --}}
                <ul role="list" id="listing-results-{{ $postTypeSlug }}" tabindex="-1"
                    aria-labelledby="search-results-title-{{ $postTypeSlug }}"
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 transition-all outline-none {{ $blockLoadingClass }}"
                    search-results-grid wire:target="searchQuery, setTypePage"
                    wire:loading.class="{{ $loadingClass }}">
                    {{-- Faire en sorte de gérer le blur via le Js --}}
                    {{-- Il faudra l'activer et le désactiver potentiellement une fois le loading terminé --}}
                    @foreach ($postTypeData['items'] as $item)
                        @if ($item->card)
                            <li class="grid"
                                wire:key="search-result-{{ $postTypeSlug }}-{{ $item->ID ?? 'card-' . $loop->index }}">
                                <x-dynamic-component :component="$item->card" :content="$item" />
                            </li>
                        @endif
                    @endforeach
                </ul>

                <x-horizon.pagination :data="$postTypeData" handle="setTypePage" :extra-handle-params="$postTypeData['extraHandleParams']" :extra-handle-params-first="true"
                    id-to-focus="listing-results-{{ $postTypeSlug }}" :has-buttons="true"
                    container-class="pagination pagination-{{ $postTypeSlug }}" />
            </section>
        @endforeach
    </div>
</div>

@script
    <script>
        const searchResultContainers = Array.from(document.querySelectorAll('[search-results]'));
        const gridContainers = Array.from(document.querySelectorAll('[search-results-grid]'));

        if (searchResultContainers.length > 0) {
            searchResultContainers.forEach(container => {
                const paginationElt = container.querySelector('.pagination');
                const gridContainer = container.querySelector('[search-results-grid]');

                if (paginationElt && gridContainer) {
                    paginationElt.addEventListener('click', (e) => {
                        let realTarget = e.target;

                        if (!realTarget.hasAttribute('wire:click.prevent') && e.target.closest(
                                '[wire\\:click\\.prevent]')) {
                            // If the target is not a link with wire:click.prevent, we prevent the default action
                            realTarget = e.target.closest('[wire\\:click\\.prevent]');
                        } else if (!realTarget.hasAttribute('wire:click.prevent')) {
                            realTarget = null;
                        }

                        if (realTarget) {
                            gridContainer.classList.remove('{{ $blockLoadingClass }}');
                        }
                    });
                }
            });
        }
    </script>
@endscript
