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
check('reindexed after skip', array_keys($partial['mainEntity']) === [0, 1]);
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

echo empty($GLOBALS['failed']) ? "\nALL PASS\n" : "\nFAILURES\n";
exit(empty($GLOBALS['failed']) ? 0 : 1);
