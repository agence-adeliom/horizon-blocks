@if (!empty($fields) && !empty($fields['enable']))
    <div class="{{ $fullClass }}">
        <x-far-clock class="flex-none icon-20" />
        <div class="flex-1">
            @if (!empty($fields['uptitle']))
                <x-typography.text :content="$fields['uptitle']" class="text-medium font-semibold mb-2xsmall" />
            @endif
            @if (!empty($fields['wysiwyg']))
                <x-typography.text :content="$fields['wysiwyg']" class="" />
            @endif
        </div>
    </div>
@endif
