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
                            $secondaryFiltersUniqId = 'secondary-filters-drawer';
                        @endphp

                        <button class="secondary-filters-btn" data-for="{{ $secondaryFiltersUniqId }}">
                            {{ !empty($secondaryFiltersButtonLabel) ? $secondaryFiltersButtonLabel : 'Filtres avancés' }}
                        </button>

                        <div class="secondary-filters hidden" wire:ignore.self data-id="{{$secondaryFiltersUniqId}}">
                            <div class="secondary-filters--container">
                                <div class="secondary-filters--header">
                                    @if($secondaryFiltersTitle)
                                        <p class="secondary-filters--title">{{ $secondaryFiltersTitle }}</p>
                                    @endif
                                    <button class="secondary-filters--close" data-close="{{ $secondaryFiltersUniqId }}">
                                        Fermer les filtres avancés
                                    </button>
                                </div>

                                <div class="secondary-filters--filters">
                                    @foreach($secondaryFilters as $type => $secondaryFilter)
                                        <x-horizon.filter :values="$secondaryFilterFields" :value="$secondaryFilter"
                                                          :model="'secondaryFilterFields.'.$secondaryFilter['name']"/>
                                    @endforeach
                                </div>

                                <div class="secondary-filters--btns">
                                    <button class="secondary-filters--reset" wire:click="resetFilters"
                                            data-reset="{{ $secondaryFiltersUniqId }}">Tout effacer
                                    </button>
                                    <button class="secondary-filters--apply" data-apply="{{ $secondaryFiltersUniqId }}">
                                        Afficher les résultats
                                    </button>
                                </div>
                            </div>
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

        $wire.on('qat-reset', () => {
            // Remove all parameters starting with 'qat-' from the URL
            const url = new URL(window.location.href);
            const params = url.searchParams;
            const keys = Array.from(params.keys());
            keys.forEach(key => {
                if (key.startsWith('qat-')) {
                    params.delete(key);
                }
            });
            window.history.pushState({}, '', url.toString());
        });
    </script>
    @endscript
</div>
