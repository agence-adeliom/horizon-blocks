<?php

declare(strict_types=1);

namespace Adeliom\HorizonBlocks\Blocks\Content;

use Adeliom\HorizonTools\Blocks\AbstractBlock;
use Adeliom\HorizonTools\Fields\Choices\TrueFalseField;
use Adeliom\HorizonTools\Services\BlogPostService;
use Adeliom\HorizonTools\Services\Compilation\CompilationService;
use App\Admin\Post\PostSummaryAdmin;
use Extended\ACF\ConditionalLogic;
use Extended\ACF\Fields\Text;

class PostSummaryBlock extends AbstractBlock
{
    public static ?string $slug = 'post-summary';
    public static ?string $title = 'Sommaire article';
    public static ?string $mode = 'preview';
    public static ?string $description = 'Donne un aperçu des sections de l’article afin de faciliter la navigation.';

    public const string FIELD_IS_TOP = 'top';
    public const string FIELD_TITLE = 'title';

    // Value used to scroll the page to the top of the block corresponding to title in the summary
    public const int SCROLL_OFFSET = 100;

    public function getFields(): ?iterable
    {
        yield TrueFalseField::make(__('Est-ce la borne supérieure ?'), self::FIELD_IS_TOP)->default(true);

        yield Text::make(__('Titre'), self::FIELD_TITLE)->conditionalLogic([ConditionalLogic::where(self::FIELD_IS_TOP, '==', 1)]);
    }

    public function addToContext(): array
    {
        global $wpdb;

        $titlesOverride = null;

        $currentPostId = is_admin() ? $_GET['post'] ?? ($_POST['post_id'] ?? null) : get_the_ID();

        if ($currentPostId) {
            $query = <<<EOF
            SELECT {$wpdb->postmeta}.meta_key, {$wpdb->postmeta}.meta_value
            FROM {$wpdb->postmeta}
            WHERE {$wpdb->postmeta}.post_id = %d AND {$wpdb->postmeta}.meta_key LIKE %s
            EOF;

            $request = $wpdb->prepare($query, $currentPostId, sprintf('%s_%%', PostSummaryAdmin::FIELD_TITLES));

            if ($values = $wpdb->get_results($request)) {
                $titles = BlogPostService::getPostTitles(
                    retrieveOnly: PostSummaryAdmin::TO_RETRIEVE,
                    useHtml: PostSummaryAdmin::USE_HTML,
                    hierarchical: PostSummaryAdmin::HIERARCHICAL,
                    useCache: false,
                );

                if (is_array($titles)) {
                    foreach ($titles as $blockTitle) {
                        $this->handleElement(titlesOverride: $titlesOverride, element: $blockTitle, values: $values);
                    }
                }
            }
        }

        return [
            'titlesOverride' => $titlesOverride,
        ];
    }

    private function handleElement(?array &$titlesOverride, string|array $element, array $values)
    {
        foreach ($values as $key => $value) {
            if (is_object($value) && property_exists($value, 'meta_key')) {
                if (
                    $value->meta_key ===
                    PostSummaryAdmin::FIELD_TITLES . '_' . sanitize_title(PostSummaryAdmin::HIERARCHICAL ? $element['content'] : $element)
                ) {
                    if (property_exists($value, 'meta_value')) {
                        $titlesOverride[PostSummaryAdmin::HIERARCHICAL ? $element['content'] : $element] = $value->meta_value;
                    }
                }
            }
        }

        if (PostSummaryAdmin::HIERARCHICAL && !empty($element['children'])) {
            foreach ($element['children'] as $child) {
                $this->handleElement(titlesOverride: $titlesOverride, element: $child, values: $values);
            }
        }
    }

    public function renderBlockCallback(): void
    {
        switch (true) {
            case CompilationService::shouldUseBud():
                CompilationService::getAsset('post-summary.js')?->enqueue();
                break;
            default:
                CompilationService::getAsset('resources/scripts/blocks/post-summary.ts')?->enqueue();
                break;
        }
    }

    public function getPostTypes(): ?array
    {
        return ['post'];
    }
}
