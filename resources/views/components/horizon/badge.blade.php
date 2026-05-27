@props([
	'type' => 'neutral',
	'size' => 'md',
	'href' => null,
])

@php
	$base = 'inline-flex items-center gap-1 rounded-pill font-medium leading-none whitespace-nowrap';

	$sizes = [
		'sm' => 'px-2 py-1 text-xs',
		'md' => 'px-3 py-1.5 text-sm',
	];

	$types = [
		'neutral'   => 'bg-neutral-100 text-neutral-1000',
		'primary'   => 'bg-primary text-white',
		'secondary' => 'bg-secondary text-white',
		'success'   => 'bg-green-100 text-green-800',
		'warning'   => 'bg-yellow-100 text-yellow-800',
		'error'     => 'bg-red-100 text-red-800',
	];

	$sizeClass = $sizes[$size] ?? $sizes['md'];
	$typeClass = $types[$type] ?? $types['neutral'];
@endphp

@if($href)
	<a href="{{ $href }}" {{ $attributes->class([$base, $sizeClass, $typeClass]) }}>
		{{ $slot }}
	</a>
@else
	<span {{ $attributes->class([$base, $sizeClass, $typeClass]) }}>
		{{ $slot }}
	</span>
@endif
