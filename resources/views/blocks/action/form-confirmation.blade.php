<x-block :fields="$fields">
	<div class="flex flex-col items-center max-w-3xl m-auto">
		
		@isset($fields['icon'])
			<div class="text-5xl lg:text-[80px] text-primary font-light">
				{!! $fields['icon'] !!}
			</div>
		@endisset
		
		@isset($fields['title'])
			<x-typography.heading :fields="$fields['title']" size="3" class="mt-8"/>
		@endisset
		
		@isset($fields['wysiwyg'])
			<x-typography.text :content="$fields['wysiwyg']" class="pt-medium text-center"/>
		@endisset
		
		@isset($fields['buttons'])
			<x-action.buttons :buttons="$fields['buttons']" class="mt-button-text-mobile lg:mt-button-text-desktop"/>
		@endisset
	</div>
</x-block>