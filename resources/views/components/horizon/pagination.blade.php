@if ($data)
    @php
        $nextLabel = $nextLabel ?? 'Suivant';
        $previousLabel = $previousLabel ?? 'Précédent';

        $containerClass = $containerClass ?? null;

        $baseButtonClass =
            'cursor-pointer block w-10 h-10 rounded flex items-center justify-center transition-opacity duration-200';
        $inactiveButtonClass = '!cursor-not-allowed opacity-30';

        $baseNumberClass =
            'block w-10 h-10 rounded-lg flex items-center justify-center cursor-pointer text-text-secondary font-semibold';
        $activeNumberClass = 'cursor-default border border-primary rounded';

        $displayAround = 3;
        $halfAround = ($displayAround - 1) / 2;
        $separator = '...';

        $pages = $data['pages'];
        $total = $data['total'];
        $current = $data['current'];

        $pageNumbers = [];
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

        // Trier et ajouter les séparateurs
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

        if(!isset($extraHandleParamsFirst)||!is_bool($extraHandleParamsFirst)) {
            $extraHandleParamsFirst=false;
        }

        if(isset($extraHandleParams)) {
            $extraHandleParams = sprintf('%s', implode(', ', array_map(function($param){
                if(is_string($param)) {
                    return "'$param'";
                }

                return $param;
            }, $extraHandleParams)));

            if($extraHandleParamsFirst){
                $extraHandleParams = $extraHandleParams.', ';
            }else {
                $extraHandleParams = ', '.$extraHandleParams;
            }
        }else {
            $extraHandleParams = '';
        }
    @endphp

    @if (!empty($displayValues) && $pages > 1)
        <div class="flex justify-center gap-4 lg:gap-10{{ $containerClass ? ' '.$containerClass : '' }}">
            @if ($hasButtons)
                @if ($current > 1)
                    <a @class([$baseButtonClass]) title="{{ $previousLabel }}"
                       href="{{ request()->fullUrlWithQuery(['pagination' => $current - 1]) }}"
                       @click.prevent="scrollToAnchor('listing')"
                       @if ($handle) wire:click.prevent="{{ $handle }}(
                       @if($extraHandleParamsFirst && $extraHandleParams) {{$extraHandleParams}} @endif{{ $current - 1 }}@if(!$extraHandleParamsFirst && $extraHandleParams) {{ $extraHandleParams }} @endif )" @endif>
                        <x-far-angle-left class="icon-20" />
                    </a>
                @else
                    <span @class([$baseButtonClass, $inactiveButtonClass])>
                        <x-far-angle-left class="icon-20" />
                    </span>
                @endif
            @endif

            <div class="flex items-center">
                @foreach ($displayValues as $page)
                    @if ($page == $current)
                        <span @class([$baseNumberClass, $activeNumberClass])>{{ $page }}</span>
                    @elseif($page != $separator)
                        <a @class([$baseNumberClass]) title="Page {{ $page }}"
                           href="{{ request()->fullUrlWithQuery(['pagination' => $page]) }}"
                           @click.prevent="scrollToAnchor('listing')"
                           @if ($handle) wire:click.prevent="{{ $handle }}(
                           @if($extraHandleParamsFirst && $extraHandleParams) {{ $extraHandleParams }} @endif {{$page}} @if(!$extraHandleParamsFirst && $extraHandleParams) {{$extraHandleParams}} @endif )" @endif>{{ $page }}</a>
                    @else
                        <span class="my-2 mx-1">{{ $separator }}</span>
                    @endif
                @endforeach
            </div>


            @if ($hasButtons)
                @if ($current < $pages)
                    <a @class([$baseButtonClass]) title="{{ $nextLabel }}"
                       href="{{ request()->fullUrlWithQuery(['pagination' => $current + 1]) }}"
                       @click.prevent="scrollToAnchor('listing')"
                       @if ($handle) wire:click.prevent="{{ $handle }}(
                       @if($extraHandleParamsFirst && $extraHandleParams) {{$extraHandleParams}} @endif {{ $current + 1 }} @if(!$extraHandleParamsFirst && $extraHandleParams) {{$extraHandleParams}} @endif )" @endif>
                        <x-far-angle-right class="icon-20" />
                    </a>
                @else
                    <span @class([$baseButtonClass, $inactiveButtonClass])>
                        <x-far-angle-right class="icon-20" />
                    </span>
                @endif
            @endif
        </div>
    @endif
@endif
