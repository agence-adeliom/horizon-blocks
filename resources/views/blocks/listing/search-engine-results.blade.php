@php use Adeliom\HorizonTools\Services\SearchEngineService; @endphp

@if(SearchEngineService::canSearchEngineBeUsed())
    <x-block :fields="$fields" :block="$block" container="none">
        <livewire:listing.search-engine-results />
    </x-block>
@elseif(is_admin())
    <x-block>
        @if(!SearchEngineService::isSearchEngineEnabled())
            <p>{{__('Veuillez contacter Adeliom afin d’activer le moteur de recherche')}}</p>
        @else
            <p>{{__('Veuillez configurer le moteur de recherche')}}</p>
            @if($configUrl = SearchEngineService::getSearchEngineConfigPageUrl())
                <a target="_blank" style="margin-top: 16px;" class="button btn" href="{{$configUrl}}">Accéder à la page
                    de configuration</a>
            @endif
        @endif
    </x-block>
@endif
