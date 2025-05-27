<x-block :fields="$fields" :block="$block">
    <div class="grid-12">
        <div class="lg:col-span-5">
            @isset($fields['uptitle'])
                <x-typography.uptitle :content="$fields['uptitle']" />
            @endisset

            @isset($fields['title'])
                <x-typography.heading :fields="$fields['title']" size="3" />
            @endisset
        </div>
        <div class="lg:col-span-7">
            @isset($fields['wysiwyg'])
                <x-typography.text :content="$fields['wysiwyg']" />
            @endisset

            @isset($fields['buttons'])
                <x-action.buttons :buttons="$fields['buttons']" class="mt-button-text-mobile lg:mt-button-text-desktop" />
            @endisset
        </div>
    </div>
</x-block>