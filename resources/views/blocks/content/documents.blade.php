<x-block :fields="$fields" :block="$block">
    <div class="grid gap-text-image-mobile lg:grid-cols-12 lg:gap-text-image-desktop">
        <div class="flex flex-col items-center text-center lg:col-span-8 lg:col-start-3">
            @if (!empty($fields['uptitle']))
                <x-typography.uptitle :content="$fields['uptitle']" />
            @endif

            @if (!empty($fields['title']))
                <x-typography.heading :fields="$fields['title']" size="3" />
            @endif

            @if (!empty($fields['wysiwyg']))
                <x-typography.text class="text-large mt-title-text-mobile lg:mt-title-text-desktop" :content="$fields['wysiwyg']" />
            @endif
        </div>
        <div class="col-span-full flex flex-col gap-2 lg:gap-4 lg:col-span-8 lg:col-start-3">
            @if (!empty($fields['documents']))
                @foreach ($fields['documents'] as $document)
                    <x-cards.card-document :document="$document" />
                @endforeach
            @endif
        </div>
    </div>
</x-block>