<div>
    <form wire:change="handleFilters">
        @if($filters)
            <div>
                @foreach($filters as $type=>$filter)
                    <x-horizon.filter :values="$filterFields" :value="$filter"
                                      :model="'filterFields.'.$filter['name']"/>
                @endforeach

                    @if(!empty($secondaryFilters))
                        @php
                            $secondaryFiltersUniqId = uniqid();
                        @endphp

                        <button class="secondary-filters-btn" data-for="{{ $secondaryFiltersUniqId }}" wire:ignore>
                            {{ !empty($secondaryFiltersButtonLabel) ? $secondaryFiltersButtonLabel : 'Filtres avancés' }}
                        </button>

                        <div class="secondary-filters hidden" wire:ignore data-id="{{$secondaryFiltersUniqId}}">
                            <button data-close="{{ $secondaryFiltersUniqId }}">Fermer les filtres avancés</button>

                            @foreach($secondaryFilters as $type => $secondaryFilter)
                                <x-horizon.filter :values="$secondaryFilterFields" :value="$secondaryFilter"
                                                  :model="'secondaryFilterFields.'.$secondaryFilter['name']"/>
                            @endforeach
                        </div>
                    @endif

            </div>
        @endif

        <div class="flex justify-between">
            <x-horizon.results-counter :value="$data" singular="élément" plural="éléments"/>
            <x-horizon.sort model="order" :options="$sortOptions"/>
        </div>
    </form>

    <button wire:click="resetFilters">Ré-initialiser</button>

    <div class="loading hidden">
        Loading
    </div>

    @if(!empty($data['items']))
        <div class="results">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3">
                @foreach($data['items'] as $post)
                    <x-dynamic-component :component="$card" :content="$post"/>
                @endforeach
            </div>
        </div>
    @else
        <div>
            Aucun élément
        </div>
    @endif

    <x-horizon.pagination :data="$data" handle="setPage" :has-buttons="true"/>

    @script
    <script>
        $wire.on('filters-reset', () => {
            // Ré-écriture de l'URL
            window.history.pushState({}, '', '');
        });
    </script>
    @endscript
</div>
