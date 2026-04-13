@if (!empty($options))
    <div class="flex gap-2 items-center text-text-secondary">
        <label @if (!empty($model)) for="{{ $model }}" @endif class="text-sm">Trier par</label>
        <select class="font-semibold bg-transparent text-sm border-none cursor-pointer "
            @if (!empty($model)) wire:model="{{ $model }}" name="{{ $model }}" id="{{ $model }}" @endif>
            @foreach ($options as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
    </div>
@endif
