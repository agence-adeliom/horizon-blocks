<?php

declare(strict_types=1);

namespace Adeliom\HorizonBlocks\Blocks\Content;

use Adeliom\HorizonTools\Blocks\AbstractBlock;
use Adeliom\HorizonTools\Fields\Buttons\ButtonField;
use Adeliom\HorizonTools\Fields\Layout\LayoutField;
use Adeliom\HorizonTools\Fields\Tabs\ContentTab;
use Adeliom\HorizonTools\Fields\Tabs\LayoutTab;
use Adeliom\HorizonTools\Fields\Text\HeadingField;
use Adeliom\HorizonTools\Fields\Text\UptitleField;
use Adeliom\HorizonTools\Fields\Text\WysiwygField;
use Adeliom\HorizonTools\Services\SeoService;
use App\PostTypes\FAQ;
use Extended\ACF\Fields\Image;
use Extended\ACF\Fields\Relationship;

class FaqBlock extends AbstractBlock
{
	public static ?string $slug = 'faq';
	public static ?string $title = 'FAQ';
	public static ?string $description = "Présente des questions souvent posées par les utilisateurices, ainsi que des réponses rapides.";
	public static ?string $icon = 'editor-help';

	public const string FIELDS_IMG = 'img';
	public const string FIELDS_QUESTIONS = 'questions';

	public function getFields(): ?iterable
	{
		yield from ContentTab::make()->fields([
			Image::make(__('Petite image'), self::FIELDS_IMG),
			UptitleField::make(),
			HeadingField::make(HeadingField::LABEL, HeadingField::NAME, null, 'h2')->required(),
			WysiwygField::minimal(),
			ButtonField::group(),
			Relationship::make(__('Liste de questions'), self::FIELDS_QUESTIONS)
				->minPosts(2)
				->maxPosts(5)
				->postTypes([FAQ::$slug]),

		]);

		yield from LayoutTab::make()->fields([
			LayoutField::margin(),
		]);
	}

	private function getStructuredData(string $question, string $answer, ?string $publicationDate = null): array
	{
		$siteName = get_bloginfo('name');

		$answerData = [
			"@context" => "https://schema.org",
			"@type" => "Answer",
			"text" => strip_tags($answer),
			"author" => [
				"@type" => "Organization",
				"name" => $siteName
			],
		];

		if ($publicationDate) {
			$answerData['datePublished'] = $publicationDate;
		}

		return [
			"@context" => "https://schema.org",
			"@type" => "Question",
			"name" => $question,
			"acceptedAnswer" => $answerData,
		];
	}

	public function addToContext(): array
	{
		$fields = get_fields();
		$structuredData = [];

		if (SeoService::isCurrentPageIndexed() && !empty($fields[self::FIELDS_QUESTIONS])) {
			foreach ($fields[self::FIELDS_QUESTIONS] as $questionPost) {
				if ($questionFields = get_fields($questionPost)) {
					if (!empty($questionFields[FAQ::FIELD_QUESTION]) && !empty($questionFields[FAQ::FIELD_ANSWER])) {
						$question = $questionFields[FAQ::FIELD_QUESTION];
						$answer = $questionFields[FAQ::FIELD_ANSWER];

						$structuredData[] = $this->getStructuredData($question, $answer, publicationDate: $questionPost->post_date);
					}
				}
			}
		}

		return [
			'structuredData' => $structuredData,
		];
	}

	public function renderBlockCallback(): void
	{
		return;
	}
}
