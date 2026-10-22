@php
    use Adeliom\HorizonTools\Services\StringService;

    $total = (int) ($data['total'] ?? 0);
    $pages = (int) ($data['pages'] ?? 0);

    /*
        Text of the status message: the number of results, and the page when there are several, so a
        page change is announced too (the number of results does not change then).
    */
    $status = $total
        ? sprintf(
            '%d %s',
            $total,
            StringService::singularOrPlural(count: $total, singular: $labelSingular, plural: $labelPlural),
        )
        : 'Aucun élément';

    if ($total && $pages > 1) {
        $status .= sprintf(' – page %d sur %d', (int) $data['current'], $pages);
    }

    $searchTerms = [];
    $activeFilters = [];

    foreach (
        [[$filters, $filterFields], [$secondaryFilters, $secondaryFilterFields]]
        as [$levelFilters, $levelValues]
    ) {
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
                ? array_keys(array_filter($value, static fn($state) => true === $state || 'true' === $state))
                : [$value];

            foreach ($slugs as $slug) {
                if (
                    null !== $slug &&
                    '' !== $slug &&
                    $slug !== ($filter['choiceAllValue'] ?? null) &&
                    isset($names[$slug])
                ) {
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
    <form wire:change="handleFilters" role="search" aria-label="Filtres" @submit.prevent>
        @if ($filters)
            <div class="listing-filters">
                <div class="listing-filters-fields">
                    @foreach ($filters as $type => $filter)
                        <x-horizon.filter :values="$filterFields" :value="$filter" :model="'filterFields.' . $filter['name']" />
                    @endforeach

                    @if (!empty($secondaryFilters))
                        @php
                            $secondaryFiltersUniqId = 'secondary-filters-' . $this->getId();
                        @endphp

                        <div class="secondary-filters" x-data="initModal()" wire:ignore.self>
                            <button type="button" class="secondary-filters-btn" aria-haspopup="dialog"
                                @click="openModal()">
                                {{ !empty($secondaryFiltersButtonLabel) ? $secondaryFiltersButtonLabel : 'Filtres avancés' }}
                            </button>

                            @teleport('body')
                                <div class="listing-drawer" wire:change="handleFilters">
                                    <x-structure.drawer :id="$secondaryFiltersUniqId" :aria-label="$secondaryFiltersTitle
                                        ? null
                                        : (!empty($secondaryFiltersButtonLabel)
                                            ? $secondaryFiltersButtonLabel
                                            : 'Filtres avancés')"
                                        class="secondary-filters--drawer">
                                        @if ($secondaryFiltersTitle)
                                            <x-slot:header>
                                                <p class="secondary-filters--title">{{ $secondaryFiltersTitle }}</p>
                                            </x-slot:header>
                                        @endif

                                        <div class="secondary-filters--filters">
                                            @foreach ($secondaryFilters as $type => $secondaryFilter)
                                                <x-horizon.filter :values="$secondaryFilterFields" :value="$secondaryFilter" :model="'secondaryFilterFields.' . $secondaryFilter['name']"
                                                    :as-dropdown="false" />
                                            @endforeach
                                        </div>

                                        <x-slot:footer class="secondary-filters--btns">
                                            <button type="button" class="secondary-filters--reset"
                                                wire:click="resetSecondaryFilters">
                                                Tout effacer
                                            </button>
                                            <button type="button" class="secondary-filters--apply" @click="close()">
                                                Afficher les résultats
                                            </button>
                                        </x-slot:footer>
                                    </x-structure.drawer>
                                </div>
                            @endteleport
                        </div>
                    @endif
                </div>

                <button type="button" class="listing-filters-reset" wire:click="resetFilters">
                    <x-fas-arrows-rotate />
                    Réinitialiser
                </button>
            </div>
        @endif

        @if ($displayNumberOfResults || $displaySort)
            <div class="flex @if (!$displayNumberOfResults) justify-end @else justify-between @endif">
                @if ($displayNumberOfResults)
                    <div aria-hidden="true">
                        <x-horizon.results-counter :value="$data" :singular="$labelSingular" :plural="$labelPlural" />
                    </div>
                @endif

                @if ($displaySort)
                    <x-horizon.sort model="order" :options="$sortOptions" />
                @endif
            </div>
        @endif
    </form>

    <p class="sr-only" role="status" aria-atomic="true">{{ $status }}</p>

    <div class="loading" wire:loading>
        Chargement…
    </div>

    @if (!empty($data['items']))
        <div class="results" wire:loading.class="is-loading">
            <ul role="list" id="listing-results" tabindex="-1" aria-label="Liste des {{ $labelPlural }}"
                class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 outline-none">
                @foreach ($data['items'] as $post)
                    <li class="grid" wire:key="listing-item-{{ $post->ID ?? 'card-' . $loop->index }}">
                        @if (property_exists($post, 'timesAlreadyDisplayed'))
                            {!! $post->html !!}
                        @else
                            <x-dynamic-component :component="$card" :content="$post" />
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

    <x-horizon.pagination :data="$data" handle="setPage" :has-buttons="true" />

    <span hidden data-listing-title="{{ implode(' – ', $titleParts) }}"></span>

    @script
        <script>
            const baseTitle = document.title;
            const updateTitle = () => {
                const parts = $wire.$el.querySelector('[data-listing-title]')?.dataset.listingTitle;
                document.title = parts ? `${parts} – ${baseTitle}` : baseTitle;
            };

            updateTitle();

            Livewire.hook('morphed', ({
                component
            }) => {
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
