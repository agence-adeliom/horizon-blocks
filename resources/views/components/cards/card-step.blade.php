<div @class([
    'card-step',
    'relative overflow-hidden rounded-card bg-primary',
    $attributes['class'],
])>
    @if (!empty($step['img']))
        <x-media.img :image="$step['img']" class="cover-full" size="medium" container-class="aspect-[1.5] relative w-full" />
    @endif
    <div class="relative z-10 bg-primary p-card">
        <div class="awc-theme-dark flex w-full flex-col items-start">
            @if (!empty($step['uptitle']))
                <x-typography.heading :content="$step['uptitle']" size="headline" />
            @endif

            @if (!empty($step['title']))
                <x-typography.heading :content="$step['title']" size="5" />
            @endif

            @if (!empty($step['content']))
                <x-typography.text :content="$step['content']" class="mt-card" />
            @endif
        </div>
    </div>
</div>
