<x-block :fields="$fields" :block="$block">
	<div class="flex flex-col items-center max-w-3xl m-auto">
		
		@if(!empty($fields['icon']))
			<div class="text-5xl lg:text-[80px] text-primary font-light">
				{!! $fields['icon'] !!}
			</div>
		@endif
		
		@if(!empty($fields['title']))
			<x-typography.heading :fields="$fields['title']" size="3" class="mt-8"/>
		@endif
		
		@if(!empty($fields['wysiwyg']))
			<x-typography.text :content="$fields['wysiwyg']" class="pt-medium text-center"/>
		@endif
		
		@if(!empty($fields['buttons']))
			<x-action.buttons :buttons="$fields['buttons']" class="mt-button-text-mobile lg:mt-button-text-desktop"/>
		@endif
	</div>
</x-block>