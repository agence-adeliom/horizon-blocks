@isset($options)
    <div>
        Trier par

        <select @isset($model) wire:model="{{ $model }}@endisset">
            @foreach($options as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
    </div>
@endisset