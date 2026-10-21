@php
    use App\Blocks\Content\PhotoCarouselBlock;
    use Adeliom\HorizonTools\Fields\Medias\ImageField;
    $gallery = $fields[PhotoCarouselBlock::FIELD_GALLERY] ?? [];
    $informative = !empty($fields[ImageField::GALLERY_INFORMATIVE]);
    $count = count($gallery ?: []);
    $dialogLabel = !empty($fields['title']['content'])
        ? $fields['title']['content']
        : __('Carrousel photo', 'horizon-blocks');
    $photoLabel = fn($item, $index) => trim(
        sprintf(__('Photo %d sur %d', 'horizon-blocks'), $index + 1, $count) .
            (!empty($item['caption']) ? ' : ' . $item['caption'] : ''),
    );
@endphp
<x-block :fields="$fields" :block="$block">
    <div class="grid-12" x-data="initPhotoCarousel()">
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
            <div class="lg:col-start-3 lg:col-span-8 col-span-full min-w-0 mt-10">
                <div x-ref="horizontalContainer" class="swiper">
                    <div class="swiper-wrapper">
                        @foreach ($gallery as $item)
                            <figure class="swiper-slide"
                                @if (!empty($item['caption'])) role="figure" aria-label="{{ $item['caption'] }}" @endif
                                data-announcement="{{ $photoLabel($item, $loop->index) }}">
                                <button type="button" class="block w-full rounded-card overflow-hidden cursor-pointer"
                                    @click="openAt({{ $loop->index }})"
                                    aria-label="{{ sprintf(__('Agrandir la photo %d sur %d', 'horizon-blocks'), $loop->iteration, $count) }}">
                                    <x-media.img :image="$item" class="object-cover w-full h-full absolute"
                                        size="full" container-class="relative aspect-[16/9] w-full h-auto" />
                                </button>
                                @if (!empty($item['caption']))
                                    <figcaption class="mt-2 text-sm italic">{{ $item['caption'] }}</figcaption>
                                @endif
                            </figure>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center justify-between gap-4 mt-4">
                    <div class="flex-1 relative h-1" aria-hidden="true">
                        <div x-ref="progressBar" class="w-full h-full bg-neutral-300 rounded overflow-hidden"></div>
                    </div>
                    @if ($count > 1)
                        <div class="flex gap-2 shrink-0">
                            <x-action.button tag="button" type="primary" iconOnly class="awc-theme-dark rounded-full"
                                at-click="horizontalSwiper.slidePrev()"
                                aria-label="{{ __('Photo précédente', 'horizon-blocks') }}">
                                <x-far-chevron-left class="icon-5" />
                            </x-action.button>
                            <x-action.button tag="button" type="primary" iconOnly class="awc-theme-dark rounded-full"
                                at-click="horizontalSwiper.slideNext()"
                                aria-label="{{ __('Photo suivante', 'horizon-blocks') }}">
                                <x-far-chevron-right class="icon-5" />
                            </x-action.button>
                        </div>
                    @endif
                </div>
                <p class="sr-only" aria-live="polite" x-text="inlineAnnouncement"></p>
            </div>

            <div x-show="open" x-transition.opacity class="fixed inset-0 z-[999] flex items-center justify-center"
                style="display: none;">
                <div class="bg-black/50 cursor-pointer absolute inset-0 z-0" @click="close()" aria-hidden="true"></div>
                <div role="dialog" aria-modal="true" aria-label="{{ $dialogLabel }}" class="relative"
                    x-trap.inert.noscroll="open" @keydown.escape.prevent.stop="close()"
                    @keydown.arrow-left="swiper.slidePrev()" @keydown.arrow-right="swiper.slideNext()">
                    <x-action.button tag="button" type="primary" iconOnly
                        class="fixed top-10 right-10 awc-theme-dark z-[100]" at-click="close()"
                        aria-label="{{ __('Fermer le carrousel', 'horizon-blocks') }}">
                        <x-far-xmark />
                    </x-action.button>
                    <div x-ref="swiperContainer"
                        class="swiper aspect-[16/9] w-[90vw] lg:w-[1000px] h-auto flex items-center justify-center">
                        <div class="swiper-wrapper">
                            @foreach ($gallery as $item)
                                <div class="swiper-slide w-full h-full"
                                    data-announcement="{{ $photoLabel($item, $loop->index) }}">
                                    <x-media.img :image="$item" :decorative="!$informative"
                                        class="max-w-full max-h-full object-contain" size="full"
                                        container-class="relative w-full h-full" />
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @if ($count > 1)
                        <x-action.button tag="button" type="primary" iconOnly
                            class="fixed top-1/2 -translate-y-1/2 left-10 awc-theme-dark rounded-full z-20"
                            at-click="swiper.slidePrev()" aria-label="{{ __('Photo précédente', 'horizon-blocks') }}">
                            <x-far-chevron-left class="icon-5" />
                        </x-action.button>
                        <x-action.button tag="button" type="primary" iconOnly
                            class="fixed top-1/2 -translate-y-1/2 right-10 awc-theme-dark rounded-full z-20"
                            at-click="swiper.slideNext()" aria-label="{{ __('Photo suivante', 'horizon-blocks') }}">
                            <x-far-chevron-right class="icon-5" />
                        </x-action.button>
                    @endif
                    <p class="sr-only" aria-live="polite" x-text="announcement"></p>
                </div>
            </div>
        @endif
    </div>
</x-block>
