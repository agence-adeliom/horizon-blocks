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
	#[Url(as: "filtres-secondaires")]
	public array $secondaryFilterFields = [];
	#[Url(as: "tri")]
	public string $order = self::DEFAULT_ORDER;
	public int $perPage = 12;

	public null|false|array $filters = [];
	public null|false|array $secondaryFilters = [];
	public ?string $secondaryFiltersButtonLabel = null;
	public ?string $secondaryFiltersTitle = null;
	private null|false|array $baseFilters = [];
	private null|false|array $baseSecondaryFilters = [];
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

	private function initSearchFilter(string $searchName, string $label, string $placeholder, int $level = 1): void
	{
		$workingFilters = match ($level) {
			1 => $this->filters,
			2 => $this->secondaryFilters,
		};

		$workingFilters[$searchName] = [
			'name' => $searchName, 'label' => $label, 'placeholder' => $placeholder, 'isSearch' => true,
		];

		switch ($level) {
			case 1:
				$this->filters = $workingFilters;
				break;
			case 2:
				$this->secondaryFilters = $workingFilters;
				break;
			default:
				break;
		}
	}

	private function initTaxonomyFilter(string $taxonomyName, string $filterName, FilterTypesEnum $filterType, string $appearance, string $label, string $placeholder, int $level = 1): void
	{
		$workingFilters = match ($level) {
			1 => $this->filters,
			2 => $this->secondaryFilters,
		};

		$taxQb = new QueryBuilder();
		$taxQb->taxonomy($taxonomyName)
			->fetchEmptyTaxonomies(false);

		foreach ($taxQb->get() as $term) {
			if ($term instanceof \WP_Term) {
				if (!isset($workingFilters[$filterName])) {
					$workingFilters[$filterName] = [
						'type' => $filterType->value,
						'name' => $filterName,
						'appearance' => $appearance,
						'value' => $taxonomyName,
						'label' => $label,
						'placeholder' => $placeholder,
						'choices' => [],
					];
				}

				$workingFilters[$filterName]['choices'][] = [
					'slug' => $term->slug,
					'name' => $term->name,
				];
			}
		}

		switch ($level) {
			case 1:
				$this->filters = $workingFilters;
				break;
			case 2:
				$this->secondaryFilters = $workingFilters;
				break;
			default:
				break;
		}
	}

	private function initMetaFilter(string $metaKey, string $filterName, FilterTypesEnum $filterType, string $appearance, string $postType, ?string $fieldClass, string $label, string $placeholder, int $level = 1): void
	{
		$workingFilters = match ($level) {
			1 => $this->filters,
			2 => $this->secondaryFilters,
		};

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
						if (!isset($workingFilters[$filterName])) {
							$workingFilters[$filterName] = [
								'type' => $filterType->value,
								'name' => $filterName,
								'appearance' => $appearance,
								'value' => $metaKey,
								'label' => $label,
								'placeholder' => $placeholder,
								'choices' => [],
							];
						}
						foreach ($choices as $choiceValue => $choiceLabel) {
							$workingFilters[$filterName]['choices'][] = [
								'slug' => $choiceValue,
								'name' => $choiceLabel,
							];
						}
					}
					break;
				default:
					foreach ($values as $value) {
						if (!empty($value)) {
							if (!isset($workingFilters[$filterName])) {
								$workingFilters[$filterName] = [
									'type' => $filterType->value,
									'name' => $filterName,
									'appearance' => $appearance,
									'value' => $metaKey,
									'label' => $label,
									'placeholder' => $placeholder,
									'choices' => [],
								];
							}

							$workingFilters[$filterName]['choices'][] = [
								'slug' => sanitize_title($value),
								'name' => $value,
							];
						}
					}
					break;
			}
		}

		switch ($level) {
			case 1:
				$this->filters = $workingFilters;
				break;
			case 2:
				$this->secondaryFilters = $workingFilters;
				break;
			default:
				break;
		}
	}

	private function initFiltersByLevel(?array $filtersData = null, int $level = 1): void
	{
		if (null !== $filtersData) {
			foreach ($filtersData as $filter) {
				$type = $filter[ListingBlock::FIELD_FILTERS_TYPE];
				$appearance = match ($type) {
					FilterTypesEnum::META->value => $filter[ListingBlock::FIELD_FILTERS_META_APPEARANCE],
					FilterTypesEnum::TAXONOMY->value => $filter[ListingBlock::FIELD_FILTERS_TAX_APPEARANCE],
					default => null,
				};

				$label = $filter[ListingBlock::FIELD_FILTERS_NAME];
				$name = sanitize_title($label);
				$placeholder = !empty($filter[ListingBlock::FIELD_FILTERS_PLACEHOLDER]) ? $filter[ListingBlock::FIELD_FILTERS_PLACEHOLDER] : $label;
				$fieldClass = null;

				$value = match ($type) {
					FilterTypesEnum::META->value => $filter[ListingBlock::FIELD_FILTERS_FIELD],
					FilterTypesEnum::TAXONOMY->value => $filter[ListingBlock::FIELD_FILTERS_TAXONOMY],
					default => null,
				};

				if ($type === FilterTypesEnum::META->value) {
					preg_match('/([a-zA-Z]+)_(.+)/', $value, $matches);

					if (isset($matches[1], $matches[2])) {
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
					case FilterTypesEnum::SEARCH->value:
						$type = FilterTypesEnum::SEARCH;
						break;
				}

				switch ($type) {
					case FilterTypesEnum::META:
						$this->initMetaFilter(metaKey: $value, filterName: $name, filterType: $type, appearance: $appearance, postType: $this->postTypeClass ?? $this->postType, fieldClass: $fieldClass, label: $label, placeholder: $placeholder, level: $level);
						break;
					case FilterTypesEnum::TAXONOMY:
						$this->initTaxonomyFilter(taxonomyName: $value, filterName: $name, filterType: $type, appearance: $appearance, label: $label, placeholder: $placeholder, level: $level);
						break;
					case FilterTypesEnum::SEARCH:
						$this->initSearchFilter(searchName: $name, label: $label, placeholder: $placeholder, level: $level);
						break;
					default:
						break;
				}
			}
		}
	}

	private function initFilters(): void
	{
		if (ListingBlock::USE_FIELDS_TO_DEFINE_FILTERS) {
			$this->baseFilters = $this->filters;
			$this->baseSecondaryFilters = $this->secondaryFilters;
			$this->filters = [];
			$this->secondaryFilters = [];
		}

		$classInstance = null !== $this->postTypeClass ? new $this->postTypeClass() : null;

		if (ListingBlock::USE_FIELDS_TO_DEFINE_FILTERS) {
			if ($this->baseFilters) {
				$this->initFiltersByLevel(filtersData: $this->baseFilters);
				$this->initFiltersByLevel(filtersData: $this->baseSecondaryFilters, level: 2);
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
							$this->initTaxonomyFilter(taxonomyName: $value, filterName: $name, filterType: $type, appearance: $appearance, label: $placeholder);
							break;
						case FilterTypesEnum::META:
							$fieldClass = $filter['fieldClass'];
							$this->initMetaFilter(metaKey: $value, filterName: $name, filterType: $type, appearance: $appearance, postType: $this->postTypeClass, fieldClass: $fieldClass, label: $name, placeholder: $placeholder);
							break;
						case FilterTypesEnum::SEARCH:
							$this->initSearchFilter(searchName: $name);
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

	private function applyFilters(array $filtersToApply, QueryBuilder $qb, int $level = 1)
	{
		$workingFilters = match ($level) {
			1 => $this->filters,
			2 => $this->secondaryFilters,
		};

		foreach ($filtersToApply as $name => $value) {
			if (!empty($value) && isset($workingFilters[$name])) {
				if ($workingFilters[$name] && isset($workingFilters[$name]['isSearch']) && $workingFilters[$name]['isSearch']) {
					$qb->search($value);
				} else {
					switch ($workingFilters[$name]['appearance']) {
						case ListingBlock::VALUE_FILTER_APPEARANCE_CHECKBOX:
						case ListingBlock::VALUE_FILTER_APPEARANCE_SELECT:
						case ListingBlock::VALUE_FILTER_APPEARANCE_TEXT:
						case ListingBlock::VALUE_FILTER_APPEARANCE_RADIO:
						case ListingBlock::VALUE_FILTER_APPEARANCE_MULTISELECT:
						case ListingBlock::VALUE_FILTER_APPEARANCE_SINGLESELECT:
							switch ($workingFilters[$name]['type']) {
								case FilterTypesEnum::TAXONOMY->value:
									$taxonomyName = $workingFilters[$name]['value'];

									$taxQuery = new TaxQuery();

									switch ($workingFilters[$name]['appearance']) {
										case ListingBlock::VALUE_FILTER_APPEARANCE_SELECT:
										case ListingBlock::VALUE_FILTER_APPEARANCE_RADIO:
										case ListingBlock::VALUE_FILTER_APPEARANCE_SINGLESELECT:
											$taxQuery->add($taxonomyName, [$value]);
											break;
										case ListingBlock::VALUE_FILTER_APPEARANCE_CHECKBOX:
										case ListingBlock::VALUE_FILTER_APPEARANCE_MULTISELECT:
											$taxQuery->setRelation('OR');

											if (is_array($value)) {
												foreach ($value as $slug => $enabled) {
													if ($enabled == 'true') {
														$subTaxQuery = new TaxQuery();
														$subTaxQuery->add($taxonomyName, [$slug]);
														$taxQuery->add($subTaxQuery);
													}
												}
											}
											break;
										default:
											break;
									}

									$qb->addTaxQuery($taxQuery);
									break;
								case FilterTypesEnum::META->value:
									$metaName = $workingFilters[$name]['value'];

									$metaSlugs = array_column($workingFilters[$name]['choices'], 'slug');
									$metaNames = array_column($workingFilters[$name]['choices'], 'name');

									// Merge $metaSlugs and $metaNames by using metaSlugs as keys
									$metaSlugs = array_combine($metaSlugs, $metaNames);

									$metaQuery = new MetaQuery();

									if (is_array($value)) {
										foreach ($value as $valueKey => $valueValue) {
											if (isset($metaSlugs[$valueKey])) {
												$value[$metaSlugs[$valueKey]] = $valueValue;
												unset($value[$valueKey]);
											}
										}
									} else {
										if (isset($metaSlugs[$value])) {
											$value = $metaSlugs[$value];
										}
									}

									switch ($workingFilters[$name]['appearance']) {
										case ListingBlock::VALUE_FILTER_APPEARANCE_TEXT:
											$metaQuery->add($metaName, $value, 'LIKE');
											break;
										case ListingBlock::VALUE_FILTER_APPEARANCE_SELECT:
										case ListingBlock::VALUE_FILTER_APPEARANCE_RADIO:
										case ListingBlock::VALUE_FILTER_APPEARANCE_SINGLESELECT:
											$metaQuery->add($metaName, $value);
											break;
										case ListingBlock::VALUE_FILTER_APPEARANCE_CHECKBOX:
										case ListingBlock::VALUE_FILTER_APPEARANCE_MULTISELECT:
											$metaQuery->setRelation('OR');

										if (is_array($value)) {
											foreach ($value as $slug => $enabled) {
												if ($enabled == 'true') {
													$subMetaQuery = new MetaQuery();
													$subMetaQuery->add($metaName, $slug);
													$metaQuery->add($subMetaQuery);
												}
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
	}

	public function getData(): void
	{
		$qb = new QueryBuilder();

		$qb->postType($this->postType)
			->page($this->page)
			->perPage($this->perPage)
			->as(BasePostViewModel::class);

		if (is_array($this->filterFields)) {
			$this->applyFilters(filtersToApply: $this->filterFields, qb: $qb);
		}

		if (is_array($this->secondaryFilterFields)) {
			$this->applyFilters(filtersToApply: $this->secondaryFilterFields, qb: $qb, level: 2);
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

		foreach ($this->secondaryFilterFields as $key => $secondaryFilterField) {
			$this->secondaryFilterFields[$key] = null;
		}

		$this->dispatch('filters-reset');

		$this->getData();
	}

	private function getValuesFromModelName(string $modelName): mixed
	{
		$values = null;

		$exploded = explode('.', $modelName, 2);

		if (isset($exploded[0], $exploded[1])) {
			switch ($exploded[0]) {
				case 'filterFields':
					if (isset($this->filterFields[$exploded[1]])) {
						$values = $this->filterFields[$exploded[1]];
					}
					break;
				case 'secondaryFilterFields':
					if (isset($this->secondaryFilterFields[$exploded[1]])) {
						$values = $this->secondaryFilterFields[$exploded[1]];
					}
					break;
				default:
					break;
			}
		}

		return $values;
	}

	public function resetFilter(string $modelName): void
	{
		$this->resetByModelName(modelName: $modelName);
	}

	private function resetByModelName(string $modelName): void
	{
		$explode = explode('.', $modelName, 2);

		if (isset($explode[0], $explode[1])) {
			switch ($explode[0]) {
				case 'filterFields':
					if (isset($this->filterFields[$explode[1]])) {
						unset($this->filterFields[$explode[1]]);
					}
					break;
				case 'secondaryFilterFields':
					if (isset($this->secondaryFilterFields[$explode[1]])) {
						unset($this->secondaryFilterFields[$explode[1]]);
					}
					break;
				default:
					break;
			}
		}

		$this->getData();
	}

	public function howManyOptionsSelected(string $modelName): int
	{
		$totalSelected = 0;
		$values = $this->getValuesFromModelName($modelName);

		if (!empty($values) && is_array($values)) {
			foreach ($values as $state) {
				if ($state == 'true') {
					$totalSelected++;
				}
			}
		}

		return $totalSelected;
	}

	public function render()
	{
		return view('livewire.listing.listing');
	}
}
