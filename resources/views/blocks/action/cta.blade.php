@php
    $isFullWidth = isset($fields['appearance']) && $fields['appearance'] == 'full-width';
@endphp
<x-block :fields="$fields" :block="$block" background="{{ $isFullWidth ? 'primary' : '' }}" padding="none">
    <div
            class="{{ $isFullWidth ? '' : 'bg-primary ' }} flex flex-col items-start gap-6 rounded-xlarge p-3xlarge lg:flex-row lg:items-center lg:justify-between lg:gap-7xlarge lg:p-6xlarge">
        <div class="awc-theme-dark flex flex-col gap-title-text-mobile lg:gap-title-text-desktop">
            @isset($fields['title'])
                <x-typography.heading :fields="$fields['title']" size="5"/>
            @endisset

            @isset($fields['wysiwyg'])
                <x-typography.text :content="$fields['wysiwyg']"/>
            @endisset
        </div>
        @isset($fields['button'])
            <x-action.button :fields="$fields['button']" type="primary" size="large"/>
        @endisset
    </div>
</x-block>