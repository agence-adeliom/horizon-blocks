<?php

declare(strict_types=1);

namespace Adeliom\HorizonBlocks\Services;

/**
 * Construit le balisage schema.org FAQPage du bloc Accordéon.
 *
 * Volontairement sans dépendance : ni WordPress, ni horizon-tools. C'est ce qui
 * permet de la tester via un simple `require` (cf. tests/AccordionSchemaTest.php).
 */
final class FaqSchemaBuilder
{
	/**
	 * @param array<int, array{title?: string|null, content?: string|null}> $items
	 * @return array<string, mixed>|null null si aucune ligne exploitable.
	 */
	public static function build(array $items, ?string $siteName = null): ?array
	{
		$questions = [];

		foreach ($items as $item) {
			$title = trim((string)($item['title'] ?? ''));
			$content = self::toPlainText((string)($item['content'] ?? ''));

			if ('' === $title || '' === $content) {
				continue;
			}

			$answer = [
				'@type' => 'Answer',
				'text' => $content,
			];

			if (null !== $siteName && '' !== $siteName) {
				$answer['author'] = [
					'@type' => 'Organization',
					'name' => $siteName,
				];
			}

			$questions[] = [
				'@type' => 'Question',
				'name' => $title,
				'acceptedAnswer' => $answer,
			];
		}

		if ([] === $questions) {
			return null;
		}

		return [
			'@context' => 'https://schema.org',
			'@type' => 'FAQPage',
			'mainEntity' => $questions,
		];
	}

	/**
	 * wp_strip_all_tags() retire le contenu des balises script/style, ce que
	 * strip_tags() laisse passer. On garde un repli pour rester testable hors WordPress.
	 */
	private static function toPlainText(string $html): string
	{
		if (function_exists('wp_strip_all_tags')) {
			return trim(wp_strip_all_tags($html));
		}

		return trim(strip_tags(preg_replace('#<(script|style)\b[^>]*>.*?</\1>#is', '', $html) ?? $html));
	}
}
