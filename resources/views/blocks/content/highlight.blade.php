<x-block :fields="$fields" :block="$block">
	<div class="grid grid-cols-12 bg-neutral-100">
		<div class="col-span-full lg:col-span-8 xl:col-span-7 p-6 lg:p-10">
			@if(!empty($fields['uptitle']))
				<x-typography.uptitle :content="$fields['uptitle']" />
			@endif
			
			@if(!empty($fields['title']))
				<x-typography.heading :fields="$fields['title']" size="4" />
			@endif
			
			@if(!empty($fields['wysiwyg']))
				<x-typography.text :content="$fields['wysiwyg']" :class="!empty($fields['title']) ? 'mt-4' : ''" />
			@endif
			
			@if(!empty($fields['buttons']))
				<x-action.buttons :buttons="$fields['buttons']"
				                  class="mt-button-text-mobile lg:mt-button-text-desktop" />
			@endif
		</div>
		<div class="col-span-full lg:col-span-4 xl:col-span-5 relative max-lg:aspect-[4/3]">
			<x-media.img :image="$fields['main_image']" class="cover-full" size="full" container-class="w-full" />
		</div>
	</div>
</x-block>