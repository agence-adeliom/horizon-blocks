<x-block :fields="$fields" :block="$block" class="overflow-hidden">
    @isset($fields['uptitle'])
        <x-typography.heading :content="$fields['uptitle']" size="headline" />
    @endisset
    @isset($fields['title'])
        <x-typography.heading :fields="$fields['title']" size="3" />
    @endisset

    <div class="mt-text-image-mobile lg:mt-text-image-desktop" x-data="initStepsSlider()">
        <div class="swiper w-full overflow-visible" x-ref="swiperContainer">
            <x-action.button @class(['center-top left-0 z-10']) type="primary" variant="outline" iconOnly x-ref="buttonPrev">
                <x-fas-angle-left />
            </x-action.button>

            <x-action.button @class(['center-top right-0 z-10']) type="primary" variant="outline" iconOnly x-ref="buttonNext">
                <x-fas-angle-right />
            </x-action.button>
            <div class="swiper-wrapper">
                @if (isset($fields['steps']) && $fields['steps'])
                    @foreach ($fields['steps'] as $step)
                        <x-cards.card-step :step="$step" class="swiper-slide h-auto" />
                    @endforeach
                @endif
            </div>

        </div>
    </div>

    <div class="mt-text-image-mobile flex justify-center lg:mt-text-image-desktop">
        @if (isset($fields['button']) && $fields['button']['link'])
            <x-action.button :fields="$fields['button']" class="max-md:w-full" />
        @endif
    </div>
</x-block>