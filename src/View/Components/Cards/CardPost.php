<?php

declare(strict_types=1);

namespace Adeliom\HorizonBlocks\View\Components\Cards;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use WP_Post;
use WP_Term;

class CardPost extends Component
{
    public ?string $title = null;
    public ?string $url = null;
    public ?array $image = null;
    public ?string $excerpt = null;
    public ?string $date = null;
    public ?WP_Term $term = null;

    /**
     * @param WP_Post|int|null $post l’article à afficher, objet ou identifiant
     * @param bool $vertical visuel au-dessus du contenu ; à false, visuel à gauche
     * @param bool $withExcerpt affiche l’extrait de l’article sous le titre
     * @param bool $withDate affiche la date de publication à côté de la catégorie
     * @param string|null $taxonomy taxonomie dont le premier terme est affiché en étiquette
     */
    public function __construct(
        public WP_Post|int|null $post = null,
        public bool $vertical = true,
        public bool $withExcerpt = false,
        public bool $withDate = false,
        public ?string $taxonomy = 'category',
    ) {
        $this->handlePost();
    }

    private function handlePost(): void
    {
        if (is_int($this->post)) {
            $this->post = get_post($this->post);
        }

        if (!$this->post instanceof WP_Post) {
            $this->post = null;

            return;
        }

        $this->title = get_the_title($this->post);
        $this->url = get_permalink($this->post);
        // get_the_excerpt() génère l’extrait depuis le contenu quand le champ dédié est vide.
        $this->excerpt = get_the_excerpt($this->post);
        $this->date = get_the_date('', $this->post) ?: null;

        if ($thumbnailId = get_post_thumbnail_id($this->post)) {
            $this->image = ['ID' => $thumbnailId];
        }

        if ($this->taxonomy) {
            $terms = get_the_terms($this->post, $this->taxonomy);

            if (is_array($terms) && !empty($terms)) {
                $this->term = reset($terms);
            }
        }
    }

    public function render(): View|Closure|string
    {
        return view('components.cards.card-post');
    }
}
