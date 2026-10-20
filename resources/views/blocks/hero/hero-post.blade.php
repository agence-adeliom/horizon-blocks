@php
    use Adeliom\HorizonTools\Services\ShareService;
@endphp

<x-block class="hero-post-block relative lg:pt-40" :block="$block">
    <div class="mb-10 hidden lg:block">
        <x-breadcrumbs />
    </div>
    <div
        class="bg-color-01-50 rounded-card relative z-10 grid grid-cols-12 items-center gap-6 gap-y-0 overflow-hidden lg:min-h-[440px]">
        <div class="col-span-full flex flex-col gap-4 p-4 lg:col-span-6 lg:p-10">
            @if (!empty($context['title']))
                <x-typography.heading :content="$context['title']" size="3" tag="h1" />
            @endif

            @if (!empty($context['excerpt']))
                <x-typography.text :content="$context['excerpt']" class="font-semibold" />
            @endif

            <div class="flex w-fit items-center gap-2">
                @if (!empty($context['readingTimeInMinutes']))
                    <div class="flex items-center gap-1">
                        <x-ui.icon icon="fal-clock" class="icon-5" />
                        <p class="p text-small">
                            <span class="sr-only">{{ __('Temps de lecture :', 'horizon-blocks') }}</span>
                            {{ sprintf('%d min', $context['readingTimeInMinutes']) }}
                        </p>
                    </div>
                @endif

                @if (!empty($context['readingTimeInMinutes']) && !empty($context['publishedAt']))
                    <x-ui.icon icon="far-minus" class="icon-5" />
                @endif

                @if (!empty($context['publishedAt']))
                    <div class="flex items-center gap-1">
                        <x-ui.icon icon="fal-calendar" class="icon-5" />
                        <p class="p text-small leading-none">
                            <span class="sr-only">{{ __('Publié le', 'horizon-blocks') }}</span>
                            <time
                                @if (!empty($context['publishedAtDatetime'])) datetime="{{ $context['publishedAtDatetime'] }}" @endif>
                                {{ $context['publishedAt'] }}
                            </time>
                        </p>
                    </div>
                @endif
            </div>

            @if (ShareService::hasAtLeastOneShareOption())
                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <p id="hero-post-share-label">{{ __('Partagez', 'horizon-blocks') }}</p>
                    <x-action.share-buttons :icon-only="true" aria-labelledby="hero-post-share-label" />
                </div>
            @endif
        </div>
        <div class="relative col-span-full h-full min-h-[260px] lg:col-span-6">
            @if (!empty($context['img']))
                <x-media.img :image="['ID' => $context['img']]" size="full" class="absolute h-full w-full object-cover"
                    container-class="absolute-full" :decorative="true" loading="eager" fetchpriority="high" />
            @endif
        </div>
    </div>
</x-block>
