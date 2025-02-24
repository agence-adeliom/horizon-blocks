<?php

declare(strict_types=1);

namespace Adeliom\HorizonBlocks\Livewire\Listing;

use Adeliom\HorizonTools\Database\MetaQuery;
use Adeliom\HorizonTools\Database\QueryBuilder;
use Adeliom\HorizonTools\Database\TaxQuery;
use Adeliom\HorizonTools\Enum\FilterTypesEnum;
use Adeliom\HorizonTools\Services\AcfService;
use Adeliom\HorizonTools\Services\ClassService;
use Adeliom\HorizonTools\ViewModels\Post\BasePostViewModel;
use Adeliom\HorizonBlocks\Blocks\Listing\ListingBlock;
use Extended\ACF\Fields\Image;
use Extended\ACF\Fields\Number;
use Extended\ACF\Fields\Select;
use Extended\ACF\Fields\Text;
use Extended\ACF\Fields\WYSIWYGEditor;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use Livewire\Attributes\Url;
use Livewire\Component;

class Listing extends Component
{
	private const DEFAULT_ORDER = 'date.DESC';

	public ?string $postType = null;

	public array $data = [];

	#[Url(as: "pagination")]
	public int $page = 1;
	#[Url(as: "filtres")]
	public array $filterFields = [];
	#[Url(as: "tri")]
	public string $order = self::DEFAULT_ORDER;
	public int $perPage = 12;

	public null|false|array $filters = [];
	private null|false|array $baseFilters = [];
	public ?string $postTypeClass = null;
	public ?string $card = null;

	public array $sortOptions = [
		'date.DESC' => 'Plus récent',
		'date.ASC' => 'Plus ancien',
	];

	private const array MANUAL_POST_TYPES = [
		'post',
		'page',
	];

	public function mount(): void
	{
		if (in_array($this->postType, self::MANUAL_POST_TYPES)) {
			$card = Config::get(sprintf('posts.listing.cards.%s', $this->postType));

			if ($card) {
				$this->card = $card;
			} else {
				throw new \Exception(sprintf('You have to set a card for the post-type "%s" in the "posts.php" config file (posts.listing.cards.%s)', $this->postType, $this->postType));
			}
		} else {
			$this->postTypeClass = ClassService::getPostTypeClassBySlug($this->postType);
			$this->card = $this->postTypeClass::$card;

			if (null === $this->card) {
				throw new \Exception(sprintf('You have to set a card for the post-type in the class "%s". It should be a static var $card', $this->postTypeClass));
			}
		}

		$this->initFilters();

		if ($page = Request::get('pagination')) {
			if (is_numeric($page)) {
				$this->page = (int)$page;
			}
		}

		$this->getData();
	}

	private function initTaxonomyFilter(string $taxonomyName, string $filterName, FilterTypesEnum $filterType, string $appearance, string $placeholder): void
	{
		$taxQb = new QueryBuilder();
		$taxQb->taxonomy($taxonomyName)
			->fetchEmptyTaxonomies(false);

		foreach ($taxQb->get() as $term) {
			if ($term instanceof \WP_Term) {
				if (!isset($this->filters[$filterName])) {
					$this->filters[$filterName] = [
						'type' => $filterType->value,
						'name' => $filterName,
						'appearance' => $appearance,
						'value' => $taxonomyName,
						'placeholder' => $placeholder,
						'choices' => [],
					];
				}

				$this->filters[$filterName]['choices'][] = [
					'slug' => $term->slug,
					'name' => $term->name,
				];
			}
		}
	}

	private function initMetaFilter(string $metaKey, string $filterName, FilterTypesEnum $filterType, string $appearance, string $postType, ?string $fieldClass, string $placeholder): void
	{
		if (null !== $fieldClass) {
			global $wpdb;

			$query = <<<EOF
        SELECT DISTINCT meta_value AS value
        FROM {$wpdb->postmeta}
        JOIN {$wpdb->posts} ON {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id
        WHERE meta_key = %s AND post_type = %s AND post_status = 'publish'
        EOF;

			$query = $wpdb->prepare($query, $metaKey, $this->postTypeClass ? $postType::$slug : $postType);

			$results = $wpdb->get_results($query);

			// Convert to array of values
			$values = array_map(function ($result) {
				return $result->value;
			}, $results);

			switch ($fieldClass) {
				case Select::class:
					$postTypeInstance = new $postType();
					if ($choices = AcfService::getChoices($postTypeInstance->getFields(), $metaKey)) {
						if (!isset($this->filters[$filterName])) {
							$this->filters[$filterName] = [
								'type' => $filterType->value,
								'name' => $filterName,
								'appearance' => $appearance,
								'value' => $metaKey,
								'placeholder' => $placeholder,
								'choices' => [],
							];
						}
						foreach ($choices as $value => $label) {
							$this->filters[$filterName]['choices'][] = [
								'slug' => $value,
								'name' => $label,
							];
						}
					}
					break;
				default:
					foreach ($values as $value) {
						if (!empty($value)) {
							if (!isset($this->filters[$filterName])) {
								$this->filters[$filterName] = [
									'type' => $filterType->value,
									'name' => $filterName,
									'appearance' => $appearance,
									'value' => $metaKey,
									'placeholder' => $placeholder,
									'choices' => [],
								];
							}

							$this->filters[$filterName]['choices'][] = [
								'slug' => $value,
								'name' => $value,
							];
						}
					}
					break;
			}
		}
	}

	private function initFilters(): void
	{
		if (ListingBlock::USE_FIELDS_TO_DEFINE_FILTERS) {
			$this->baseFilters = $this->filters;
			$this->filters = [];
		}

		$classInstance = null !== $this->postTypeClass ? new $this->postTypeClass() : null;

		if (ListingBlock::USE_FIELDS_TO_DEFINE_FILTERS) {
			if ($this->baseFilters) {
				foreach ($this->baseFilters as $filter) {
					$type = $filter[ListingBlock::FIELD_FILTERS_TYPE];
					$appearance = $filter[ListingBlock::FIELD_FILTERS_APPEARANCE];
					$name = sanitize_title($filter[ListingBlock::FIELD_FILTERS_NAME]);
					$placeholder = $filter[ListingBlock::FIELD_FILTERS_PLACEHOLDER];
					$fieldClass = null;

					$value = match ($type) {
						FilterTypesEnum::META->value => $filter[ListingBlock::FIELD_FILTERS_FIELD],
						FilterTypesEnum::TAXONOMY->value => $filter[ListingBlock::FIELD_FILTERS_TAXONOMY],
						default => null,
					};

					if ($type === FilterTypesEnum::META->value) {
						preg_match('/([a-zA-Z]+)_(.+)/', $value, $matches);

						if (isset($matches[1], $matches[2])) {
							//dd($matches);
							$value = $matches[2];
							$fieldType = $matches[1];

							switch ($fieldType) {
								case 'number':
									$fieldClass = Number::class;
									break;
								case 'text':
									$fieldClass = Text::class;
									break;
								case 'image':
									$fieldClass = Image::class;
									break;
								case 'wysiwyg':
									$fieldClass = WYSIWYGEditor::class;
									break;
								default:
									throw new \Exception(sprintf('Field type "%s" not handled', $fieldType));
							}
						}
					}

					if (empty($appearance)) {
						$appearance = ListingBlock::VALUE_FILTER_APPEARANCE_SELECT;
					}

					switch ($type) {
						case FilterTypesEnum::META->value:
							$type = FilterTypesEnum::META;
							break;
						case FilterTypesEnum::TAXONOMY->value:
							$type = FilterTypesEnum::TAXONOMY;
							break;
					}

					switch ($type) {
						case FilterTypesEnum::META:
							$this->initMetaFilter(metaKey: $value, filterName: $name, filterType: $type, appearance: $appearance, postType: $this->postTypeClass ?? $this->postType, fieldClass: $fieldClass, placeholder: $placeholder);
							break;
						case FilterTypesEnum::TAXONOMY:
							$this->initTaxonomyFilter(taxonomyName: $value, filterName: $name, filterType: $type, appearance: $appearance, placeholder: $placeholder);
							break;
						default:
							break;
					}
				}
			}
		} else {
			if (method_exists($classInstance, 'getFilters')) {
				foreach ($classInstance->getFilters() as $filter) {
					if (!isset($filter['type'], $filter['appearance'], $filter['value'])) {
						throw new \Exception("Filter must have a type, appearance and value");
					}

					$type = $filter['type'];
					$appearance = $filter['appearance'];
					$value = $filter['value'];
					$name = $filter['name'] ?? $value;
					$placeholder = $filter['placeholder'] ?? 'Filtre';

					switch ($type) {
						case FilterTypesEnum::TAXONOMY:
							$this->initTaxonomyFilter(taxonomyName: $value, filterName: $name, filterType: $type, appearance: $appearance, placeholder: $placeholder);
							break;
						case FilterTypesEnum::META:
							$fieldClass = $filter['fieldClass'];
							$this->initMetaFilter(metaKey: $value, filterName: $name, filterType: $type, appearance: $appearance, postType: $this->postTypeClass, fieldClass: $fieldClass, placeholder: $placeholder);
							break;
						default:
							break;
					}
				}
			}
		}
	}

	public function handleFilters(): void
	{
		$this->getData();
	}

	public function getData(): void
	{
		$qb = new QueryBuilder();

		$qb->postType($this->postType)
			->page($this->page)
			->perPage($this->perPage)
			->as(BasePostViewModel::class);

		if (is_array($this->filterFields)) {
			foreach ($this->filterFields as $name => $value) {
				if (!empty($value) && isset($this->filters[$name])) {
					switch ($this->filters[$name]['appearance']) {
						case ListingBlock::VALUE_FILTER_APPEARANCE_CHECKBOX:
						case ListingBlock::VALUE_FILTER_APPEARANCE_SELECT:
							switch ($this->filters[$name]['type']) {
								case FilterTypesEnum::TAXONOMY->value:
									$taxonomyName = $this->filters[$name]['value'];

									$taxQuery = new TaxQuery();

									switch ($this->filters[$name]['appearance']) {
										case ListingBlock::VALUE_FILTER_APPEARANCE_SELECT:
											$taxQuery->add($taxonomyName, [$value]);
											break;
										case ListingBlock::VALUE_FILTER_APPEARANCE_CHECKBOX:
											$taxQuery->setRelation('OR');

											foreach ($value as $slug => $enabled) {
												if ($enabled == 'true') {
													$subTaxQuery = new TaxQuery();
													$subTaxQuery->add($taxonomyName, [$slug]);
													$taxQuery->add($subTaxQuery);
												}
											}
											break;
										default:
											break;
									}

									$qb->addTaxQuery($taxQuery);
									break;
								case FilterTypesEnum::META->value:
									$metaName = $this->filters[$name]['value'];

									$metaQuery = new MetaQuery();

									switch ($this->filters[$name]['appearance']) {
										case ListingBlock::VALUE_FILTER_APPEARANCE_SELECT:
											$metaQuery->add($metaName, $value);
											break;
										case ListingBlock::VALUE_FILTER_APPEARANCE_CHECKBOX:
											$metaQuery->setRelation('OR');

											foreach ($value as $slug => $enabled) {
												if ($enabled == 'true') {
													$subMetaQuery = new MetaQuery();
													$subMetaQuery->add($metaName, $slug);
													$metaQuery->add($subMetaQuery);
												}
											}
											break;
										default:
											break;
									}

									$qb->addMetaQuery($metaQuery);
									break;
								default:
									break;
							}
							break;
						default:
							break;
					}
				}
			}
		}

		if ($this->order) {
			[$orderBy, $order] = explode('.', $this->order);

			switch ($orderBy) {
				case 'date':
					$qb->orderBy($order, $orderBy);
					break;
				default:
					// TODO Handle meta fields
					break;
			}
		}

		$this->data = $qb->getPaginatedData(callback: function (BasePostViewModel $post) {
			return $post->toStdClass();
		});

		if ($this->data['current'] > $this->data['pages'] || null === $this->data['pages']) {
			$this->page = 1;
		}
	}

	public function setPage(int $page): void
	{
		$this->page = $page;
		$this->getData();
	}

	public function resetFilters(): void
	{
		$this->page = 1;
		$this->order = self::DEFAULT_ORDER;

		foreach ($this->filterFields as $key => $filterField) {
			$this->filterFields[$key] = null;
		}

		$this->dispatch('filters-reset');

		$this->getData();
	}

	public function render()
	{
		return view('livewire.listing.listing');
	}
}
