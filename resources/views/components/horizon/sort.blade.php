<div>
    Trier par

    <select @isset($model) wire:model="{{ $model }}@endisset">
        <option value="date.desc">Plus récent au plus ancien</option>
        <option value="date.asc">Plus ancien au plus récent</option>
    </select>
</div>