@use('Adeliom\HorizonBlocks\Blocks\Action\NavbarBlock')

{{--
    On écarte les lignes sans URL : le repeater accepte une ligne vide, et un `<a>` sans href
    n'est pas focusable — il apparaîtrait dans le sommaire sans jamais pouvoir être atteint.
--}}
@php
    $anchors = array_values(
        array_filter(
            $fields[NavbarBlock::FIELD_ANCHORS] ?? [],
            static fn (array $anchor): bool => ! empty($anchor[NavbarBlock::FIELD_BUTTON]['link']['url']),
        ),
    );

    $cta = $fields[NavbarBlock::FIELD_BUTTON] ?? null;
@endphp

@if (! empty($anchors))
    {{--
        Volontairement pas de `<x-block>` : le composant rend une `<section>` avec ses paddings de
        section, et un enfant `sticky` ne colle que dans les limites de son parent — la barre
        disparaîtrait donc avec la section. La racine du bloc doit porter `sticky` elle-même pour
        rester enfant direct du `.main` et coller à l'échelle de la page.
        
        `#sticky-navbar` sert de point d'entrée au script (cf. scripts/blocks/navbar.ts).
    --}}
    {{--
        `awc-theme-dark` : la barre est une surface sombre du design system (cf. la fiche
        block-header-topnavbar sur webcomponents.adeliom.io). Les couleurs qu'elle utilise
        — `color-01-50` pour le fond, `neutral-950` pour les ancres — ne prennent leurs valeurs
        sombres que sous cette classe.
    --}}
    <div class="navbar-anchors awc-theme-dark" id="sticky-navbar" x-data="initNavBar">
        <nav class="navbar-anchors-inner container" aria-label="{{ __('Sommaire de la page', 'horizon-blocks') }}">
            {{--
                Le scroller et son dégradé de fondu sont dans un wrapper à part : le dégradé se
                positionne sur le bord du scroller, pas sur celui du conteneur qui porte le CTA.
            --}}
            <div class="navbar-anchors-scroller">
                <ul class="navbar-anchors-list">
                    @foreach ($anchors as $anchor)
                        @php($link = $anchor[NavbarBlock::FIELD_BUTTON]['link'])

                        <li>
                            {{--
                                `data-anchor` duplique le href pour que le script n'ait pas à
                                composer avec l'URL absolue que le navigateur renvoie sur
                                `a.href` (`https://…/page#ancre`), là où il lui faut le seul
                                fragment. `aria-current` est posé par le script.
                            --}}
                            <a href="{{ $link['url'] }}" data-anchor="{{ $link['url'] }}" class="navbar-anchors-link" js-anchor>
                                {{ $link['title'] ?: $link['url'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{--
                Pas d'icône : `<x-ui.icon>` appelle `@svg($iconName, $class)` avec le `icon-class`
                du bouton, vide par défaut. Le SVG sortait donc sans classe, la règle
                `.btn-md .icon { h-3 w-3 }` de button.css ne s'y appliquait pas et il était calculé
                à 0x0 — tout en restant un élément flex, donc le `gap` du bouton ajoutait 16px de
                vide à droite du libellé, vers un icône invisible.
            --}}
            @if (! empty($cta['link']['url']))
                <x-action.button :fields="$cta" type="secondary" size="medium" class="navbar-anchors-cta" />
            @endif
        </nav>
    </div>
@endif
