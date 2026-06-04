@php
    $bgType = $fields['bg']['bgType'] ?? 'bgColorType';
    $bgColor = ($bgType  === "bgColorType" && !empty($fields['bg']['bgColor'])) ? $fields['bg']['bgColor'] : "";
    $bgImage = ($bgType === "bgImageType" && !empty($fields['bg']['bgImage'])) ? $fields['bg']['bgImage']['sizes']['large'] : "";
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