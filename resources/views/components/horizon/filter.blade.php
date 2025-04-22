@php
    use Adeliom\HorizonBlocks\Blocks\Listing\ListingBlock;
	use App\Livewire\Listing\Listing;

    $name = $value['label'] ?? null;

    if (!isset($withEmpty)) {
        $withEmpty = true;
    }
@endphp

@if($value && isset($value['isSearch'])&& $value['isSearch'])
    <div class="select-group">
        <label for="{{ $model }}">
            {{ $name }}
        </label>

        <input type="text" @if($model) wire:model="{{ $model }}" @endif placeholder="{{ $name }}">
    </div>
@elseif ($value && isset($value['appearance'], $value['choices']))
    <div class="select-group">
        <label for="{{ $model }}">
            {{ $name }}
        </label>


        @switch($value['appearance'])
            @case(ListingBlock::VALUE_FILTER_APPEARANCE_SELECT)
                <select class="select" id="{{ $model }}" name="{{ $model }}"
                        @if ($model) wire:model="{{ $model }}" @endif>
                    @if ($withEmpty)
                        <option value="" selected>
                            {{ $name ?? 'Sélectionner' }}
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

            @case(ListingBlock::VALUE_FILTER_APPEARANCE_SINGLESELECT)
                <div>
                    <div>
                        <p class="indicator">
                            @if(isset($values[$value['name']])&& $values[$value['name']])
                                @foreach($value['choices'] as $choiceKey => $choice)
                                    @if(isset($choice['slug'])&&$choice['slug'] === $values[$value['name']])
                                        <span class="label">{{ $choice['name'] }}</span>
                                        @break
                                    @endif
                                @endforeach
                            @else
                                <span class="placeholder">{{$value['placeholder']}}</span>
                            @endif
                        </p>

                        <div class="dropdown">
                            <div class="values">
                                @isset($value['choices'])
                                    @foreach($value['choices'] as $key => $choice)
                                        <div wire:key="{{ $key }}">
                                            <input type="radio" id="{{ $model }}_{{ $key }}" name="{{ $model }}"
                                                   value="{{ $choice['slug'] }}"
                                                   @if($model) wire:model="{{ $model }}" @endif
                                                   @isset($values[$value['name']][$choice['slug']])
                                                       @if($values[$value['name']][$choice['slug']] == 'true')
                                                           checked="checked"
                                                    @endif
                                                    @endisset>
                                            <label for="{{ $model }}_{{$key}}">{{ $choice['name'] }}</label>
                                        </div>
                                    @endforeach
                                @endisset
                            </div>
                            <div class="bottom">
                                <p wire:click="resetFilter('{{ $model }}')">Réinitialiser</p>
                                <p>Afficher</p>
                            </div>
                        </div>
                    </div>
                </div>
                @break

            @case(ListingBlock::VALUE_FILTER_APPEARANCE_MULTISELECT)
                @php
                    $baseName = explode(".", $model)[1];
					$labelPlaceholder = $value['label'];

					$selectedOptions = [];

					if($baseName && isset($values[$baseName])) {
						foreach ($values[$baseName] as $fieldName => $fieldValue) {
                            if ((is_bool($fieldValue) && $fieldValue)||is_string($fieldValue)&&$fieldValue==='true') {
                                if (
                                    $associatedOption = array_find($value['choices'], function ($option) use ($fieldName) {
                                        return isset($option[Listing::KEY_SLUG], $option[Listing::KEY_NAME]) && $option["slug"] === $fieldName;
                                    })
                                ) {
                                    $selectedOptions[] = $associatedOption;
                                }
                            }
                        }
					}

					if (!empty($selectedOptions) && Listing::DISPLAY_VALUES_IN_MULTISELECT_LABEL) {
						$labelPlaceholder = implode(", ", array_column($selectedOptions, Listing::KEY_NAME));

                        if (strlen($labelPlaceholder) > Listing::MULTISELECT_LABEL_MAXLENGTH) {
                            $labelPlaceholder = substr($labelPlaceholder, 0, Listing::MULTISELECT_LABEL_MAXLENGTH - 3) . "...";
                        }
					}
                @endphp
                <div>
                    <div>
                        <p class="indicator">
                            @if($selected = $this->howManyOptionsSelected($model))
                                <span class="label">{{ $labelPlaceholder }}</span>
                                <span class="counter">{{ $selected }}</span>
                            @else
                                <span class="placeholder">{{ $value['placeholder'] }}</span>
                            @endif
                        </p>

                        <div class="dropdown">
                            <div class="values">
                                @isset($value['choices'])
                                    @foreach($value['choices'] as $key => $choice)
                                        <div wire:key="{{$key}}">
                                            <input type="checkbox" id="{{ $model }}_{{$key}}"
                                                   name="{{$model}}.{{$choice['slug']}}"
                                                   @if($model) wire:model="{{$model}}.{{$choice['slug']}}" @endif
                                                   @isset($values[$value['name']][$choice['slug']])
                                                       @if($values[$value['name']][$choice['slug']] == 'true')
                                                           checked="checked"
                                                    @endif
                                                    @endisset>
                                            <label for="{{$model}}_{{$key}}">{{ $choice['name'] }}</label>
                                        </div>
                                    @endforeach
                                @endisset
                            </div>
                            <div class="bottom">
                                <p wire:click="resetFilter('{{$model}}')">Réinitialiser</p>
                                <p>Afficher</p>
                            </div>
                        </div>
                    </div>
                </div>
                @break

            @default
                @break
        @endswitch
    </div>
@endif
