<?php

declare(strict_types=1);

namespace Adeliom\HorizonBlocks\Blocks\Blog;

use Adeliom\HorizonTools\Blocks\AbstractBlock;
use Adeliom\HorizonTools\Database\QueryBuilder;
use Adeliom\HorizonTools\Database\TaxQuery;
use Adeliom\HorizonTools\Fields\Buttons\ButtonField;
use Adeliom\HorizonTools\Fields\Layout\LayoutField;
use Adeliom\HorizonTools\Fields\Select\TaxonomySelectField;
use Adeliom\HorizonTools\Fields\Tabs\ContentTab;
use Adeliom\HorizonTools\Fields\Tabs\LayoutTab;
use Adeliom\HorizonTools\Fields\Text\HeadingField;
use Adeliom\HorizonTools\Fields\Text\UptitleField;
use Adeliom\HorizonTools\Fields\Text\WysiwygField;
use Adeliom\HorizonTools\Services\PostService;
use Extended\ACF\ConditionalLogic;
use Extended\ACF\Fields\ButtonGroup;
use Extended\ACF\Fields\Relationship;
use Extended\ACF\Fields\Taxonomy;

class LatestPostBlock extends AbstractBlock
{
    public static ?string $slug = 'latest-post';
    public static string $category = 'blog';
    public static ?string $mode = 'preview';
    public static ?string $icon = 'welcome-write-blog';

    public static function getTitle(): ?string
    {
        return __('Remontée d’articles', 'horizon-blocks');
    }

    public static function getDescription(): ?string
    {
        return __('Remonte les derniers articles du blog, en mode automatique, par catégorie ou en sélection manuelle.', 'horizon-blocks');
    }

    public const string ASSOCIATED_POST_TYPE = 'post';

    /** Nombre d’articles affichés selon la mise en page choisie. */
    public const array MAX_POSTS_PER_LAYOUT = [
        self::VALUE_LAYOUT_THREE => 3,
        self::VALUE_LAYOUT_FOUR => 4,
    ];

    /** Nombre d’articles mis en avant en grand format (mise en page « quatre articles » uniquement). */
    public const int NUMBER_OF_FEATURED_POSTS = 1;

    public const string FIELD_TYPE = 'type';
    public const string VALUE_TYPE_AUTOMATIC = 'automatic';
    public const string VALUE_TYPE_TAXONOMY = 'taxonomy';
    public const string VALUE_TYPE_MANUAL = 'manual';

    public const string FIELD_TAXONOMY = 'taxonomy';
    public const string FIELD_MANUAL_POSTS = 'manualPosts';

    public const string FIELD_LAYOUT = 'layout';
    public const string VALUE_LAYOUT_THREE = 'three';
    public const string VALUE_LAYOUT_FOUR = 'four';

    public function getFields(): ?iterable
    {
        $maxPosts = max(self::MAX_POSTS_PER_LAYOUT);

        $taxonomies = self::getSelectableTaxonomies();

        // Les taxonomies écartées ci-dessus n’ont pas à apparaître dans le sélecteur de taxonomie.
        $excludedTaxonomies = array_values(
            array_diff(
                PostService::getAllAssociatedTaxonomies(postType: self::ASSOCIATED_POST_TYPE, onlySlugs: true),
                array_keys($taxonomies),
            ),
        );

        /**
         * Un champ Taxonomie par taxonomie associée aux articles : celui qui s’affiche dépend de la
         * taxonomie choisie juste au-dessus. Impossible de n’en poser qu’un seul, ACF a besoin de
         * connaître la taxonomie à l’enregistrement du champ.
         */
        $taxonomyFields = [];

        foreach ($taxonomies as $taxonomySlug => $taxonomyName) {
            $taxonomyFields[] = Taxonomy::make($taxonomyName, $taxonomySlug)
                ->taxonomy($taxonomySlug)
                ->appearance('multi_select')
                ->conditionalLogic([ConditionalLogic::where(self::FIELD_TAXONOMY, '==', $taxonomySlug)])
                ->required();
        }

        yield from ContentTab::make()->fields([
            UptitleField::make(),
            HeadingField::make(HeadingField::LABEL, HeadingField::NAME, null, 'h2')->required(),
            WysiwygField::minimal(),
            ButtonGroup::make(__('Type de remontée', 'horizon-blocks'), self::FIELD_TYPE)
                ->choices([
                    self::VALUE_TYPE_AUTOMATIC => __('Automatique', 'horizon-blocks'),
                    self::VALUE_TYPE_TAXONOMY => __('Catégorisation', 'horizon-blocks'),
                    self::VALUE_TYPE_MANUAL => __('Manuelle', 'horizon-blocks'),
                ])
                ->default(self::VALUE_TYPE_AUTOMATIC)
                ->helperText(
                    sprintf(
                        '<strong>%s</strong> %s<br><strong>%s</strong> %s<br><strong>%s</strong> %s',
                        __('Automatique :', 'horizon-blocks'),
                        sprintf(__('récupère les %d derniers articles publiés.', 'horizon-blocks'), $maxPosts),
                        __('Catégorisation :', 'horizon-blocks'),
                        __('ne remonte que les derniers articles des catégories choisies.', 'horizon-blocks'),
                        __('Manuelle :', 'horizon-blocks'),
                        __('affiche les articles choisis en premier, puis complète avec les derniers publiés.', 'horizon-blocks'),
                    ),
                ),
            TaxonomySelectField::make(
                postType: self::ASSOCIATED_POST_TYPE,
                label: __('Taxonomie', 'horizon-blocks'),
                name: self::FIELD_TAXONOMY,
                excluded: $excludedTaxonomies,
            )->conditionalLogic([ConditionalLogic::where(self::FIELD_TYPE, '==', self::VALUE_TYPE_TAXONOMY)]),
            ...$taxonomyFields,
            Relationship::make(__('Articles', 'horizon-blocks'), self::FIELD_MANUAL_POSTS)
                ->postTypes([self::ASSOCIATED_POST_TYPE])
                ->minPosts(1)
                ->maxPosts($maxPosts)
                ->helperText(
                    __(
                        'Les articles sélectionnés sont affichés en premier.<br>Selon la mise en page retenue, les derniers d’entre eux peuvent ne pas être affichés.',
                        'horizon-blocks',
                    ),
                )
                ->conditionalLogic([ConditionalLogic::where(self::FIELD_TYPE, '==', self::VALUE_TYPE_MANUAL)]),
            ButtonField::group(),
        ]);

        yield from LayoutTab::make()->fields([
            ButtonGroup::make(__('Mise en page', 'horizon-blocks'), self::FIELD_LAYOUT)
                ->choices([
                    self::VALUE_LAYOUT_THREE => __('Trois articles', 'horizon-blocks'),
                    self::VALUE_LAYOUT_FOUR => __('Quatre articles', 'horizon-blocks'),
                ])
                ->default(self::VALUE_LAYOUT_FOUR)
                ->helperText(
                    __(
                        'Trois articles : une rangée de cartes verticales.<br>Quatre articles : le premier article en grand format, les trois suivants en cartes horizontales.',
                        'horizon-blocks',
                    ),
                ),
            LayoutField::margin(),
        ]);
    }

    /**
     * Les taxonomies proposées à l’éditeur : celles qui ont une interface d’administration. Des
     * plugins accrochent aux articles des taxonomies techniques — Polylang par exemple, avec
     * `language` et `post_translations` — dont le libellé peut valoir false : elles n’ont rien à
     * faire dans les champs du bloc, et un libellé non textuel fait échouer la déclaration ACF.
     *
     * @return array<string, string> libellés indexés par slug de taxonomie
     */
    private static function getSelectableTaxonomies(): array
    {
        $taxonomies = [];

        foreach (PostService::getAllAssociatedTaxonomies(postType: self::ASSOCIATED_POST_TYPE) as $slug => $label) {
            $taxonomy = get_taxonomy($slug);

            if ($taxonomy && $taxonomy->show_ui && is_string($label) && '' !== $label) {
                $taxonomies[$slug] = $label;
            }
        }

        return $taxonomies;
    }

    public function addToContext(): array
    {
        $fields = get_fields() ?: [];

        $layout = $fields[self::FIELD_LAYOUT] ?? self::VALUE_LAYOUT_FOUR;
        $maxPosts = self::MAX_POSTS_PER_LAYOUT[$layout] ?? max(self::MAX_POSTS_PER_LAYOUT);

        $posts = $this->getPosts(fields: $fields, maxPosts: $maxPosts);
        $featuredPosts = [];

        if ($layout === self::VALUE_LAYOUT_FOUR) {
            // array_splice retire les articles mis en avant de la liste : aucun doublon possible.
            $featuredPosts = array_splice($posts, 0, self::NUMBER_OF_FEATURED_POSTS);
        }

        return [
            'layout' => $layout,
            'featuredPosts' => $featuredPosts,
            'posts' => $posts,
            'buttons' => $this->getButtons($fields),
        ];
    }

    /**
     * @return \WP_Post[]
     */
    private function getPosts(array $fields, int $maxPosts): array
    {
        $posts = [];
        $type = $fields[self::FIELD_TYPE] ?? self::VALUE_TYPE_AUTOMATIC;

        if ($type === self::VALUE_TYPE_MANUAL && !empty($fields[self::FIELD_MANUAL_POSTS])) {
            $posts = array_slice($fields[self::FIELD_MANUAL_POSTS], 0, $maxPosts);
        }

        $missingPosts = $maxPosts - count($posts);

        if ($missingPosts < 1) {
            return $posts;
        }

        $queryBuilder = new QueryBuilder()->postType(self::ASSOCIATED_POST_TYPE)->perPage($missingPosts);

        // Sur un article, on ne se remonte pas soi-même.
        if (get_post_type() === self::ASSOCIATED_POST_TYPE && is_int($currentId = get_the_ID())) {
            $queryBuilder->whereIdNotIn($currentId);
        }

        if (!empty($posts)) {
            $queryBuilder->whereIdNotIn(array_column($posts, 'ID'));
        }

        if ($type === self::VALUE_TYPE_TAXONOMY && !empty(($taxonomySlug = $fields[self::FIELD_TAXONOMY] ?? null))) {
            if (!empty($fields[$taxonomySlug])) {
                $queryBuilder->addTaxQuery(new TaxQuery()->add($taxonomySlug, $fields[$taxonomySlug], 'term_id'));
            }
        }

        return array_merge($posts, $queryBuilder->get());
    }

    /**
     * Le bouton principal renvoie vers la page des articles quand il n’est pas renseigné : le bloc
     * garde ainsi sa sortie de secours vers le blog sans configuration.
     */
    private function getButtons(array $fields): array
    {
        $buttons = $fields[ButtonField::BUTTONS] ?? [];

        if (empty($buttons[ButtonField::BUTTON_ONE][ButtonField::BUTTON_LINK]) && ($postsPageId = get_option('page_for_posts'))) {
            $buttons[ButtonField::BUTTON_ONE] = [
                ButtonField::BUTTON_LINK => [
                    'title' => __('Voir tous les articles', 'horizon-blocks'),
                    'url' => get_permalink((int) $postsPageId),
                    'target' => '',
                ],
            ];
        }

        return $buttons;
    }

    public function renderBlockCallback(): void
    {
        return;
    }
}
