@php
    use Adeliom\HorizonTools\Services\StringService;

	if (!isset($singular)) {
        $singular = 'résultat';
    }

    if (!isset($plural)) {
        $plural = 'résultats';
    }
@endphp

@if (isset($value['total']))
    <p class="flex gap-2 text-text-secondary">
        <span class="font-semibold">
            {{ $value['total'] }}
        </span>

        {{ StringService::singularOrPlural(count: $value['total'], singular: $singular,plural: $plural) }}
    </p>
@endif
