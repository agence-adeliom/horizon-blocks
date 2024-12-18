<?php

declare(strict_types=1);

namespace Adeliom\HorizonBlocks\View\Components\Cards;

use App\PostTypes\FAQ;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CardFaq extends Component
{
	public ?string $fullClass = null;

	/**
	 * Create a new component instance.
	 */
	public function __construct(
		public \WP_Post|array|null $question = null,
		public ?string             $title = null,
		public ?string             $answer = null,
		public ?string             $class = null,
	)
	{
		$this->handleQuestion();
		$this->initializeClasses();
	}

	private function handleQuestion(): void
	{
		// si c'est un WP_post et on fait le get_field
		// laissez la possibilité de mettre title et answer
		if ($this->question instanceof \WP_Post) {
			$this->question = [
				'question' => get_field(FAQ::FIELD_QUESTION, $this->question->ID),
				'answer' => get_field(FAQ::FIELD_ANSWER, $this->question->ID),
			];
		} else if (empty($this->question['question']) && !empty($this->title) && !empty($this->answer)) {
			$this->question['question'] = $this->title;
			$this->question['answer'] = $this->answer;
		} else if (is_array($this->question)) {
			$this->title = $this->question['title'] ?? $this->title;
			$this->answer = $this->question['answer'] ?? $this->answer;
		}

	}

	private function initializeClasses(): void
	{
		$this->fullClass = implode(' ', array_filter([
			$this->class ?? '',
		]));
	}

	/**
	 * Get the view / contents that represent the component.
	 */
	public function render(): View|Closure|string
	{
		return view('components.cards.card-faq');
	}
}