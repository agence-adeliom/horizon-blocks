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
			$title = self::toPlainText(is_string($item['title'] ?? null) ? $item['title'] : '');
			$content = self::toPlainText(is_string($item['content'] ?? null) ? $item['content'] : '');

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
	 * Normalise du HTML de wysiwyg en texte brut exploitable par schema.org.
	 *
	 * Les deux branches partagent le même pré- et post-traitement : ni strip_tags()
	 * ni wp_strip_all_tags() n'insèrent d'espace aux frontières de bloc, et aucune
	 * des deux ne décode les entités — or le JSON-LD n'est pas du HTML.
	 */
	private static function toPlainText(string $html): string
	{
		$html = preg_replace('#<br\s*/?>#i', ' ', $html) ?? $html;
		$html = preg_replace('#</(p|div|li|h[1-6]|tr|blockquote)>#i', '$0 ', $html) ?? $html;

		if (function_exists('wp_strip_all_tags')) {
			$text = wp_strip_all_tags($html);
		} else {
			$text = strip_tags(preg_replace('#<(script|style)\b[^>]*>.*?</\1>#is', '', $html) ?? $html);
		}

		$text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

		return trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
	}
}
