@if ($display && ! empty($content))
    <div class="{{ $attributes['class'] }} main-breadcrumbs">
        {!! $content !!}
    </div>
@endif
