@if (! empty($element['content']))
    @php($title = $element['content'])
    @php($level = $element['tag'])

    <li class="group font-semibold">
        <span class="summary-elt" data-title="{{ trim($title) }}" data-level="{{ $level }}">
            @if (! empty($titleOverrides[$title]))
                {{ trim($titleOverrides[$title]) }}
            @else
                {{ trim($title) }}
            @endif
        </span>

        @if (! empty($element['children']))
            <ul class="summary-list pt-2">
                @foreach ($element['children'] as $child)
                    @if (is_array($child))
                        <x-navigation.hierarchical-summary-element :element="$child" :title-overrides="$titleOverrides" />
                    @endif
                @endforeach
            </ul>
        @endif
    </li>
@endif
