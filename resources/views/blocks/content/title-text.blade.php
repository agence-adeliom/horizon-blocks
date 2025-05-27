<x-block :fields="$fields" :block="$block">
    <div class="grid-12">
        <div class="lg:col-span-5">
            @if(!empty($fields['uptitle']))
                <x-typography.uptitle :content="$fields['uptitle']" />
            @endif

             @if(!empty($fields['title']))
                <x-typography.heading :fields="$fields['title']" size="3" />
            @endif
        </div>
        <div class="lg:col-span-7">
             @if(!empty($fields['wysiwyg']))
                <x-typography.text :content="$fields['wysiwyg']" />
            @endif

             @if(!empty($fields['buttons']))
                <x-action.buttons :buttons="$fields['buttons']" class="mt-button-text-mobile lg:mt-button-text-desktop" />
            @endif
        </div>
    </div>
</x-block>