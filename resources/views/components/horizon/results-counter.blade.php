@php
    if(!isset($singular)) {
	    $singular = 'résultat';
    }

	if(!isset($plural)) {
        $plural = 'résultats';
	}
@endphp

@if(isset($value['total']))
    <p>
        {{ $value['total'] }}

        @switch(true)
            @case($value['total'] == 1)
                {{ $singular }}
                @break;
            @default
                {{ $plural }}
                @break;
        @endswitch
    </p>
@endif