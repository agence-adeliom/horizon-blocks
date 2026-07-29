# Bloc FAQ → bloc Accordéon — Plan d'implémentation

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Remplacer le bloc FAQ de `horizon-blocks` par un bloc Accordéon autonome, dont les paires titre/contenu sont saisies dans le bloc, sans dépendance au CPT FAQ, avec un balisage schema.org optionnel et un markup accessible.

**Architecture:** La logique de construction du `FAQPage` est extraite dans une classe pure `FaqSchemaBuilder` (aucune dépendance WordPress ni horizon-tools), seule partie testable dans ce repo. `AccordionBlock` déclare les champs ACF et délègue le balisage au builder. `CardAccordion` reçoit des props plates (`title`, `content`) au lieu d'un `WP_Post`, et son blade implémente le motif accordéon accessible.

**Tech Stack:** PHP 8.4, ACF via `extended-acf`, helpers de champs `horizon-tools`, Blade (Sage 11 / Acorn), Alpine.js (`x-collapse`), Tailwind.

## Global Constraints

- **Branche de travail : `sage/11`.** Ne pas committer sur `main`.
- **Périmètre strictement limité à ce repo.** Ne rien modifier dans `horizon-posttypes` : le CPT FAQ y reste intact.
- **Aucune dépendance de dev.** `composer.json` ne déclare ni PHPUnit ni Pest. Les tests sont des **scripts PHP autonomes** sur le modèle de `tests/ListingPeriodTest.php` : `require` direct du fichier source, helper `check()`, `exit(0|1)`. Lancement : `php tests/<Nom>Test.php`. Ne pas introduire de dépendance de dev pour ce chantier.
- **`vendor/` est installé** (`composer install` passé au démarrage). Les signatures d'API utilisées dans ce plan ont été vérifiées dans `vendor/` : les prendre telles quelles, sans repartir en exploration.
- **Noms de champs ACF en camelCase** (`argTitle`, `bgColor`, `priceSuffix`…), conformément au commit `094961a`.
- **Toutes les chaînes visibles passent par `__('…', 'horizon-blocks')`.**
- **Tout bloc déclare un `$icon` et une `getDescription()`** (commit `74ea5f9`).
- **Indentation : tabulations** dans les fichiers `src/**.php` (convention des blocs existants), espaces dans les Blade.
- **`declare(strict_types=1);`** en tête de chaque fichier PHP.
- **schema.org désactivé par défaut** (`->default(false)`).
- **Ne pas toucher** à `resources/views/blocks/action/navbar.blade.php` : une modification locale non commitée, étrangère à ce chantier, s'y trouve.

## Structure des fichiers

| Fichier | Responsabilité |
|---|---|
| `src/Services/FaqSchemaBuilder.php` (créé) | Construit le nœud `FAQPage` à partir de lignes brutes. Pur : aucun `use`, aucun appel WordPress obligatoire. Seule unité testable. |
| `tests/AccordionSchemaTest.php` (créé) | Couvre `FaqSchemaBuilder::build()`. |
| `src/View/Components/Cards/CardAccordion.php` (créé) | Composant Blade : props plates `title`, `content`, `class`. |
| `resources/views/components/cards/card-accordion.blade.php` (créé) | Markup accordéon accessible (bouton, `aria-*`, `role="region"`). |
| `src/Blocks/Content/AccordionBlock.php` (créé) | Champs ACF + `addToContext()` déléguant au builder. |
| `resources/views/blocks/content/accordion.blade.php` (créé) | Vue du bloc : en-tête + boucle sur le repeater. |
| `src/Services/HorizonBlockService.php` (modifié) | Remplace l'entrée `FaqBlock` par `AccordionBlock`, retire `REQUIRED_POSTTYPES`. |
| `src/View/Components/Cards/CardStep.php` (modifié) | Retrait d'un `use` mort. |
| `FaqBlock.php`, `CardFaq.php`, `faq.blade.php`, `card-faq.blade.php` (supprimés) | Remplacés. |
| `images/admin/blocks/content/faq.jpg` → `accordion.jpg` | L'image d'admin suit le slug du bloc. |
| `images/admin/blocks/listing/faq-listing.jpg` → `accordion-listing.jpg` | Renommage de cohérence (fichier orphelin conservé). |

**Ordre imposé par les dépendances :** builder → composant → bloc → vue → recâblage du service → suppressions. Supprimer `FaqBlock` avant d'avoir recâblé `HorizonBlockService` casserait l'autoload du service.

---

### Task 1: `FaqSchemaBuilder` — construction du FAQPage

**Files:**
- Create: `src/Services/FaqSchemaBuilder.php`
- Test: `tests/AccordionSchemaTest.php`

**Interfaces:**
- Consumes: rien (premier task).
- Produces: `Adeliom\HorizonBlocks\Services\FaqSchemaBuilder::build(array $items, ?string $siteName = null): ?array`
  - `$items` : liste de `['title' => ?string, 'content' => ?string]`. `content` peut contenir du HTML.
  - Retour : `null`, ou `['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => [...]]`.

- [ ] **Step 1: Écrire le test qui échoue**

Créer `tests/AccordionSchemaTest.php` :

```php
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
```

- [ ] **Step 2: Lancer le test pour vérifier qu'il échoue**

Run: `php tests/AccordionSchemaTest.php`
Expected: échec immédiat — `Failed to open stream: No such file or directory` sur le `require` de `src/Services/FaqSchemaBuilder.php`.

- [ ] **Step 3: Écrire l'implémentation minimale**

Créer `src/Services/FaqSchemaBuilder.php` :

```php
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
```

- [ ] **Step 4: Lancer le test pour vérifier qu'il passe**

Run: `php tests/AccordionSchemaTest.php`
Expected: toutes les lignes `PASS`, puis `ALL PASS`, code de sortie 0.

- [ ] **Step 5: Committer**

```bash
git add src/Services/FaqSchemaBuilder.php tests/AccordionSchemaTest.php
git commit -m "feat(accordion): add pure FAQPage schema builder"
```

---

### Task 2: Composant `CardAccordion` et son markup accessible

**Files:**
- Create: `src/View/Components/Cards/CardAccordion.php`
- Create: `resources/views/components/cards/card-accordion.blade.php`

**Interfaces:**
- Consumes: rien.
- Produces: `<x-cards.card-accordion :title="string" :content="string" class="?string" />`. Le composant n'accepte **pas** de `WP_Post` — c'est la rupture volontaire avec `CardFaq`.

- [ ] **Step 1: Créer la classe du composant**

Créer `src/View/Components/Cards/CardAccordion.php` :

```php
<?php

declare(strict_types=1);

namespace Adeliom\HorizonBlocks\View\Components\Cards;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CardAccordion extends Component
{
	public function __construct(
		public ?string $title = null,
		public ?string $content = null,
	)
	{
	}

	/**
	 * Get the view / contents that represent the component.
	 */
	public function render(): View|Closure|string
	{
		return view('components.cards.card-accordion');
	}
}
```

- [ ] **Step 2: Vérifier la syntaxe PHP**

Run: `php -l src/View/Components/Cards/CardAccordion.php`
Expected: `No syntax errors detected`

- [ ] **Step 3: Créer le blade accessible**

Créer `resources/views/components/cards/card-accordion.blade.php` :

```blade
@if (!empty($title) && !empty($content))
    @php($uid = wp_unique_id('accordion-'))

    <div x-data="{ open: false }" @class([
        'accordion-item',
        'rounded-card bg-color-03-50 p-medium',
        $attributes['class'],
    ])>
        <h3>
            <button type="button" id="{{ $uid }}-btn" aria-controls="{{ $uid }}-panel"
                :aria-expanded="open" @click="open = !open"
                class="flex justify-between items-center gap-medium w-full text-left cursor-pointer">
                <x-typography.text :content="$title" class="font-semibold transition-all"
                    x-bind:class="{ 'text-primary': open }" />
                <svg aria-hidden="true" :class="{ 'rotate-180': open }"
                    class="shrink-0 transform transition-transform duration-300 w-5 h-5" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
        </h3>
        <div id="{{ $uid }}-panel" role="region" aria-labelledby="{{ $uid }}-btn" x-show="open" x-collapse
            class="pt-medium">
            <x-typography.text :content="$content" />
        </div>
    </div>
@endif
```

Notes pour l'implémenteur :
- `wp_unique_id()` (WordPress ≥ 5.0) garantit des `id` uniques si plusieurs accordéons cohabitent sur une page.
- `shrink-0` sur le SVG : sans lui, le chevron s'écrase quand le titre est long, le bouton étant en `flex`.
- Le `<h3>` est en dur : il doit rester cohérent avec le `<h2>` du bloc et stable pour `aria-labelledby`. C'est un choix structurel, pas éditorial.

- [ ] **Step 4: Vérifier la présence des attributs d'accessibilité**

`php -l` ne s'applique pas aux Blade (les directives ne sont pas du PHP valide). Vérification structurelle :

```bash
for attr in 'type="button"' 'aria-controls' ':aria-expanded' 'role="region"' 'aria-labelledby' 'aria-hidden="true"'; do
  grep -q "$attr" resources/views/components/cards/card-accordion.blade.php \
    && echo "PASS - $attr" || echo "FAIL - $attr"
done
```
Expected: six lignes `PASS`.

- [ ] **Step 5: Committer**

```bash
git add src/View/Components/Cards/CardAccordion.php resources/views/components/cards/card-accordion.blade.php
git commit -m "feat(accordion): add accessible card-accordion component"
```

---

### Task 3: Classe `AccordionBlock`

**Files:**
- Create: `src/Blocks/Content/AccordionBlock.php`

**Interfaces:**
- Consumes: `FaqSchemaBuilder::build(array $items, ?string $siteName = null): ?array` (Task 1).
- Produces:
  - `AccordionBlock::$slug === 'accordion'`
  - Constantes `FIELD_IMG = 'img'`, `FIELD_ITEMS = 'items'`, `FIELD_ITEM_TITLE = 'itemTitle'`, `FIELD_SCHEMA_ORG = 'schemaOrg'`
  - `addToContext(): array` renvoyant `['structuredData' => ?array]`

- [ ] **Step 1: Créer la classe**

Créer `src/Blocks/Content/AccordionBlock.php` :

```php
<?php

declare(strict_types=1);

namespace Adeliom\HorizonBlocks\Blocks\Content;

use Adeliom\HorizonBlocks\Services\FaqSchemaBuilder;
use Adeliom\HorizonTools\Blocks\AbstractBlock;
use Adeliom\HorizonTools\Fields\Buttons\ButtonField;
use Adeliom\HorizonTools\Fields\Choices\TrueFalseField;
use Adeliom\HorizonTools\Fields\Layout\LayoutField;
use Adeliom\HorizonTools\Fields\Tabs\ContentTab;
use Adeliom\HorizonTools\Fields\Tabs\LayoutTab;
use Adeliom\HorizonTools\Fields\Text\HeadingField;
use Adeliom\HorizonTools\Fields\Text\UptitleField;
use Adeliom\HorizonTools\Fields\Text\WysiwygField;
use Adeliom\HorizonTools\Services\SeoService;
use Extended\ACF\Fields\Image;
use Extended\ACF\Fields\Repeater;
use Extended\ACF\Fields\Text;

class AccordionBlock extends AbstractBlock
{
	public static ?string $slug = 'accordion';
	public static ?string $icon = 'list-view';

	public static function getTitle(): ?string
	{
		return __('Accordéon', 'horizon-blocks');
	}

	public static function getDescription(): ?string
	{
		return __("Présente une liste d'éléments repliables, chacun dépliant son contenu au clic.", 'horizon-blocks');
	}

	public const string FIELD_IMG = 'img';
	public const string FIELD_ITEMS = 'items';
	public const string FIELD_ITEM_TITLE = 'itemTitle';
	public const string FIELD_ITEM_CONTENT = 'itemContent';
	public const string FIELD_SCHEMA_ORG = 'schemaOrg';

	public function getFields(): ?iterable
	{
		yield from ContentTab::make()->fields([
			Image::make(__('Petite image', 'horizon-blocks'), self::FIELD_IMG),
			UptitleField::make(),
			HeadingField::make(HeadingField::LABEL, HeadingField::NAME, null, 'h2')->required(),
			WysiwygField::minimal(),
			ButtonField::group(),
			Repeater::make(__('Éléments', 'horizon-blocks'), self::FIELD_ITEMS)
				->fields([
					Text::make(__('Titre', 'horizon-blocks'), self::FIELD_ITEM_TITLE)
						->maxLength(150)
						->helperText(__('Maximum 150 caractères', 'horizon-blocks'))
						->required(),
					WysiwygField::minimal(__('Contenu', 'horizon-blocks'), self::FIELD_ITEM_CONTENT),
				])
				->collapsed(self::FIELD_ITEM_TITLE)
				->minRows(2)
				->button(__('Ajouter un élément', 'horizon-blocks')),
			TrueFalseField::make(__('Activer le balisage schema.org (FAQ)', 'horizon-blocks'), self::FIELD_SCHEMA_ORG)
				->helperText(__("À n'activer que s'il s'agit d'une véritable FAQ. Sur un accordéon générique, ce balisage peut nuire au référencement.", 'horizon-blocks'))
				->default(false),
		]);

		yield from LayoutTab::make()->fields([
			LayoutField::margin(),
		]);
	}

	public function addToContext(): array
	{
		$fields = get_fields();

		if (empty($fields[self::FIELD_SCHEMA_ORG]) || !SeoService::isCurrentPageIndexed()) {
			return ['structuredData' => null];
		}

		$items = [];

		foreach ($fields[self::FIELD_ITEMS] ?? [] as $item) {
			$items[] = [
				'title' => $item[self::FIELD_ITEM_TITLE] ?? null,
				'content' => $item[self::FIELD_ITEM_CONTENT] ?? null,
			];
		}

		return [
			'structuredData' => FaqSchemaBuilder::build($items, get_bloginfo('name')),
		];
	}

	public function renderBlockCallback(): void
	{
		return;
	}
}
```

- [ ] **Step 2: Vérifier la syntaxe PHP**

Run: `php -l src/Blocks/Content/AccordionBlock.php`
Expected: `No syntax errors detected`

- [ ] **Step 3: Ne rien explorer — les signatures sont déjà vérifiées**

`vendor/` a été installé et les API confirmées avant l'écriture de ce task. Ne pas repartir en exploration, et **ne pas** remplacer les valeurs ci-dessous :

- `WysiwygField::minimal(string $label = 'Description', string|null $name = self::WYSIWYG): WYSIWYGEditor` — accepte bien `(label, name)`, d'où la constante explicite `FIELD_ITEM_CONTENT`. La constante de nom par défaut du helper s'appelle `WysiwygField::WYSIWYG` (valeur `'wysiwyg'`), **pas** `WysiwygField::NAME` qui n'existe pas.
- Le champ wysiwyg de l'en-tête reste appelé nu, donc sa clé de lecture est `'wysiwyg'` — c'est ce que fait déjà `faq.blade.php`.
- `TrueFalseField::make(?string $label, ?string $name): TrueFalse` existe bien dans `Adeliom\HorizonTools\Fields\Choices`, applique `->stylized()`, et le `TrueFalse` retourné expose `->default()` (trait `DefaultValue`) et `->helperText()` (trait `HelperText`).
- `Repeater` expose `collapsed()`, `minRows()`, `maxRows()` en propre et `button()` via le trait `ButtonLabel`.
- `Text` expose `maxLength()`, `helperText()` et `required()`.

Aucune action à cette étape hors la lecture de ces contraintes.

- [ ] **Step 4: Relancer le lint après tout ajustement**

Run: `php -l src/Blocks/Content/AccordionBlock.php`
Expected: `No syntax errors detected`

- [ ] **Step 5: Committer**

```bash
git add src/Blocks/Content/AccordionBlock.php
git commit -m "feat(accordion): add AccordionBlock with repeater and schema.org toggle"
```

---

### Task 4: Vue du bloc `accordion.blade.php`

**Files:**
- Create: `resources/views/blocks/content/accordion.blade.php`

**Interfaces:**
- Consumes: `AccordionBlock` (Task 3) via `$fields` et `$context['structuredData']` ; `<x-cards.card-accordion>` (Task 2).
- Produces: rien de consommé en aval.

- [ ] **Step 1: Créer la vue**

Créer `resources/views/blocks/content/accordion.blade.php`. L'en-tête reprend `faq.blade.php` à l'identique, groupe de boutons compris :

```blade
<x-block :fields="$fields" :block="$block">
    @if(!empty($context['structuredData']))
        <script type="application/ld+json">
            @json($context['structuredData'])
        </script>
    @endif

    <div class="grid-12">
        <div class="lg:col-span-5 lg:mr-12">
            @if (!empty($fields['img']))
                <x-media.img :image="$fields['img']" class="w-20 mb-3" size="thumbnail" />
            @endif

            @if (!empty($fields['uptitle']))
                <x-typography.uptitle :content="$fields['uptitle']" />
            @endif

            @if (!empty($fields['title']))
                <x-typography.heading :fields="$fields['title']" size="3" />
            @endif

            @if (!empty($fields['wysiwyg']))
                <x-typography.text :content="$fields['wysiwyg']" class="pt-medium" />
            @endif

            @if (!empty($fields['buttons']))
                <x-action.buttons :buttons="$fields['buttons']" class="mt-button-text-mobile lg:mt-button-text-desktop" />
            @endif

        </div>
        <div class="lg:col-span-7 flex flex-col gap-medium">
            @if (!empty($fields['items']))
                @foreach ($fields['items'] as $item)
                    <x-cards.card-accordion :title="$item['itemTitle'] ?? null" :content="$item['itemContent'] ?? null" />
                @endforeach
            @endif
        </div>
    </div>
</x-block>
```

**Les clés sont toutes vérifiées, ne rien deviner.** `img`, `uptitle`, `title`, `buttons` viennent de `faq.blade.php` ; `wysiwyg` est la valeur de `WysiwygField::WYSIWYG` pour le champ d'en-tête appelé nu ; `items`, `itemTitle`, `itemContent` sont les constantes du Task 3. La ligne du repeater lit bien `itemContent` — et non `wysiwyg`, puisque le sous-champ reçoit un nom explicite.

- [ ] **Step 2: Vérifier la structure de la vue**

```bash
for token in 'x-cards.card-accordion' "fields\['items'\]" 'application/ld+json' 'x-action.buttons'; do
  grep -q "$token" resources/views/blocks/content/accordion.blade.php \
    && echo "PASS - $token" || echo "FAIL - $token"
done
grep -c 'card-faq\|questions' resources/views/blocks/content/accordion.blade.php
```
Expected: quatre `PASS`, puis `0` (aucune trace de l'ancien modèle).

- [ ] **Step 3: Committer**

```bash
git add resources/views/blocks/content/accordion.blade.php
git commit -m "feat(accordion): add accordion block view"
```

---

### Task 5: Recâbler `HorizonBlockService`

**Files:**
- Modify: `src/Services/HorizonBlockService.php` (imports lignes 17, 42, 54 ; entrée lignes 174-180)

**Interfaces:**
- Consumes: `AccordionBlock` (Task 3), `CardAccordion` (Task 2).
- Produces: entrée `AccordionBlock::class` dans la table de métadonnées, sans `REQUIRED_POSTTYPES`.

- [ ] **Step 1: Remplacer les imports**

Dans `src/Services/HorizonBlockService.php` :
- ligne 17 : `use Adeliom\HorizonBlocks\Blocks\Content\FaqBlock;` → `use Adeliom\HorizonBlocks\Blocks\Content\AccordionBlock;`
- ligne 42 : `use Adeliom\HorizonBlocks\View\Components\Cards\CardFaq;` → `use Adeliom\HorizonBlocks\View\Components\Cards\CardAccordion;`
- ligne 54 : supprimer entièrement `use Adeliom\HorizonPostTypes\PostTypes\FAQ;`

Les imports étant triés alphabétiquement, repositionner les deux premiers en conséquence (`AccordionBlock` remonte avant les autres blocs `Content`, `CardAccordion` avant `CardFaq`'s neighbours).

- [ ] **Step 2: Remplacer l'entrée du bloc**

Remplacer le bloc lignes 174-180 :

```php
            FaqBlock::class => [
                self::REQUIRES_LIVEWIRE => false,
                self::ASSET_FILES => [],
                self::LIVEWIRE_COMPONENTS => [],
                self::COMPONENTS => [CardFaq::class],
                self::REQUIRED_POSTTYPES => [FAQ::class],
            ],
```

par :

```php
            AccordionBlock::class => [
                self::REQUIRES_LIVEWIRE => false,
                self::ASSET_FILES => [],
                self::LIVEWIRE_COMPONENTS => [],
                self::COMPONENTS => [CardAccordion::class],
            ],
```

C'est la disparition de `REQUIRED_POSTTYPES` qui fait que `ddev acorn import:block` n'installera plus le CPT FAQ dans les nouveaux projets.

- [ ] **Step 3: Vérifier qu'aucune référence FAQ ne subsiste dans le service**

```bash
php -l src/Services/HorizonBlockService.php
grep -n "FaqBlock\|CardFaq\|PostTypes\\\\FAQ" src/Services/HorizonBlockService.php || echo "PASS - aucune référence FAQ restante"
```
Expected: `No syntax errors detected`, puis la ligne `PASS`.

- [ ] **Step 4: Committer**

```bash
git add src/Services/HorizonBlockService.php
git commit -m "refactor(accordion): register AccordionBlock and drop FAQ post type requirement"
```

---

### Task 6: Supprimer l'ancien bloc et nettoyer

**Files:**
- Delete: `src/Blocks/Content/FaqBlock.php`
- Delete: `src/View/Components/Cards/CardFaq.php`
- Delete: `resources/views/blocks/content/faq.blade.php`
- Delete: `resources/views/components/cards/card-faq.blade.php`
- Rename: `resources/images/admin/blocks/content/faq.jpg` → `accordion.jpg`
- Rename: `resources/images/admin/blocks/listing/faq-listing.jpg` → `accordion-listing.jpg`
- Modify: `src/View/Components/Cards/CardStep.php:7`

**Interfaces:**
- Consumes: Task 5 doit être terminé — supprimer `FaqBlock` avant le recâblage laisserait `HorizonBlockService` avec un import cassé.
- Produces: rien.

- [ ] **Step 1: Supprimer les quatre fichiers de l'ancien bloc**

```bash
git rm src/Blocks/Content/FaqBlock.php \
       src/View/Components/Cards/CardFaq.php \
       resources/views/blocks/content/faq.blade.php \
       resources/views/components/cards/card-faq.blade.php
```

- [ ] **Step 2: Renommer les deux images d'admin**

```bash
git mv resources/images/admin/blocks/content/faq.jpg resources/images/admin/blocks/content/accordion.jpg
git mv resources/images/admin/blocks/listing/faq-listing.jpg resources/images/admin/blocks/listing/accordion-listing.jpg
```

- [ ] **Step 3: Retirer le `use` mort de `CardStep`**

Supprimer la ligne 7 de `src/View/Components/Cards/CardStep.php` :

```php
use App\PostTypes\FAQ;
```

C'est un import mort : `FAQ` n'apparaît nulle part ailleurs dans le fichier, dont le constructeur ne prend qu'un `array $step`.

- [ ] **Step 4: Vérifier qu'il ne reste aucune référence pendante**

```bash
php -l src/View/Components/Cards/CardStep.php
grep -rn "FaqBlock\|CardFaq\|card-faq\|PostTypes\\\\FAQ" src/ resources/ || echo "PASS - aucune référence pendante"
ls resources/images/admin/blocks/content/accordion.jpg resources/images/admin/blocks/listing/accordion-listing.jpg
```
Expected: `No syntax errors detected`, la ligne `PASS`, puis les deux images listées.

- [ ] **Step 5: Committer**

```bash
git add -A src/ resources/
git commit -m "refactor(accordion): remove FaqBlock and rename admin images"
```

---

### Task 7: Vérification finale

**Files:** aucun (contrôle).

**Interfaces:**
- Consumes: Tasks 1 à 6.
- Produces: rien.

- [ ] **Step 1: Relancer le test du builder**

Run: `php tests/AccordionSchemaTest.php`
Expected: `ALL PASS`, code de sortie 0.

- [ ] **Step 2: Linter tous les fichiers PHP touchés**

```bash
for f in src/Services/FaqSchemaBuilder.php \
         src/Services/HorizonBlockService.php \
         src/Blocks/Content/AccordionBlock.php \
         src/View/Components/Cards/CardAccordion.php \
         src/View/Components/Cards/CardStep.php; do
  php -l "$f"
done
```
Expected: cinq `No syntax errors detected`.

- [ ] **Step 3: Contrôler l'état du dépôt**

```bash
git status --short
git log --oneline sage/11 ^origin/sage/11
git diff --check origin/sage/11..sage/11
```
Expected : arbre de travail propre (`git status --short` ne renvoie rien) — le travail sur `navbar.blade.php` a finalement été commité séparément par l'humain (`c902970`), hors périmètre de ce chantier ; la plage de commits de ce plan compte 7 commits de code plus les commits de documentation associés ; aucun signalement de whitespace.

- [ ] **Step 4: Pusher**

```bash
git push origin sage/11
```

`sage/11` est une branche de dev partagée : on pousse après une feature complète, sans force-push.

---

## Reste à faire hors de ce plan

- **Recette dans un vrai projet.** Rien ici ne s'exécute dans WordPress : le repo n'a ni bootstrap WP ni ACF installé. Il faut importer le bloc dans un thème (`ddev acorn import:block`) pour valider le rendu, le repli des panneaux, la navigation clavier et le balisage `FAQPage` via le Rich Results Test de Google.
- **Suppression réelle du CPT FAQ** dans `horizon-posttypes` — hors périmètre, décidé au brainstorming.
- **Mise à jour du catalogue Notion** des blocs Horizon si une fiche « Bloc FAQ » y existe.
