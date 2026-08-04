@if ($data)
    @php
        $nextLabel = $nextLabel ?? 'Suivant';
        $previousLabel = $previousLabel ?? 'Précédent';
        $paginationLabel = $paginationLabel ?? 'Pagination';

        $containerClass = $containerClass ?? null;

        /*
            Opt-in: leaving it false keeps the historical numbering, so projects already running the
            package see no change. See the comment on the two branches below.
        */
        $slidingWindow = $slidingWindow ?? false;

        $baseButtonClass =
            'horizon-pagination-arrow cursor-pointer block w-10 h-10 rounded flex items-center justify-center transition-opacity duration-200';
        $inactiveButtonClass = 'horizon-pagination-arrow-disabled !cursor-not-allowed opacity-30';

        $baseNumberClass =
            'horizon-pagination-page block w-10 h-10 rounded-lg flex items-center justify-center cursor-pointer text-text-secondary font-semibold';
        $activeNumberClass = 'horizon-pagination-page-current cursor-default border border-primary rounded';
        $idToScroll = 'listing-container';

        $separator = '...';

        $pages = (int) $data['pages'];
        $current = (int) $data['current'];

        $pageNumbers = [];

        if ($slidingWindow) {
            /*
                First page, the current page with its direct neighbours, last page. Nothing else:
                the historical rule below also listed every multiple of five, which carries no
                meaning for the reader and pushes the pages that do matter out of reach — on 37
                pages sitting on page 18 it rendered thirteen entries.
            */
            $pageNumbers = array_filter([1, $current - 1, $current, $current + 1, $pages], static function (int $page) use ($pages): bool {
                return $page >= 1 && $page <= $pages;
            });
        } else {
            $pageNumbers[] = 1;

            // Pages proches de la page actuelle (3 pages autour)
            for ($x = max(2, $current - 1); $x <= min($pages - 1, $current + 1); $x++) {
                $pageNumbers[] = $x;
            }

            // Ajouter les multiples de 5
            for ($x = 5; $x <= $pages; $x += 5) {
                if (!in_array($x, $pageNumbers)) {
                    $pageNumbers[] = $x;
                }
            }

            // Ajouter la dernière page si elle n'est pas déjà incluse
            if (!in_array($pages, $pageNumbers)) {
                $pageNumbers[] = $pages;
            }
        }

        // Trier et ajouter les séparateurs
        $pageNumbers = array_unique($pageNumbers);
        sort($pageNumbers);
        $displayValues = [];
        $last = null;

        foreach ($pageNumbers as $value) {
            if ($last !== null && $value - $last > 1) {
                $displayValues[] = $separator;
            }
            $displayValues[] = $value;
            $last = $value;
        }

        $extraHandleParamsFirst = $extraHandleParamsFirst ?? false;
        if (!is_bool($extraHandleParamsFirst)) {
            $extraHandleParamsFirst = false;
        }

        if (!empty($extraHandleParams)) {
            $extraHandleParams = sprintf('%s', implode(', ', array_map(function($param){
                if (is_string($param)) {
                    return "'$param'";
                }

                return $param;
            }, $extraHandleParams)));

            if ($extraHandleParamsFirst) {
                $extraHandleParams = $extraHandleParams.', ';
            } else {
                $extraHandleParams = ', '.$extraHandleParams;
            }
        } else {
            $extraHandleParams = '';
        }
    @endphp

    @if (!empty($displayValues) && $pages > 1)
        {{--
            <nav> and not <div>: a set of page links is a navigation landmark, and screen readers
            announce it as such. The utility classes are unchanged, so the rendering is identical —
            this is the only change to the DOM structure.
        --}}
        <nav class="horizon-pagination flex justify-center gap-4 lg:gap-10{{ $containerClass ? ' '.$containerClass : '' }}"
             aria-label="{{ $paginationLabel }}">
            @if ($hasButtons)
                @if ($current > 1)
                    <a @class([$baseButtonClass]) title="{{ $previousLabel }}" aria-label="{{ $previousLabel }}"
                       href="{{ request()->fullUrlWithQuery(['pagination' => $current - 1]) }}"
                       @click.prevent="scrollToAnchor('{{ $idToScroll }}')"
                       @if ($handle) wire:click.prevent="{{ $handle }}(
                       @if($extraHandleParamsFirst && $extraHandleParams) {{$extraHandleParams}} @endif{{ $current - 1 }}@if(!$extraHandleParamsFirst && $extraHandleParams) {{ $extraHandleParams }} @endif )" @endif>
                        <x-far-angle-left class="icon-20" />
                    </a>
                @else
                    {{-- Kept in place so the row does not shift by one slot on the first and last pages. --}}
                    <span @class([$baseButtonClass, $inactiveButtonClass]) aria-hidden="true">
                        <x-far-angle-left class="icon-20" />
                    </span>
                @endif
            @endif

            <div class="horizon-pagination-pages flex items-center">
                @foreach ($displayValues as $page)
                    @if ($page == $current)
                        <span @class([$baseNumberClass, $activeNumberClass]) aria-current="page">{{ $page }}</span>
                    @elseif($page != $separator)
                        <a @class([$baseNumberClass]) title="Page {{ $page }}" aria-label="Page {{ $page }}"
                           href="{{ request()->fullUrlWithQuery(['pagination' => $page]) }}"
                           @click.prevent="scrollToAnchor('{{ $idToScroll }}')"
                           @if ($handle) wire:click.prevent="{{ $handle }}(
                           @if($extraHandleParamsFirst && $extraHandleParams) {{ $extraHandleParams }} @endif {{$page}} @if(!$extraHandleParamsFirst && $extraHandleParams) {{$extraHandleParams}} @endif )" @endif>{{ $page }}</a>
                    @else
                        <span class="horizon-pagination-ellipsis my-2 mx-1" aria-hidden="true">{{ $separator }}</span>
                    @endif
                @endforeach
            </div>


            @if ($hasButtons)
                @if ($current < $pages)
                    <a @class([$baseButtonClass]) title="{{ $nextLabel }}" aria-label="{{ $nextLabel }}"
                       href="{{ request()->fullUrlWithQuery(['pagination' => $current + 1]) }}"
                       @click.prevent="scrollToAnchor('{{ $idToScroll }}')"
                       @if ($handle) wire:click.prevent="{{ $handle }}(
                       @if($extraHandleParamsFirst && $extraHandleParams) {{$extraHandleParams}} @endif {{ $current + 1 }} @if(!$extraHandleParamsFirst && $extraHandleParams) {{$extraHandleParams}} @endif )" @endif>
                        <x-far-angle-right class="icon-20" />
                    </a>
                @else
                    <span @class([$baseButtonClass, $inactiveButtonClass]) aria-hidden="true">
                        <x-far-angle-right class="icon-20" />
                    </span>
                @endif
            @endif
        </nav>
    @endif
@endif
