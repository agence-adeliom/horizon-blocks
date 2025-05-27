@if ($fields && $fields['items'])

    @php
        // Handle different display if both data and title are filled or not
        $hasDataAndTitle = !empty(
            array_filter($fields['items'], function ($item) {
                return !empty($item['data']) && !empty($item['title']);
            })
        );
    @endphp

    <x-block :fields="$fields" :block="$block">
        <div class="flex flex-wrap">
            @foreach ($fields['items'] as $item)
                <div @class([
                    'lg:basis-1/' .
                    count($fields['items']) .
                    ' flex flex-row gap-medium p-small mx-auto md:p-xlarge',
                    'basis-full md:basis-1/2 items-center' => $hasDataAndTitle,
                    'basis-1/2 items-baseline' => !$hasDataAndTitle,
                ])>
                    @if (@isset($item['icon']) && $item['icon'])
                        <x-ui.icon :icon="$item['icon']" class="icon-24 text-primary" />
                    @endif

                    @if (@isset($item['data']) && $item['data'])
                        <div class="heading-4">
                            {{ $item['data'] }}
                        </div>
                    @endif

                    @if (@isset($item['title']) && $item['title'])
                        <div class="text-large text-text-secondary">
                            {{ $item['title'] }}
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </x-block>
@endif