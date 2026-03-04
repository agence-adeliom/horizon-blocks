@if ($fields && $fields['items'])
    @php
        $type = $fields['type'] ?? 'default';
        $withBg = $type === 'with_bg';
        $framed = $type === 'framed';
    @endphp

    <x-block :fields="$fields" :block="$block">
        <div class="flex flex-col items-center text-center mb-8 lg:mb-10">
            @isset($fields['uptitle'])
                <x-typography.uptitle :content="$fields['uptitle']" />
            @endisset

            @isset($fields['title'])
                <x-typography.heading :fields="$fields['title']" :size="3" />
            @endisset

            @isset($fields['wysiwyg'])
                <x-typography.text :content="$fields['wysiwyg']" class="mt-4 text-large" />
            @endisset
        </div>

        <div @class([
            'grid grid-cols-4 md:grid-cols-4 lg:grid-cols-' .
            count($fields['items']) * 2 .
            ' lg:gap-6',
            'max-lg:gap-4' => !$withBg,
            'bg-neutral-100' => $withBg,
        ])>
            @foreach ($fields['items'] as $item)
                <div @class([
                    'col-span-2 flex items-center text-center flex-col gap-1 lg:gap-4',
                    'max-lg:col-start-2' => count($fields['items']) === 3 && $loop->index === 2,
                    'bg-neutral-100' => $framed,
                    'p-xlarge' => $framed || $withBg,
                ])>
                    @if (@isset($item['icon']) && $item['icon'])
                        <x-ui.icon :icon="$item['icon']" class="icon-24 text-primary lg:w-10 lg:h-10" />
                    @endif

                    @if (@isset($item['data']) && $item['data'])
                        <div @class(['heading text-3xlarge md:text-4xlarge lg:text-6xlarge'])>
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