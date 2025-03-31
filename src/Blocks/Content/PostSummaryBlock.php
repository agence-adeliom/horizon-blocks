<?php

declare(strict_types=1);

namespace Adeliom\HorizonBlocks\Blocks\Content;

use Adeliom\HorizonTools\Blocks\AbstractBlock;
use Adeliom\HorizonTools\Fields\Choices\TrueFalseField;
use Adeliom\HorizonTools\Services\BlogPostService;
use Adeliom\HorizonTools\Services\BudService;
use App\Admin\Post\PostSummaryAdmin;
use Extended\ACF\ConditionalLogic;
use Extended\ACF\Fields\Text;

class PostSummaryBlock extends AbstractBlock
{
	public static ?string $slug = 'post-summary';
	public static ?string $title = 'Sommaire article';
	public static ?string $mode = 'preview';
	public static ?string $description = 'Donne un aperçu des sections de l’article afin de faciliter la navigation.';

	public const FIELD_IS_TOP = 'top';
	public const FIELD_TITLE = 'title';

	// Value used to scroll the page to the top of the block corresponding to title in the summary
	public const SCROLL_OFFSET = 100;

	public function getFields(): ?iterable
	{
		yield TrueFalseField::make(__('Est-ce la borne supérieure ?'), self::FIELD_IS_TOP)
			->default(true);

		yield Text::make(__('Titre'), self::FIELD_TITLE)->conditionalLogic([
			ConditionalLogic::where(self::FIELD_IS_TOP, '==', 1)
		]);
	}

	public function addToContext(): array
	{
		global $wpdb;

		$titlesOverride = null;

		$currentPostId = is_admin() ? ($_GET['post'] ?? $_POST['post_id'] ?? null) : get_the_ID();

		if ($currentPostId) {
			$query = <<<EOF
SELECT {$wpdb->postmeta}.meta_key, {$wpdb->postmeta}.meta_value
FROM {$wpdb->postmeta}
WHERE {$wpdb->postmeta}.post_id = %d AND {$wpdb->postmeta}.meta_key LIKE %s
EOF;

			$request = $wpdb->prepare($query, $currentPostId, sprintf('%s_%%', PostSummaryAdmin::FIELD_TITLES));

			if ($values = $wpdb->get_results($request)) {
				foreach (BlogPostService::getPostTitles() as $blockTitle) {
					foreach ($values as $key => $value) {
						if (is_object($value) && property_exists($value, 'meta_key')) {
							if ($value->meta_key === PostSummaryAdmin::FIELD_TITLES . '_' . sanitize_title($blockTitle)) {
								if (property_exists($value, 'meta_value')) {
									$titlesOverride[$blockTitle] = $value->meta_value;
								}
							}
						}
					}
				}
			}
		}

		return [
			'titlesOverride' => $titlesOverride,
		];
	}

	public function renderBlockCallback(): void
	{
		if ($postSummaryJs = BudService::getUrl('post-summary.js')) {
			wp_enqueue_script('post-summary-block', $postSummaryJs);
		}
	}

	public function getPostTypes(): ?array
	{
		return ['post'];
	}
}
