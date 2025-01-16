@isset($options)
    <div class="flex gap-2 items-center text-text-secondary">
        <label @isset($model) for="{{ $model }}" @endisset class="text-sm">Trier par</label>
        <select class="font-semibold bg-transparent text-sm border-none cursor-pointer "
            @isset($model) wire:model="{{ $model }}" name="{{ $model }}" @endisset>
            @foreach ($options as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
    </div>
@endisset
