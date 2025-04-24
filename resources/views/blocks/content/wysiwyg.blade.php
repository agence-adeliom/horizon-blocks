@php
    use App\Blocks\Content\WysiwygBlock;

    $alignClass = 'text-center';
    $itemsClass = 'items-center';

    if (! empty($fields[WysiwygBlock::FIELD_ALIGNMENT])) {
        switch ($fields[WysiwygBlock::FIELD_ALIGNMENT]) {
            case WysiwygBlock::VALUE_ALIGNMENT_LEFT:
                $alignClass = 'text-left';
                $itemsClass = 'items-start';
                break;
            case WysiwygBlock::VALUE_ALIGNMENT_RIGHT:
                $alignClass = 'text-right';
                $itemsClass = 'items-end';
                break;
            case WysiwygBlock::VALUE_ALIGNMENT_CENTER:
            default:
                break;
        }
    }
@endphp

<x-block class="simple-text-block" :fields="$fields">
    <div class="grid grid-cols-12 items-center gap-6">
        <div class="{{ $alignClass }} {{ $itemsClass }} col-span-full flex flex-col lg:col-start-3 lg:col-end-11">
            @if (! empty($fields['uptitle']))
                <x-typography.uptitle :content="$fields['uptitle']"/>
            @endif

            @if (! empty($fields['title']))
                <x-typography.heading :fields="$fields['title']" size="3"
                                      class="mt-headline-title-mobile lg:mt-headline-title-desktop"/>
            @endif

            @if (! empty($fields['wysiwyg']))
                <x-typography.text :content="$fields['wysiwyg']" class="mt-title-text-mobile lg:mt-title-text-desktop"/>
            @endif

            @if (! empty($fields['buttons']) && is_array($fields['buttons']))
                <div class="mt-4 flex flex-col gap-4 max-md:w-full md:flex-row">
                    @foreach ($fields['buttons'] as $button)
                        @if (! empty($button['link']))
                            <x-action.button :fields="$button" :type="$loop->first ? 'secondary' : 'outline'"
                                             class="max-lg:w-full"/>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-block>