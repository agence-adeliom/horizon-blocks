<?php

declare(strict_types=1);

namespace Adeliom\HorizonBlocks\Livewire\Listing;

use Adeliom\HorizonBlocks\ViewModels\ListingInnerCardViewModel;
use Adeliom\HorizonTools\Database\MetaQuery;
use Adeliom\HorizonTools\Database\QueryBuilder;
use Adeliom\HorizonTools\Database\TaxQuery;
use Adeliom\HorizonTools\Enum\FilterTypesEnum;
use Adeliom\HorizonTools\Services\AcfService;
use Adeliom\HorizonTools\Services\ArrayService;
use Adeliom\HorizonTools\Services\ClassService;
use Adeliom\HorizonTools\ViewModels\Post\BasePostViewModel;
use Adeliom\HorizonBlocks\Blocks\Listing\ListingBlock;
use Extended\ACF\Fields\Image;
use Extended\ACF\Fields\Number;
use Extended\ACF\Fields\Select;
use Extended\ACF\Fields\Taxonomy;
use Extended\ACF\Fields\Text;
use Extended\ACF\Fields\WYSIWYGEditor;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use Livewire\Attributes\Url;
use Livewire\Component;

class Listing extends Component
{
	private const DEFAULT_ORDER = 'date.DESC';
	private const DEFAULT_PER_PAGE = 12;

	public bool $useCache = false;

	public ?string $postType = null;

	public array $data = [];

	#[Url(as: 'pagination')]
	public int $page = 1;
	#[Url(as: 'filtres')]
	public array $filterFields = [];
	#[Url(as: 'filtres-secondaires')]
	public array $secondaryFilterFields = [];
	#[Url(as: 'tri')]
	public string $order = self::DEFAULT_ORDER;
	public int $perPage = self::DEFAULT_PER_PAGE;
	public ?array $innerCards = null;
	/**
	 * @var ListingInnerCardViewModel[]
	 */
	private array $displayedInnerCards = [];

	public null|false|array $filters = [];
	public null|false|array $secondaryFilters = [];
	public null|false|array $forcedFilters = [];
	public ?string $secondaryFiltersButtonLabel = null;
	public ?string $secondaryFiltersTitle = null;
	private null|false|array $baseFilters = [];
	private null|false|array $baseSecondaryFilters = [];
	public ?string $postTypeClass = null;
	public ?string $card = null;

	public bool $displaySort = true;
	public bool $displayNumberOfResults = true;
	public string $labelSingular = 'élément';
	public string $labelPlural = 'éléments';

	private const QUICK_ACCESS_TAXONOMY_PREFIX = 'qat-';

	public const DISPLAY_VALUES_IN_MULTISELECT_LABEL = false;
	public const MULTISELECT_LABEL_MAXLENGTH = 30;

	public const KEY_NAME = 'name';
	public const KEY_SLUG = 'slug';

	public array $sortOptions = [
		'date.DESC' => 'Plus récent',
		'date.ASC' => 'Plus ancien',
	];

	private const MANUAL_POST_TYPES = ['post', 'page'];

	public function mount(): void
	{
		if ($this->perPage !== -1 && $this->perPage <= 0) {
			$this->perPage = self::DEFAULT_PER_PAGE;
		}

		if (in_array($this->postType, self::MANUAL_POST_TYPES)) {
			$card = Config::get(sprintf('posts.listing.cards.%s', $this->postType));

			if ($card) {
				$this->card = $card;
			} else {
				throw new \Exception(
					sprintf(
						'You have to set a card for the post-type "%s" in the "posts.php" config file (posts.listing.cards.%s)',
						$this->postType,
						$this->postType
					)
				);
			}
		} elseif (null !== $this->postType) {
			$this->postTypeClass = ClassService::getPostTypeClassBySlug($this->postType);
			$this->card = $this->postTypeClass::$card;

			if (null === $this->card) {
				throw new \Exception(
					sprintf(
						'You have to set a card for the post-type in the class "%s". It should be a static var $card',
						$this->postTypeClass
					)
				);
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

	public function updated(): void
	{
		$this->resetAllQAT();
	}

	private function initSearchFilter(string $searchName, string $label, string $placeholder, int $level = 1): void
	{
		$workingFilters = match ($level) {
			1 => $this->filters,
			2 => $this->secondaryFilters,
		};

		$workingFilters[$searchName] = [
			'name' => $searchName,
			'label' => $label,
			'placeholder' => $placeholder,
			'isSearch' => true,
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

	private function initTaxonomyFilter(
		string $taxonomyName,
		string $filterName,
		FilterTypesEnum $filterType,
		string $appearance,
		string $label,
		string $placeholder,
		int $level = 1
	): void
	{
		$workingFilters = match ($level) {
			1 => $this->filters,
			2 => $this->secondaryFilters,
		};

		$taxQb = new QueryBuilder();
		$taxQb->taxonomy($taxonomyName)->fetchEmptyTaxonomies(false);

		if ($this->useCache) {
			$taxQb->useCache();
		}

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
					self::KEY_SLUG => $term->slug,
					self::KEY_NAME => $term->name,
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

	private function initMetaFilter(
		string $metaKey,
		string $filterName,
		FilterTypesEnum $filterType,
		string $appearance,
		string $postType,
		?string $fieldClass,
		string $label,
		string $placeholder,
		int    $level = 1
	): void
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
				case Taxonomy::class:
					global $wpdb;

					$workingFilters[$filterName] = [
						'type' => $filterType->value,
						'metaType' => $fieldClass,
						'name' => $filterName,
						'appearance' => $appearance,
						'value' => $metaKey,
						'label' => $label,
						'placeholder' => $placeholder,
						'choices' => [],
					];

					$ids = implode(',', $values);

					$query = <<<EOF
SELECT {$wpdb->terms}.name, {$wpdb->terms}.term_id AS 'id'
FROM {$wpdb->terms}
WHERE {$wpdb->terms}.term_id IN ({$ids})
EOF;

					$results = $wpdb->get_results($query);

					$nameById = [];

					if (is_array($results)) {
						foreach ($results as $result) {
							if (property_exists($result, 'name') && property_exists($result, 'id')) {
								$nameById[$result->id] = $result->name;
							}
						}
					}

					foreach ($values as $value) {
						$workingFilters[$filterName]['choices'][] = [
							self::KEY_SLUG => $value,
							self::KEY_NAME => $nameById[$value] ?? $value,
						];
					}
					break;
				case Select::class:
					$postTypeInstance = new $postType();
					if ($choices = AcfService::getChoices($postTypeInstance->getFields(), $metaKey)) {
						if (!isset($workingFilters[$filterName])) {
							$workingFilters[$filterName] = [
								'type' => $filterType->value,
								'metaType' => $fieldClass,
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
								self::KEY_SLUG => $choiceValue,
								self::KEY_NAME => $choiceLabel,
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
									'metaType' => $fieldClass,
									'name' => $filterName,
									'appearance' => $appearance,
									'value' => $metaKey,
									'label' => $label,
									'placeholder' => $placeholder,
									'choices' => [],
								];
							}

							$workingFilters[$filterName]['choices'][] = [
								self::KEY_SLUG => sanitize_title($value),
								self::KEY_NAME => $value,
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

	private function getFilterFieldName(array $filter): string
	{
		return sanitize_title($filter[ListingBlock::FIELD_FILTERS_NAME]);
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
				$name = $this->getFilterFieldName($filter);
				$placeholder = !empty($filter[ListingBlock::FIELD_FILTERS_PLACEHOLDER])
					? $filter[ListingBlock::FIELD_FILTERS_PLACEHOLDER]
					: $label;
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

						$fieldClass = match ($fieldType) {
							'number' => Number::class,
							'text' => Text::class,
							'image' => Image::class,
							'wysiwyg' => WYSIWYGEditor::class,
							'taxonomy' => Taxonomy::class,
							default => throw new \Exception(sprintf('Field type "%s" not handled', $fieldType)),
						};
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
						$this->initMetaFilter(
							metaKey: $value,
							filterName: $name,
							filterType: $type,
							appearance: $appearance,
							postType: $this->postTypeClass ?? $this->postType,
							fieldClass: $fieldClass,
							label: $label,
							placeholder: $placeholder,
							level: $level
						);
						break;
					case FilterTypesEnum::TAXONOMY:
						$this->initTaxonomyFilter(
							taxonomyName: $value,
							filterName: $name,
							filterType: $type,
							appearance: $appearance,
							label: $label,
							placeholder: $placeholder,
							level: $level
						);
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

	private function handleQuickAccessFilterByLevel(mixed $value, string $taxonomyName, int $level = 1): bool
	{
		$hasChangedSomething = false;

		$baseFilters = match ($level) {
			default => $this->baseFilters,
			2 => $this->baseSecondaryFilters,
		};

		foreach ($baseFilters as $baseFilterValue) {
			if (
				isset($baseFilterValue[ListingBlock::FIELD_FILTERS_TYPE]) &&
				$baseFilterValue[ListingBlock::FIELD_FILTERS_TYPE] === ListingBlock::FIELD_FILTERS_TAXONOMY
			) {
				if (
					isset($baseFilterValue[ListingBlock::FIELD_FILTERS_TAXONOMY]) &&
					$baseFilterValue[ListingBlock::FIELD_FILTERS_TAXONOMY] === $taxonomyName
				) {
					$fieldName = $this->getFilterFieldName($baseFilterValue);

					if (isset($baseFilterValue[ListingBlock::FIELD_FILTERS_TAX_APPEARANCE])) {
						switch ($baseFilterValue[ListingBlock::FIELD_FILTERS_TAX_APPEARANCE]) {
							case ListingBlock::VALUE_FILTER_APPEARANCE_RADIO:
							case ListingBlock::VALUE_FILTER_APPEARANCE_SINGLESELECT:
							case ListingBlock::VALUE_FILTER_APPEARANCE_SELECT:
								if (is_string($value)) {
									switch ($level) {
										case 2:
											$this->secondaryFilterFields[$fieldName] = $value;
											break;
										case 1:
										default:
											$this->filterFields[$fieldName] = $value;
											break;
									}
								}
								break;
							case ListingBlock::VALUE_FILTER_APPEARANCE_MULTISELECT:
							case ListingBlock::VALUE_FILTER_APPEARANCE_CHECKBOX:
								if (is_string($value)) {
									$value = [$value];
								}

								if (is_array($value)) {
									$toPush = [];

									foreach ($value as $item) {
										$toPush[$item] = 'true';
									}

									switch ($level) {
										case 2:
											$this->secondaryFilterFields[$fieldName] = array_merge(
												$this->secondaryFilterFields[$fieldName] ?? [],
												$toPush
											);
											break;
										case 1:
										default:
											$this->filterFields[$fieldName] = array_merge($this->filterField[$fieldName] ?? [], $toPush);
											break;
									}

									$hasChangedSomething = true;
								}
								break;
							default:
								break;
						}
					}

					break;
				}
			}
		}

		return $hasChangedSomething;
	}

	/**
	 * @param string|string[] $value
	 */
	public static function generateQuickAccessQueryParam(string $taxonomy, string|array $value): ?string
	{
		$queryString = null;

		switch (true) {
			case is_array($value):
				for ($x = 0; $x < count($value); $x++) {
					if (null === $queryString) {
						$queryString = '';
					} else {
						$queryString .= '&';
					}

					$queryString .= self::QUICK_ACCESS_TAXONOMY_PREFIX . $taxonomy . '[' . $x . ']=' . $value[$x];
				}
				break;
			default:
				$queryString = self::QUICK_ACCESS_TAXONOMY_PREFIX . $taxonomy . '=' . $value;
				break;
		}

		return $queryString;
	}

	private function initQuickAccessFilters(): void
	{
		foreach (Request::all() as $key => $value) {
			// QAT stands for Quick Access Taxonomy
			if (str_starts_with($key, self::QUICK_ACCESS_TAXONOMY_PREFIX)) {
				$taxonomyName = str_replace(self::QUICK_ACCESS_TAXONOMY_PREFIX, '', $key);

				if (!$this->handleQuickAccessFilterByLevel(value: $value, taxonomyName: $taxonomyName, level: 1)) {
					$this->handleQuickAccessFilterByLevel(value: $value, taxonomyName: $taxonomyName, level: 2);
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

		$this->initQuickAccessFilters();

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
						throw new \Exception('Filter must have a type, appearance and value');
					}

					$type = $filter['type'];
					$appearance = $filter['appearance'];
					$value = $filter['value'];
					$name = $filter['name'] ?? $value;
					$placeholder = $filter['placeholder'] ?? 'Filtre';

					switch ($type) {
						case FilterTypesEnum::TAXONOMY:
							$this->initTaxonomyFilter(
								taxonomyName: $value,
								filterName: $name,
								filterType: $type,
								appearance: $appearance,
								label: $placeholder
							);
							break;
						case FilterTypesEnum::META:
							$fieldClass = $filter['fieldClass'];
							$this->initMetaFilter(
								metaKey: $value,
								filterName: $name,
								filterType: $type,
								appearance: $appearance,
								postType: $this->postTypeClass,
								fieldClass: $fieldClass,
								label: $name,
								placeholder: $placeholder
							);
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

									$metaSlugs = array_column($workingFilters[$name]['choices'], self::KEY_SLUG);
									$metaNames = array_column($workingFilters[$name]['choices'], self::KEY_NAME);

									// Merge $metaSlugs and $metaNames by using metaSlugs as keys
									$metaSlugs = array_combine($metaSlugs, $metaNames);

									$metaQuery = new MetaQuery();

									if (is_array($value)) {
										foreach ($value as $valueKey => $valueValue) {
											if (isset($metaSlugs[$valueKey])) {
												switch ($workingFilters[$name]['metaType']) {
													case Taxonomy::class:
														break;
													default:
														$value[$metaSlugs[$valueKey]] = $valueValue;

														if ($metaSlugs[$valueKey] != $valueKey) {
															unset($value[$valueKey]);
														}
														break;
												}
											}
										}
									} else {
										if (isset($metaSlugs[$value])) {
											switch ($workingFilters[$name]['metaType']) {
												case Taxonomy::class:
													break;
												default:
													$value = $metaSlugs[$value];
													break;
											}
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

	private function applyForcedFilters(QueryBuilder $qb): void
	{
		$hasAtLeastOne = false;
		$taxQuery = new TaxQuery();

		if (is_array($this->forcedFilters)) {
			foreach ($this->forcedFilters as $forcedFilter) {
				if (is_array($forcedFilter) && !empty($forcedFilter[ListingBlock::FIELD_FILTERS_TYPE]) && !empty($forcedFilter[ListingBlock::FIELD_FILTERS_TAXONOMY]) && !empty($forcedFilter[ListingBlock::FIELD_FILTERS_TAXONOMY_VALUE])) {
					switch ($forcedFilter[ListingBlock::FIELD_FILTERS_TYPE]) {
						case FilterTypesEnum::TAXONOMY->value:
							$taxonomySlug = $forcedFilter[ListingBlock::FIELD_FILTERS_TAXONOMY];
							$termIds = $forcedFilter[ListingBlock::FIELD_FILTERS_TAXONOMY_VALUE];

							if (is_array($termIds)) {
								$hasAtLeastOne = true;

								$subTaxQuery = new TaxQuery();
								$subTaxQuery->add($taxonomySlug, $termIds, 'term_id');

								$taxQuery->add($subTaxQuery);
							}
							break;
						default:
							break;
					}
				}
			}
		}

		if ($hasAtLeastOne) {
			$qb->addTaxQuery($taxQuery);
		}
	}

	private function adaptQueryBuilderToListingCards(QueryBuilder $qb): void
	{
		$cards = [];
		$cardsAlreadyDisplayed = 0;
		$cardsDisplayedOnCurrentPage = 0;

		$displayed = [];
		$this->displayedInnerCards = [];

		if (!empty($this->innerCards)) {
			foreach ($this->innerCards as $innerCard) {
				$card = new ListingInnerCardViewModel();
				$card->setClass($innerCard['class'] ?? null)->setPosition((int)$innerCard['position'] ?? null);

				if (!empty($innerCard[ListingBlock::FIELD_INNER_CARD_PAGES])) {
					switch ($innerCard[ListingBlock::FIELD_INNER_CARD_PAGES]) {
						case ListingBlock::VALUE_INNER_CARD_PAGES_FIRST:
							$card->setPages([1]);
							break;
						case ListingBlock::VALUE_INNER_CARD_PAGES_CUSTOM:
							$card->setPages(array_filter(array_map('intval', explode(',', $innerCard[ListingBlock::FIELD_INNER_CARD_CUSTOM_PAGES]))));
							break;
						case ListingBlock::VALUE_INNER_CARD_PAGES_ODD:
							$card->setPages('odd');
							break;
						case ListingBlock::VALUE_INNER_CARD_PAGES_EVEN:
							$card->setPages('even');
							break;
						case ListingBlock::VALUE_INNER_CARD_PAGES_ALL:
						default:
							$card->setPages('all');
							break;
					}
				}

				$card->setTimesAlreadyDisplayed(currentPage: $qb->getPage());

				$cards[] = $card;
			}
		}

		foreach ($cards as $card) {
			$cardsAlreadyDisplayed += $card->getTimesAlreadyDisplayed();

			$shouldDisplay = false;

			if ((is_array($card->getPages()) && in_array($qb->getPage(), $card->getPages()))) {
				$shouldDisplay = true;
			} elseif (is_string($card->getPages())) {
				switch ($card->getPages()) {
					case ListingBlock::VALUE_INNER_CARD_PAGES_ALL:
						$shouldDisplay = true;
						break;
					case ListingBlock::VALUE_INNER_CARD_PAGES_ODD:
						if ($qb->getPage() % 2 !== 0) {
							$shouldDisplay = true;
						}
						break;
					case ListingBlock::VALUE_INNER_CARD_PAGES_EVEN:
						if ($qb->getPage() % 2 === 0) {
							$shouldDisplay = true;
						}
						break;
					default:
						break;
				}
			}

			if ($shouldDisplay) {
				$cardsDisplayedOnCurrentPage++;
				$displayed[] = $card;
			}
		}

		$oldPage = $qb->getPage();
		$qb->page(1);
		$totalCardsDisplayedPerPage = $this->getTotalCardsDisplayedPerPage(numberOfItems: $qb->getCount(), perPage: $qb->getPerPage(), cards: $cards);
		$qb->page($oldPage);

		$qb->forcedPageNumber(count($totalCardsDisplayedPerPage));

		$originalPerPage = $qb->getPerPage();

		$adjustedPerPage = max(0, $originalPerPage - $cardsDisplayedOnCurrentPage);
		$offset = max(0, ($qb->getPage() - 1) * $originalPerPage - $cardsAlreadyDisplayed);

		$qb->perPage($adjustedPerPage);
		$qb->forcedOffset($offset);

		foreach ($displayed as $item) {
			$this->displayedInnerCards[$item->getPosition() - 1] = $item;
		}
	}

	/**
	 * @param ListingInnerCardViewModel[] $cards
	 * @return array<int, int>
	 */
	private function getTotalCardsDisplayedPerPage(int $numberOfItems, int $perPage, array $cards): array
	{
		$pages = [];

		$this->handlePage(pages: $pages, page: 1, number: $numberOfItems, perPage: $perPage, cards: $cards);

		return $pages;
	}

	/**
	 * @param ListingInnerCardViewModel[] $cards
	 */
	private function handlePage(array &$pages, int $page, int $number, int $perPage, array $cards): void
	{
		if (!isset($pages[$page])) {
			$pages[$page] = 0;
		}

		foreach ($cards as $card) {
			if (is_array($card->getPages()) && in_array($page, $card->getPages())) {
				$pages[$page]++;
			} elseif (is_string($card->getPages())) {
				switch ($card->getPages()) {
					case ListingBlock::VALUE_INNER_CARD_PAGES_ALL:
						$pages[$page]++;
						break;
					case ListingBlock::VALUE_INNER_CARD_PAGES_EVEN:
						if ($page % 2 === 0) {
							$pages[$page]++;
						}
						break;
					case ListingBlock::VALUE_INNER_CARD_PAGES_ODD:
						if ($page % 2 !== 0) {
							$pages[$page]++;
						}
						break;
					default:
						break;
				}
			}
		}

		$cardsOnCurrentPage = $pages[$page] ?? 0;
		$remainingElementsOnPage = $perPage - $cardsOnCurrentPage;
		$remainingElements = $number - $remainingElementsOnPage;

		if ($remainingElements > 0) {
			$this->handlePage(pages: $pages, page: $page + 1, number: $remainingElements, perPage: $perPage, cards: $cards);
		}
	}

	public function getData(): void
	{
		if (null !== $this->postType) {
			$qb = new QueryBuilder();

			if ($this->useCache) {
				$qb->useCache();
			}

			$qb->postType($this->postType)->page($this->page)->perPage($this->perPage)->as(BasePostViewModel::class);

			if (is_array($this->filterFields)) {
				$this->applyFilters(filtersToApply: $this->filterFields, qb: $qb);
			}

			if (is_array($this->secondaryFilterFields)) {
				$this->applyFilters(filtersToApply: $this->secondaryFilterFields, qb: $qb, level: 2);
			}

			if (is_array($this->forcedFilters)) {
				$this->applyForcedFilters(qb: $qb);
			}

			if ($this->order) {
				$orderData = explode('.', $this->order);
				$type = null;

				switch (count($orderData)) {
					case 2:
						$type = 'default';
						$orderBy = $orderData[0];
						$order = $orderData[1];
						break;
					case 3:
						$type = $orderData[0];
						$orderBy = $orderData[1];
						$order = $orderData[2];
						break;
					default:
						break;
				}

				switch ($type) {
					case 'default':
						switch ($orderBy) {
							case 'date':
								$qb->orderBy($order, $orderBy);
								break;
							default:
								// TODO Handle meta fields
								break;
						}
						break;
					case 'taxonomy':
						$qb->orderByTaxonomy($orderBy, $order);
						break;
					default:
						break;
				}
			}

			$this->adaptQueryBuilderToListingCards($qb);

			$this->data = $qb->getPaginatedData(
				callback: function (BasePostViewModel $post) {
					return $post->toStdClass();
				}
			);

			if (!empty($this->displayedInnerCards)) {
				$items = $this->data['items'];

				foreach ($this->displayedInnerCards as $displayedInnerCard) {
					$indexToHave = $displayedInnerCard->getPosition() - 1;

					ArrayService::insertAtIndex($items, $indexToHave, $displayedInnerCard->toStdClass());
				}

				$this->data['items'] = $items;
			}

			if (
				$this->page > 1 &&
				($this->data['current'] > $this->data['pages'] || null === $this->data['pages'] || 0 === $this->data['pages'])
			) {
				$this->page = 1;
				$this->getData();
			}
		}
	}

	public function setPage(int $page): void
	{
		$this->page = $page;
		$this->getData();
	}

	public function resetSecondaryFilters(bool $withEvents = true, bool $fetchData = true): void
	{
		if ($this->page !== 1) {
			$this->page = 1;
		}

		foreach ($this->secondaryFilterFields as $key => $secondaryFilterField) {
			$this->secondaryFilterFields[$key] = null;
		}

		if ($withEvents) {
			$this->dispatch('filters-reset');
			$this->resetAllQAT();
		}

		if ($fetchData) {
			$this->getData();
		}
	}

	public function resetFilters(): void
	{
		if ($this->page !== 1) {
			$this->page = 1;
		}

		if ($this->order !== self::DEFAULT_ORDER) {
			$this->order = self::DEFAULT_ORDER;
		}

		foreach ($this->filterFields as $key => $filterField) {
			unset($this->filterFields[$key]);
		}

		$this->resetSecondaryFilters(withEvents: false, fetchData: false);

		$this->dispatch('filters-reset');
		$this->resetAllQAT();

		$this->getData();
	}

	public function resetAllQAT(): void
	{
		$this->dispatch('qat-reset');
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
