@if (!empty($fields) && !empty($fields['items']))
    @php
        $type = $fields['type'] ?? 'default';
        $withBg = $type === 'with_bg';
        $framed = $type === 'framed';
    @endphp

    <x-block :fields="$fields" :block="$block">
        <div class="flex flex-col items-center text-center mb-8 lg:mb-10">
            @if (!empty($fields['uptitle']))
                <x-typography.uptitle :content="$fields['uptitle']" />
            @endif

            @if (!empty($fields['title']))
                <x-typography.heading :fields="$fields['title']" :size="3" />
            @endif

            @if (!empty($fields['wysiwyg']))
                <x-typography.text :content="$fields['wysiwyg']" class="mt-4 text-large" />
            @endif
        </div>

        <ul role="list" @class([
            'grid grid-cols-4 lg:gap-6',
            'lg:grid-cols-6' => count($fields['items']) === 3,
            'lg:grid-cols-8' => count($fields['items']) === 4,
            'max-lg:gap-4' => !$withBg,
            'bg-neutral-100' => $withBg,
        ])>
            @foreach ($fields['items'] as $item)
                <li @class([
                    'col-span-2 flex items-center text-center flex-col gap-1 lg:gap-4',
                    'max-lg:col-start-2' => count($fields['items']) === 3 && $loop->index === 2,
                    'bg-neutral-100' => $framed,
                    'p-xlarge' => $framed || $withBg,
                ])>
                    @if (!empty($item['icon']))
                        <x-ui.icon :icon="$item['icon']" class="icon-8 text-primary lg:icon-10" />
                    @endif

                    @if (!empty($item['data']))
                        <p @class(['heading text-3xlarge md:text-4xlarge lg:text-6xlarge'])>
                            {{ $item['data'] }}
                        </p>
                    @endif

                    @if (!empty($item['title']))
                        <p class="text-large text-text-secondary">
                            {{ $item['title'] }}
                        </p>
                    @endif
                </li>
            @endforeach
        </ul>
    </x-block>
@endif
