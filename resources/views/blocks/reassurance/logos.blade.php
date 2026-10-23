@if (!empty($fields))
    <x-block :fields="$fields" :block="$block" class="overflow-hidden">
        <div class="flex flex-col gap-text-image-mobile lg:gap-text-image-desktop" x-data="initLogosSlider()">

            <div class="grid-12">
                <div class="flex flex-col items-center text-center lg:col-span-8 lg:col-start-3">
                    @if (!empty($fields['uptitle']))
                        <x-typography.uptitle :content="$fields['uptitle']" />
                    @endif

                    @if (!empty($fields['title']))
                        <x-typography.heading :fields="$fields['title']" size="3"
                            class="mt-headline-title-mobile lg:mt-headline-title-desktop" />
                    @endif

                    @if (!empty($fields['wysiwyg']))
                        <x-typography.text :content="$fields['wysiwyg']" class="mt-title-text-mobile lg:mt-title-text-desktop" />
                    @endif
                </div>
            </div>


            @php
                $logos = array_values(array_filter($fields['logos'] ?? [], fn($logo) => !empty($logo['logo'])));
            @endphp

            @if (!empty($logos))
                <div class="relative w-full px-12 lg:px-16">
                    {{-- Flèches avant les logos dans le code placées sur les côtés en CSS --}}
                    @if (count($logos) > 1)
                        <x-action.button tag="button" type="secondary" iconOnly class="center-top left-0"
                            x-show="!locked" at-click="swiper.slidePrev()"
                            aria-label="{{ __('Logos précédents', 'horizon-blocks') }}">
                            <x-fas-angle-left />
                        </x-action.button>
                        <x-action.button tag="button" type="secondary" iconOnly class="center-top right-0"
                            x-show="!locked" at-click="swiper.slideNext()"
                            aria-label="{{ __('Logos suivants', 'horizon-blocks') }}">
                            <x-fas-angle-right />
                        </x-action.button>
                    @endif

                    <div class="swiper w-full" x-ref="swiperContainer">
                        <ul role="list" class="swiper-wrapper">
                            @foreach ($logos as $logo)
                                @php
                                    $logoLink = $logo['link'] ?? null;
                                    $index = $loop->index;
                                @endphp
                                <li class="swiper-slide rounded-card bg-white transition-opacity duration-300 ease-in-out"
                                    :class="{ 'opacity-50': activeLogo !== null && activeLogo !== {{ $index }} }">
                                    @if (!empty($logoLink['url']))
                                        <a href="{{ $logoLink['url'] }}"
                                            @if (!empty($logoLink['target'])) target="{{ $logoLink['target'] }}" @endif
                                            @if (!empty($logoLink['title'])) title="{{ $logoLink['title'] }}" @endif
                                            x-on:mouseover="activeLogo = {{ $index }}"
                                            x-on:mouseleave="activeLogo = null"
                                            x-on:focus="activeLogo = {{ $index }}" x-on:blur="activeLogo = null"
                                            class="block p-4 focus-visible:-outline-offset-2 md:p-6 lg:px-10">
                                            <x-media.img :image="$logo['logo']" :decorative="false" class="contain-full"
                                                size="small" containerClass="aspect-[2/1] relative" />
                                        </a>
                                    @else
                                        <div class="p-4 md:p-6 lg:px-10">
                                            <x-media.img :image="$logo['logo']" :decorative="false" class="contain-full"
                                                size="small" containerClass="aspect-[2/1] relative" />
                                        </div>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
        </div>
    </x-block>
@endif
