<div class="z-100 relative" x-data="initHeroPostSlider">
    <div class="fixed top-[144px] z-[1000] h-2 w-full bg-neutral-300" x-ref="progressContainer">
        <div class="bg-primary h-full w-[0px]" x-ref="progressBar"></div>
    </div>

    <x-block class="hero-post-block relative lg:pt-40" :block="$block">
        <div class="mb-10 hidden lg:block">
            <x-breadcrumbs />
        </div>
        <div
            class="bg-color-01-50 rounded-card relative z-10 grid grid-cols-12 items-center gap-6 gap-y-0 overflow-hidden lg:min-h-[440px]"
        >
            <div class="col-span-full flex flex-col gap-4 p-4 lg:col-span-6 lg:p-10">
                @if (! empty($context['title']))
                    <x-typography.heading :content="$context['title']" size="3" tag="h1" />
                @endif

                @if (! empty($context['excerpt']))
                    <x-typography.text :content="$context['excerpt']" class="font-semibold" />
                @endif

                <div class="flex w-fit items-center gap-2">
                    @if (! empty($context['readingTimeInMinutes']))
                        <div class="flex items-center gap-1">
                            <x-ui.icon icon="fal-clock" class="icon-5" />
                            <x-typography.text :content="sprintf('%d min', $context['readingTimeInMinutes'])" class="text-small" />
                        </div>
                    @endif

                    @if (! empty($context['readingTimeInMinutes']) && ! empty($context['publishedAt']))
                        <x-ui.icon icon="far-minus" class="icon-5" />
                    @endif

                    @if (! empty($context['publishedAt']))
                        <div class="flex items-center gap-1">
                            <x-ui.icon icon="fal-calendar" class="icon-5" />
                            <x-typography.text :content="$context['publishedAt']" class="text-small leading-none" />
                        </div>
                    @endif
                </div>
                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <p>Partagez</p>
                    <x-action.share-buttons :icon-only="true" />
                </div>
            </div>
            <div class="relative col-span-full h-full min-h-[260px] lg:col-span-6">
                {!! wp_get_attachment_image($context['img'], 'full', false, ['class' => 'w-full absolute h-full object-cover']) !!}
            </div>
        </div>
    </x-block>
</div>
