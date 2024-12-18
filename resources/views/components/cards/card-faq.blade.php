<div x-data="{ open: false }" class="faq-item rounded-card bg-color-03-50 p-medium">
	<div @click="open = !open" class="flex justify-between items-center cursor-pointer">
		@if(isset($question['question']) || isset($title))
			<x-typography.text :content="$question['question'] ?? $title" class="font-semibold transition-all "
			                   x-bind:class="{ 'text-primary': open }"/>
		@endif
		<svg :class="{ 'rotate-180': open }" class="transform transition-transform duration-300 w-5 h-5"
		     fill="none" stroke="currentColor" viewBox="0 0 24 24">
			<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
		</svg>
	</div>
	<div x-show="open" x-collapse class="pt-medium">
		@if(isset($question['answer']) || isset($answer))
			<x-typography.text :content="$question['answer'] ?? $answer"/>
		@endif
	</div>
</div>