@php
    use App\Blocks\Content\GalleryBlock;
    use Adeliom\HorizonTools\Fields\Medias\ImageField;
    $displayType = $fields[GalleryBlock::FIELD_DISPLAY_TYPE] ?? GalleryBlock::VALUE_DISPLAY_MOSAIC;
    $isSlider = $displayType === GalleryBlock::VALUE_DISPLAY_SLIDER;
    $images = $fields[GalleryBlock::FIELD_GALLERY] ?? [];
    $count = count($images ?: []);
    $dialogLabel = !empty($fields['title']['content']) ? $fields['title']['content'] : __('Galerie', 'horizon-blocks');
    $informative = !empty($fields[ImageField::GALLERY_INFORMATIVE]);
    $openLabel = fn($item, $index) => trim(
        sprintf(__("Agrandir l'image %d sur %d", 'horizon-blocks'), $index + 1, $count) .
            ($informative && !empty($item['alt']) ? ' : ' . $item['alt'] : ''),
    );
@endphp
<x-block :fields="$fields" :block="$block" :class="$isSlider ? 'overflow-x-clip' : ''">
    <div class="grid-12" x-data="initGallery()">
        @if ($isSlider)
            <div class="col-span-full min-w-0 flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="lg:max-w-2xl">
                    @if (!empty($fields['uptitle']))
                        <x-typography.uptitle :content="$fields['uptitle']" />
                    @endif

                    @if (!empty($fields['title']))
                        <x-typography.heading :fields="$fields['title']" size="3" />
                    @endif

                    @if (!empty($fields['wysiwyg']))
                        <x-typography.text :content="$fields['wysiwyg']" />
                    @endif

                    @if (!empty($fields['buttons']['one']['link']) || !empty($fields['buttons']['two']['link']))
                        <x-action.buttons :buttons="$fields['buttons']"
                            class="w-fit mt-button-text-mobile lg:mt-button-text-desktop" />
                    @endif
                </div>
                @if ($count > 1)
                    <div class="flex gap-2">
                        <x-action.button tag="button" type="primary" iconOnly class="awc-theme-dark rounded-full"
                            at-click="horizontalSwiper.slidePrev()"
                            aria-label="{{ __('Images précédentes', 'horizon-blocks') }}">
                            <x-far-chevron-left class="icon-5" />
                        </x-action.button>
                        <x-action.button tag="button" type="primary" iconOnly class="awc-theme-dark rounded-full"
                            at-click="horizontalSwiper.slideNext()"
                            aria-label="{{ __('Images suivantes', 'horizon-blocks') }}">
                            <x-far-chevron-right class="icon-5" />
                        </x-action.button>
                    </div>
                @endif
            </div>

            @if ($count)
                <div class="col-span-full min-w-0 mt-10">
                    <div x-ref="horizontalContainer" class="swiper !overflow-visible">
                        <ul role="list" class="swiper-wrapper">
                            @foreach ($images as $item)
                                <li class="swiper-slide">
                                    <button type="button"
                                        class="block w-full rounded-card overflow-hidden cursor-pointer"
                                        @click="openAt({{ $loop->index }})"
                                        @focus="showHorizontalSlide({{ $loop->index }})"
                                        aria-label="{{ $openLabel($item, $loop->index) }}">
                                        <x-media.img :image="$item" class="object-cover w-full h-full absolute"
                                            size="full"
                                            container-class="relative aspect-[4/3] relative w-full h-auto" />
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
        @else
            <div class="lg:col-start-3 lg:col-span-8 text-center">
                @if (!empty($fields['uptitle']))
                    <x-typography.uptitle :content="$fields['uptitle']" />
                @endif

                @if (!empty($fields['title']))
                    <x-typography.heading :fields="$fields['title']" size="3" />
                @endif

                @if (!empty($fields['wysiwyg']))
                    <x-typography.text :content="$fields['wysiwyg']" />
                @endif

                @if (!empty($fields['buttons']['one']['link']) || !empty($fields['buttons']['two']['link']))
                    <x-action.buttons :buttons="$fields['buttons']"
                        class="w-fit mx-auto mt-button-text-mobile lg:mt-button-text-desktop" />
                @endif
            </div>
            @if ($count)
                <ul role="list"
                    class="col-span-full grid grid-cols-1 md:grid-cols-2 gap-6 mt-10 {{ $count % 4 === 0 ? 'lg:grid-cols-4' : 'lg:grid-cols-3' }}">
                    @foreach ($images as $item)
                        <li>
                            <button type="button"
                                class="block w-full rounded-card overflow-hidden group relative cursor-pointer"
                                @click="openAt({{ $loop->index }})" aria-label="{{ $openLabel($item, $loop->index) }}">
                                <x-media.img :image="$item" class="object-cover w-full h-full absolute"
                                    size="full" container-class="relative aspect-[4/3] relative w-full h-auto" />
                                <span
                                    class="rounded-full bg-primary w-full absolute left-0 aspect-square scale-50 -bottom-full lg:group-hover:scale-[3] lg:group-focus-visible:scale-[3] transition-all ease-linear duration-300 opacity-0 lg:group-hover:opacity-50 lg:group-focus-visible:opacity-50"></span>
                            </button>
                        </li>
                    @endforeach
                </ul>
            @endif
        @endif

        {{-- Popin + slider (commune aux deux modes) --}}
        @if ($count)
            <div x-show="open" x-transition.opacity class="fixed inset-0 z-[999] flex items-center justify-center"
                style="display: none;">
                <div class="bg-black/50 cursor-pointer absolute inset-0 z-0" @click="close()" aria-hidden="true"></div>
                <div role="dialog" aria-modal="true" aria-label="{{ $dialogLabel }}" class="relative"
                    x-trap.inert.noscroll="open" @keydown.escape.prevent.stop="close()"
                    @keydown.arrow-left="swiper.slidePrev()" @keydown.arrow-right="swiper.slideNext()">
                    <x-action.button tag="button" type="primary" iconOnly
                        class="fixed top-10 right-10 awc-theme-dark z-[100]" at-click="close()"
                        aria-label="{{ __('Fermer la galerie', 'horizon-blocks') }}">
                        <x-far-xmark />
                    </x-action.button>
                    <div x-ref="swiperContainer"
                        class="swiper aspect-[4/3] w-[90vw] lg:w-[800px] h-auto flex items-center justify-center">
                        <div class="swiper-wrapper">
                            @foreach ($images as $item)
                                <div class="swiper-slide w-full h-full"
                                    data-announcement="{{ sprintf(__('Image %d sur %d', 'horizon-blocks'), $loop->iteration, $count) }}">
                                    <x-media.img :image="$item" :decorative="!$informative"
                                        class=" max-w-full max-h-full object-contain" size="full"
                                        container-class="relative w-full h-full" />
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @if ($count > 1)
                        <x-action.button tag="button" type="primary" iconOnly
                            class="fixed top-1/2 -translate-y-1/2 left-10 awc-theme-dark rounded-full z-20"
                            at-click="swiper.slidePrev()" aria-label="{{ __('Image précédente', 'horizon-blocks') }}">
                            <x-far-chevron-left class="icon-5" />
                        </x-action.button>
                        <x-action.button tag="button" type="primary" iconOnly
                            class="fixed top-1/2 -translate-y-1/2 right-10 awc-theme-dark rounded-full z-20"
                            at-click="swiper.slideNext()" aria-label="{{ __('Image suivante', 'horizon-blocks') }}">
                            <x-far-chevron-right class="icon-5" />
                        </x-action.button>
                    @endif
                    <p class="sr-only" aria-live="polite" x-text="announcement"></p>
                </div>
            </div>
        @endif
    </div>
</x-block>
