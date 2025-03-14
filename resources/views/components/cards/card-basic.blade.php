<div @class([
    'card-basic',
    'col-span-6 flex bg-full min-h-80 relative rounded-card overflow-hidden group awc-theme-dark',
    $attributes['class'],
])>
    <x-media.img :image="$card['img']" class="cover-full group-hover:scale-110 transition-transform duration-300 ease-in-out"
        size="medium_large" container-class="absolute-full" />
    <div class="absolute-full bg-linear rounded-card"></div>
    <div class="p-card flex flex-col items-start gap-card shadow-small-blur z-10 mt-auto">
        @if (isset($card['title']) && $card['title'])
            <x-typography.heading :fields="$card['title']" :size="5" />
        @endif
        @if (isset($card['wysiwyg']) && $card['wysiwyg'])
            <x-typography.text :content="$card['wysiwyg']" />
        @endif
        @if (isset($card['button']) && $card['button'])
            <x-action.button :fields="$card['button']" full-link="true" />
        @endif
    </div>
</div>
