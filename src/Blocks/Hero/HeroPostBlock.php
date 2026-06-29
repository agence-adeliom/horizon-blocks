<?php

declare(strict_types=1);

namespace Adeliom\HorizonBlocks\Blocks\Hero;

use Adeliom\HorizonTools\Blocks\AbstractBlock;
use Adeliom\HorizonTools\Services\DateService;
use Adeliom\HorizonTools\Services\PostService;
use Adeliom\HorizonTools\Services\SeoService;
use Adeliom\HorizonTools\Services\Compilation\CompilationService;

class HeroPostBlock extends AbstractBlock
{
    public static ?string $slug = 'hero-post';
    public static ?string $mode = 'preview';
    public static ?string $icon = 'editor-aligncenter';

    public static function getTitle(): ?string
    {
        return __('Haut de page - Article', 'horizon-blocks');
    }

    public static function getDescription(): ?string
    {
        return __("Affiche les informations principales d'un article : titre, extrait, catégories, date de publication, temps de lecture et image à la une.", 'horizon-blocks');
    }

    public function getPostTypes(): ?array
    {
        return ['post'];
    }

    public function getFields(): ?iterable
    {
        yield from [];
    }

    public function addToContext(): array
    {
        $post = get_post(post: get_the_ID());
        return [
            'title' => $post?->post_title,
            'excerpt' => $post?->post_excerpt,
            'readingTimeInMinutes' => PostService::getReadingTimeInMinutes(post: $post),
            'publishedAt' => DateService::getPrettyDate(date: $post?->post_date),
            'hasBreadcrumbs' => SeoService::isRankMathActive(),
            'categories' => get_the_category($post?->ID),
            'img' => get_post_thumbnail_id($post?->ID),
        ];
    }

    public function renderBlockCallback(): void
    {
        CompilationService::getAsset('resources/scripts/blocks/hero-post.ts')?->enqueueAll();
    }
}
