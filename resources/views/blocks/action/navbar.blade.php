@use('Adeliom\HorizonBlocks\Blocks\Action\NavbarBlock')

@php
    $anchors = array_values(
        array_filter(
            $fields[NavbarBlock::FIELD_ANCHORS] ?? [],
            static fn(array $anchor): bool => !empty($anchor[NavbarBlock::FIELD_BUTTON]['link']['url']),
        ),
    );

    $cta = $fields[NavbarBlock::FIELD_BUTTON] ?? null;
@endphp

@if (!empty($anchors))
    <div class="navbar-anchors" id="sticky-navbar" x-data="initNavBar">
        <nav class="navbar-anchors-inner container" aria-label="{{ __('Sommaire de la page', 'horizon-blocks') }}">
            <div class="navbar-anchors-scroller">
                <ul role="list" class="navbar-anchors-list">
                    @foreach ($anchors as $anchor)
                        @php($link = $anchor[NavbarBlock::FIELD_BUTTON]['link'])
                        @php($label = $link['title'] ?: $link['url'])

                        <li>
                            <a href="{{ $link['url'] }}" data-anchor="{{ $link['url'] }}"
                                data-label="{{ $label }}" class="navbar-anchors-link" js-anchor>
                                <span>{{ $label }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            @if (!empty($cta['link']['url']))
                <x-action.button :fields="$cta" type="secondary" size="medium" class="navbar-anchors-cta" />
            @endif
        </nav>
    </div>
@endif
