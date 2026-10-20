<div @class([
    'card-basic',
    'col-span-6 flex bg-full min-h-80 relative rounded-card overflow-hidden group awc-theme-dark bg-background',
    $attributes['class'],
])>
    @if (!empty($card['img']))
        <x-media.img :image="$card['img']" class="cover-full group-hover:scale-110 group-focus-within:scale-110 transition-transform duration-300 ease-in-out"
            size="medium_large" container-class="absolute-full" />
        <div class="absolute-full bg-linear rounded-card"></div>
    @endif
    <div class="p-card flex flex-col items-start gap-card shadow-small-blur z-10 mt-auto">
        @if (!empty($card['title']))
            <x-typography.heading :content="$card['title']" :tag="$headingTag" :size="5" />
        @endif
        @if (!empty($card['wysiwyg']))
            <x-typography.text :content="$card['wysiwyg']" class="[&_p]:text-text-primary!" />
        @endif
        @if (!empty($card['button']))
            <x-action.button :fields="$card['button']" full-link="true" />
        @endif
    </div>
</div>
