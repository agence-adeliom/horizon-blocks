@php
    use Adeliom\HorizonBlocks\Blocks\Listing\ListingBlock;

    $placeholder = $value['placeholder'] ?? null;

    if (!isset($withEmpty)) {
        $withEmpty = true;
    }
@endphp

@if($value && isset($value['isSearch'])&& $value['isSearch'])
    <div class="select-group">
        <label for="{{ $model }}">
            {{ $placeholder }}
        </label>

        <input type="text" @if($model) wire:model="{{ $model }}" @endif placeholder="{{ $placeholder }}">
    </div>
@elseif ($value && isset($value['appearance'], $value['choices']))
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
                            <input type="checkbox" id="{{ $model }}_{{ $key }}" name="{{ $model }}.{{$choice['slug']}}"
                                   @if($model) wire:model="{{ $model }}.{{ $choice['slug'] }}" @endif
                                   @isset($values[$value['name']][$choice['slug']])
                                       @if($values[$value['name']][$choice['slug']] == 'true')
                                           checked="checked"
                                    @endif
                                    @endisset>
                            <label for="{{ $model }}_{{ $key }}">{{ $choice['name'] }}</label>
                        </div>
                    @endforeach
                @endisset
                @break

            @case(ListingBlock::VALUE_FILTER_APPEARANCE_RADIO)
                @isset($value['choices'])
                    @foreach($value['choices'] as $key => $choice)
                        <div>
                            <input type="radio" id="{{ $model }}_{{ $key }}" name="{{ $model }}"
                                   value="{{$choice['slug']}}"
                                   @if($model) wire:model="{{ $model }}" @endif
                                   @isset($values[$value['name']][$choice['slug']])
                                       @if($values[$value['name']][$choice['slug']] == 'true')
                                           checked="checked"
                                    @endif
                                    @endisset>
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
