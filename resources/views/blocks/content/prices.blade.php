<x-block :fields="$fields">

    <div class="flex flex-col items-center text-center max-w-3xl mx-auto">
        @isset($fields['uptitle'])
            <x-typography.uptitle :content="$fields['uptitle']" class="mb-1" />
        @endisset

        @isset($fields['title'])
            <x-typography.heading :fields="$fields['title']" size="3" />
        @endisset

        @isset($fields['wysiwyg'])
            <x-typography.text :content="$fields['wysiwyg']" class="mt-4" />
        @endisset
    </div>

    @isset($fields['prices'])
        <div class="w-full mt-10 grid gap-6 lg:grid-cols-3">
            @foreach ($fields['prices'] as $price)
                <x-cards.card-price :fields="$price" />
            @endforeach
        </div>
    @endisset

</x-block>

