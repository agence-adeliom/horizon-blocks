<div @if ($containerClass) class="{{ $containerClass }}" @endif>
    <div @if ($contentClass) class="{{ $contentClass }}" @endif>
        @if ($uptitle)
            <x-typography.uptitle :content="$uptitle" />
        @endif

        @if ($title)
            <x-typography.heading :fields="$title" size="3"
                class="mt-headline-title-mobile lg:mt-headline-title-desktop" />
        @endif

        @if ($content)
            <x-typography.text :content="$content" class="mt-title-text-mobile lg:mt-title-text-desktop" />
        @endif

        @isset($buttons)
            <x-action.buttons :buttons="$buttons" class="mt-button-text-mobile max-md:w-full lg:mt-button-text-desktop" />
        @endisset
    </div>

    <div @class(['relative', $mediaClass]) @if ($videoUrl) x-data="initLightbox()" @endif>
        @if ($isImage)
            <x-media.img :image="$image" @class(['rounded-image', 'cover-full' => $ratioClass]) :ratio="$ratioClass" size="large" />
        @elseif($isVideo || $isYouTube)
            <x-media.img :image="$thumbnail" @class(['w-full rounded-image', 'cover-full' => $ratioClass]) :ratio="$ratioClass" />

            @if ($videoUrl)
                <a href="{{ $videoUrl }}" data-glightbox class="absolute-full group flex items-center justify-center">
                    <div class="btn btn--play">
                        <x-fas-play class="icon-6 ml-1" />
                    </div>
                </a>
            @endif
        @endif
    </div>
</div>
