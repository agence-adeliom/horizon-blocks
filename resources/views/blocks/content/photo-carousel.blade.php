@php
	use App\Blocks\Content\PhotoCarouselBlock;
	$btnClass = "awc-theme-dark cursor-pointer -translate-y-1/2 fixed top-1/2 btn--contained flex btn--icon-only btn--primary rounded-full flex-center w-10 h-10 z-20";
	$arrowClass = "awc-theme-dark cursor-pointer btn--contained flex btn--icon-only btn--primary rounded-full flex-center w-12 h-12";
	$gallery = $fields[PhotoCarouselBlock::FIELD_GALLERY] ?? [];
@endphp
<x-block :fields="$fields" :block="$block">
	<div class="grid-12" x-data="initPhotoCarousel()">
		<div class="lg:col-start-3 lg:col-span-8 text-center">
			@if(!empty($fields['uptitle']))
				<x-typography.uptitle :content="$fields['uptitle']" />
			@endif

			@if(!empty($fields['title']))
				<x-typography.heading :fields="$fields['title']" size="3" />
			@endif

			@if(!empty($fields['wysiwyg']))
				<x-typography.text :content="$fields['wysiwyg']" />
			@endif

			@if(!empty($fields['buttons']['one']['link']) || !empty($fields['buttons']['two']['link']))
				<x-action.buttons :buttons="$fields['buttons']"
				                  class="w-fit mx-auto mt-button-text-mobile lg:mt-button-text-desktop" />
			@endif
		</div>

		@if(!empty($gallery))
			<div class="lg:col-start-3 lg:col-span-8 col-span-full mt-10">
				<div x-ref="horizontalContainer" class="swiper">
					<div class="swiper-wrapper">
						@foreach ($gallery as $item)
							<div class="swiper-slide rounded-card overflow-hidden cursor-pointer"
							     @click="open = true; document.body.classList.add('overflow-hidden'); swiper.slideTo( {{ $loop->index }} )">
								<x-media.img :image="$item" class="object-cover w-full h-full absolute"
								             size="full"
								             container-class="relative aspect-[16/9] w-full h-auto" />
							</div>
						@endforeach
					</div>
				</div>

				@if (collect($gallery)->contains(fn($item) => !empty($item['caption'])))
					<div class="mt-2 text-sm italic swiper-caption-container min-h-[1.5em]" x-ref="captionContainer">
						@foreach ($gallery as $item)
							<p class="swiper-caption {{ $loop->first ? '' : 'hidden' }}"
							   data-caption-index="{{ $loop->index }}">
								{{ $item['caption'] ?? '' }}
							</p>
						@endforeach
					</div>
				@endif

				<div class="flex items-center justify-between gap-4 mt-4">
					<div x-ref="progressBar"
					     class="h-1 bg-neutral-300 rounded overflow-hidden flex-1 relative"></div>
					<div class="flex gap-2 shrink-0">
						<div class="{{ $arrowClass }}" x-ref="horizontalPrev" role="button"
						     aria-label="{{ __('Précédent') }}">
							<x-far-chevron-left class="icon-5" />
						</div>
						<div class="{{ $arrowClass }}" x-ref="horizontalNext" role="button"
						     aria-label="{{ __('Suivant') }}">
							<x-far-chevron-right class="icon-5" />
						</div>
					</div>
				</div>
			</div>

			{{-- Lightbox plein écran --}}
			<div x-transition.opacity class="fixed inset-0 z-[999] flex items-center justify-center" x-cloak
			     x-show="open">
				<div class="bg-black cursor-pointer bg-opacity-50 absolute inset-0 z-0"
				     @click="open = false; document.body.classList.remove('overflow-hidden')"></div>
				<div x-ref="swiperContainer"
				     class="swiper aspect-[16/9] w-[90vw] lg:w-[1000px] h-auto flex items-center justify-center">
					<x-action.button
							aria-label="{{__('Fermer le carrousel')}}"
							type="primary"
							class="fixed top-10 right-10 awc-theme-dark z-[100]"
							iconOnly
							@click="open = false; document.body.classList.remove('overflow-hidden')"
					>
						<x-far-xmark />
					</x-action.button>
					<div class="swiper-wrapper">
						@foreach ($gallery as $item)
							<div class="swiper-slide w-full h-full">
								<x-media.img :image="$item" class="max-w-full max-h-full object-contain"
								             size="full"
								             container-class="relative w-full h-full" />
							</div>
						@endforeach
					</div>
					<div class="left-10 {{ $btnClass }}" x-ref="buttonPrev">
						<x-far-chevron-left class="icon-5" />
					</div>
					<div class="right-10 {{ $btnClass }}" x-ref="buttonNext">
						<x-far-chevron-right class="icon-5" />
					</div>
				</div>
			</div>
		@endif
	</div>
</x-block>
