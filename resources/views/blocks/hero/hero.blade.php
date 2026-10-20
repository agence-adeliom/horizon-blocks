@php
    if (empty($fields['title']['content'])) {
        $fields['title']['content'] = get_the_title();
        $fields['title']['tag'] = 'h1';
    }
@endphp

<x-block :fields="$fields">

    @if (!empty($fields['mainImage']['sizes']['large']))
        <x-media.img :image="$fields['mainImage']" class="cover-full" size="full" container-class="w-full" :decorative="true"
            loading="eager" fetchpriority="high" />
        <div @class([
            'absolute-full bg-gradient-to-b lg:bg-gradient-to-r lg:via-50%',
            'from-white via-white/90 to-white/80 lg:to-white/25' => empty(
                $fields['dark_mode']
            ),
            'from-black via-black/90 to-black/80 lg:to-black/25' => !empty(
                $fields['dark_mode']
            ),
        ])></div>
    @endif

    <div class="relative z-10 lg:grid-12">
        <div class="lg:col-span-6">
            <x-breadcrumbs />
            <div class="flex flex-col">
                @if (!empty($fields['title']))
                    <x-typography.heading :fields="$fields['title']" size="2" />
                @endif

                @if (!empty($fields['uptitle']))
                    <x-typography.uptitle :content="$fields['uptitle']" class="-order-1 mb-headline-title-desktop" />
                @endif
            </div>

            @if (!empty($fields['wysiwyg']))
                <div class="wysiwyg mt-title-text-desktop">
                    {!! $fields['wysiwyg'] !!}
                </div>
            @endif

            @if (!empty($fields['buttons']))
                <x-action.buttons class="mt-8" :buttons="$fields['buttons']" />
            @endif
        </div>
    </div>
</x-block>
