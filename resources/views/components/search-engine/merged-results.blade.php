@php
    use Adeliom\HorizonTools\Services\StringService;
    use App\Livewire\Listing\SearchEngineResults;
@endphp

<div id="listing-container" search-results-container="merged">
    @if ($displayTypeFilters && $typeChoices)
        {{-- Affichage du filtre par type de résultat --}}
        <fieldset class="results-filters">
            <legend>{{ __('Type de contenu', 'horizon-blocks') }}</legend>

            @foreach ($typeChoices as $typeSlug => $typeName)
                @if ($typeSlug === SearchEngineResults::VALUE_ALL_TYPE || in_array($typeSlug, $foundPostTypes))
                    <input id="type_{{ $typeSlug }}" type="radio" name="type" wire:model.live="typeChoice"
                        value="{{ $typeSlug }}" @if ($typeChoice === $typeSlug) checked="checked" @endif
                        wire:click="clickOnFilter">
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
                @endif
            @endforeach
        </fieldset>
    @endif

    <p aria-hidden="true">
        <span wire:target="clickOnFilter" class="transition-all"
            wire:loading.class="blur-sm">{{ $results['total'] }}</span>{{ ' ' }}{{ StringService::singularOrPlural($results['total'], 'résultat', 'résultats') }}
    </p>

    {{-- Conteneur des résultats non-séparés par type --}}
    <ul role="list" id="listing-results" tabindex="-1"
        aria-label="{{ __('Résultats de la recherche', 'horizon-blocks') }}"
        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 transition-all outline-none"
        wire:loading.class="blur">
        @foreach ($results['items'] as $item)
            @if ($item->card)
                <li class="grid" wire:key="search-result-{{ $item->ID ?? 'card-' . $loop->index }}">
                    <x-dynamic-component :component="$item->card" :content="$item" />
                </li>
            @endif
        @endforeach
    </ul>

    <x-horizon.pagination :data="$results" handle="setPage" :has-buttons="true" />
</div>
