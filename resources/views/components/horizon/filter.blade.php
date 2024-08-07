@php
    if(!isset($withEmpty)){
        $withEmpty = true;
    }
@endphp

@if($value && isset($value['appearance'], $value['choices']))
    @switch($value['appearance'])
        @case('select')
            <select @if($model) wire:model="{{ $model }}" @endif>
                @if($withEmpty)
                    <option value=""></option>
                @endif
                @foreach($value['choices'] as $choice)
                    <option value="{{ $choice['slug'] }}">
                        {{ $choice['name'] }}
                    </option>
                @endforeach
            </select>
            @break
        @default
            @break
    @endswitch
@endif