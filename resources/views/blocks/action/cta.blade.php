@php
    $isFullWidth = !empty($fields['appearance']) && $fields['appearance'] == 'full-width';
@endphp
<x-block :fields="$fields" :block="$block" background="{{ $isFullWidth ? 'primary' : '' }}" padding="none">
    <div
            class="{{ $isFullWidth ? '' : 'bg-primary ' }} flex flex-col items-start gap-6 rounded-xlarge p-3xlarge lg:flex-row lg:items-center lg:justify-between lg:gap-7xlarge lg:p-6xlarge">
        <div class="awc-theme-dark flex flex-col gap-title-text-mobile lg:gap-title-text-desktop">
            @if(!empty($fields['title']))
                <x-typography.heading :fields="$fields['title']" size="5"/>
            @endif

            @if(!empty($fields['wysiwyg']))
                <x-typography.text :content="$fields['wysiwyg']"/>
            @endif
        </div>
        @if(!empty($fields['button']))
            <x-action.button :fields="$fields['button']" type="primary" size="large"/>
        @endif
    </div>
</x-block>