<?php

declare(strict_types=1);

namespace Adeliom\HorizonBlocks\Blocks\Listing;

use Adeliom\HorizonTools\Blocks\AbstractBlock;
use Adeliom\HorizonTools\Enum\FilterTypesEnum;
use Adeliom\HorizonTools\Fields\Select\PostTypeSelectField;
use Adeliom\HorizonTools\Fields\Tabs\ContentTab;
use Adeliom\HorizonTools\Fields\Tabs\LayoutTab;
use Adeliom\HorizonTools\Fields\Tabs\SettingsTab;
use Adeliom\HorizonTools\Fields\Text\HeadingField;
use Adeliom\HorizonTools\Fields\Text\UptitleField;
use Adeliom\HorizonTools\Fields\Text\WysiwygField;
use Adeliom\HorizonTools\Services\ClassService;
use Adeliom\HorizonTools\Services\Compilation\CompilationService;
use Adeliom\HorizonTools\Services\FileService;
use Adeliom\HorizonTools\Services\PostService;
use Adeliom\HorizonTools\Taxonomies\AbstractTaxonomy;
use Extended\ACF\ConditionalLogic;
use Extended\ACF\Fields\ButtonGroup;
use Extended\ACF\Fields\Field;
use Extended\ACF\Fields\Group;
use Extended\ACF\Fields\Message;
use Extended\ACF\Fields\Number;
use Extended\ACF\Fields\RadioButton;
use Extended\ACF\Fields\Repeater;
use Extended\ACF\Fields\Select;
use Extended\ACF\Fields\Taxonomy;
use Extended\ACF\Fields\Text;
use Extended\ACF\Fields\TrueFalse;
use Extended\ACF\Location;
use Illuminate\Support\Facades\Cache;

class ListingBlock extends AbstractBlock
{
	public static ?string $slug = 'listing';
	public static ?string $title = 'Liste d’éléments';
	public static ?string $mode = 'preview';
	public static ?string $icon = 'list-view';

	private array $treatedFields = [];

	public const bool USE_FIELDS_TO_DEFINE_FILTERS = true;
	public const bool ALWAYS_DISPLAY_FILTERS = true;
	public const bool ENABLE_SECONDARY_FILTERS = true;
	public const bool ENABLED_FORCED_FILTERS = true;

	public const string FIELD_PER_PAGE = 'perPage';
	public const string FIELD_DISPLAY_SORT = 'displaySort';
	public const string FIELD_DISPLAY_NUMBER_OF_RESULTS = 'displayNumberOfResults';
	public const string FIELD_ELEMENTS_LABEL_SINGULAR = 'elementsLabelSingular';
	public const string FIELD_ELEMENTS_LABEL_PLURAL = 'elementsLabelPlural';
	public const string FIELD_INNER_CARDS = 'innerCards';
	public const string FIELD_INNER_CARD_CLASS = 'class';
	public const string FIELD_INNER_CARD_POSITION = 'position';
	public const string FIELD_INNER_CARD_PAGES = 'pages';
	public const string VALUE_INNER_CARD_PAGES_FIRST = 'first';
	public const string VALUE_INNER_CARD_PAGES_ALL = 'all';
	public const string VALUE_INNER_CARD_PAGES_ODD = 'odd';
	public const string VALUE_INNER_CARD_PAGES_EVEN = 'even';
	public const string VALUE_INNER_CARD_PAGES_CUSTOM = 'custom';
	public const string FIELD_INNER_CARD_CUSTOM_PAGES = 'customPages';

	public const string FIELD_FILTERS = 'filters';
	public const string FIELD_SECONDARY_FILTERS = 'secondaryFilters';
	public const string FIELD_SECONDARY_FILTERS_BUTTON_LABEL = 'secondaryFiltersButtonLabel';
	public const string FIELD_SECONDARY_FILTERS_TITLE = 'secondaryFiltersTitle';
	public const string FIELD_WITH_SECONDARY_FILTERS = 'withSecondaryFilters';
	public const string FIELD_WITH_FORCED_FILTERS = 'withForcedFilters';
	public const string FIELD_FORCED_FILTERS = 'forcedFilters';

	public const string FIELD_FILTERS_TYPE = 'type';
	public const string FIELD_FILTERS_FIELD = 'field';
	public const string FIELD_FILTERS_TAXONOMY = 'taxonomy';
	public const string FIELD_FILTERS_TAXONOMY_VALUE = 'taxonomyValue';
	public const string FIELD_FILTERS_APPEARANCE = 'appearance';
	public const string FIELD_FILTERS_META_APPEARANCE = self::FIELD_FILTERS_APPEARANCE . 'Meta';
	public const string FIELD_FILTERS_TAX_APPEARANCE = self::FIELD_FILTERS_APPEARANCE . 'Tax';
	public const string FIELD_FILTERS_CHOICE_ALL = 'choiceAll';
	public const string FIELD_FILTERS_META_CHOICE_ALL = self::FIELD_FILTERS_CHOICE_ALL . 'Meta';
	public const string FIELD_FILTERS_TAX_CHOICE_ALL = self::FIELD_FILTERS_CHOICE_ALL . 'Tax';
	public const string FIELD_FILTERS_NAME = 'name';
	public const string FIELD_FILTERS_PLACEHOLDER = 'placeholder';

	public const string VALUE_FILTER_APPEARANCE_SELECT = 'select';
	public const string VALUE_FILTER_APPEARANCE_CHECKBOX = 'checkbox';
	public const string VALUE_FILTER_APPEARANCE_RADIO = 'radio';
	public const string VALUE_FILTER_APPEARANCE_TEXT = 'text';
	public const string VALUE_FILTER_APPEARANCE_MULTISELECT = 'multiselect';
	public const string VALUE_FILTER_APPEARANCE_SINGLESELECT = 'singleselect';

	public function getFields(): ?iterable
	{
		$postTypeField = PostTypeSelectField::make(callback: function ($postType): bool {
			if (property_exists($postType, 'availableInListingBlock')) {
				return (bool)$postType::$availableInListingBlock;
			}

			return false;
		});

		if (self::USE_FIELDS_TO_DEFINE_FILTERS) {
			$postTypeField->helperText('Enregistrez la page après modification de ce champ pour afficher les bonnes valeurs dans les filtres.');
		}

		yield from ContentTab::make()->fields([
			UptitleField::make(),
			HeadingField::make(HeadingField::LABEL, HeadingField::NAME, null, 'h1')->required(),
			WysiwygField::simple(),
			$postTypeField,
		]);

		$layoutFields = [
			Number::make(__('Nombre d’éléments par page'), self::FIELD_PER_PAGE)
				->max(24)
				->min(3)
				->step(3),
			TrueFalse::make(__('Afficher le tri'), self::FIELD_DISPLAY_SORT)
				->default(true)
				->stylized(),
			TrueFalse::make(__('Afficher le nombre de résultats'), self::FIELD_DISPLAY_NUMBER_OF_RESULTS)
				->default(true)
				->stylized(),
			Text::make(__('Nom des éléments au singulier'), self::FIELD_ELEMENTS_LABEL_SINGULAR)
				->placeholder('élément')
				->wrapper(['width' => 50]),
			Text::make(__('Nom des éléments au pluriel'), self::FIELD_ELEMENTS_LABEL_PLURAL)
				->placeholder('éléments')
				->wrapper(['width' => 50])
		];

		if ($listingCardsClasses = ClassService::getAllClassesFromPath(get_template_directory() . '/app/View/Components/Cards/ListingCards')) {
			$listingCardChoices = [];

			foreach ($listingCardsClasses as $listingCardsClass) {
				$cardName = ClassService::getClassNameFromFullName($listingCardsClass);

				// Check if constant NAME exists in the class
				if (defined($listingCardsClass . '::NAME')) {
					$cardName = constant($listingCardsClass . '::NAME');
				}

				$listingCardChoices[$listingCardsClass] = $cardName;
			}

			if (!empty($listingCardChoices)) {
				$layoutFields[] = Repeater::make(__('Cards internes'), self::FIELD_INNER_CARDS)
					->helperText(__('Les cards internes sont des cards qui peuvent être insérées dans le listing, soit sur la première page, soit sur toutes les pages.'))
					->layout('block')->fields([
						Select::make(__('Card à afficher'), self::FIELD_INNER_CARD_CLASS)
							->stylized()
							->nullable()
							->choices($listingCardChoices),
						Number::make(__('Position de la card'), self::FIELD_INNER_CARD_POSITION)
							->required()
							->conditionalLogic([
								ConditionalLogic::where(self::FIELD_INNER_CARD_CLASS, '!=', '')
							]),
						RadioButton::make(__('Pages'), self::FIELD_INNER_CARD_PAGES)
							->choices([
								self::VALUE_INNER_CARD_PAGES_ALL => __('Toutes'),
								self::VALUE_INNER_CARD_PAGES_FIRST => __('Première'),
								self::VALUE_INNER_CARD_PAGES_ODD => __('Impaires'),
								self::VALUE_INNER_CARD_PAGES_EVEN => __('Paires'),
								self::VALUE_INNER_CARD_PAGES_CUSTOM => __('Personnalisées')
							])
							->conditionalLogic([
								ConditionalLogic::where(self::FIELD_INNER_CARD_CLASS, '!=', '')
							]),
						Text::make(__('Pages'), self::FIELD_INNER_CARD_CUSTOM_PAGES)
							->required()
							->helperText(__('Indiquez les numéros de pages séparés par des virgules (ex: 1,2,3)'))
							->conditionalLogic([
								ConditionalLogic::where(self::FIELD_INNER_CARD_CLASS, '!=', '')
									->and(self::FIELD_INNER_CARD_PAGES, '==', self::VALUE_INNER_CARD_PAGES_CUSTOM)
							])
					]);
			}
		}

		yield from LayoutTab::make()->fields($layoutFields);

		if (self::USE_FIELDS_TO_DEFINE_FILTERS) {
			yield from self::filterFields();
		}
	}

	private function getMetaAppearanceChoices(): array
	{
		return [
			self::VALUE_FILTER_APPEARANCE_SELECT => __('Sélection'),
			self::VALUE_FILTER_APPEARANCE_CHECKBOX => __('Cases à cocher'),
			self::VALUE_FILTER_APPEARANCE_RADIO => __('Choix unique'),
			self::VALUE_FILTER_APPEARANCE_TEXT => __('Champ libre'),
			self::VALUE_FILTER_APPEARANCE_MULTISELECT => __('Sélection multiple'),
			self::VALUE_FILTER_APPEARANCE_SINGLESELECT => __('Sélection unique'),
		];
	}

	private function getTaxonomyAppearanceChoices(): array
	{
		return [
			self::VALUE_FILTER_APPEARANCE_SELECT => __('Sélection'),
			self::VALUE_FILTER_APPEARANCE_CHECKBOX => __('Cases à cocher'),
			self::VALUE_FILTER_APPEARANCE_RADIO => __('Choix unique'),
			self::VALUE_FILTER_APPEARANCE_MULTISELECT => __('Sélection multiple'),
			self::VALUE_FILTER_APPEARANCE_SINGLESELECT => __('Sélection unique'),
		];
	}

	private function getFilterRepeaterFields(int $level = 1, array $excludedTypes = [], bool $withFilterName = true, bool $withDefaultText = true, bool $withAppearance = true, bool $withTaxonomyValue = false): array
	{
		$filterFields = [];
		$availableFields = [];
		$availableTaxonomies = $this->getAvailableTaxonomies(level: $level);

		$hasTaxonomy = !in_array(FilterTypesEnum::TAXONOMY, $excludedTypes);
		$hasMeta = !in_array(FilterTypesEnum::META, $excludedTypes);
		$hasSearch = !in_array(FilterTypesEnum::SEARCH, $excludedTypes);

		if ($hasMeta) {
			$availableFields = $this->getAvailableFilterChoices(level: $level);
		}

		$typeChoices = [];

		if ($hasMeta) {
			if (!empty($availableFields) || self::ALWAYS_DISPLAY_FILTERS) {
				$typeChoices[FilterTypesEnum::META->value] = __('Méta');
			}
		}

		if ($hasTaxonomy) {
			if (!empty($availableTaxonomies) || self::ALWAYS_DISPLAY_FILTERS) {
				$typeChoices[FilterTypesEnum::TAXONOMY->value] = __('Taxonomie');
			}
		}

		if ($hasSearch) {
			$typeChoices[FilterTypesEnum::SEARCH->value] = __('Recherche');
		}

		$filterFields[] = ButtonGroup::make(__('Type'), self::FIELD_FILTERS_TYPE)
			->required()
			->choices($typeChoices);

		if ($hasSearch) {
			$filterFields[] = Message::make(__('Recherche'), 'searchinfo')
				->body(__('Actuellement, seule la recherche dans le titre et dans le contenu de l’élément sont prises en charge.'))
				->conditionalLogic([
					ConditionalLogic::where(self::FIELD_FILTERS_TYPE, '==', FilterTypesEnum::SEARCH->value)
				]);
		}

		if ($withFilterName) {
			$filterFields[] = Text::make(__('Nom du filtre'), self::FIELD_FILTERS_NAME)->required();
		}

		if ($withDefaultText) {
			$filterFields[] = Text::make(__('Texte par défaut du filtre'), self::FIELD_FILTERS_PLACEHOLDER)->helperText(__('Si non renseigné, le nom sera utilisé'));
		}

		if (!empty($availableFields) || !empty($availableTaxonomies) || self::ALWAYS_DISPLAY_FILTERS) {
			if ($hasMeta) {
				if ($withAppearance) {
					$filterFields[] = RadioButton::make(__('Apparence du filtre'), self::FIELD_FILTERS_META_APPEARANCE)
						->choices($this->getMetaAppearanceChoices())
						->default(self::VALUE_FILTER_APPEARANCE_SELECT)
						->conditionalLogic([ConditionalLogic::where(self::FIELD_FILTERS_TYPE, '==', FilterTypesEnum::META->value)]);

					$filterFields[] = Text::make(__('Choix "Tous"'), self::FIELD_FILTERS_META_CHOICE_ALL)
						->helperText(__('Laisser vide pour ne pas afficher le choix "Tous"'))
						->conditionalLogic([
							ConditionalLogic::where(self::FIELD_FILTERS_META_APPEARANCE, '==', self::VALUE_FILTER_APPEARANCE_RADIO),
						]);
				}

				$filterFields[] = Select::make(__('Champ'), self::FIELD_FILTERS_FIELD)
					->stylized()
					->helperText(__('Si aucune option n’est sélectionnée, le filtre ne s’affichera pas.'))
					->choices($availableFields)
					->lazyLoad()
					->conditionalLogic([ConditionalLogic::where(self::FIELD_FILTERS_TYPE, '==', FilterTypesEnum::META->value)]);
			}
		}

		if ($hasTaxonomy) {
			if ($withAppearance) {
				$filterFields[] = RadioButton::make(__('Apparence du filtre'), self::FIELD_FILTERS_TAX_APPEARANCE)
					->choices($this->getTaxonomyAppearanceChoices())
					->default(self::VALUE_FILTER_APPEARANCE_SELECT)
					->conditionalLogic([ConditionalLogic::where(self::FIELD_FILTERS_TYPE, '==', FilterTypesEnum::TAXONOMY->value)]);

				$filterFields[] = Text::make(__('Choix "Tous"'), self::FIELD_FILTERS_TAX_CHOICE_ALL)
					->helperText(__('Laisser vide pour ne pas afficher le choix "Tous"'))
					->conditionalLogic([
						ConditionalLogic::where(self::FIELD_FILTERS_TAX_APPEARANCE, '==', self::VALUE_FILTER_APPEARANCE_RADIO),
					]);
			}

			$filterFields[] = Select::make(__('Taxonomie'), self::FIELD_FILTERS_TAXONOMY)
				->stylized()
				->helperText(__('Si aucune option n’est sélectionnée, le filtre ne s’affichera pas.'))
				->choices($availableTaxonomies)
				->lazyLoad()
				->conditionalLogic([ConditionalLogic::where(self::FIELD_FILTERS_TYPE, '==', FilterTypesEnum::TAXONOMY->value)]);

			if ($withTaxonomyValue) {
				foreach ($availableTaxonomies as $taxonomySlug => $taxonomyName) {
					$filterFields[] = Taxonomy::make($taxonomyName, self::FIELD_FILTERS_TAXONOMY_VALUE . ucfirst($taxonomySlug))
						->required()
						->taxonomy($taxonomySlug)
						->appearance('multi_select')
						->load(false)
						->save(false)
						->conditionalLogic([ConditionalLogic::where(self::FIELD_FILTERS_TAXONOMY, '==', $taxonomySlug)]);
				}
			}
		}

		return $filterFields;
	}

	private function filterFields(): iterable
	{
		if (!empty($availableFields) || !empty($availableTaxonomies) || self::ALWAYS_DISPLAY_FILTERS) {
			$fields = [
				Repeater::make(__('Filtres primaires'), self::FIELD_FILTERS)
					->helperText(__('Les filtres primaires sont les filtres affichés par défaut au-dessus du listing'))
					->button(__('Ajouter un filtre'))
					->collapsed(self::FIELD_FILTERS_NAME)
					->layout('block')
					->minRows(0)
					->maxRows(self::ENABLE_SECONDARY_FILTERS ? 3 : 4)
					->fields($this->getFilterRepeaterFields()),
			];

			if (self::ENABLE_SECONDARY_FILTERS) {
				$fields = array_merge($fields, [
					TrueFalse::make(__('Activer les filtres secondaires'), self::FIELD_WITH_SECONDARY_FILTERS)
						->helperText(__('Les filtres secondaires sont des filtres que l’on peut afficher au clic sur un bouton'))
						->stylized(),
					Text::make(__('Label du bouton'), self::FIELD_SECONDARY_FILTERS_BUTTON_LABEL)
						->helperText(__('Texte affiché sur le bouton pour afficher les filtres secondaires'))
						->placeholder('Filtres avancés')
						->conditionalLogic([ConditionalLogic::where(self::FIELD_WITH_SECONDARY_FILTERS, '==', 1)]),
					Text::make(__('Titre des filtres secondaires'), self::FIELD_SECONDARY_FILTERS_TITLE)
						->helperText(__('Titre affiché au-dessus des filtres secondaires'))
						->placeholder('Filtres avancés')
						->conditionalLogic([ConditionalLogic::where(self::FIELD_WITH_SECONDARY_FILTERS, '==', 1)]),
					Repeater::make(__('Filtres secondaires'), self::FIELD_SECONDARY_FILTERS)
						->button(__('Ajouter un filtre secondaire'))
						->layout('block')
						->fields($this->getFilterRepeaterFields(level: 2))
						->conditionalLogic([ConditionalLogic::where(self::FIELD_WITH_SECONDARY_FILTERS, '==', 1)]),
				]);
			}

			if (self::ENABLED_FORCED_FILTERS) {
				$fields = array_merge($fields, [
					TrueFalse::make(__('Activer les filtres forcés'), self::FIELD_WITH_FORCED_FILTERS)
						->helperText(__('Les filtres forcés sont des filtres qui seront toujours appliqués et qu’il n’est pas possible de désélectionner'))
						->stylized(),
					Repeater::make(__('Filtres forcés'), self::FIELD_FORCED_FILTERS)
						->button(__('Ajouter un filtre forcé'))
						->layout('block')
						->fields($this->getFilterRepeaterFields(excludedTypes: [FilterTypesEnum::META, FilterTypesEnum::SEARCH], withFilterName: false, withDefaultText: false, withAppearance: false, withTaxonomyValue: true))
						->conditionalLogic([ConditionalLogic::where(self::FIELD_WITH_FORCED_FILTERS, '==', 1)])
				]);
			}

			yield from SettingsTab::make()->fields($fields);
		}
	}

	public function addToContext(): array
	{
		return [];
	}

	public function renderBlockCallback(): void
	{
		switch (true) {
			case CompilationService::shouldUseBud():
				CompilationService::getAsset('listing.js')?->enqueue();
				break;
			default:
				CompilationService::getAsset('resources/scripts/blocks/listing.ts')?->enqueue();
				break;
		}
	}

	private function getAvailableFilterChoices(int $level = 1): array
	{
		$postType = $this->getFilteredPostType();
		$metaTaxonomyKeys = [];

		$postTypeTaxonomies = null !== $postType ? Cache::remember(sprintf('post-type-taxonomies-%s', $postType), 3600, function () use ($postType) {
			return PostService::getAllAssociatedTaxonomies(postType: $postType);
		}) : [];

		if (!empty($postTypeTaxonomies)) {
			$metaTaxonomyKeys = array_map(function ($taxonomySlug) {
				return sprintf('taxonomy_%s', $taxonomySlug);
			}, array_keys($postTypeTaxonomies));
		}

		$fieldChoices = [];

		if ($postType) {
			$fields = $this->getPostTypeFields(postTypeSlug: $postType, level: $level);

			foreach ($fields as $field) {
				$fieldKey = sprintf('%s_%s', $field['type'], $field['name']);

				if (!in_array($fieldKey, $metaTaxonomyKeys)) {
					$fieldChoices[$fieldKey] = $field['label'];
				}
			}
		}

		return $fieldChoices;
	}

	private function getAvailableTaxonomies(int $level = 1): array
	{
		$postType = $this->getFilteredPostType();
		$taxonomyChoices = [];

		switch ($postType) {
			case 'post':
				$taxonomyChoices['category'] = __('Catégories');
				break;
			case 'page':
			default:
				break;
		}

		if ($postType) {
			if ($taxonomies = FileService::getCustomTaxonomyFiles()) {
				foreach ($taxonomies as $taxonomy) {
					require_once $taxonomy;
				}

				if ($availableTaxonomies = ClassService::getAllCustomTaxonomyClasses()) {
					foreach ($availableTaxonomies as $availableTaxonomy) {
						$availableTaxonomy = new $availableTaxonomy();

						if ($availableTaxonomy instanceof AbstractTaxonomy) {
							if (in_array($postType, $availableTaxonomy->getPostTypes())) {
								$name = get_class($availableTaxonomy);

								if (isset($availableTaxonomy->getConfig()['args']['label'])) {
									$name = $availableTaxonomy->getConfig()['args']['label'];
								}

								if (property_exists($availableTaxonomy, 'slug')) {
									$taxonomyChoices[$availableTaxonomy::$slug] = $name;
								}
							}
						}
					}
				}
			}
		}

		return $taxonomyChoices;
	}

	private function getPostTypeFields(string $postTypeSlug, int $level = 1): array
	{
		$fields = [];

		if (in_array($postTypeSlug, ['post', 'page'])) {
			foreach (FileService::getCustomAdminFiles() as $customAdminFile) {
				require_once $customAdminFile;
			}
			foreach (ClassService::getAllCustomAdminClasses() as $adminClass) {
				if (method_exists($adminClass, 'getLocation') && method_exists($adminClass, 'getFields')) {
					$class = new $adminClass();
					foreach (iterator_to_array($class->getLocation(), false) as $item) {
						if ($item instanceof Location) {
							// Use reflection to get protected "rules" property
							$reflection = new \ReflectionClass($item);

							$property = $reflection->getProperty('rules');
							$rules = $property->getValue($item);

							foreach ($rules as $rule) {
								if (isset($rule['param'], $rule['operator'], $rule['value'])) {
									if ($rule['param'] === 'post_type') {
										if (($rule['operator'] === '==' && $rule['value'] === $postTypeSlug) || $rule['operator'] === '!=' && $rule['value'] !== $postTypeSlug) {
											$this->handleFieldClass(classInstance: $class, fields: $fields, level: $level);
										}
									}
								}
							}
						}
					}
				}
			}
		} else {
			$class = ClassService::getPostTypeClassBySlug(slug: $postTypeSlug);
			$classInstance = new $class();

			$this->handleFieldClass(classInstance: $classInstance, fields: $fields, level: $level);
		}

		return $fields;
	}

	private function handleFieldClass($classInstance, array &$fields, int $level = 1): void
	{
		if (method_exists($classInstance, 'getFields')) {
			if ($classFields = iterator_to_array($classInstance->getFields(), preserve_keys: false)) {
				$this->handleFields(fields: $classFields, array: $fields, level: $level);
			}
		}
	}

	/**
	 * @param Field[] $fields
	 */
	private function handleFields(array $fields, array &$array = [], int $level = 1): void
	{
		if ($level === 1) {
			foreach ($fields as $field) {
				$key = null;

				$fieldData = $field->get();

				if ($field instanceof Group) {
					if (isset($fieldData['sub_fields']) && is_array($fieldData['sub_fields'])) {
						if (isset($fieldData['name'])) {
							$key = sprintf('%s_', $fieldData['name']);
						}


						foreach ($fieldData['sub_fields'] as $subField) {
							$toAdd = [];

							if (isset($subField['type'])) {
								$toAdd['type'] = $subField['type'];
							}

							if (isset($subField['name'])) {
								$toAdd['name'] = sprintf('%s%s', $key, $subField['name']);
							}

							if (isset($subField['key'])) {
								$toAdd['key'] = $subField['key'];
							}

							if (isset($subField['label'])) {
								$toAdd['label'] = $subField['label'];
							}

							$this->treatedFields[$toAdd['name']] = $toAdd;

							$array[] = $toAdd;
						}
					}
				} else {
					if ($level === 1) {
						$fieldData = $field->get();
					}

					$toAdd = [];
					if (isset($fieldData['type'])) {
						$toAdd['type'] = $fieldData['type'];
					}

					if (isset($fieldData['name'])) {
						$toAdd['name'] = $fieldData['name'];
					}

					if (isset($fieldData['key'])) {
						$toAdd['key'] = $fieldData['key'];
					}

					if (isset($fieldData['label'])) {
						$toAdd['label'] = $fieldData['label'];
					}

					$this->treatedFields[$toAdd['name']] = $toAdd;

					$array[] = $toAdd;
				}
			}
		} else {
			$array = array_values($this->treatedFields);
		}
	}

	private function getFilteredPostType(): ?string
	{
		$selectedPostType = null;

		$postId = $_GET['post'] ?? $_POST['post_id'] ?? null;

		if (null === $postId) {
			$rawBody = file_get_contents('php://input');
			$data = json_decode($rawBody, true); // true pour avoir un tableau associatif

			if (json_last_error() === JSON_ERROR_NONE) {
				if (is_array($data) && !empty($data['id']) && is_numeric($data['id'])) {
					$postId = $data['id'];
				}
			}
		}

		if (null === $postId) {
			$postId = get_the_ID();
		}

		if ($postId) {
			$post = get_post($postId);

			if ($post && $post->post_content) {
				preg_match('/<!-- wp:acf\/listing.*?"postType":"([^"]+)".*?-->/', $post->post_content, $matches);

				if (isset($matches[1])) {
					$selectedPostType = $matches[1];
				}
			}
		}

		return $selectedPostType;
	}
}
