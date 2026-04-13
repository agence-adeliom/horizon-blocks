@php
    use Adeliom\HorizonTools\Services\StringService;

    $singular = $singular ?? 'résultat';
    $plural = $plural ?? 'résultats';
@endphp

@if (is_array($value ?? null) && array_key_exists('total', $value))
    <p class="flex gap-2 text-text-secondary">
        <span class="font-semibold">
            {{ $value['total'] }}
        </span>

        {{ StringService::singularOrPlural(count: $value['total'], singular: $singular,plural: $plural) }}
    </p>
@endif
