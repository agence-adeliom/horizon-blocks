<?php

declare(strict_types=1);

namespace Adeliom\HorizonBlocks\Admin\Post;

use Adeliom\HorizonTools\Admin\AbstractAdmin;
use Adeliom\HorizonTools\Services\BlogPostService;
use Extended\ACF\Fields\Group;
use Extended\ACF\Fields\Text;
use Extended\ACF\Location;

class PostSummaryAdmin extends AbstractAdmin
{
    public static ?string $title = 'Sommaire';
    public static bool $isOptionPage = false;
    public static ?string $optionPageIcon = null;

    public const string FIELD_TITLES = 'summaryTitles';
    public const array TO_RETRIEVE = ['h2'];
    public const bool USE_HTML = true;
    public const bool HIERARCHICAL = true;

    public function getFields(): ?iterable
    {
        $fields = [];

        $currentPostId = is_admin() ? $_GET['post'] ?? ($_POST['post_id'] ?? null) : get_the_ID();

        if (is_numeric($currentPostId)) {
            $postType = get_post_type($currentPostId);

            if ('post' === $postType) {
                $titles = BlogPostService::getPostTitles(
                    retrieveOnly: self::TO_RETRIEVE,
                    useHtml: self::USE_HTML,
                    hierarchical: self::HIERARCHICAL,
                    useCache: false,
                );

                $treatedSlugTitles = [];

                if (is_array($titles)) {
                    foreach ($titles as $title) {
                        if (!self::HIERARCHICAL) {
                            $slug = sanitize_title($title);

                            if (!in_array($slug, $treatedSlugTitles)) {
                                $fields[] = Text::make(__('Surcharge de :') . ' ' . $title, $slug)->placeholder($title);
                                $treatedSlugTitles[] = $slug;
                            }
                        } else {
                            $slug = sanitize_title($title['content']);

                            if (!in_array($slug, $treatedSlugTitles)) {
                                $fields[] = Text::make(__('Surcharge de :') . ' ' . $title['content'], $slug)->placeholder(
                                    $title['content'],
                                );
                                $treatedSlugTitles[] = $slug;
                            }

                            $this->handleChildrenFields(fields: $fields, element: $title, treatedSlugTitles: $treatedSlugTitles);
                        }
                    }
                }
            }
        }

        yield Group::make(__('Titres'), self::FIELD_TITLES)
            ->helperText(__('Cette section permet de surcharger les titres dans le sommaire de l’article.'))
            ->fields($fields);
    }

    private function handleChildrenFields(array &$fields, array $element, array &$treatedSlugTitles): void
    {
        if (empty($element['children'])) {
            return;
        }

        foreach ($element['children'] as $child) {
            $slug = sanitize_title($child['content']);

            if (!in_array($slug, $treatedSlugTitles)) {
                $fields[] = Text::make(__('Surcharge de :') . ' ' . $child['content'], $slug)->placeholder($child['content']);
                $treatedSlugTitles[] = $slug;
            }

            $this->handleChildrenFields(fields: $fields, element: $child, treatedSlugTitles: $treatedSlugTitles);
        }
    }

    public function getPosition(): string
    {
        return 'side';
    }

    public function getLocation(): iterable
    {
        yield Location::where('post_type', '==', 'post');
    }
}
