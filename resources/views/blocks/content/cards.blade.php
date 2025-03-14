<x-block :fields="$fields">
    <div class="grid gap-text-image-mobile lg:grid-cols-12 lg:gap-text-image-desktop">
        <div class="flex flex-col items-center text-center lg:col-span-8 lg:col-start-3">
            @if (@isset($fields['uptitle']) && $fields['uptitle'])
                <x-typography.uptitle :content="$fields['uptitle']" />
            @endif

            @if (@isset($fields['title']) && $fields['title'])
                <x-typography.heading :fields="$fields['title']" size="3" />
            @endif

            @if (@isset($fields['wysiwyg']) && $fields['wysiwyg'])
                <x-typography.text class="text-large mt-title-text-mobile lg:mt-title-text-desktop" :content="$fields['wysiwyg']" />
            @endif
        </div>
        <div class="col-span-full grid-12">
            @if (@isset($fields['cards']) && $fields['cards'])
                @foreach ($fields['cards'] as $card)
                    <x-cards.card-basic :card="$card" />
                @endforeach
            @endif
        </div>
    </div>
</x-block>
