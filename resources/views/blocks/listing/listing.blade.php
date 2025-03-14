@php
    $postType = null;
    $perPage = 12;
    $filters = $fields['filters'] ?? [];
    $secondaryFilters = $fields['secondaryFilters'] ?? [];
    $secondaryFiltersLabel = $fields['secondaryFiltersButtonLabel'] ?? 'Filtres avancés';
    $hasSecondaryFilters = !empty($secondaryFilters) && $fields['withSecondaryFilters'];
    $secondaryFiltersTitle = $fields['secondaryFiltersTitle'] ?? null;

    if (isset($fields['postType'])) {
        $postType = $fields['postType'];
    }

    if (!empty($fields['perPage']) && is_numeric($fields['perPage'])) {
        $perPage = intval($fields['perPage']);
    }
@endphp

<x-block :fields="$fields" class="listing-block">
    @isset($fields['uptitle'])
        <x-typography.uptitle :content="$fields['uptitle']" />
    @endisset

    @isset($fields['title'])
        <x-typography.heading :fields="$fields['title']" />
    @endisset

    <livewire:listing.listing :post-type="$postType" :per-page="$perPage" :filters="$filters" :secondary-filters="$hasSecondaryFilters ? $secondaryFilters : null" :secondary-filters-button-label="$secondaryFiltersLabel"
        :secondary-filters-title="$secondaryFiltersTitle" />
</x-block>
