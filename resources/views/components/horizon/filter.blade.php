@php
    use Adeliom\HorizonBlocks\Blocks\Listing\ListingBlock;
    use App\Livewire\Listing\Listing;

    $name = $value["label"] ?? null;

    $withEmpty = ($withEmpty ?? true) || !empty($value["hasChoiceAll"]);

    $withReinitButton = $withReinitButton ?? true;
    $withDisplayButton = $withDisplayButton ?? true;
    $withIndicator = $withIndicator ?? true;
@endphp

@if (!empty($value) && !empty($value["isSearch"]))
    <div class="select-group">
        <label for="{{ $model }}">
            {{ $name }}
        </label>

        <input type="text" @if (!empty($model)) wire:model="{{ $model }}" @endif placeholder="{{ $name }}" />
    </div>
@elseif (!empty($value) && !empty($value["appearance"]) && !empty($value["choices"]))
    <div class="select-group">
        <label for="{{ $model }}">
            {{ $name }}
        </label>

        @switch($value["appearance"])
            @case(ListingBlock::VALUE_FILTER_APPEARANCE_SELECT)
                <select class="select" id="{{ $model }}" name="{{ $model }}" @if (!empty($model)) wire:model="{{ $model }}" @endif>
                    @if ($withEmpty)
                        <option value="" selected>
                            {{ $name ?? "Sélectionner" }}
                        </option>
                    @endif

                    @foreach ($value["choices"] as $choice)
                        <option value="{{ $choice["slug"] }}">
                            {{ $choice["name"] }}
                        </option>
                    @endforeach
                </select>

                @break
            @case(ListingBlock::VALUE_FILTER_APPEARANCE_CHECKBOX)
                @if (!empty($value["choices"]))
                    @foreach ($value["choices"] as $key => $choice)
                        <div>
                            <input
                                type="checkbox"
                                id="{{ $model }}_{{ $key }}"
                                name="{{ $model }}.{{ $choice["slug"] }}"
                                @if (!empty($model)) wire:model="{{ $model }}.{{ $choice['slug'] }}" @endif
                                @if (!empty($values[$value["name"]][$choice["slug"]]))
                                    @if ($values[$value["name"]][$choice["slug"]] == "true")
                                        checked="checked"
                                    @endif
                                @endif
                            />
                            <label for="{{ $model }}_{{ $key }}">{{ $choice["name"] }}</label>
                        </div>
                    @endforeach
                @endif

                @break
            @case(ListingBlock::VALUE_FILTER_APPEARANCE_RADIO)
                @if (!empty($value["choices"]))
                    @foreach ($value["choices"] as $key => $choice)
                        <div>
                            <input
                                type="radio"
                                id="{{ $model }}_{{ $key }}"
                                name="{{ $model }}"
                                value="{{ $choice["slug"] }}"
                                @if (!empty($model)) wire:model="{{ $model }}" @endif
                                @if (!empty($values[$value["name"]][$choice["slug"]]))
                                    @if ($values[$value["name"]][$choice["slug"]] == "true")
                                        checked="checked"
                                    @endif
                                @endif
                                @if ((empty($values) || empty($values[$value["name"]])) && $withEmpty && !empty($value["choiceAllValue"]) && $choice["slug"] === $value["choiceAllValue"])
                                    checked="checked"
                                @endif
                            />
                            <label for="{{ $model }}_{{ $key }}">{{ $choice["name"] }}</label>
                        </div>
                    @endforeach
                @endif

                @break
            @case(ListingBlock::VALUE_FILTER_APPEARANCE_TEXT)
                <div>
                    <input type="text" id="{{ $model }}" @if (!empty($model)) wire:model="{{ $model }}" @endif />
                </div>

                @break
            @case(ListingBlock::VALUE_FILTER_APPEARANCE_SINGLESELECT)
                <div>
                    <div>
                        @if ($withIndicator)
                            <p class="indicator">
                                @if (!empty($values[$value["name"]]))
                                    @foreach ($value["choices"] as $choiceKey => $choice)
                                        @if (!empty($choice["slug"]) && $choice["slug"] === $values[$value["name"]])
                                            <span class="label">{{ $choice["name"] }}</span>

                                            @break
                                        @endif
                                    @endforeach
                                @else
                                    <span class="placeholder">{{ $value["placeholder"] }}</span>
                                @endif
                            </p>
                        @endif

                        <div class="dropdown">
                            <div class="values">
                                @if (!empty($value["choices"]))
                                    @foreach ($value["choices"] as $key => $choice)
                                        <div wire:key="{{ $key }}">
                                            <input
                                                type="radio"
                                                id="{{ $model }}_{{ $key }}"
                                                name="{{ $model }}"
                                                value="{{ $choice["slug"] }}"
                                                @if (!empty($model)) wire:model="{{ $model }}" @endif
                                                @if (!empty($values[$value["name"]][$choice["slug"]]))
                                                    @if ($values[$value["name"]][$choice["slug"]] == "true")
                                                        checked="checked"
                                                    @endif
                                                @endif
                                            />
                                            <label for="{{ $model }}_{{ $key }}">{{ $choice["name"] }}</label>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            @if ($withReinitButton || $withDisplayButton)
                                <div class="bottom">
                                    @if ($withReinitButton)
                                        <p wire:click="resetFilter('{{ $model }}')">Réinitialiser</p>
                                    @endif

                                    @if ($withDisplayButton)
                                        <p>Afficher</p>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                @break
            @case(ListingBlock::VALUE_FILTER_APPEARANCE_MULTISELECT)
                @php
                    $baseName = explode(".", $model)[1];
                    $labelPlaceholder = $value["label"];

                    $selectedOptions = [];

                    if ($baseName && !empty($values[$baseName])) {
                        foreach ($values[$baseName] as $fieldName => $fieldValue) {
                            if ((is_bool($fieldValue) && $fieldValue) || (is_string($fieldValue) && $fieldValue === "true")) {
                                if (
                                    $associatedOption = array_find($value["choices"], function ($option) use ($fieldName) {
                                        return !empty($option[Listing::KEY_SLUG]) && !empty($option[Listing::KEY_NAME]) && $option["slug"] === $fieldName;
                                    })
                                ) {
                                    $selectedOptions[] = $associatedOption;
                                }
                            }
                        }
                    }

                    if (! empty($selectedOptions) && Listing::DISPLAY_VALUES_IN_MULTISELECT_LABEL) {
                        $labelPlaceholder = implode(", ", array_column($selectedOptions, Listing::KEY_NAME));

                        if (strlen($labelPlaceholder) > Listing::MULTISELECT_LABEL_MAXLENGTH) {
                            $labelPlaceholder = substr($labelPlaceholder, 0, Listing::MULTISELECT_LABEL_MAXLENGTH - 3) . "...";
                        }
                    }
                @endphp

                <div>
                    <div>
                        @if ($withIndicator)
                            <p class="indicator">
                                @if ($selected = $this->howManyOptionsSelected($model))
                                    <span class="label">{{ $labelPlaceholder }}</span>
                                    <span class="counter">{{ $selected }}</span>
                                @else
                                    <span class="placeholder">{{ $value["placeholder"] }}</span>
                                @endif
                            </p>
                        @endif

                        <div class="dropdown">
                            <div class="values">
                                @if (!empty($value["choices"]))
                                    @foreach ($value["choices"] as $key => $choice)
                                        <div wire:key="{{ $key }}">
                                            <input
                                                type="checkbox"
                                                id="{{ $model }}_{{ $key }}"
                                                name="{{ $model }}.{{ $choice["slug"] }}"
                                                @if (!empty($model)) wire:model="{{ $model }}.{{ $choice['slug'] }}" @endif
                                                @if (!empty($values[$value["name"]][$choice["slug"]]))
                                                    @if ($values[$value["name"]][$choice["slug"]] == "true")
                                                        checked="checked"
                                                    @endif
                                                @endif
                                            />
                                            <label for="{{ $model }}_{{ $key }}">{{ $choice["name"] }}</label>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            @if ($withReinitButton || $withDisplayButton)
                                <div class="bottom">
                                    @if ($withReinitButton)
                                        <p wire:click="resetFilter('{{ $model }}')">Réinitialiser</p>
                                    @endif

                                    @if ($withDisplayButton)
                                        <p>Afficher</p>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                @break
            @default
                @break
        @endswitch
    </div>
@endif
