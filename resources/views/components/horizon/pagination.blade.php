@if($data)
    @php
        $nextLabel = $nextLabel ?? 'Suivant';
        $previousLabel = $previousLabel ?? 'Précédent';

        $displayAround = 5;
        $halfAround = ($displayAround - 1) / 2;
        $separator ='...';

        $pages = $data['pages'];
        $total = $data['total'];
        $current = $data['current'];

        $pageNumbers = [];

        for($x=1; $x<=3;$x++){
            if($x <= $pages){
                $pageNumbers[] = $x;
            }
        }

        for($x = $pages; $x >= $pages-2; $x--){
            if($x > 3){
                $pageNumbers[] = $x;
            }
        }

        $start = $current - $halfAround;
        $end = $current + $halfAround;

        if($start < 1){
            $end += abs($start) + 1;
            $start = 1;
        }

        if($end > $pages){
            $start -= $end - $pages;
            $end = $pages;
        }

		for($x=$start; $x<=$end;$x++){
            $pageNumbers[] = $x;
        }

        $pageNumbers = array_unique($pageNumbers);
        sort($pageNumbers);

        $last = null;
        $displayValues = [];

        foreach ($pageNumbers as $value) {
            if($value>0){
				if(null!==$last){
                    if($value - $last > 1){
                        $displayValues[] = $separator;
                    }
                }

                $displayValues[] = $value;
                $last = $value;
            }
        }
    @endphp

    @if(!empty($displayValues) && $pages > 1)
        @if($hasButtons)
            @if($current > 1)
                <a @if($handle) wire:click="{{ $handle }}({{ $current - 1 }})" @endif>{{ $previousLabel }}</a>
            @else
                <span>{{ $previousLabel }}</span>
            @endif
        @endif

        @foreach($displayValues as $page)
            @if($page == $current)
                <span>{{ $page }}</span>
            @elseif($page != $separator)
                <a @if($handle) wire:click="{{ $handle }}({{ $page }})" @endif>{{ $page }}</a>
            @else
                <span>{{ $separator }}</span>
            @endif
        @endforeach

        @if($hasButtons)
            @if($current < $pages)
                <a @if($handle) wire:click="{{ $handle }}({{ $current + 1 }})" @endif>{{ $nextLabel }}</a>
            @else
                <span>{{ $nextLabel }}</span>
            @endif
        @endif
    @endif
@endif