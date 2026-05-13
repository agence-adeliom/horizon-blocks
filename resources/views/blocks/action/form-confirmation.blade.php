@php
	use Adeliom\HorizonTools\Fields\Buttons\ButtonField;
	use Adeliom\HorizonTools\Fields\Text\HeadingField;
	use Adeliom\HorizonTools\Fields\Text\WysiwygField;
@endphp
<x-block :fields="$fields" :block="$block">
	<div class="flex flex-col items-center max-w-3xl m-auto">

		@if(!empty($fields['icon']))
			<div class="text-5xl lg:text-[80px] text-primary font-light">
				{!! $fields['icon'] !!}
			</div>
		@endif

		@if(!empty($fields[HeadingField::NAME]))
			<x-typography.heading :fields="$fields[HeadingField::NAME]" size="3" class="mt-8"/>
		@endif

		@if(!empty($fields[WysiwygField::WYSIWYG]))
			<x-typography.text :content="$fields[WysiwygField::WYSIWYG]" class="pt-medium text-center"/>
		@endif

		@if(!empty($fields[ButtonField::BUTTONS]))
			<x-action.buttons :buttons="$fields[ButtonField::BUTTONS]" class="mt-button-text-mobile lg:mt-button-text-desktop"/>
		@endif
	</div>
</x-block>