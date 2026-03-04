@if ($fields)
    <x-block :fields="$fields" :block="$block" class="overflow-hidden">
        <div class="flex flex-col gap-text-image-mobile lg:gap-text-image-desktop" x-data="initLogosSlider()">

            <div class="grid-12">
                <div class="flex flex-col items-center text-center lg:col-span-8 lg:col-start-3">
                    @isset($fields['uptitle'])
                        <x-typography.uptitle :content="$fields['uptitle']" />
                    @endisset

                    @isset($fields['title'])
                        <x-typography.heading :fields="$fields['title']" size="3"
                            class="mt-headline-title-mobile lg:mt-headline-title-desktop" />
                    @endisset

                    @isset($fields['wysiwyg'])
                        <x-typography.text :content="$fields['wysiwyg']" class="mt-title-text-mobile lg:mt-title-text-desktop" />
                    @endisset
                </div>
            </div>


            @isset($fields['logos'])
                <div class="relative w-full px-12 lg:px-16">
                    <div class="swiper w-full" x-ref="swiperContainer">
                        <div class="swiper-wrapper">
                            @foreach ($fields['logos'] as $logo)
                                @php
                                    $logoImg = $logo['logo'] ?? null;
                                    $logoLink = $logo['link'] ?? null;
                                    $index = $loop->index;
                                    $containerClass = 'p-4 transition-opacity duration-300 ease-in-out md:p-6 lg:px-10';
                                @endphp
                                @if (@isset($logoImg) && $logoImg)
                                    <div class="swiper-slide rounded-card bg-white">
                                        @if (!empty($logoLink))
                                            <a href="{{ $logoLink['url'] }}" target="{{ $logoLink['target'] }}"
                                                x-on:mouseover="activeLogo = {{ $index }}"
                                                x-on:mouseleave="activeLogo = null" title="{{ $logoLink['title'] }}"
                                                :class="{
                                                    'opacity-50': activeLogo !== null && activeLogo !==
                                                        {{ $index }}
                                                }"
                                                @class(['block', $containerClass])>
                                            @else
                                                <div :class="{
                                                    'opacity-50': activeLogo !== null && activeLogo !==
                                                        {{ $index }}
                                                }"
                                                    @class([$containerClass])>
                                        @endif

                                        <x-media.img :image="$logoImg" class="contain-full" size="small"
                                            containerClass="aspect-[2/1] relative" />
                                        @if (!empty($logoLink))
                                            </a>
                                        @else
                                    </div>
                                @endif
                        </div>
    @endif
    @endforeach
    </div>
    </div>
    @php
        $logoCount = count($fields['logos']);
    @endphp
    <x-action.button @class([
        'center-top left-0',
        'md:hidden' => $logoCount < 4,
        'lg:hidden' => $logoCount < 5,
        'xl:hidden' => $logoCount < 6,
    ]) type="secondary" x-ref="buttonPrev" iconOnly>
        <x-fas-angle-left />
    </x-action.button>
    <x-action.button @class([
        'center-top right-0',
        'md:hidden' => $logoCount < 4,
        'lg:hidden' => $logoCount < 5,
        'xl:hidden' => $logoCount < 6,
    ]) type="secondary" x-ref="buttonNext" iconOnly>
        <x-fas-angle-right />
    </x-action.button>
    </div>
@endisset
</div>
</x-block>
@endif