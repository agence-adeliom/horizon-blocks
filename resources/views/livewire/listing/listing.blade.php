@php
    use Adeliom\HorizonTools\Services\StringService;

    $total = (int) ($data['total'] ?? 0);
    $pages = (int) ($data['pages'] ?? 0);

    /*
        Text of the status message: the number of results, and the page when there are several, so a
        page change is announced too (the number of results does not change then).
    */
    $status = $total
        ? sprintf('%d %s', $total, StringService::singularOrPlural(count: $total, singular: $labelSingular, plural: $labelPlural))
        : 'Aucun élément';

    if ($total && $pages > 1) {
        $status .= sprintf(' – page %d sur %d', (int) $data['current'], $pages);
    }

    /*
        Page title (RGAA 8.6): search, filters and page change through Livewire without reloading the
        page, so the <title> has to say them (« Recherche « x » – Filtres : A, B – Page 2 sur 3 »). The
        script below puts this text in front of the original title after every render.
    */
    $searchTerms = [];
    $activeFilters = [];

    foreach ([[$filters, $filterFields], [$secondaryFilters, $secondaryFilterFields]] as [$levelFilters, $levelValues]) {
        foreach ($levelFilters ?: [] as $filter) {
            $value = $levelValues[$filter['name'] ?? ''] ?? null;

            if (!empty($filter['isSearch'])) {
                if (is_string($value) && '' !== trim($value)) {
                    $searchTerms[] = trim($value);
                }

                continue;
            }

            $names = array_column($filter['choices'] ?? [], 'name', 'slug');

            // Checkboxes and multiselect: [slug => 'true'|'false'] ; other appearances: the slug.
            $slugs = is_array($value)
                ? array_keys(array_filter($value, static fn ($state) => true === $state || 'true' === $state))
                : [$value];

            foreach ($slugs as $slug) {
                if (null !== $slug && '' !== $slug && $slug !== ($filter['choiceAllValue'] ?? null) && isset($names[$slug])) {
                    $activeFilters[] = $names[$slug];
                }
            }
        }
    }

    $titleParts = array_filter([
        $searchTerms ? sprintf('Recherche « %s »', implode(' ', $searchTerms)) : null,
        $activeFilters ? sprintf('Filtres : %s', implode(', ', $activeFilters)) : null,
        $pages > 1 ? sprintf('Page %d sur %d', (int) $data['current'], $pages) : null,
    ]);
@endphp

<div id="listing-container">
    {{--
        role="search": landmark of the filters (RGAA 12.6). @submit.prevent: with a single text field,
        Enter submits the form natively and reloads the page. Not wire:submit.prevent, which disables
        every field during the request and loses the focus.
    --}}
    <form wire:change="handleFilters" role="search" aria-label="Filtres" @submit.prevent>
        @if($filters)
            <div>
                @foreach($filters as $type=>$filter)
                    <x-horizon.filter :values="$filterFields" :value="$filter"
                                      :model="'filterFields.'.$filter['name']"/>
                @endforeach

                @if(!empty($secondaryFilters))
                    @php
                        $secondaryFiltersUniqId = 'secondary-filters-' . $this->getId();
                    @endphp

                    {{-- wire:ignore.self: aria-expanded is set by the script, a Livewire render must not reset it. --}}
                    <button type="button" class="secondary-filters-btn" data-for="{{ $secondaryFiltersUniqId }}"
                            aria-expanded="false" aria-controls="{{ $secondaryFiltersUniqId }}" wire:ignore.self>
                        {{ !empty($secondaryFiltersButtonLabel) ? $secondaryFiltersButtonLabel : 'Filtres avancés' }}
                    </button>

                    <div class="secondary-filters hidden" wire:ignore.self id="{{ $secondaryFiltersUniqId }}"
                         data-id="{{ $secondaryFiltersUniqId }}" role="group"
                         @if($secondaryFiltersTitle) aria-labelledby="{{ $secondaryFiltersUniqId }}-title" @endif>
                        <div class="secondary-filters--container">
                            <div class="secondary-filters--header">
                                @if($secondaryFiltersTitle)
                                    <p class="secondary-filters--title" id="{{ $secondaryFiltersUniqId }}-title">{{ $secondaryFiltersTitle }}</p>
                                @endif
                                <button type="button" class="secondary-filters--close" data-close="{{ $secondaryFiltersUniqId }}">
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
                                <button type="button" class="secondary-filters--reset" wire:click="resetSecondaryFilters"
                                        data-reset="{{ $secondaryFiltersUniqId }}">Tout effacer
                                </button>
                                <button type="button" class="secondary-filters--apply" data-apply="{{ $secondaryFiltersUniqId }}">
                                    Afficher les résultats
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        @endif

        @if($displayNumberOfResults || $displaySort)
            <div class="flex @if(!$displayNumberOfResults) justify-end @else justify-between @endif">
                @if($displayNumberOfResults)
                    {{-- Hidden from screen readers: the status message below already gives the number. --}}
                    <div aria-hidden="true">
                        <x-horizon.results-counter :value="$data" :singular="$labelSingular" :plural="$labelPlural"/>
                    </div>
                @endif

                @if($displaySort)
                    <x-horizon.sort model="order" :options="$sortOptions"/>
                @endif
            </div>
        @endif
    </form>

    @if(!empty($filters) || !empty($secondaryFilters))
        <button type="button" wire:click="resetFilters">Ré-initialiser</button>
    @endif

    {{--
        Status message (RGAA 7.5): present from the first render, otherwise screen readers ignore its
        updates. It is announced after every filter, sort or page change.
    --}}
    <p class="sr-only" role="status" aria-atomic="true">{{ $status }}</p>

    {{--
        Loading state driven by wire:loading rather than by a `change` listener on the form: the
        listener missed page and sort changes, and left the results hidden for good if the request
        failed. wire:loading reacts to any request made by the component.

        The `is-loading` class is only a hook — projects decide what it looks like. Keeping it on
        `.results` rather than on a shared parent lets a project blur the results without blurring
        its own loading indicator.
    --}}
    <div class="loading" wire:loading>
        Chargement…
    </div>

    @if(!empty($data['items']))
        <div class="results" wire:loading.class="is-loading">
            {{--
                The results are a list (RGAA 9.3). tabindex="-1": target of the focus after a page
                change (see components/horizon/pagination), it is kept in place by Livewire.
            --}}
            <ul role="list" id="listing-results" tabindex="-1" aria-label="Liste des {{ $labelPlural }}"
                class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 outline-none">
                @foreach($data['items'] as $post)
                    <li class="grid" wire:key="listing-item-{{ $post->ID ?? 'card-' . $loop->index }}">
                        @if(property_exists($post, 'timesAlreadyDisplayed'))
                            {!! $post->html !!}
                        @else
                            <x-dynamic-component :component="$card" :content="$post"/>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    @else
        <div>
            Aucun élément
        </div>
    @endif

    <x-horizon.pagination :data="$data" handle="setPage" :has-buttons="true"/>

    <span hidden data-listing-title="{{ implode(' – ', $titleParts) }}"></span>

    {{--
        Page title updated after every render. The morphed hook runs once the DOM is updated: commit +
        succeed runs before it and gives a title one render late.
        Nothing before the first `const` in the script: Alpine, which evaluates it, wraps it in a function
        only when it starts with const or let, otherwise the declarations are a syntax error.
    --}}
    @script
    <script>
        const baseTitle = document.title;
        const updateTitle = () => {
            const parts = $wire.$el.querySelector('[data-listing-title]')?.dataset.listingTitle;
            document.title = parts ? `${parts} – ${baseTitle}` : baseTitle;
        };

        updateTitle();

        Livewire.hook('morphed', ({ component }) => {
            if (component.id === $wire.$id) {
                updateTitle();
            }
        });

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
