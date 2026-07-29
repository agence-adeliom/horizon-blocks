<?php

declare(strict_types=1);

require __DIR__ . '/../src/Services/FaqSchemaBuilder.php';

use Adeliom\HorizonBlocks\Services\FaqSchemaBuilder;

$failed = false;
function check(string $label, bool $ok): void
{
	echo ($ok ? "PASS" : "FAIL") . " - {$label}\n";
	if (!$ok) {
		$GLOBALS['failed'] = true;
	}
}

// Aucune ligne exploitable -> null (jamais un FAQPage sans mainEntity).
check('no items = null', FaqSchemaBuilder::build([]) === null);
check('blank title = null', FaqSchemaBuilder::build([['title' => '  ', 'content' => 'Réponse']]) === null);
check('blank content = null', FaqSchemaBuilder::build([['title' => 'Question ?', 'content' => '']]) === null);
check('missing keys = null', FaqSchemaBuilder::build([[]]) === null);

// Cas nominal : un seul noeud FAQPage, @context une seule fois.
$schema = FaqSchemaBuilder::build([
	['title' => 'Livrez-vous en Belgique ?', 'content' => '<p>Oui, sous <strong>48h</strong>.</p>'],
	['title' => 'Puis-je payer en plusieurs fois ?', 'content' => '<p>Oui, en 3 fois.</p>'],
]);

check('root is FAQPage', $schema['@type'] === 'FAQPage');
check('root has @context', $schema['@context'] === 'https://schema.org');
check('two questions', count($schema['mainEntity']) === 2);
check('question type', $schema['mainEntity'][0]['@type'] === 'Question');
check('question name', $schema['mainEntity'][0]['name'] === 'Livrez-vous en Belgique ?');
check('answer type', $schema['mainEntity'][0]['acceptedAnswer']['@type'] === 'Answer');
check('answer html stripped', $schema['mainEntity'][0]['acceptedAnswer']['text'] === 'Oui, sous 48h.');
check('no @context inside question', !isset($schema['mainEntity'][0]['@context']));
check('mainEntity is a list', array_keys($schema['mainEntity']) === [0, 1]);

// Les lignes incomplètes sont ignorées sans décaler les index.
$partial = FaqSchemaBuilder::build([
	['title' => 'Gardée', 'content' => 'Oui'],
	['title' => '', 'content' => 'Ignorée'],
	['title' => 'Gardée aussi', 'content' => 'Oui'],
]);
check('incomplete rows skipped', count($partial['mainEntity']) === 2);
check('first kept row is the first input', $partial['mainEntity'][0]['name'] === 'Gardée');
check('second kept row is the third input', $partial['mainEntity'][1]['name'] === 'Gardée aussi');

// siteName absent -> pas d'author ; présent -> author Organization.
$noAuthor = FaqSchemaBuilder::build([['title' => 'Q ?', 'content' => 'R']]);
check('no author without siteName', !isset($noAuthor['mainEntity'][0]['acceptedAnswer']['author']));

$withAuthor = FaqSchemaBuilder::build([['title' => 'Q ?', 'content' => 'R']], 'WOAH');
$author = $withAuthor['mainEntity'][0]['acceptedAnswer']['author'];
check('author type', $author['@type'] === 'Organization');
check('author name', $author['name'] === 'WOAH');

// Le contenu des balises script/style ne doit pas fuiter dans le texte.
$scripted = FaqSchemaBuilder::build([
	['title' => 'Q ?', 'content' => '<p>Visible</p><script>alert("x")</script>'],
]);
check('script content removed', $scripted['mainEntity'][0]['acceptedAnswer']['text'] === 'Visible');

// Cas limite assumé : une balise <script> non refermée n'est pas retirée par la
// regexp (qui exige </script>) ; strip_tags()/wp_strip_all_tags() avalent alors
// le contenu comme du texte. On documente le résultat réel plutôt que de laisser
// croire que le nettoyage est parfait.
$unterminated = FaqSchemaBuilder::build([
	['title' => 'Q ?', 'content' => '<p>V</p><script>alert(1)'],
]);
check(
	'unterminated script tag is not stripped (documented limitation)',
	$unterminated['mainEntity'][0]['acceptedAnswer']['text'] === 'V alert(1)'
);

// --- Finding 1 : toPlainText() ne doit plus fusionner les phrases ni laisser des
// entités HTML non décodées dans le JSON-LD.
$multiParagraph = FaqSchemaBuilder::build([
	['title' => 'Q ?', 'content' => '<p>Oui, sous 48h.</p><p>Et gratuitement.</p>'],
]);
check(
	'multi-paragraph content gets a space between blocks',
	$multiParagraph['mainEntity'][0]['acceptedAnswer']['text'] === 'Oui, sous 48h. Et gratuitement.'
);

$listContent = FaqSchemaBuilder::build([
	['title' => 'Q ?', 'content' => '<ul><li>A</li><li>B</li></ul>'],
]);
check(
	'list items get a space between them',
	$listContent['mainEntity'][0]['acceptedAnswer']['text'] === 'A B'
);

$brContent = FaqSchemaBuilder::build([
	['title' => 'Q ?', 'content' => 'Ligne 1<br>Ligne 2'],
]);
check(
	'<br> becomes a space',
	$brContent['mainEntity'][0]['acceptedAnswer']['text'] === 'Ligne 1 Ligne 2'
);

// &nbsp; décode en U+00A0, que le collapse /\s+/u transforme ensuite en espace normal (U+0020).
$entities = FaqSchemaBuilder::build([
	['title' => 'Q ?', 'content' => '<p>Tarifs&nbsp;: 10&nbsp;&euro; &amp; plus</p>'],
]);
check(
	'HTML entities are decoded',
	$entities['mainEntity'][0]['acceptedAnswer']['text'] === 'Tarifs : 10 € & plus'
);

// --- Finding 4 : name doit passer par toPlainText() comme le texte de la réponse.
$htmlTitle = FaqSchemaBuilder::build([
	['title' => '<strong>Titre</strong>', 'content' => 'ok'],
]);
check('HTML in title is stripped from name', $htmlTitle['mainEntity'][0]['name'] === 'Titre');

// --- Finding 9 : un titre non-string ne doit ni lever de warning ni produire "Array".
$nonStringTitle = FaqSchemaBuilder::build([
	['title' => ['a'], 'content' => 'ok'],
]);
check('non-string title is skipped without warning', $nonStringTitle === null);

// --- Branche WordPress -------------------------------------------------------
// Jusqu'ici wp_strip_all_tags() n'existait pas, donc seule la branche de repli a
// tourné. On la définit maintenant pour prouver que la branche réellement utilisée
// en production est bien câblée et donne le même résultat sur les mêmes fixtures.
if (!function_exists('wp_strip_all_tags')) {
	function wp_strip_all_tags(string $text, bool $remove_breaks = false): string
	{
		$text = preg_replace('#<(script|style)\b[^>]*>.*?</\1>#is', '', $text) ?? $text;
		$text = strip_tags($text);

		return $remove_breaks ? trim(preg_replace('/[\r\n\t ]+/', ' ', $text) ?? $text) : trim($text);
	}
}

check('wp branch is now active', function_exists('wp_strip_all_tags'));

$multiParagraphWp = FaqSchemaBuilder::build([
	['title' => 'Q ?', 'content' => '<p>Oui, sous 48h.</p><p>Et gratuitement.</p>'],
]);
check(
	'multi-paragraph content gets a space between blocks (wp)',
	$multiParagraphWp['mainEntity'][0]['acceptedAnswer']['text'] === 'Oui, sous 48h. Et gratuitement.'
);

$entitiesWp = FaqSchemaBuilder::build([
	['title' => 'Q ?', 'content' => '<p>Tarifs&nbsp;: 10&nbsp;&euro; &amp; plus</p>'],
]);
check(
	'HTML entities are decoded (wp)',
	$entitiesWp['mainEntity'][0]['acceptedAnswer']['text'] === 'Tarifs : 10 € & plus'
);

$scriptedWp = FaqSchemaBuilder::build([
	['title' => 'Q ?', 'content' => '<p>Visible</p><script>alert("x")</script>'],
]);
check('script content removed (wp)', $scriptedWp['mainEntity'][0]['acceptedAnswer']['text'] === 'Visible');

$nominalWp = FaqSchemaBuilder::build([
	['title' => 'Livrez-vous en Belgique ?', 'content' => '<p>Oui, sous <strong>48h</strong>.</p>'],
], 'WOAH');
check('nominal FAQPage build (wp)', $nominalWp['@type'] === 'FAQPage'
	&& $nominalWp['mainEntity'][0]['name'] === 'Livrez-vous en Belgique ?'
	&& $nominalWp['mainEntity'][0]['acceptedAnswer']['text'] === 'Oui, sous 48h.'
	&& $nominalWp['mainEntity'][0]['acceptedAnswer']['author']['name'] === 'WOAH');

echo empty($GLOBALS['failed']) ? "\nALL PASS\n" : "\nFAILURES\n";
exit(empty($GLOBALS['failed']) ? 0 : 1);
