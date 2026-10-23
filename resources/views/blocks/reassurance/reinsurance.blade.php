@if (!empty($fields) && !empty($fields['items']))

    @php
        // Handle different display if both data and title are filled or not
        $hasDataAndTitle = !empty(
            array_filter($fields['items'], function ($item) {
                return !empty($item['data']) && !empty($item['title']);
            })
        );
    @endphp

    <x-block :fields="$fields" :block="$block">
        <ul role="list" class="flex flex-wrap lg:flex-nowrap">
            @foreach ($fields['items'] as $item)
                <li @class([
                    'flex flex-row gap-medium p-small mx-auto md:p-xlarge lg:flex-1 lg:min-w-0',
                    'basis-full md:basis-1/2 items-center' => $hasDataAndTitle,
                    'basis-1/2 items-baseline' => !$hasDataAndTitle,
                ])>
                    @if (!empty($item['icon']))
                        <x-ui.icon :icon="$item['icon']" class="icon-8 text-primary lg:icon-10" />
                    @endif

                    <div class="flex flex-row items-center gap-medium min-w-0 lg:flex-col lg:items-start lg:gap-1">
                        @if (!empty($item['data']))
                            <p class="heading-4">
                                {{ $item['data'] }}
                            </p>
                        @endif

                        @if (!empty($item['title']))
                            <p class="text-large text-text-secondary">
                                {{ $item['title'] }}
                            </p>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>
    </x-block>
@endif
