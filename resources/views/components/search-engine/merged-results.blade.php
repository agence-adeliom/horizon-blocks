@php use Adeliom\HorizonTools\Services\StringService;use App\Livewire\Listing\SearchEngineResults; @endphp

<div search-results-container="merged">
    @if($displayTypeFilters && $typeChoices)
        {{-- Affichage du filtre par type de résultat --}}
        <div class="results-filters">
            @foreach($typeChoices as $typeSlug => $typeName)
                @if($typeSlug === SearchEngineResults::VALUE_ALL_TYPE || in_array($typeSlug, $foundPostTypes))
                    <label for="type_{{$typeSlug}}">
                        {{$typeName}}

                        @if($typeSlug!== SearchEngineResults::VALUE_ALL_TYPE&&!empty($totalPerType[$typeSlug]))
                            <span>
                                {{-- Nombre de résultats par type --}}
                                {{ $totalPerType[$typeSlug] }}
                            </span>
                        @endif
                    </label>
                    <input id="type_{{$typeSlug}}" type="radio" wire:model.live="typeChoice" value="{{$typeSlug}}"
                           @if($typeChoice === $typeSlug) checked="checked" @endif wire:loading.attr="disabled"
                           wire:click="clickOnFilter">
                @endif
            @endforeach
        </div>
    @endif

    <p>
        <span wire:target="clickOnFilter" class="transition-all"
              wire:loading.class="blur-sm">{{ $results['total'] }}</span>{{ ' ' }}{{ StringService::singularOrPlural($results['total'], 'résultat', 'résultats') }}
    </p>

    {{-- Conteneur des résultats non-séparés par type --}}
    <div class="grid grid-cols-4 gap-4 transition-all" wire:loading.class="blur">
        @foreach($results['items'] as $item)
            @if($item->card)
                <x-dynamic-component :component="$item->card" :content="$item" />
            @endif
        @endforeach
    </div>

    <x-horizon.pagination :data="$results" handle="setPage" :has-buttons="true" />
</div>
