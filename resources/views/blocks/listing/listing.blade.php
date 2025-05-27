@php
    use App\Blocks\Listing\ListingBlock;

    $postType = null;
    $perPage = 12;
    $filters = $fields['filters'] ?? [];
    $secondaryFilters = $fields['secondaryFilters'] ?? [];
    $secondaryFiltersLabel = $fields['secondaryFiltersButtonLabel'] ?? 'Filtres avancés';
    $hasSecondaryFilters = !empty($secondaryFilters) && $fields['withSecondaryFilters'];
    $secondaryFiltersTitle = $fields['secondaryFiltersTitle'] ?? null;
    $forcedFilters = $fields['forcedFilters'] ?? [];

    $labelSingular = !empty($fields[ListingBlock::FIELD_ELEMENTS_LABEL_SINGULAR]) ? $fields[ListingBlock::FIELD_ELEMENTS_LABEL_SINGULAR] :__('élément');
    $labelPlural = !empty($fields[ListingBlock::FIELD_ELEMENTS_LABEL_PLURAL]) ? $fields[ListingBlock::FIELD_ELEMENTS_LABEL_PLURAL]: __('éléments');

    $displaySort = $fields['displaySort'] ?? true;
    $displayNumberOfResults=$fields['displayNumberOfResults']??true;

    if(is_array($forcedFilters)){
        foreach ($forcedFilters as $forcedFilterKey=>$forcedFilterValue) {
        if(!empty($block['data'])){
            $key = sprintf('%s_%s_%s',ListingBlock::FIELD_FORCED_FILTERS,$forcedFilterKey,ListingBlock::FIELD_FILTERS_TAXONOMY_VALUE);

            // Search key in $block['data'] beginning with $key
            $realKeys = array_filter(array_keys($block['data']), function($k) use ($key) {
                return str_starts_with($k, $key);
            });

            if($realKey = array_pop($realKeys)){
                if(!empty($block['data'][$realKey])) {
                    $forcedFilters[$forcedFilterKey][ListingBlock::FIELD_FILTERS_TAXONOMY_VALUE]=array_map('intval',$block['data'][$realKey]);
                }
            }
        }
    }
    }

    if (isset($fields['postType'])) {
        $postType = $fields['postType'];
    }

    if (!empty($fields['perPage']) && is_numeric($fields['perPage'])) {
        $perPage = intval($fields['perPage']);
    }
@endphp

<x-block :fields="$fields" :block="$block" class="listing-block">
    @isset($fields['uptitle'])
        <x-typography.uptitle :content="$fields['uptitle']"/>
    @endisset

    @isset($fields['title'])
        <x-typography.heading :fields="$fields['title']"/>
    @endisset

        @isset($fields['wysiwyg'])
            <x-typography.text :content="$fields['wysiwyg']"/>
        @endisset

    <livewire:listing.listing :post-type="$postType" :per-page="$perPage" :filters="$filters"
                              :secondary-filters="$hasSecondaryFilters ? $secondaryFilters : null"
                              :secondary-filters-button-label="$secondaryFiltersLabel"
                              :secondary-filters-title="$secondaryFiltersTitle" :forced-filters="$forcedFilters"
                              :display-sort="$displaySort" :display-number-of-results="$displayNumberOfResults"
                              :label-singular="$labelSingular" :label-plural="$labelPlural"/>
</x-block>