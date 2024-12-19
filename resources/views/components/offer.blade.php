@if ($fields && $fields['enable'])
  <div class="{{ $fullClass }}">
        <x-typography.icon icon="clock" class="flex-none" />
        <div class="flex-1">
            @if ($fields['uptitle'])
                <x-typography.text :content="$fields['uptitle']" class="text-medium font-semibold mb-2xsmall" />
            @endif
            @if ($fields['wysiwyg'])
                <x-typography.text :content="$fields['wysiwyg']" class="" />
            @endif
        </div>
    </div>
@endif
