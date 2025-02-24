@php
    use Adeliom\HorizonBlocks\Blocks\Listing\ListingBlock;

    $placeholder = $value['placeholder'] ?? null;

    if (!isset($withEmpty)) {
        $withEmpty = true;
    }
@endphp

@if ($value && isset($value['appearance'], $value['choices']))
    <div class="select-group">
        <label for="{{ $model }}">
            {{ $placeholder }}
        </label>
        @switch($value['appearance'])
            @case(ListingBlock::VALUE_FILTER_APPEARANCE_SELECT)
                <select class="select" id="{{ $model }}" name="{{ $model }}"
                        @if ($model) wire:model="{{ $model }}" @endif>
                    @if ($withEmpty)
                        <option value="" selected>
                            {{ $placeholder ?? 'Sélectionner' }}
                        </option>
                    @endif
                    @foreach ($value['choices'] as $choice)
                        <option value="{{ $choice['slug'] }}">
                            {{ $choice['name'] }}
                        </option>
                    @endforeach
                </select>
                @break

            @case(ListingBlock::VALUE_FILTER_APPEARANCE_CHECKBOX)
                @isset($value['choices'])
                    @foreach($value['choices'] as $key => $choice)
                        <div>
                            <input type="checkbox" value="{{ $choice['slug'] }}" id="{{ $model }}_{{ $key }}"
                                   @if($model) wire:model="{{ $model }}.{{ $choice['slug'] }}" @endif>
                            <label for="{{ $model }}_{{ $key }}">{{ $choice['name'] }}</label>
                        </div>
                    @endforeach
                @endisset
                @break

            @case(ListingBlock::VALUE_FILTER_APPEARANCE_TEXT)
                <div>
                    <input type="text" id="{{ $model }}" @if($model) wire:model="{{ $model }}" @endif>
                </div>
                @break

            @default
                @break
        @endswitch
    </div>
@endif
