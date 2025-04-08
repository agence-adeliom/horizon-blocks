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

	public const FIELD_TITLES = 'summaryTitles';

	public function getFields(): ?iterable
	{
		$fields = [];

		$currentPostId = is_admin() ? $_GET['post'] ?? ($_POST['post_id'] ?? null) : get_the_ID();

		if (is_numeric($currentPostId)) {
			$postType = get_post_type($currentPostId);

			if ('post' === $postType) {
				$titles = BlogPostService::getPostTitles();

				if (is_array($titles)) {
					foreach ($titles as $title) {
						$fields[] = Text::make(__('Surcharge de :') . ' ' . $title, sanitize_title($title))->placeholder($title);
					}
				}
			}
		}

		yield Group::make(__('Titres'), self::FIELD_TITLES)
			->helperText(__('Cette section permet de surcharger les titres dans le sommaire de l’article.'))
			->fields($fields);
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
