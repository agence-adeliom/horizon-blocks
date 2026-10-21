<x-block :fields="$fields" :block="$block" class="overflow-hidden">
    @if (!empty($fields['uptitle']))
        <x-typography.heading :content="$fields['uptitle']" size="headline" />
    @endif
    @if (!empty($fields['title']))
        <x-typography.heading :fields="$fields['title']" size="3" />
    @endif

    @php
        $steps = $fields['steps'] ?? [];
        $count = count($steps ?: []);
        $stepHeadingTag =
            preg_match('/^h([1-5])$/', $fields['title']['tag'] ?? '', $level) && !empty($fields['title']['content'])
                ? 'h' . ($level[1] + 1)
                : 'h2';
    @endphp

    @if ($count)
        <div class="mt-text-image-mobile lg:mt-text-image-desktop" x-data="initStepsSlider()">
            <div class="swiper w-full overflow-visible" x-ref="swiperContainer">
                <ol role="list" class="swiper-wrapper">
                    @foreach ($steps as $step)
                        <li class="swiper-slide h-auto">
                            <x-cards.card-step :step="$step" :heading-tag="$stepHeadingTag" class="h-full" />
                        </li>
                    @endforeach
                </ol>
            </div>

            @if ($count > 1)
                <div class="mt-4 flex justify-end gap-2">
                    <x-action.button tag="button" type="primary" variant="outline" iconOnly
                        at-click="swiper.slidePrev()" aria-label="{{ __('Étape précédente', 'horizon-blocks') }}">
                        <x-fas-angle-left />
                    </x-action.button>
                    <x-action.button tag="button" type="primary" variant="outline" iconOnly
                        at-click="swiper.slideNext()" aria-label="{{ __('Étape suivante', 'horizon-blocks') }}">
                        <x-fas-angle-right />
                    </x-action.button>
                </div>
            @endif
        </div>
    @endif

    <div class="mt-text-image-mobile flex justify-center lg:mt-text-image-desktop">
        @if (!empty($fields['button']) && !empty($fields['button']['link']))
            <x-action.button :fields="$fields['button']" class="max-md:w-full" />
        @endif
    </div>
</x-block>
