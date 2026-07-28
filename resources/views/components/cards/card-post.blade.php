@if ($post)
    {{-- relative : sert de référence au lien pleine carte posé par le bouton (full-link). --}}
    <article
        @class([
            'card-post bg-background rounded-card group relative flex overflow-hidden',
            'flex-col' => $vertical,
            'max-sm:flex-col' => ! $vertical,
            $attributes['class'],
        ])
    >
        <div
            @class(['relative shrink-0 overflow-hidden', 'aspect-[384/217]' => $vertical, 'aspect-[384/217] sm:aspect-[240/207] sm:w-60' => ! $vertical])
        >
            @if ($image)
                <x-media.img
                    :image="$image"
                    size="medium_large"
                    class="cover-full transition-transform duration-300 ease-in-out group-hover:scale-105"
                    container-class="absolute-full"
                    :decorative="true"
                />
            @endif
        </div>

        <div class="p-card gap-card flex flex-1 flex-col items-start">
            @if ($term || ($withDate && $date))
                <div class="gap-medium flex w-full flex-wrap items-center justify-between">
                    @if ($term)
                        <span
                            class="rounded-pill bg-primary text-primary-contrast text-small px-2 py-1 font-semibold uppercase tracking-wider"
                        >
                            {{ $term->name }}
                        </span>
                    @endif

                    @if ($withDate && $date)
                        <span class="text-text-secondary text-small flex items-center gap-1">
                            <x-far-calendar class="icon-4" />
                            {{ $date }}
                        </span>
                    @endif
                </div>
            @endif

            <x-typography.heading tag="h3" :content="$title" size="5" class="group-hover:text-primary transition-colors" />

            @if ($withExcerpt && $excerpt)
                <x-typography.text :content="$excerpt" />
            @endif

            {{-- mt-auto : le lien reste collé au bas de la carte, quelle que soit la longueur du titre. --}}
            <x-action.button :url="$url" type="tertiary" size="medium" class="mt-auto" :full-link="true">
                <span>{{ __('Lire l’article', 'horizon-blocks') }}</span>
                <x-far-angle-right class="icon-3" />
            </x-action.button>
        </div>
    </article>
@endif
