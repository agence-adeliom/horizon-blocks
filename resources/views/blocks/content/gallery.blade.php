@php
	use App\Blocks\Content\GalleryBlock;
	$btnClass = "awc-theme-dark cursor-pointer -translate-y-1/2 fixed top-1/2 btn--contained flex btn--icon-only btn--primary rounded-full flex-center w-10 h-10 z-20" ;
@endphp
<x-block :fields="$fields" :block="$block">
	<div class="grid-12">
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
		@if(!empty($fields[GalleryBlock::FIELD_GALLERY]))
			<div class="col-span-full grid grid-cols-1 md:grid-cols-2 gap-6 mt-10 {{ count($fields['gallery']) % 4 === 0 ? 'lg:grid-cols-4' : 'lg:grid-cols-3' }}"
			     x-data="initGallery()">
				{{-- Grille des images --}}
				@foreach ($fields[GalleryBlock::FIELD_GALLERY] as $item)
					<div class="rounded-card overflow-hidden group relative cursor-pointer"
					     @click="open = true; document.body.classList.add('overflow-hidden'); swiper.slideTo( {{ $loop->index }} )">
						<x-media.img :image="$item" class="object-cover w-full h-full absolute" size="full"
						             container-class="relative aspect-[4/3] relative w-full h-auto" />
						<div class="rounded-full bg-primary w-full absolute aspect-square scale-50 -bottom-full lg:group-hover:scale-[3] transition-all ease-linear duration-300 opacity-0 lg:group-hover:opacity-50"></div>
					</div>
				@endforeach
				{{-- Popin + slider --}}
				<div x-transition.opacity class="fixed inset-0 z-[999] flex items-center justify-center" x-cloak
				     x-show="open">
					<div class="bg-black cursor-pointer bg-opacity-50 absolute inset-0 z-0"
					     @click="open = false; document.body.classList.remove('overflow-hidden')"></div>
					<div x-ref="swiperContainer"
					     class="swiper aspect-[4/3] w-[90vw] lg:w-[800px] h-auto flex items-center justify-center">
						<x-action.button
								aria-label="{{__('Fermer la galerie')}}"
								type="primary"
								class="fixed top-10 right-10 awc-theme-dark z-[100]"
								iconOnly
								@click="open = false; $refs.body.classList.remove('overflow-hidden')"
						>
							<x-far-xmark />
						</x-action.button>
						<div class="swiper-wrapper">
							@foreach ($fields['gallery'] as $item)
								<div class="swiper-slide w-full h-full">
									<x-media.img :image="$item" class=" max-w-full max-h-full object-contain"
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
			</div>
		
		@endif
	</div>
</x-block>