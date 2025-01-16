@php
    if (!isset($singular)) {
        $singular = 'résultat';
    }

    if (!isset($plural)) {
        $plural = 'résultats';
    }
@endphp

@if (isset($value['total']))
    <p class="flex gap-2 text-text-secondary">
        <span class="font-semibold">
            {{ $value['total'] }}
        </span>
        @switch(true)
            @case($value['total'] == 1 || $value['total'] == 0)
                {{ $singular }}
            @break;

            @default
                {{ $plural }}
            @break;
        @endswitch
    </p>
@endif
