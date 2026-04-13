@php
    $bgType = $fields['bg']['bg-type'] ?? 'bg-color-type';
    $bgColor = ($bgType  === "bg-color-type" && !empty($fields['bg']['bg-color'])) ? $fields['bg']['bg-color'] : "";
    $bgImage = ($bgType === "bg-image-type" && !empty($fields['bg']['bg-image'])) ? $fields['bg']['bg-image']['sizes']['large'] : "";
@endphp


<x-block :fields="$fields" :block="$block" class="{{ $bgColor }} relative" background="none">
    @if (!empty($bgImage))
        <div class="absolute inset-0 bg-cover bg-center z-0" style="background-image: url('{{ $bgImage }}')"></div>
    @endif
    <div class="grid-12 relative z-10">
        <div class="lg:col-span-8 lg:col-start-3">
            @if (!empty($fields['title']))
                <x-heading :fields="$fields['title']" :size="5" class="text-center"/>
            @endif
        </div>
    </div>
</x-block>