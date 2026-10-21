<x-block :fields="$fields" :block="$block">

    <div class="flex flex-col items-center text-center max-w-3xl mx-auto">
        @if (!empty($fields['uptitle']))
            <x-typography.uptitle :content="$fields['uptitle']" class="mb-1" />
        @endif

        @if (!empty($fields['title']))
            <x-typography.heading :fields="$fields['title']" size="3" />
        @endif

        @if (!empty($fields['wysiwyg']))
            <x-typography.text :content="$fields['wysiwyg']" class="mt-4" />
        @endif
    </div>

    @if (!empty($fields['prices']))
        @php($priceHeadingTag = preg_match('/^h([1-4])$/', $fields['title']['tag'] ?? '', $level) && !empty($fields['title']['content']) ? 'h' . ($level[1] + 1) : 'h2')

        <ul role="list" class="w-full mt-10 grid gap-6 lg:grid-cols-3">
            @foreach ($fields['prices'] as $price)
                <li>
                    <x-cards.card-price :fields="$price" :heading-tag="$priceHeadingTag" />
                </li>
            @endforeach
        </ul>
    @endif

</x-block>
