<x-block :fields="$fields">
	<div class="grid-12">
		<div class="lg:col-span-5 lg:mr-12">
			
			@isset($fields['img'])
				<x-media.img :image="$fields['img']" class="w-20 mb-3" size="thumbnail"/>
			@endisset
			
			@isset($fields['uptitle'])
				<x-typography.uptitle :content="$fields['uptitle']"/>
			@endisset
			
			@isset($fields['title'])
				<x-typography.heading :fields="$fields['title']" size="3"/>
			@endisset
			
			@isset($fields['wysiwyg'])
				<x-typography.text :content="$fields['wysiwyg']" class="pt-medium"/>
			@endisset
			@isset($fields['buttons'])
				<x-action.buttons :buttons="$fields['buttons']"
				                  class="mt-button-text-mobile lg:mt-button-text-desktop"/>
			@endisset
		</div>
		<div class="lg:col-span-7 flex flex-col gap-medium">
			@isset($fields['questions'])
				@foreach($fields['questions'] as $question)
					<x-cards.card-faq :question="$question"/>
				@endforeach
			@endisset
		</div>
	</div>
</x-block>