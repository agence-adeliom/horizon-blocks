# Bloc FAQ → bloc Accordéon — Design

**Date** : 2026-07-29
**Auteur** : Emma Louviot
**Source** : [Notion — Bloc FAQ > le transformer en bloc accordéons](https://app.notion.com/p/31eda39b354280658e67fa14f8b06163)
**Package** : `agence-adeliom/horizon-blocks`
**Branche** : `sage/11`

## Objectif

Le bloc FAQ n'est en pratique pas utilisé comme une FAQ, mais comme un accordéon générique. Comme répéter les mêmes questions d'une page à l'autre est une mauvaise pratique SEO, le CPT FAQ n'a plus de raison d'être comme source de contenu.

On transforme donc le bloc en **accordéon** : les paires titre/contenu sont saisies directement dans le bloc via un repeater, le bloc ne dépend plus du CPT FAQ, et le balisage schema.org devient une option explicite.

## Décisions verrouillées pendant le brainstorming

- **Périmètre limité à `horizon-blocks`.** Le CPT FAQ vit dans `agence-adeliom/horizon-posttypes` et **reste en place** : on coupe seulement la dépendance côté bloc. Aucun projet existant ne casse.
- **Nouveau bloc `accordion`, `FaqBlock` supprimé.** `ImportBlock` copie les blocs dans le projet consommateur en réécrivant les namespaces (`Adeliom\HorizonBlocks\…` → `App\…`), donc chaque projet a déjà sa propre copie sous `App\Blocks\`. Renommer dans la librairie n'affecte que les futurs imports.
- **schema.org désactivé par défaut.** Le ticket demandait `true` par défaut, mais émettre du `FAQPage` sur tout accordéon générique réintroduit le problème SEO que le ticket cherche à fuir. Le champ existe, il est simplement à `false` par défaut, avec un `helperText` qui explique quand l'activer.
- **Lignes du repeater** : titre en `Text`, contenu en `WysiwygField::minimal()`.
- **Accessibilité corrigée dans le même chantier**, le composant étant de toute façon réécrit.

## Périmètre — fichiers

| Action | Fichier |
|---|---|
| Créé | `src/Services/FaqSchemaBuilder.php` |
| Créé | `src/Blocks/Content/AccordionBlock.php` |
| Créé | `src/View/Components/Cards/CardAccordion.php` |
| Créé | `resources/views/blocks/content/accordion.blade.php` |
| Créé | `resources/views/components/cards/card-accordion.blade.php` |
| Créé | `tests/AccordionSchemaTest.php` |
| Renommé | `resources/images/admin/blocks/content/faq.jpg` → `accordion.jpg` |
| Renommé | `resources/images/admin/blocks/listing/faq-listing.jpg` → `accordion-listing.jpg` |
| Supprimé | `src/Blocks/Content/FaqBlock.php` |
| Supprimé | `src/View/Components/Cards/CardFaq.php` |
| Supprimé | `resources/views/blocks/content/faq.blade.php` |
| Supprimé | `resources/views/components/cards/card-faq.blade.php` |
| Modifié | `src/Services/HorizonBlockService.php` |
| Modifié | `src/View/Components/Cards/CardStep.php` |

Les images d'admin sont nommées d'après le slug du bloc (`documents.jpg`, `gallery.jpg`, `step.jpg`…), d'où le renommage de `faq.jpg`.

## Champs ACF

`AccordionBlock` reprend l'en-tête de `FaqBlock` à l'identique — `Image` (petite image), `UptitleField`, `HeadingField` en `h2` requis, `WysiwygField::minimal()`, `ButtonField::group()` — et remplace le `Relationship` par :

```php
Repeater::make(__('Éléments', 'horizon-blocks'), self::FIELD_ITEMS)
    ->fields([
        Text::make(__('Titre', 'horizon-blocks'), self::FIELD_ITEM_TITLE)
            ->maxLength(150)
            ->helperText(__('Maximum 150 caractères', 'horizon-blocks'))
            ->required(),
        WysiwygField::minimal(__('Contenu', 'horizon-blocks'), self::FIELD_ITEM_CONTENT)
            ->required(),
    ])
    ->layout('row')
    ->collapsed(self::FIELD_ITEM_TITLE)
    ->minRows(2)
    ->button(__('Ajouter un élément', 'horizon-blocks')),

TrueFalseField::make(__('Activer le balisage schema.org (FAQ)', 'horizon-blocks'), self::FIELD_SCHEMA_ORG)
    ->helperText(__("À n'activer que s'il s'agit d'une véritable FAQ. Sur un accordéon générique, ce balisage peut nuire au référencement.", 'horizon-blocks'))
    ->default(false),
```

Constantes : `FIELD_IMG`, `FIELD_ITEMS`, `FIELD_ITEM_TITLE`, `FIELD_ITEM_CONTENT`, `FIELD_SCHEMA_ORG`.

Le repeater force `->layout('row')` : le layout par défaut d'ACF (`table`) comprime le wysiwyg dans une cellule de tableau, ce qui rend TinyMCE inutilisable. C'est le même correctif que celui déjà appliqué dans `CardsBlock`, seul autre repeater du repo contenant un wysiwyg. Le sous-champ contenu est aussi `->required()` : un titre sans contenu est un état enregistrable mais invisible (le garde `@if` du Blade masque silencieusement toute la ligne), donc le contrat ACF doit interdire cet état plutôt que de laisser le rendu le faire disparaître.

Imports : `Extended\ACF\Fields\Text`, `Extended\ACF\Fields\Repeater`, `Extended\ACF\Fields\Image`, `Adeliom\HorizonTools\Fields\Choices\TrueFalseField` (helper Horizon, comme dans `PostSummaryBlock`, plutôt que le `TrueFalse` brut d'Extended ACF).

**Vérifié dans `vendor/` (résolu).** Les deux inconnues signalées lors du brainstorming ont été levées en installant les dépendances avant l'implémentation :

1. `WysiwygField::minimal(string $label = 'Description', string|null $name = self::WYSIWYG)` **accepte** `(label, name)`. On lui passe donc un nom explicite `FIELD_ITEM_CONTENT = 'itemContent'` pour le sous-champ du repeater, plus lisible qu'un appel nu.
2. La constante de nom par défaut du helper s'appelle `WysiwygField::WYSIWYG` (valeur `'wysiwyg'`) — il n'existe pas de `WysiwygField::NAME`. Elle ne concerne que le champ wysiwyg de l'en-tête, qui reste appelé nu.

`TrueFalseField` existe bien dans `Adeliom\HorizonTools\Fields\Choices` et le `TrueFalse` qu'il retourne expose `->default()` et `->helperText()`.

**Écart assumé par rapport à `CardsBlock`.** Ce bloc utilise `HeadingField::make()` pour le titre de ligne, ce qui expose un sélecteur de balise au rédacteur. Pour un accordéon, le niveau de titre est une décision structurelle (il doit être cohérent avec le `<h2>` du bloc et stable pour `aria-labelledby`), pas un choix éditorial — d'où un `Text` simple. Le sous-champ wysiwyg du repeater reçoit, lui, un nom explicite (`WysiwygField::minimal(__('Contenu', 'horizon-blocks'), self::FIELD_ITEM_CONTENT)`) plutôt que d'être appelé nu : c'est plus lisible dans la définition des champs, et c'est la clé que `AccordionBlock::addToContext()` relit ensuite dans `$item[self::FIELD_ITEM_CONTENT]`.

**Non repris** : le `maxPosts(5)` de l'ancien `Relationship`. Un accordéon générique n'a pas de raison d'être plafonné à 5 entrées. `minRows(2)` est conservé — un accordéon d'un seul élément n'a pas de sens.

## schema.org

Deux corrections, en plus du toggle.

**1. La structure actuelle est invalide.** `addToContext()` empile des objets `Question` et le blade les sérialise tels quels :

```json
[ {"@context":"…","@type":"Question",…}, {"@context":"…","@type":"Question",…} ]
```

Google attend un `FAQPage` dont `mainEntity` contient les `Question`. Un tableau nu au premier niveau, chaque entrée répétant son `@context`, n'est pas exploitable pour les rich results. Nouvelle forme :

```php
if (empty($fields[self::FIELD_SCHEMA_ORG]) || !SeoService::isCurrentPageIndexed()) {
    return ['structuredData' => null];
}

// $questions = [['@type' => 'Question', 'name' => …, 'acceptedAnswer' => ['@type' => 'Answer', 'text' => …]], …]
return ['structuredData' => $questions === [] ? null : [
    '@context'   => 'https://schema.org',
    '@type'      => 'FAQPage',
    'mainEntity' => $questions,
]];
```

**2. `strip_tags()` → `wp_strip_all_tags()`** sur le texte des réponses. Le contenu vient d'un wysiwyg ; `strip_tags` laisse passer le contenu des balises `<script>`/`<style>`, ce que la version WordPress retire.

**Supprimé** : `datePublished`. Il venait de `$questionPost->post_date`, qui n'existe plus sans CPT.

**Conservé** : l'`author` de type `Organization` portant le nom du site, qu'émet déjà `FaqBlock`. Il est passé au builder en paramètre optionnel (`?string $siteName`) plutôt que lu via `get_bloginfo()` à l'intérieur, pour que le builder reste testable hors WordPress. Sans ce paramètre, aucun `author` n'est émis.

Les lignes dont le titre ou le contenu est vide sont ignorées à la construction, comme aujourd'hui.

**Où vit cette logique.** Dans une classe dédiée `Adeliom\HorizonBlocks\Services\FaqSchemaBuilder`, sans aucun `use` ni appel WordPress obligatoire. C'est une contrainte du dispositif de test du repo : `tests/ListingPeriodTest.php` est un script PHP autonome qui fait un `require` direct du fichier source, sans autoloader. `AccordionBlock` étendant `AbstractBlock` (horizon-tools) n'est pas chargeable ainsi — d'où l'extraction dans un fichier sans dépendance.

## Rendu et accessibilité

`accordion.blade.php` reprend l'en-tête de `faq.blade.php` — y compris le groupe de boutons — et boucle sur `FIELD_ITEMS` en passant des props plates à `<x-cards.card-accordion :title="…" :content="…" />`. Plus d'objet post transmis au composant.

`card-accordion.blade.php` remplace le `<div @click>` actuel par le motif accordéon accessible :

```blade
@php($uid = wp_unique_id('accordion-'))

<div x-data="{ open: false }" @class(['accordion-item', 'rounded-card bg-color-03-50 p-medium', $attributes['class']])>
    <h3>
        <button type="button"
                id="{{ $uid }}-btn"
                aria-controls="{{ $uid }}-panel"
                :aria-expanded="open"
                @click="open = !open"
                class="flex justify-between items-center w-full text-left cursor-pointer">
            <x-typography.text :content="$title" tag="span" class="font-semibold transition-all" x-bind:class="{ 'text-primary': open }" />
            <svg aria-hidden="true" :class="{ 'rotate-180': open }" …></svg>
        </button>
    </h3>
    <div id="{{ $uid }}-panel" aria-labelledby="{{ $uid }}-btn" x-show="open" x-collapse class="pt-medium">
        <x-typography.text :content="$content" />
    </div>
</div>
```

Points clés : `<button type="button">` (accès clavier et restitution correcte), `aria-expanded` piloté par Alpine, `aria-controls`/`aria-labelledby` appairés, chevron `aria-hidden`, `<h3>` cohérent avec le `<h2>` du bloc. `wp_unique_id()` garantit l'unicité des `id` si plusieurs accordéons cohabitent sur une page. `<x-typography.text>` rend un `<p>` par défaut ; à l'intérieur du `<button>`, dont le modèle de contenu n'accepte que du phrasing content, on force `tag="span"` sur le titre — le panneau, lui, n'est pas dans un bouton et n'a pas besoin de ce forçage. Pas de `role="region"` sur le panneau : au-delà d'une demi-douzaine d'éléments, ce que l'accordéon n'a justement pas de raison de plafonner, l'ARIA APG déconseille ce rôle pour éviter la prolifération de landmarks — décision humaine explicite.

Chaque panneau reste replié au chargement — comportement actuel, conservé.

## Découplage du CPT FAQ

Dans `HorizonBlockService.php` : l'entrée du bloc perd `REQUIRED_POSTTYPES => [FAQ::class]`, et le `use Adeliom\HorizonPostTypes\PostTypes\FAQ` disparaît. C'est ce qui fait que `ddev acorn import:block` n'installera plus le CPT FAQ dans les nouveaux projets. `COMPONENTS` passe de `[CardFaq::class]` à `[CardAccordion::class]`.

`CardStep.php` porte un `use App\PostTypes\FAQ` **mort** (aucune utilisation dans le fichier) — retiré au passage.

## Tests

`tests/AccordionSchemaTest.php`, aligné sur la forme de `ListingPeriodTest.php`, couvre la seule logique testable sans WordPress — la construction du `FAQPage` :

- toggle à `false` → `structuredData` vaut `null`
- toggle à `true` → un unique nœud `FAQPage`, un `Question` par ligne, `@context` présent une seule fois
- lignes au titre ou contenu vide → ignorées
- toutes les lignes vides → `null` plutôt qu'un `FAQPage` sans `mainEntity`

Le test est un **script PHP autonome**, pas un test PHPUnit : le repo ne déclare aucune dépendance de dev, et `ListingPeriodTest.php` fonctionne par `require` direct + helper `check()` + `exit(0|1)`. Lancement : `php tests/AccordionSchemaTest.php`.

`SeoService::isCurrentPageIndexed()` et la lecture du toggle restent côté `addToContext()`, hors du builder — ce sont les deux points qui exigent WordPress.

## Hors périmètre

- La suppression réelle du CPT FAQ dans `horizon-posttypes`.
- Toute migration du contenu existant : les projets déjà en production gardent leur copie de `FaqBlock` sous `App\Blocks\` et ne sont pas touchés par ce changement.
- Le style CSS de l'accordéon, inchangé (les classes actuelles sont conservées, `faq-item` devenant `accordion-item`).

## Point d'attention

La classe utilitaire `faq-item` devient `accordion-item`. Si un projet la cible dans son CSS, le renommage est à répercuter — mais comme les vues sont copiées à l'import, seuls les futurs imports sont concernés.
