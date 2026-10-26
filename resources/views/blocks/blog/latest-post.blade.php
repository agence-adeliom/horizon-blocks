@php
    use App\Blocks\Blog\LatestPostBlock;

    // Titres des cartes un cran sous le titre du bloc ; h2 si le bloc n’a pas de titre.
    $cardHeadingTag =
        preg_match('/^h([1-5])$/', $fields['title']['tag'] ?? '', $level) && !empty($fields['title']['content'])
            ? 'h' . ($level[1] + 1)
            : 'h2';
@endphp

@if (! empty($context['posts']) || ! empty($context['featuredPosts']))
    <x-block :fields="$fields" :block="$block">
        <div class="gap-text-image-mobile lg:gap-text-image-desktop flex flex-col">
            <div class="grid-12">
                <div class="gap-title-text-mobile lg:gap-title-text-desktop col-span-12 flex flex-col lg:col-span-8">
                    @if (! empty($fields['uptitle']))
                        <x-typography.uptitle :content="$fields['uptitle']" />
                    @endif

                    @if (! empty($fields['title']['content']))
                        <x-typography.heading :fields="$fields['title']" size="3" />
                    @endif

                    @if (! empty($fields['wysiwyg']))
                        <x-typography.text class="text-large" :content="$fields['wysiwyg']" />
                    @endif
                </div>
            </div>

            @if ($context['layout'] === LatestPostBlock::VALUE_LAYOUT_THREE)
                <ul role="list" class="gap-xlarge grid lg:grid-cols-3">
                    @foreach ($context['posts'] as $post)
                        <li class="flex">
                            <x-cards.card-post :post="$post" :heading-tag="$cardHeadingTag" class="w-full" />
                        </li>
                    @endforeach
                </ul>
            @else
                {{--
                    Mise en page « quatre articles » : le premier article occupe la moitié gauche en
                    grand format, les suivants s’empilent en cartes horizontales à droite. Une seule
                    liste, la disposition est faite en grille : le lecteur d’écran annonce autant
                    d’éléments que d’articles.
                --}}
                <ul role="list" class="gap-xlarge grid lg:grid-cols-2">
                    @foreach ($context['featuredPosts'] as $post)
                        <li class="flex lg:row-span-3">
                            <x-cards.card-post :post="$post" :with-excerpt="true" :heading-tag="$cardHeadingTag" class="w-full" />
                        </li>
                    @endforeach

                    @foreach ($context['posts'] as $post)
                        <li class="flex">
                            <x-cards.card-post :post="$post" :vertical="false" :heading-tag="$cardHeadingTag" class="w-full" />
                        </li>
                    @endforeach
                </ul>
            @endif

            @if (! empty($context['buttons']))
                <x-action.buttons
                    :buttons="$context['buttons']"
                    base-class="gap-button-button-mobile lg:gap-button-button-desktop flex justify-center"
                />
            @endif
        </div>
    </x-block>
@endif
