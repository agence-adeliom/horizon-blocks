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

        $idToFocus = $idToFocus ?? 'listing-results';

        /*
            Link URLs: during a Livewire update the request URL is /livewire/update, so the links pointed
            there (opening one in a new tab or copying it failed). The page URL comes from the Referer
            instead (current address, filters included, as Livewire keeps them in the query string), or
            from Livewire's original path when there is no usable Referer.
        */
        $pageBaseUrl = request()->fullUrl();

        if (\Livewire\Livewire::isLivewireRequest()) {
            $referer = request()->header('Referer');
            $pageBaseUrl = $referer && parse_url($referer, PHP_URL_HOST) === request()->getHost()
                ? $referer
                : \Livewire\Livewire::originalUrl();
        }

        $pageUrl = static fn (int $page): string => add_query_arg('pagination', $page, $pageBaseUrl);
        $onClick = "scrollToAnchor('{$idToScroll}'); document.getElementById('{$idToFocus}')?.focus({ preventScroll: true })";

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
            $pageNumbers = array_filter([1, $current - 1, $current, $current + 1, $pages], static function (
                int $page,
            ) use ($pages): bool {
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
    $extraHandleParams = sprintf(
        '%s',
        implode(
            ', ',
            array_map(function ($param) {
                if (is_string($param)) {
                    return "'$param'";
                }

                return $param;
            }, $extraHandleParams),
        ),
    );

    if ($extraHandleParamsFirst) {
        $extraHandleParams = $extraHandleParams . ', ';
    } else {
        $extraHandleParams = ', ' . $extraHandleParams;
    }
} else {
    $extraHandleParams = '';
        }
    @endphp

    @if (!empty($displayValues) && $pages > 1)
        {{--
            <nav> and not <div>: a set of page links is a navigation landmark, and screen readers
            announce it as such. Inside, the links form a list (RGAA 9.3). The margins on the arrows
            keep the spacing the former flex gap gave between the arrows and the page numbers.
        --}}
        <nav class="horizon-pagination{{ $containerClass ? ' ' . $containerClass : '' }}"
            aria-label="{{ $paginationLabel }}">
            <ol role="list" class="flex justify-center items-center">
                @if ($hasButtons)
                    @if ($current > 1)
                        <li class="me-4 lg:me-10">
                            <a @class([$baseButtonClass]) title="{{ $previousLabel }}" aria-label="{{ $previousLabel }}"
                                href="{{ $pageUrl($current - 1) }}"
                                @click.prevent="{{ $onClick }}"
                                @if ($handle) wire:click.prevent="{{ $handle }}(
                               @if ($extraHandleParamsFirst && $extraHandleParams) {{ $extraHandleParams }} @endif{{ $current - 1 }}@if (!$extraHandleParamsFirst && $extraHandleParams) {{ $extraHandleParams }} @endif
                                )" @endif>
                                <x-far-angle-left class="icon-5" />
                            </a>
                        </li>
                    @else
                        {{-- Kept in place so the row does not shift by one slot on the first and last pages. --}}
                        <li class="me-4 lg:me-10" aria-hidden="true">
                            <span @class([$baseButtonClass, $inactiveButtonClass])>
                                <x-far-angle-left class="icon-5" />
                            </span>
                        </li>
                    @endif
                @endif

                @foreach ($displayValues as $page)
                    @if ($page == $current)
                        <li>
                            <span @class([$baseNumberClass, $activeNumberClass]) aria-current="page"><span class="sr-only">Page
                                </span>{{ $page }}</span>
                        </li>
                    @elseif($page != $separator)
                        <li>
                            <a @class([$baseNumberClass]) title="Page {{ $page }}"
                                aria-label="Page {{ $page }}"
                                href="{{ $pageUrl($page) }}"
                                @click.prevent="{{ $onClick }}"
                                @if ($handle) wire:click.prevent="{{ $handle }}(
                               @if ($extraHandleParamsFirst && $extraHandleParams) {{ $extraHandleParams }} @endif
                                {{ $page }} @if (!$extraHandleParamsFirst && $extraHandleParams) {{ $extraHandleParams }} @endif
                                )" @endif>{{ $page }}</a>
                        </li>
                    @else
                        <li class="horizon-pagination-ellipsis my-2 mx-1" aria-hidden="true">{{ $separator }}</li>
                    @endif
                @endforeach

                @if ($hasButtons)
                    @if ($current < $pages)
                        <li class="ms-4 lg:ms-10">
                            <a @class([$baseButtonClass]) title="{{ $nextLabel }}"
                                aria-label="{{ $nextLabel }}"
                                href="{{ $pageUrl($current + 1) }}"
                                @click.prevent="{{ $onClick }}"
                                @if ($handle) wire:click.prevent="{{ $handle }}(
                               @if ($extraHandleParamsFirst && $extraHandleParams) {{ $extraHandleParams }} @endif
                                {{ $current + 1 }} @if (!$extraHandleParamsFirst && $extraHandleParams) {{ $extraHandleParams }} @endif
                                )" @endif>
                                <x-far-angle-right class="icon-5" />
                            </a>
                        </li>
                    @else
                        <li class="ms-4 lg:ms-10" aria-hidden="true">
                            <span @class([$baseButtonClass, $inactiveButtonClass])>
                                <x-far-angle-right class="icon-5" />
                            </span>
                        </li>
                    @endif
                @endif
            </ol>
        </nav>
    @endif
@endif
