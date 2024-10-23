@php
    $postType = null;
    $perPage = 12;
    $filters = $fields['filters']??[];

    if (isset($fields['postType'])) {
        $postType = $fields['postType'];
    }

    if (!empty($fields['perPage']) && is_numeric($fields['perPage'])) {
        $perPage = intval($fields['perPage']);
    }
@endphp

<x-block :fields="$fields">
    @isset($fields['uptitle'])
        <x-typography.uptitle :content="$fields['uptitle']"/>
    @endisset

    @isset($fields['title'])
        <x-typography.heading :fields="$fields['title']"/>
    @endisset

    <livewire:listing.listing :post-type="$postType" :per-page="$perPage" :filters="$filters"/>
</x-block>
