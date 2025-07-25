@php
    if (empty($fields['title']['content'])) {
        $fields['title']['content'] = get_the_title();
        $fields['title']['tag'] = 'h1';
    }
@endphp

<x-block :fields="$fields">

    @isset($fields['main_image']['sizes']['large'])
        <x-media.img :image="$fields['main_image']" class="cover-full" size="full" container-class="w-full" />
        <div @class([
            'absolute-full bg-gradient-to-b lg:bg-gradient-to-r',
            'from-white to-[rgba(255,255,255,0.25)]' => !$fields['dark_mode'],
            'from-black to-[rgba(0,0,0,0.25)]' => $fields['dark_mode'],
        ])></div>
    @endisset

    <div class="relative z-10 lg:grid-12">
        <div class="lg:col-span-6">
            <x-breadcrumbs />
            @isset($fields['uptitle'])
                <x-typography.uptitle :content="$fields['uptitle']" class="mb-headline-title-desktop" />
            @endisset

            @isset($fields['title'])
                <x-typography.heading :fields="$fields['title']" size="2" />
            @endisset

            @isset($fields['wysiwyg'])
                <div class="wysiwyg mt-title-text-desktop">
                    {!! $fields['wysiwyg'] !!}
                </div>
            @endisset

            @isset($fields['buttons'])
                <x-action.buttons class="mt-8" :buttons="$fields['buttons']" />
            @endisset
        </div>
    </div>
</x-block>
