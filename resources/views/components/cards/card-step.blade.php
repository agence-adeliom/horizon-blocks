<div @class([
    'card-step',
    'relative overflow-hidden rounded-card bg-primary',
    $attributes['class'],
])>
    @isset($step['img'])
        <x-media.img :image="$step['img']" class="cover-full" size="medium" container-class="aspect-[1.5] relative w-full" />
    @endisset
    <div class="relative z-10 bg-primary p-card">
        <div class="awc-theme-dark flex w-full flex-col items-start">
            @isset($step['uptitle'])
                <x-typography.heading :content="$step['uptitle']" size="headline" />
            @endisset

            @isset($step['title'])
                <x-typography.heading :content="$step['title']" size="5" />
            @endisset

            @isset($step['content'])
                <x-typography.text :content="$step['content']" class="mt-card" />
            @endisset
        </div>
    </div>
</div>
